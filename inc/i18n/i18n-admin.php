<?php
/**
 * Translation management: Tools > Translations.
 *
 * How the language layer (inc/i18n/i18n.php) is run day to day, with no plugin.
 *
 *   SCAN      Finds every translatable string on every public page by fetching
 *             the page and walking it with the SAME code that translates it, so
 *             the list can never drift from what the site actually renders.
 *   EDIT      Per page and language: translate each string, set the page's
 *             SEO title and description, and publish or unpublish the page.
 *   REVIEW    Saved as a draft (not live) or approved (live). Imports arrive as
 *             drafts so a bad file cannot go straight onto the site.
 *   MOVE      Export a language as CSV or JSON for a translator, import it back.
 *   UPDATE    Edits are stored in the database and laid over the shipped
 *             dictionaries, so a theme update never overwrites them.
 *
 * Storage is three small additive tables. Nothing here touches the posts,
 * postmeta or options of the English site.
 */

defined( 'ABSPATH' ) || exit;

const REMOTIVE_I18N_DB_VERSION = '1';

/* ==========================================================================
 * Tables
 * ======================================================================== */

/** Table name: '' = translations, 'seen' = strings found on pages, 'pages' = per-language page state. */
function remotive_i18n_table( $name ) {
	global $wpdb;

	return $wpdb->prefix . 'rm_i18n' . ( '' === $name ? '' : '_' . $name );
}

/** Have the tables been created? Cached so the check costs one option read. */
function remotive_i18n_db_ready() {
	static $ready = null;

	if ( null === $ready ) {
		$ready = (string) get_option( 'remotive_i18n_db_version' ) === REMOTIVE_I18N_DB_VERSION;
	}

	return $ready;
}

/** Create or update the tables. Safe to run any number of times. */
function remotive_i18n_install_tables() {
	global $wpdb;

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$charset = $wpdb->get_charset_collate();
	$t       = remotive_i18n_table( '' );
	$seen    = remotive_i18n_table( 'seen' );
	$pages   = remotive_i18n_table( 'pages' );

	dbDelta( "CREATE TABLE $t (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  lang varchar(10) NOT NULL,
  kind varchar(8) NOT NULL,
  ref varchar(191) NOT NULL,
  source longtext NOT NULL,
  target longtext NOT NULL,
  status varchar(10) NOT NULL DEFAULT 'approved',
  updated_at datetime NOT NULL,
  updated_by bigint(20) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY  (id),
  UNIQUE KEY entry (lang,kind,ref)
) $charset;" );

	dbDelta( "CREATE TABLE $seen (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  path varchar(191) NOT NULL,
  hash varchar(32) NOT NULL,
  source longtext NOT NULL,
  scan bigint(20) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY  (id),
  UNIQUE KEY page_hash (path,hash),
  KEY hash (hash)
) $charset;" );

	dbDelta( "CREATE TABLE $pages (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  lang varchar(10) NOT NULL,
  path varchar(191) NOT NULL,
  published tinyint(1) NOT NULL DEFAULT 1,
  updated_at datetime NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY page (lang,path)
) $charset;" );

	update_option( 'remotive_i18n_db_version', REMOTIVE_I18N_DB_VERSION, false );
}

/** Create the tables on the first admin load after an update, and from the version sync. */
function remotive_i18n_maybe_install() {
	if ( ! remotive_i18n_db_ready() && is_admin() && current_user_can( 'manage_options' ) ) {
		remotive_i18n_install_tables();
	}
}
add_action( 'admin_init', 'remotive_i18n_maybe_install' );

/* ==========================================================================
 * Scanner
 * ======================================================================== */

/** The stable key for a string: what the dictionaries and the database are keyed by. */
function remotive_i18n_hash( $source ) {
	return md5( remotive_i18n_norm( $source ) );
}

/**
 * Every public page the scan should visit: [ English path ('' = home) => label ].
 * The 404 template has no page of its own, so it is reached through a URL that
 * cannot exist.
 */
function remotive_i18n_scan_targets() {
	$targets = array();
	$posts   = get_posts( array( 'post_type' => array( 'page', 'post' ), 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'menu_order title', 'order' => 'ASC' ) );
	$front   = (int) get_option( 'page_on_front' );
	$base    = remotive_i18n_base();

	foreach ( $posts as $post ) {
		if ( $front && (int) $post->ID === $front ) {
			$targets[''] = $post->post_title;
			continue;
		}

		$path = (string) wp_parse_url( get_permalink( $post ), PHP_URL_PATH );
		$path = '' !== $base && 0 === strpos( $path, $base ) ? substr( $path, strlen( $base ) ) : $path;
		$targets[ trim( $path, '/' ) ] = $post->post_title;
	}

	if ( ! isset( $targets[''] ) ) {
		$targets[''] = get_bloginfo( 'name' );
	}

	$targets['__404'] = '404 page';

	return $targets;
}

/** The URL to fetch for a path. */
function remotive_i18n_scan_url( $path ) {
	if ( '__404' === $path ) {
		return home_url( '/rm-i18n-404-probe/' );
	}

	return home_url( '' === $path ? '/' : '/' . $path . '/' );
}

/**
 * Pull the translatable strings, and the English title and description, out of
 * a rendered page. Walks the body with the translating code against a language
 * that has no dictionary, so every string lands in $missing.
 *
 * @return array{strings:string[],title:string,description:string}
 */
function remotive_i18n_scan_html( $html ) {
	$parts   = explode( '</head>', $html, 2 );
	$head    = $parts[0];
	$body    = isset( $parts[1] ) ? $parts[1] : '';
	$missing = array();

	remotive_i18n_translate_body( $body, '__scan', $missing );

	$title = preg_match( '#<title[^>]*>(.*?)</title>#is', $head, $m ) ? remotive_i18n_norm( $m[1] ) : '';
	$desc  = preg_match( '#<meta\s+[^>]*name=(["\'])description\1[^>]*\scontent=(["\'])(.*?)\2#is', $head, $m ) ? remotive_i18n_norm( $m[3] ) : '';

	return array( 'strings' => array_keys( $missing ), 'title' => $title, 'description' => $desc );
}

/**
 * Fetch one page and record what it contains. Forwards the administrator's own
 * session so a login-protected or private site can still be scanned. Returns an
 * error message, or '' on success.
 */
function remotive_i18n_scan_page( $path, $scan_id ) {
	global $wpdb;

	$cookies = array();

	foreach ( $_COOKIE as $name => $value ) { // phpcs:ignore WordPress.Security.NonceVerification
		if ( preg_match( '/^(wordpress|wp-settings|wp_)/', $name ) ) {
			$cookies[] = new WP_Http_Cookie( array( 'name' => $name, 'value' => $value ) );
		}
	}

	$response = wp_remote_get(
		remotive_i18n_scan_url( $path ),
		array(
			'timeout'     => 25,
			'redirection' => 0,
			'sslverify'   => apply_filters( 'https_local_ssl_verify', false ),
			'cookies'     => $cookies,
			'user-agent'  => 'RemotiveTranslationScan',
		)
	);

	if ( is_wp_error( $response ) ) {
		return $response->get_error_message();
	}

	$code = (int) wp_remote_retrieve_response_code( $response );

	if ( 200 !== $code && ! ( '__404' === $path && 404 === $code ) ) {
		return sprintf( 'HTTP %d', $code );
	}

	$found = remotive_i18n_scan_html( wp_remote_retrieve_body( $response ) );
	$table = remotive_i18n_table( 'seen' );

	$wpdb->query( $wpdb->prepare( "DELETE FROM $table WHERE path = %s", $path ) ); // phpcs:ignore WordPress.DB.PreparedSQL

	$rows = array();

	foreach ( $found['strings'] as $source ) {
		$rows[ remotive_i18n_hash( $source ) ] = $source;
	}

	// The English title and description are kept as reference for the SEO fields.
	$rows['seo_title'] = $found['title'];
	$rows['seo_desc']  = $found['description'];

	foreach ( $rows as $hash => $source ) {
		$wpdb->query( $wpdb->prepare( "INSERT IGNORE INTO $table (path, hash, source, scan) VALUES (%s, %s, %s, %d)", $path, $hash, $source, $scan_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL
	}

	return '';
}

/* ==========================================================================
 * Reading state
 * ======================================================================== */

/** Does this language have a translation for this (normalised) string, by exact entry or by pattern? */
function remotive_i18n_has_translation( $lang, $source ) {
	$data = remotive_i18n_data( $lang );

	if ( isset( $data['strings'][ $source ] ) && '' !== $data['strings'][ $source ] ) {
		return true;
	}

	foreach ( $data['patterns'] as $pattern ) {
		if ( preg_match( $pattern['regex'], $source ) ) {
			return true;
		}
	}

	return false;
}

/** Every page seen by the last scan: path => array( total strings, label ). */
function remotive_i18n_seen_pages() {
	global $wpdb;

	if ( ! remotive_i18n_db_ready() ) {
		return array();
	}

	$table = remotive_i18n_table( 'seen' );
	$rows  = $wpdb->get_results( "SELECT path, COUNT(*) c FROM $table WHERE hash NOT IN ('seo_title','seo_desc') GROUP BY path ORDER BY path", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL
	$out   = array();

	foreach ( (array) $rows as $row ) {
		$out[ $row['path'] ] = (int) $row['c'];
	}

	return $out;
}

/** Strings seen on one page: hash => source. */
function remotive_i18n_page_strings( $path ) {
	global $wpdb;

	$table = remotive_i18n_table( 'seen' );
	$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT hash, source FROM $table WHERE path = %s AND hash NOT IN ('seo_title','seo_desc') ORDER BY id", $path ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL
	$out   = array();

	foreach ( (array) $rows as $row ) {
		$out[ $row['hash'] ] = $row['source'];
	}

	return $out;
}

/** The English title and description recorded for a page. */
function remotive_i18n_page_english_seo( $path ) {
	global $wpdb;

	$table = remotive_i18n_table( 'seen' );
	$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT hash, source FROM $table WHERE path = %s AND hash IN ('seo_title','seo_desc')", $path ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL
	$out   = array( 'title' => '', 'description' => '' );

	foreach ( (array) $rows as $row ) {
		$out['seo_title' === $row['hash'] ? 'title' : 'description'] = $row['source'];
	}

	return $out;
}

/** Coverage for one language on one page: [ translated, total ]. */
function remotive_i18n_page_coverage( $lang, $path ) {
	$strings = remotive_i18n_page_strings( $path );
	$done    = 0;

	foreach ( $strings as $source ) {
		if ( remotive_i18n_has_translation( $lang, remotive_i18n_norm( $source ) ) ) {
			++$done;
		}
	}

	return array( $done, count( $strings ) );
}

/** Where is a stored translation coming from: 'draft', 'approved' (saved in the admin), or 'shipped'. */
function remotive_i18n_db_row( $lang, $kind, $ref ) {
	global $wpdb;

	$table = remotive_i18n_table( '' );

	return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE lang = %s AND kind = %s AND ref = %s", $lang, $kind, $ref ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL
}

/* ==========================================================================
 * Writing
 * ======================================================================== */

/** Plain text only: a translation is a text node, never markup. */
function remotive_i18n_clean( $text ) {
	return trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( (string) wp_unslash( $text ) ) ) );
}

/**
 * Save, or with an empty value remove, one translation.
 * Removing falls back to the shipped dictionary, so a translator can always undo.
 */
function remotive_i18n_save_entry( $lang, $kind, $ref, $source, $target, $status ) {
	global $wpdb;

	$table  = remotive_i18n_table( '' );
	$target = remotive_i18n_clean( $target );

	if ( '' === $target ) {
		$wpdb->delete( $table, array( 'lang' => $lang, 'kind' => $kind, 'ref' => $ref ) );
		return;
	}

	$status = 'draft' === $status ? 'draft' : 'approved';
	$wpdb->query( $wpdb->prepare( "INSERT INTO $table (lang, kind, ref, source, target, status, updated_at, updated_by) VALUES (%s, %s, %s, %s, %s, %s, %s, %d) ON DUPLICATE KEY UPDATE source = VALUES(source), target = VALUES(target), status = VALUES(status), updated_at = VALUES(updated_at), updated_by = VALUES(updated_by)", $lang, $kind, $ref, $source, $target, $status, current_time( 'mysql', true ), get_current_user_id() ) ); // phpcs:ignore WordPress.DB.PreparedSQL
}

/* ==========================================================================
 * Admin screen
 * ======================================================================== */

function remotive_i18n_admin_menu() {
	add_management_page( __( 'Translations', 'remotive' ), __( 'Translations', 'remotive' ), 'manage_options', 'rm-translations', 'remotive_i18n_admin_page' );
}
add_action( 'admin_menu', 'remotive_i18n_admin_menu' );

/** Link for the screen. $args are extra query parameters. */
function remotive_i18n_admin_url( $args = array() ) {
	return add_query_arg( array_merge( array( 'page' => 'rm-translations' ), $args ), admin_url( 'tools.php' ) );
}

/** A path as it travels in a URL: '' becomes 'home'. */
function remotive_i18n_path_param( $path ) {
	return '' === $path ? 'home' : $path;
}

/** ...and back, restricted to what a path can contain. */
function remotive_i18n_param_path( $param ) {
	$param = trim( (string) wp_unslash( $param ), '/' );

	if ( 'home' === $param ) {
		return '';
	}

	return preg_match( '#^[a-z0-9/_-]+$#i', $param ) ? $param : '';
}

/** A language code from a request, or '' if it is not one of ours. */
function remotive_i18n_param_lang( $param ) {
	$langs = remotive_i18n_languages();
	$param = sanitize_key( wp_unslash( $param ) );

	return ( 'en' !== $param && isset( $langs[ $param ] ) ) ? $param : '';
}

function remotive_i18n_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to manage translations.', 'remotive' ) );
	}

	remotive_i18n_maybe_install();

	$view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : 'overview'; // phpcs:ignore WordPress.Security.NonceVerification
	$tabs = array( 'overview' => __( 'Overview', 'remotive' ), 'strings' => __( 'All strings', 'remotive' ), 'transfer' => __( 'Import / export', 'remotive' ), 'maintenance' => __( 'Maintenance', 'remotive' ) );

	echo '<div class="wrap rm-i18n"><h1>' . esc_html__( 'Translations', 'remotive' ) . '</h1>';
	remotive_i18n_admin_styles();
	remotive_i18n_admin_notice();

	echo '<h2 class="nav-tab-wrapper">';

	foreach ( $tabs as $slug => $label ) {
		printf( '<a class="nav-tab%s" href="%s">%s</a>', ( $view === $slug || ( 'edit' === $view && 'overview' === $slug ) ) ? ' nav-tab-active' : '', esc_url( remotive_i18n_admin_url( array( 'view' => $slug ) ) ), esc_html( $label ) );
	}

	echo '</h2>';

	switch ( $view ) {
		case 'edit':
			remotive_i18n_view_edit();
			break;
		case 'strings':
			remotive_i18n_view_strings();
			break;
		case 'transfer':
			remotive_i18n_view_transfer();
			break;
		case 'maintenance':
			remotive_i18n_view_maintenance();
			break;
		default:
			remotive_i18n_view_overview();
	}

	echo '</div>';
}

function remotive_i18n_admin_styles() {
	echo '<style>
.rm-i18n .rm-bar{display:inline-block;width:90px;height:8px;background:#dcdcde;border-radius:4px;vertical-align:middle;overflow:hidden}
.rm-i18n .rm-bar i{display:block;height:100%;background:#2271b1}
.rm-i18n .rm-bar.is-full i{background:#00a32a}
.rm-i18n td.rm-lang-cell{white-space:nowrap}
.rm-i18n textarea.rm-t{width:100%;min-height:54px;font-size:13px}
.rm-i18n td.rm-src{width:42%;vertical-align:top}
.rm-i18n .rm-state{display:inline-block;padding:1px 7px;border-radius:9px;font-size:11px;background:#f0f0f1;margin-left:6px}
.rm-i18n .rm-state.is-draft{background:#fcf9e8;color:#996800}
.rm-i18n .rm-state.is-pattern{background:#f0f6fc;color:#0a4b78}
.rm-i18n .rm-state.is-miss{background:#fcf0f1;color:#8a2424}
.rm-i18n .rm-actions{margin:14px 0}
.rm-i18n .rm-stickybar{position:sticky;bottom:0;background:#fff;border-top:1px solid #c3c4c7;padding:10px 0;z-index:5}
</style>';
}

/** One-time notice carried in a query argument after an action. */
function remotive_i18n_admin_notice() {
	if ( empty( $_GET['rm_msg'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}

	$class = ! empty( $_GET['rm_err'] ) ? 'notice-error' : 'notice-success'; // phpcs:ignore WordPress.Security.NonceVerification
	echo '<div class="notice ' . esc_attr( $class ) . ' is-dismissible"><p>' . esc_html( sanitize_text_field( wp_unslash( $_GET['rm_msg'] ) ) ) . '</p></div>'; // phpcs:ignore WordPress.Security.NonceVerification
}

/* ---- Overview --------------------------------------------------------- */

function remotive_i18n_view_overview() {
	$langs = remotive_i18n_languages();
	$seen  = remotive_i18n_seen_pages();
	$pages = remotive_i18n_scan_targets();

	echo '<div class="rm-actions"><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline">';
	wp_nonce_field( 'rm_i18n_scan' );
	echo '<input type="hidden" name="action" value="rm_i18n_scan" /><button class="button button-primary">' . esc_html( $seen ? __( 'Rescan the site', 'remotive' ) : __( 'Scan the site for strings', 'remotive' ) ) . '</button></form> ';
	echo '<span class="description">' . esc_html__( 'Finds every translatable string on every page. Run it after changing English content.', 'remotive' ) . '</span></div>';

	if ( ! $seen ) {
		echo '<p>' . esc_html__( 'Nothing has been scanned yet. Run the scan to list the pages and their strings.', 'remotive' ) . '</p>';
		return;
	}

	echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Page', 'remotive' ) . '</th><th>' . esc_html__( 'Strings', 'remotive' ) . '</th>';

	foreach ( $langs as $code => $lang ) {
		if ( 'en' !== $code ) {
			echo '<th>' . esc_html( $lang['native'] ) . '</th>';
		}
	}

	echo '</tr></thead><tbody>';

	$totals = array();

	foreach ( $seen as $path => $count ) {
		$label = isset( $pages[ $path ] ) ? $pages[ $path ] : $path;
		$url   = '__404' === $path ? '' : remotive_i18n_scan_url( $path );

		echo '<tr><td><strong>' . esc_html( $label ) . '</strong><br /><code>/' . esc_html( $path ) . '</code></td><td>' . (int) $count . '</td>';

		foreach ( $langs as $code => $lang ) {
			if ( 'en' === $code ) {
				continue;
			}

			list( $done, $total ) = remotive_i18n_page_coverage( $code, $path );
			$pct                  = $total ? (int) floor( 100 * $done / $total ) : 100;
			$published            = '__404' !== $path && remotive_i18n_available( $code, $path );

			echo '<td class="rm-lang-cell"><span class="rm-bar' . ( $pct >= 100 ? ' is-full' : '' ) . '"><i style="width:' . (int) $pct . '%"></i></span> ' . (int) $done . '/' . (int) $total;

			if ( '__404' !== $path ) {
				echo ' <span class="rm-state' . ( $published ? '' : ' is-miss' ) . '">' . esc_html( $published ? __( 'live', 'remotive' ) : __( 'not live', 'remotive' ) ) . '</span>';
			}

			echo '<br /><a href="' . esc_url( remotive_i18n_admin_url( array( 'view' => 'edit', 'lang' => $code, 'p' => remotive_i18n_path_param( $path ) ) ) ) . '">' . esc_html__( 'Edit', 'remotive' ) . '</a>';

			if ( $published ) {
				echo ' &middot; <a href="' . esc_url( remotive_i18n_url( $code, $path ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'View', 'remotive' ) . '</a>';
			}

			echo '</td>';
		}

		echo '</tr>';
	}

	echo '</tbody></table>';
}

/* ---- Edit one page in one language ------------------------------------- */

function remotive_i18n_view_edit() {
	$lang = isset( $_GET['lang'] ) ? remotive_i18n_param_lang( $_GET['lang'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$path = isset( $_GET['p'] ) ? remotive_i18n_param_path( $_GET['p'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

	if ( '' === $lang ) {
		echo '<p>' . esc_html__( 'Choose a page and language from the overview.', 'remotive' ) . '</p>';
		return;
	}

	$langs   = remotive_i18n_languages();
	$strings = remotive_i18n_page_strings( $path );
	$only    = ! empty( $_GET['missing'] ); // phpcs:ignore WordPress.Security.NonceVerification
	$english = remotive_i18n_page_english_seo( $path );
	$data    = remotive_i18n_data( $lang );
	$seo     = isset( $data['seo'][ $path ] ) ? $data['seo'][ $path ] : array( 'title' => '', 'description' => '' );
	$is_page = '__404' !== $path;

	echo '<p><a href="' . esc_url( remotive_i18n_admin_url() ) . '">&larr; ' . esc_html__( 'All pages', 'remotive' ) . '</a></p>';
	echo '<h2>' . esc_html( ( '' === $path ? __( 'Home', 'remotive' ) : '/' . $path . '/' ) . ' — ' . $langs[ $lang ]['native'] ) . '</h2>';

	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	wp_nonce_field( 'rm_i18n_save' );
	echo '<input type="hidden" name="action" value="rm_i18n_save" /><input type="hidden" name="lang" value="' . esc_attr( $lang ) . '" /><input type="hidden" name="p" value="' . esc_attr( remotive_i18n_path_param( $path ) ) . '" />';

	if ( $is_page ) {
		$published = remotive_i18n_available( $lang, $path );

		echo '<h3>' . esc_html__( 'Search listing', 'remotive' ) . '</h3><table class="form-table" role="presentation"><tbody>';
		echo '<tr><th>' . esc_html__( 'Title', 'remotive' ) . '</th><td><input type="text" class="large-text" name="seo_title" value="' . esc_attr( $seo['title'] ) . '" /><p class="description">' . esc_html__( 'English:', 'remotive' ) . ' ' . esc_html( $english['title'] ) . '</p></td></tr>';
		echo '<tr><th>' . esc_html__( 'Description', 'remotive' ) . '</th><td><textarea class="large-text" rows="3" name="seo_desc">' . esc_textarea( $seo['description'] ) . '</textarea><p class="description">' . esc_html__( 'English:', 'remotive' ) . ' ' . esc_html( $english['description'] ) . '</p></td></tr>';
		echo '<tr><th>' . esc_html__( 'Live', 'remotive' ) . '</th><td><label><input type="checkbox" name="published" value="1"' . checked( $published, true, false ) . ' /> ' . esc_html__( 'Show this page in this language (needs a title and description). When off, its address redirects to the English page. Only the home page is live to begin with.', 'remotive' ) . '</label></td></tr>';
		echo '</tbody></table>';
	}

	echo '<h3>' . esc_html__( 'Text on the page', 'remotive' ) . '</h3>';
	echo '<p><a href="' . esc_url( remotive_i18n_admin_url( array( 'view' => 'edit', 'lang' => $lang, 'p' => remotive_i18n_path_param( $path ), 'missing' => $only ? 0 : 1 ) ) ) . '">' . esc_html( $only ? __( 'Show all strings', 'remotive' ) : __( 'Show only untranslated', 'remotive' ) ) . '</a></p>';

	echo '<table class="widefat striped"><thead><tr><th>English</th><th>' . esc_html( $langs[ $lang ]['native'] ) . '</th></tr></thead><tbody>';

	$shown = 0;

	foreach ( $strings as $hash => $source ) {
		$norm = remotive_i18n_norm( $source );
		$row  = remotive_i18n_db_row( $lang, 'string', $hash );
		$have = remotive_i18n_has_translation( $lang, $norm );
		$cur  = isset( $data['strings'][ $norm ] ) ? $data['strings'][ $norm ] : '';

		// A draft is stored but not live, so it is not in $data: show it anyway.
		if ( $row && 'draft' === $row['status'] ) {
			$cur  = $row['target'];
			$have = false;
		}

		$pattern = $have && '' === $cur;

		if ( $only && $have ) {
			continue;
		}

		++$shown;

		echo '<tr><td class="rm-src">' . esc_html( $source ) . '</td><td>';

		if ( $pattern ) {
			$m = array();
			echo '<em>' . esc_html( (string) remotive_i18n_lookup( $norm, $lang, $m ) ) . '</em><span class="rm-state is-pattern">' . esc_html__( 'by pattern', 'remotive' ) . '</span>';
		} else {
			echo '<textarea class="rm-t" name="t[' . esc_attr( $hash ) . ']" lang="' . esc_attr( $langs[ $lang ]['hreflang'] ) . '">' . esc_textarea( $cur ) . '</textarea>';

			if ( $row && 'draft' === $row['status'] ) {
				echo '<span class="rm-state is-draft">' . esc_html__( 'draft', 'remotive' ) . '</span>';
			} elseif ( ! $have ) {
				echo '<span class="rm-state is-miss">' . esc_html__( 'missing', 'remotive' ) . '</span>';
			} elseif ( $row ) {
				echo '<span class="rm-state">' . esc_html__( 'edited', 'remotive' ) . '</span>';
			}
		}

		echo '</td></tr>';
	}

	if ( ! $shown ) {
		echo '<tr><td colspan="2">' . esc_html__( 'Nothing to show.', 'remotive' ) . '</td></tr>';
	}

	echo '</tbody></table><div class="rm-stickybar"><button class="button button-primary" name="status" value="approved">' . esc_html__( 'Save and make live', 'remotive' ) . '</button> <button class="button" name="status" value="draft">' . esc_html__( 'Save as draft', 'remotive' ) . '</button> <span class="description">' . esc_html__( 'Leave a box empty to use the shipped translation.', 'remotive' ) . '</span></div></form>';
}

/* ---- All strings ------------------------------------------------------- */

function remotive_i18n_view_strings() {
	global $wpdb;

	$lang   = isset( $_GET['lang'] ) ? remotive_i18n_param_lang( $_GET['lang'] ) : 'ms'; // phpcs:ignore WordPress.Security.NonceVerification
	$lang   = $lang ? $lang : 'ms';
	$filter = isset( $_GET['filter'] ) ? sanitize_key( wp_unslash( $_GET['filter'] ) ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification
	$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$pg     = isset( $_GET['pg'] ) ? max( 1, (int) $_GET['pg'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification
	$langs  = remotive_i18n_languages();
	$table  = remotive_i18n_table( 'seen' );

	echo '<form method="get" class="rm-actions"><input type="hidden" name="page" value="rm-translations" /><input type="hidden" name="view" value="strings" /><select name="lang">';

	foreach ( $langs as $code => $l ) {
		if ( 'en' !== $code ) {
			echo '<option value="' . esc_attr( $code ) . '"' . selected( $lang, $code, false ) . '>' . esc_html( $l['native'] ) . '</option>';
		}
	}

	echo '</select> <select name="filter">';

	foreach ( array( 'all' => __( 'All', 'remotive' ), 'missing' => __( 'Untranslated', 'remotive' ), 'done' => __( 'Translated', 'remotive' ), 'draft' => __( 'Drafts', 'remotive' ) ) as $k => $label ) {
		echo '<option value="' . esc_attr( $k ) . '"' . selected( $filter, $k, false ) . '>' . esc_html( $label ) . '</option>';
	}

	echo '</select> <input type="search" name="s" value="' . esc_attr( $search ) . '" placeholder="' . esc_attr__( 'Search English or translation', 'remotive' ) . '" /> <button class="button">' . esc_html__( 'Filter', 'remotive' ) . '</button></form>';

	$rows = $wpdb->get_results( "SELECT hash, source, COUNT(*) pages FROM $table WHERE hash NOT IN ('seo_title','seo_desc') GROUP BY hash, source ORDER BY source", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL
	$list = array();
	$data = remotive_i18n_data( $lang );

	foreach ( (array) $rows as $row ) {
		$norm  = remotive_i18n_norm( $row['source'] );
		$have  = remotive_i18n_has_translation( $lang, $norm );
		$entry = remotive_i18n_db_row( $lang, 'string', $row['hash'] );
		$draft = $entry && 'draft' === $entry['status'];
		$text  = $draft ? $entry['target'] : ( isset( $data['strings'][ $norm ] ) ? $data['strings'][ $norm ] : '' );

		if ( 'missing' === $filter && ( $have || $draft ) ) {
			continue;
		}

		if ( 'done' === $filter && ! $have ) {
			continue;
		}

		if ( 'draft' === $filter && ! $draft ) {
			continue;
		}

		if ( '' !== $search && false === stripos( $row['source'] . ' ' . $text, $search ) ) {
			continue;
		}

		$list[] = array( $row, $text, $have, $draft );
	}

	$per   = 40;
	$total = count( $list );
	$list  = array_slice( $list, ( $pg - 1 ) * $per, $per );

	echo '<p>' . esc_html( sprintf( /* translators: %d: number of strings. */ _n( '%d string', '%d strings', $total, 'remotive' ), $total ) ) . '</p>';

	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	wp_nonce_field( 'rm_i18n_save' );
	echo '<input type="hidden" name="action" value="rm_i18n_save" /><input type="hidden" name="lang" value="' . esc_attr( $lang ) . '" /><input type="hidden" name="back" value="strings" />';
	echo '<table class="widefat striped"><thead><tr><th>English</th><th>' . esc_html( $langs[ $lang ]['native'] ) . '</th><th>' . esc_html__( 'Pages', 'remotive' ) . '</th></tr></thead><tbody>';

	foreach ( $list as $item ) {
		list( $row, $text, $have, $draft ) = $item;
		$is_pattern                          = $have && '' === $text;

		echo '<tr><td class="rm-src">' . esc_html( $row['source'] ) . '</td><td>';

		if ( $is_pattern ) {
			echo '<em>' . esc_html__( 'translated by a pattern', 'remotive' ) . '</em>';
		} else {
			echo '<textarea class="rm-t" name="t[' . esc_attr( $row['hash'] ) . ']" lang="' . esc_attr( $langs[ $lang ]['hreflang'] ) . '">' . esc_textarea( $text ) . '</textarea>' . ( $draft ? '<span class="rm-state is-draft">' . esc_html__( 'draft', 'remotive' ) . '</span>' : ( $have ? '' : '<span class="rm-state is-miss">' . esc_html__( 'missing', 'remotive' ) . '</span>' ) );
		}

		echo '</td><td>' . (int) $row['pages'] . '</td></tr>';
	}

	echo '</tbody></table><div class="rm-stickybar"><button class="button button-primary" name="status" value="approved">' . esc_html__( 'Save and make live', 'remotive' ) . '</button> <button class="button" name="status" value="draft">' . esc_html__( 'Save as draft', 'remotive' ) . '</button></form>';

	$pages = (int) ceil( $total / $per );

	if ( $pages > 1 ) {
		echo '<p>';

		for ( $i = 1; $i <= $pages; $i++ ) {
			echo $i === $pg ? '<strong>' . (int) $i . '</strong> ' : '<a href="' . esc_url( remotive_i18n_admin_url( array( 'view' => 'strings', 'lang' => $lang, 'filter' => $filter, 's' => $search, 'pg' => $i ) ) ) . '">' . (int) $i . '</a> ';
		}

		echo '</p>';
	}
}

/* ---- Import / export --------------------------------------------------- */

function remotive_i18n_view_transfer() {
	$langs = remotive_i18n_languages();

	echo '<h2>' . esc_html__( 'Export', 'remotive' ) . '</h2><p>' . esc_html__( 'Download a language to send to a translator. The file lists every string and its translation, plus each page\'s search title and description.', 'remotive' ) . '</p>';

	foreach ( $langs as $code => $l ) {
		if ( 'en' === $code ) {
			continue;
		}

		echo '<p><strong>' . esc_html( $l['native'] ) . '</strong>: ';

		foreach ( array( array( 'csv', 'all', __( 'CSV, everything', 'remotive' ) ), array( 'csv', 'missing', __( 'CSV, untranslated only', 'remotive' ) ), array( 'json', 'all', __( 'JSON, everything', 'remotive' ) ) ) as $opt ) {
			echo '<a class="button" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=rm_i18n_export&lang=' . $code . '&format=' . $opt[0] . '&scope=' . $opt[1] ), 'rm_i18n_export' ) ) . '">' . esc_html( $opt[2] ) . '</a> ';
		}

		echo '</p>';
	}

	echo '<h2>' . esc_html__( 'Import', 'remotive' ) . '</h2><p>' . esc_html__( 'Upload a CSV or JSON file in the same format. Only strings the site currently contains are accepted. They arrive as drafts, so nothing goes live until you approve it.', 'remotive' ) . '</p>';
	echo '<form method="post" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	wp_nonce_field( 'rm_i18n_import' );
	echo '<input type="hidden" name="action" value="rm_i18n_import" /><select name="lang">';

	foreach ( $langs as $code => $l ) {
		if ( 'en' !== $code ) {
			echo '<option value="' . esc_attr( $code ) . '">' . esc_html( $l['native'] ) . '</option>';
		}
	}

	echo '</select> <input type="file" name="file" accept=".csv,.json" required /> <button class="button button-primary">' . esc_html__( 'Import as drafts', 'remotive' ) . '</button></form>';
}

/* ---- Maintenance -------------------------------------------------------- */

function remotive_i18n_view_maintenance() {
	global $wpdb;

	$langs = remotive_i18n_languages();
	$t     = remotive_i18n_table( '' );
	$seen  = remotive_i18n_table( 'seen' );

	echo '<h2>' . esc_html__( 'Drafts', 'remotive' ) . '</h2>';

	foreach ( $langs as $code => $l ) {
		if ( 'en' === $code ) {
			continue;
		}

		$n = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $t WHERE lang = %s AND status = 'draft'", $code ) ); // phpcs:ignore WordPress.DB.PreparedSQL

		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin:6px 0">';
		wp_nonce_field( 'rm_i18n_approve' );
		echo '<input type="hidden" name="action" value="rm_i18n_approve" /><input type="hidden" name="lang" value="' . esc_attr( $code ) . '" /><strong>' . esc_html( $l['native'] ) . '</strong>: ' . (int) $n . ' ' . esc_html__( 'drafts', 'remotive' ) . ' <button class="button"' . ( $n ? '' : ' disabled' ) . '>' . esc_html__( 'Approve all', 'remotive' ) . '</button></form>';
	}

	echo '<h2>' . esc_html__( 'Saved translations the site no longer uses', 'remotive' ) . '</h2><p>' . esc_html__( 'These were saved here for English text that has since changed or been removed. They do no harm, and removing them is optional.', 'remotive' ) . '</p>';

	$unused = $wpdb->get_results( "SELECT t.id, t.lang, t.source FROM $t t WHERE t.kind = 'string' AND t.ref NOT IN (SELECT hash FROM $seen) ORDER BY t.lang, t.source LIMIT 200", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL

	if ( ! $unused ) {
		echo '<p>' . esc_html__( 'None.', 'remotive' ) . '</p>';
		return;
	}

	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	wp_nonce_field( 'rm_i18n_prune' );
	echo '<input type="hidden" name="action" value="rm_i18n_prune" /><table class="widefat striped"><tbody>';

	foreach ( $unused as $row ) {
		echo '<tr><td><input type="checkbox" name="ids[]" value="' . (int) $row['id'] . '" /></td><td>' . esc_html( $row['lang'] ) . '</td><td>' . esc_html( wp_trim_words( $row['source'], 20 ) ) . '</td></tr>';
	}

	echo '</tbody></table><p><button class="button">' . esc_html__( 'Delete selected', 'remotive' ) . '</button></p></form>';
}

/* ==========================================================================
 * Actions
 * ======================================================================== */

/** Redirect back to the screen with a message. */
function remotive_i18n_done( $message, $args = array(), $error = false ) {
	$args['rm_msg'] = rawurlencode( $message );

	if ( $error ) {
		$args['rm_err'] = 1;
	}

	wp_safe_redirect( remotive_i18n_admin_url( $args ) );
	exit;
}

function remotive_i18n_require_admin( $nonce ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to manage translations.', 'remotive' ), '', array( 'response' => 403 ) );
	}

	check_admin_referer( $nonce );
	remotive_i18n_maybe_install();
}

/** Scan, a few pages per request so a slow host never times out; it redirects to itself until done. */
function remotive_i18n_action_scan() {
	global $wpdb;

	remotive_i18n_require_admin( 'rm_i18n_scan' );

	$targets = array_keys( remotive_i18n_scan_targets() );
	$offset  = isset( $_REQUEST['offset'] ) ? max( 0, (int) $_REQUEST['offset'] ) : 0;
	$scan_id = 0 === $offset ? time() : ( isset( $_REQUEST['scan'] ) ? (int) $_REQUEST['scan'] : time() );
	$errors  = isset( $_REQUEST['errs'] ) ? (int) $_REQUEST['errs'] : 0;
	$started = microtime( true );

	while ( $offset < count( $targets ) && ( microtime( true ) - $started ) < 12 ) {
		if ( remotive_i18n_scan_page( $targets[ $offset ], $scan_id ) ) {
			++$errors;
		}

		++$offset;
	}

	if ( $offset < count( $targets ) ) {
		wp_safe_redirect( wp_nonce_url( add_query_arg( array( 'action' => 'rm_i18n_scan', 'offset' => $offset, 'scan' => $scan_id, 'errs' => $errors ), admin_url( 'admin-post.php' ) ), 'rm_i18n_scan' ) );
		exit;
	}

	// Pages that no longer exist drop out of the list.
	$seen = remotive_i18n_table( 'seen' );
	$wpdb->query( $wpdb->prepare( "DELETE FROM $seen WHERE scan <> %d", $scan_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL

	$count = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT hash) FROM $seen WHERE hash NOT IN ('seo_title','seo_desc')" ); // phpcs:ignore WordPress.DB.PreparedSQL

	remotive_i18n_done(
		sprintf( /* translators: 1: pages scanned, 2: strings, 3: pages that failed. */ __( 'Scanned %1$d pages and found %2$d distinct strings. %3$d pages could not be fetched.', 'remotive' ), count( $targets ), $count, $errors ),
		array(),
		$errors > 0
	);
}
add_action( 'admin_post_rm_i18n_scan', 'remotive_i18n_action_scan' );

/** Save translations (and a page's search listing) from the edit and strings screens. */
function remotive_i18n_action_save() {
	global $wpdb;

	remotive_i18n_require_admin( 'rm_i18n_save' );

	$lang   = isset( $_POST['lang'] ) ? remotive_i18n_param_lang( $_POST['lang'] ) : '';
	$status = isset( $_POST['status'] ) && 'draft' === $_POST['status'] ? 'draft' : 'approved';

	if ( '' === $lang ) {
		remotive_i18n_done( __( 'Unknown language.', 'remotive' ), array(), true );
	}

	$seen  = remotive_i18n_table( 'seen' );
	$saved = 0;

	if ( ! empty( $_POST['t'] ) && is_array( $_POST['t'] ) ) {
		foreach ( wp_unslash( $_POST['t'] ) as $hash => $value ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$hash = preg_match( '/^[a-f0-9]{32}$/', (string) $hash ) ? (string) $hash : '';

			if ( '' === $hash ) {
				continue;
			}

			$source = $wpdb->get_var( $wpdb->prepare( "SELECT source FROM $seen WHERE hash = %s LIMIT 1", $hash ) ); // phpcs:ignore WordPress.DB.PreparedSQL

			if ( null === $source ) {
				continue; // Only text the site really contains.
			}

			// Skip what has not changed: the shipped value shown in a box must not be copied into the database.
			$norm    = remotive_i18n_norm( $source );
			$data    = remotive_i18n_data( $lang );
			$shipped = isset( $data['strings'][ $norm ] ) ? $data['strings'][ $norm ] : '';
			$row     = remotive_i18n_db_row( $lang, 'string', $hash );

			if ( ! $row && remotive_i18n_clean( $value ) === $shipped ) {
				continue;
			}

			remotive_i18n_save_entry( $lang, 'string', $hash, $norm, $value, $status );
			++$saved;
		}
	}

	$path = isset( $_POST['p'] ) ? remotive_i18n_param_path( $_POST['p'] ) : null;

	if ( null !== $path && isset( $_POST['seo_title'] ) ) {
		remotive_i18n_save_entry( $lang, 'title', $path, '', $_POST['seo_title'], 'approved' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		remotive_i18n_save_entry( $lang, 'desc', $path, '', isset( $_POST['seo_desc'] ) ? $_POST['seo_desc'] : '', 'approved' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		$wpdb->query( $wpdb->prepare( 'INSERT INTO ' . remotive_i18n_table( 'pages' ) . ' (lang, path, published, updated_at) VALUES (%s, %s, %d, %s) ON DUPLICATE KEY UPDATE published = VALUES(published), updated_at = VALUES(updated_at)', $lang, $path, empty( $_POST['published'] ) ? 0 : 1, current_time( 'mysql', true ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL
	}

	$args = isset( $_POST['back'] ) && 'strings' === $_POST['back'] ? array( 'view' => 'strings', 'lang' => $lang ) : array( 'view' => 'edit', 'lang' => $lang, 'p' => isset( $_POST['p'] ) ? sanitize_text_field( wp_unslash( $_POST['p'] ) ) : 'home' );

	remotive_i18n_done( sprintf( /* translators: %d: number of translations saved. */ _n( 'Saved %d change.', 'Saved %d changes.', $saved, 'remotive' ), $saved ), $args );
}
add_action( 'admin_post_rm_i18n_save', 'remotive_i18n_action_save' );

/** Export a language. */
function remotive_i18n_action_export() {
	global $wpdb;

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to manage translations.', 'remotive' ), '', array( 'response' => 403 ) );
	}

	check_admin_referer( 'rm_i18n_export' );

	$lang   = isset( $_GET['lang'] ) ? remotive_i18n_param_lang( $_GET['lang'] ) : '';
	$format = isset( $_GET['format'] ) && 'json' === $_GET['format'] ? 'json' : 'csv';
	$scope  = isset( $_GET['scope'] ) && 'missing' === $_GET['scope'] ? 'missing' : 'all';

	if ( '' === $lang ) {
		wp_die( esc_html__( 'Unknown language.', 'remotive' ) );
	}

	$seen = remotive_i18n_table( 'seen' );
	$rows = array();
	$data = remotive_i18n_data( $lang );

	foreach ( (array) $wpdb->get_results( "SELECT hash, source, COUNT(*) pages FROM $seen WHERE hash NOT IN ('seo_title','seo_desc') GROUP BY hash, source ORDER BY source", ARRAY_A ) as $row ) { // phpcs:ignore WordPress.DB.PreparedSQL
		$norm = remotive_i18n_norm( $row['source'] );
		$have = remotive_i18n_has_translation( $lang, $norm );

		if ( 'missing' === $scope && $have ) {
			continue;
		}

		$rows[] = array( 'string', $row['hash'], $row['source'], isset( $data['strings'][ $norm ] ) ? $data['strings'][ $norm ] : '' );
	}

	foreach ( array_keys( remotive_i18n_seen_pages() ) as $path ) {
		if ( '__404' === $path ) {
			continue;
		}

		$english = remotive_i18n_page_english_seo( $path );
		$seo     = isset( $data['seo'][ $path ] ) ? $data['seo'][ $path ] : array();

		foreach ( array( 'title' => 'title', 'desc' => 'description' ) as $kind => $field ) {
			if ( 'missing' !== $scope || empty( $seo[ $field ] ) ) {
				$rows[] = array( $kind, $path, $english[ $field ], isset( $seo[ $field ] ) ? $seo[ $field ] : '' );
			}
		}
	}

	$name = 'translations-' . $lang . ( 'missing' === $scope ? '-untranslated' : '' ) . '.' . $format;
	nocache_headers();

	if ( 'json' === $format ) {
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $name . '"' );
		echo wp_json_encode( array_map( function ( $r ) {
			return array( 'kind' => $r[0], 'ref' => $r[1], 'source' => $r[2], 'translation' => $r[3] );
		}, $rows ), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ); // phpcs:ignore WordPress.Security.EscapeOutput
		exit;
	}

	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $name . '"' );
	$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	fwrite( $out, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions -- BOM so spreadsheets read the Chinese correctly.
	fputcsv( $out, array( 'kind', 'ref', 'source', 'translation' ) );

	foreach ( $rows as $r ) {
		fputcsv( $out, $r );
	}

	fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	exit;
}
add_action( 'admin_post_rm_i18n_export', 'remotive_i18n_action_export' );

/** Import a CSV or JSON file as drafts. */
function remotive_i18n_action_import() {
	global $wpdb;

	remotive_i18n_require_admin( 'rm_i18n_import' );

	$lang = isset( $_POST['lang'] ) ? remotive_i18n_param_lang( $_POST['lang'] ) : '';

	if ( '' === $lang || empty( $_FILES['file']['tmp_name'] ) || ! is_uploaded_file( $_FILES['file']['tmp_name'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		remotive_i18n_done( __( 'Choose a language and a file.', 'remotive' ), array( 'view' => 'transfer' ), true );
	}

	if ( (int) $_FILES['file']['size'] > 4 * MB_IN_BYTES ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		remotive_i18n_done( __( 'That file is too large (4 MB limit).', 'remotive' ), array( 'view' => 'transfer' ), true );
	}

	$raw  = (string) file_get_contents( $_FILES['file']['tmp_name'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.WP.AlternativeFunctions
	$raw  = preg_replace( '/^\xEF\xBB\xBF/', '', $raw );
	$rows = array();

	if ( '' !== $raw && ( '[' === $raw[0] || '{' === $raw[0] ) ) {
		foreach ( (array) json_decode( $raw, true ) as $r ) {
			if ( is_array( $r ) ) {
				$rows[] = array( isset( $r['kind'] ) ? $r['kind'] : '', isset( $r['ref'] ) ? $r['ref'] : '', isset( $r['source'] ) ? $r['source'] : '', isset( $r['translation'] ) ? $r['translation'] : '' );
			}
		}
	} else {
		$stream = fopen( 'php://temp', 'w+' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		fwrite( $stream, $raw ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		rewind( $stream );
		fgetcsv( $stream ); // Header.

		while ( false !== ( $r = fgetcsv( $stream ) ) ) {
			if ( count( $r ) >= 4 ) {
				$rows[] = array_slice( $r, 0, 4 );
			}
		}

		fclose( $stream ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}

	$seen_t = remotive_i18n_table( 'seen' );
	$pages  = remotive_i18n_seen_pages();
	$known  = array();

	foreach ( (array) $wpdb->get_col( "SELECT DISTINCT hash FROM $seen_t" ) as $h ) { // phpcs:ignore WordPress.DB.PreparedSQL
		$known[ $h ] = true;
	}

	$imported = $skipped = 0;

	foreach ( $rows as $r ) {
		list( $kind, $ref, $source, $target ) = array_map( 'strval', $r );

		if ( '' === remotive_i18n_clean( $target ) ) {
			continue;
		}

		if ( 'string' === $kind && isset( $known[ $ref ] ) && 'seo_title' !== $ref && 'seo_desc' !== $ref ) {
			remotive_i18n_save_entry( $lang, 'string', $ref, remotive_i18n_norm( $source ), $target, 'draft' );
			++$imported;
		} elseif ( ( 'title' === $kind || 'desc' === $kind ) && isset( $pages[ $ref ] ) ) {
			remotive_i18n_save_entry( $lang, $kind, $ref, '', $target, 'draft' );
			++$imported;
		} else {
			++$skipped;
		}
	}

	remotive_i18n_done( sprintf( /* translators: 1: imported, 2: skipped. */ __( 'Imported %1$d as drafts. Skipped %2$d that the site does not contain.', 'remotive' ), $imported, $skipped ), array( 'view' => 'maintenance' ) );
}
add_action( 'admin_post_rm_i18n_import', 'remotive_i18n_action_import' );

/** Approve every draft in a language. */
function remotive_i18n_action_approve() {
	global $wpdb;

	remotive_i18n_require_admin( 'rm_i18n_approve' );

	$lang = isset( $_POST['lang'] ) ? remotive_i18n_param_lang( $_POST['lang'] ) : '';
	$n    = $lang ? (int) $wpdb->query( $wpdb->prepare( 'UPDATE ' . remotive_i18n_table( '' ) . " SET status = 'approved' WHERE lang = %s AND status = 'draft'", $lang ) ) : 0; // phpcs:ignore WordPress.DB.PreparedSQL

	remotive_i18n_done( sprintf( /* translators: %d: number approved. */ __( 'Approved %d drafts.', 'remotive' ), $n ), array( 'view' => 'maintenance' ) );
}
add_action( 'admin_post_rm_i18n_approve', 'remotive_i18n_action_approve' );

/** Delete saved translations the site no longer uses. */
function remotive_i18n_action_prune() {
	global $wpdb;

	remotive_i18n_require_admin( 'rm_i18n_prune' );

	$ids = isset( $_POST['ids'] ) ? array_filter( array_map( 'intval', (array) $_POST['ids'] ) ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$n   = 0;

	if ( $ids ) {
		$n = (int) $wpdb->query( 'DELETE FROM ' . remotive_i18n_table( '' ) . " WHERE kind = 'string' AND id IN (" . implode( ',', $ids ) . ')' ); // phpcs:ignore WordPress.DB.PreparedSQL -- integers only.
	}

	remotive_i18n_done( sprintf( /* translators: %d: number deleted. */ __( 'Deleted %d.', 'remotive' ), $n ), array( 'view' => 'maintenance' ) );
}
add_action( 'admin_post_rm_i18n_prune', 'remotive_i18n_action_prune' );
