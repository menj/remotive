<?php
/**
 * Re:Motive Media — child theme of Twenty Twenty-Five.
 *
 * theme.json carries the design tokens (palette, type, spacing) and merges
 * automatically with the parent's theme.json. This file handles what
 * theme.json can't: bespoke assets, editor parity, progressive WebMCP support,
 * and — once, on activation — setting the bundled logo as the site's custom
 * logo and site icon if nothing is set yet.
 */

defined( 'ABSPATH' ) || exit;

// Loaded first so it is already registered if any later require fatals.
require get_stylesheet_directory() . '/inc/error-handler.php';

require get_stylesheet_directory() . '/inc/theme-options.php';
require get_stylesheet_directory() . '/inc/site-setup.php';
require get_stylesheet_directory() . '/inc/lead-form-handler.php';
require get_stylesheet_directory() . '/inc/cta-form-handler.php';
require get_stylesheet_directory() . '/inc/about-form-handler.php';
require get_stylesheet_directory() . '/inc/contact-form-handler.php';
require get_stylesheet_directory() . '/inc/schema-markup.php';
require get_stylesheet_directory() . '/inc/classic-menus.php';
require get_stylesheet_directory() . '/inc/accessibility.php';
require get_stylesheet_directory() . '/inc/leads.php';
require get_stylesheet_directory() . '/inc/akismet.php';
require get_stylesheet_directory() . '/inc/avif.php';
require get_stylesheet_directory() . '/inc/branded-login.php';
require get_stylesheet_directory() . '/inc/content-seed.php';
require get_stylesheet_directory() . '/inc/feature-grids.php';
require get_stylesheet_directory() . '/inc/thank-you.php';
require get_stylesheet_directory() . '/inc/stats-band.php';
require get_stylesheet_directory() . '/inc/webmcp.php';
require get_stylesheet_directory() . '/inc/security.php';
require get_stylesheet_directory() . '/inc/maintenance-mode.php';
require get_stylesheet_directory() . '/inc/one-page.php';

/**
 * Gate scroll motion on a body class.
 *
 * Everything in the motion section of remotive.css is scoped to .rm-motion,
 * and the JavaScript reveal fallback checks for the same class before doing
 * any work. With the Theme Options toggle off the class is absent, so no
 * rule matches and no observer is created — the feature costs nothing rather
 * than being switched off after the fact.
 *
 * Front-end only: the block editor renders post content in an iframe where
 * a reveal animation would hide content the author is trying to edit.
 *
 * @param string[] $classes
 * @return string[]
 */
function remotive_motion_body_class( $classes ) {
	if ( is_admin() ) {
		return $classes;
	}

	if ( '1' === (string) remotive_get_theme_option( 'motion_effects' ) ) {
		$classes[] = 'rm-motion';
	}

	return $classes;
}
add_filter( 'body_class', 'remotive_motion_body_class' );

/**
 * Wrap the trailing arrow in these links so it can animate on its own.
 *
 * The templates write the arrow inline — "All services →" — as part of the
 * text node, which means CSS cannot move it independently of the label.
 * This filter splits a trailing → (with or without a preceding space) into
 * <span class="rm-link-arrow" aria-hidden="true">, leaving the label alone.
 *
 * aria-hidden because the arrow is decoration: the link text already says
 * where it goes, and a screen reader announcing "right arrow" after every
 * one of them is noise. Removing it from the accessibility tree does not
 * change the link's accessible name, which comes from the remaining text.
 *
 * Runs on render_block so it applies wherever these classes are used —
 * templates, seeded content or the editor — rather than needing every
 * occurrence hand-edited. Blocks without one of the three classes are
 * returned untouched, so the cost on other blocks is a single strpos.
 *
 * @param string $block_content
 * @param array  $block
 * @return string
 */
function remotive_split_link_arrow( $block_content, $block ) {
	if ( false === strpos( $block_content, 'rm-section-link' )
		&& false === strpos( $block_content, 'rm-row__link' )
		&& false === strpos( $block_content, 'rm-cs-card__link' ) ) {
		return $block_content;
	}

	// Already processed (nested render passes) — do not double-wrap.
	if ( false !== strpos( $block_content, 'rm-link-arrow' ) ) {
		return $block_content;
	}

	// A trailing arrow immediately before a closing tag, optionally
	// preceded by whitespace or a non-breaking space.
	return preg_replace(
		'/(?:\s|&nbsp;)*→(?=\s*<\/)/u',
		'<span class="rm-link-arrow" aria-hidden="true">→</span>',
		$block_content
	);
}
add_filter( 'render_block', 'remotive_split_link_arrow', 20, 2 );

/**
 * Front-end + editor styles.
 */
function remotive_enqueue_assets() {
	$critical_css_path = get_stylesheet_directory() . '/assets/css/critical.css';
	$css_path = get_stylesheet_directory() . '/assets/css/remotive.css';

	// Inline only the header/hero shell. The complete component stylesheet is
	// fetched early below but applied asynchronously, removing its network trip
	// from the render-blocking path without exposing the first viewport to FOUC.
	if ( file_exists( $critical_css_path ) ) {
		$critical_css = file_get_contents( $critical_css_path );
		$critical_css = str_replace(
			'__REMOTIVE_MARK_URL__',
			esc_url_raw( remotive_hero_mark_url() ),
			$critical_css
		);

		wp_register_style( 'remotive-critical', false, array(), filemtime( $critical_css_path ) );
		wp_enqueue_style( 'remotive-critical' );
		wp_add_inline_style( 'remotive-critical', $critical_css );
	}

	wp_enqueue_style(
		'remotive-style',
		get_stylesheet_directory_uri() . '/assets/css/remotive.css',
		array(),
		file_exists( $css_path ) ? filemtime( $css_path ) : '1.0.0'
	);

	// Portrait rules for every roster member with shipped photography;
	// see remotive_team_portrait_css() for why these are generated.
	$portrait_css = function_exists( 'remotive_team_portrait_css' ) ? remotive_team_portrait_css() : '';

	if ( $portrait_css ) {
		wp_add_inline_style( 'remotive-style', $portrait_css );
	}

	// Ticker custom-property overrides from Theme Options.
	$ticker_css = function_exists( 'remotive_ticker_css' ) ? remotive_ticker_css() : '';

	if ( $ticker_css ) {
		wp_add_inline_style( 'remotive-style', $ticker_css );
	}

	$print_css_path = get_stylesheet_directory() . '/assets/css/print.css';

	wp_enqueue_style(
		'remotive-print',
		get_stylesheet_directory_uri() . '/assets/css/print.css',
		array( 'remotive-style' ),
		file_exists( $print_css_path ) ? filemtime( $print_css_path ) : '1.0.0',
		'print'
	);

	// One bundle for the colour-mode toggle, the mobile navigation fallback
	// and the ticker pause. Each part guards on its own markup, so bundling
	// runs nothing extra on a page that lacks those elements, and it saves
	// two requests on every view.
	$js_path = get_stylesheet_directory() . '/assets/js/remotive.js';

	wp_enqueue_script(
		'remotive-front',
		get_stylesheet_directory_uri() . '/assets/js/remotive.js',
		array(),
		file_exists( $js_path ) ? filemtime( $js_path ) : '1.0.0',
		true
	);

	wp_script_add_data( 'remotive-front', 'strategy', 'defer' );

	// The status script only consumes a post-redirect query flag. Loading it on
	// every ordinary visit added a request that could never do useful work.
	$lead_status_keys = array( 'remotive_cta', 'remotive_about', 'remotive_contact' );
	$has_lead_status  = false;
	foreach ( $lead_status_keys as $lead_status_key ) {
		if ( isset( $_GET[ $lead_status_key ] ) ) {
			$has_lead_status = true;
			break;
		}
	}

	if ( $has_lead_status ) {
		$lead_form_js_path = get_stylesheet_directory() . '/assets/js/lead-form-status.js';

		wp_enqueue_script(
			'remotive-lead-form-status',
			get_stylesheet_directory_uri() . '/assets/js/lead-form-status.js',
			array(),
			file_exists( $lead_form_js_path ) ? filemtime( $lead_form_js_path ) : '1.0.0',
			true
		);
		wp_script_add_data( 'remotive-lead-form-status', 'strategy', 'defer' );
	}

}
add_action( 'wp_enqueue_scripts', 'remotive_enqueue_assets', 20 );

/**
 * Apply the complete component stylesheet without blocking first paint.
 * The critical header/hero shell above remains inline, and noscript keeps the
 * full stylesheet available when JavaScript is disabled.
 */
function remotive_async_component_stylesheet( $html, $handle, $href, $media ) {
	if ( 'remotive-style' !== $handle ) {
		return $html;
	}

	$media = $media ? $media : 'all';

	return sprintf(
		"<link rel='preload' href='%1\$s' as='style'>\n<link rel='stylesheet' id='remotive-style-css' href='%1\$s' media='print' onload=\"this.media='%2\$s'\">\n<noscript><link rel='stylesheet' href='%1\$s' media='%2\$s'></noscript>\n",
		esc_url( $href ),
		esc_attr( $media )
	);
}
add_filter( 'style_loader_tag', 'remotive_async_component_stylesheet', 10, 4 );

/**
 * Guarantee the responsive viewport meta tag.
 *
 * Core adds this for block themes through _block_theme_viewport_meta_tag(),
 * but a plugin or a filter can remove that hook, and without the tag a
 * phone lays the page out at roughly 980px and then scales it down. Every
 * max-width media query in the theme then evaluates against 980, so the
 * mobile header keeps its desktop CTA and the navigation block never
 * reaches the width at which its overlay menu engages, which is exactly
 * how the header came apart on a phone. The tag is emitted here only when
 * core's own callback is absent, so it is never duplicated.
 */
function remotive_viewport_meta() {
	if ( has_action( 'wp_head', '_block_theme_viewport_meta_tag' ) ) {
		return;
	}

	echo '<meta name="viewport" content="width=device-width, initial-scale=1">' . "\n";
}
add_action( 'wp_head', 'remotive_viewport_meta', 1 );

/**
 * Whether the AVIF hero mark should be served.
 *
 * The mark is a CSS background, so there is no <picture> element to do the
 * negotiation; the request's own Accept header decides instead. Both files
 * ship, so a browser without AVIF support still gets the PNG, and the
 * inline critical CSS and the preload always agree because they call the
 * same helper within one request.
 *
 * @return bool
 */
function remotive_hero_mark_is_avif() {
	static $avif = null;

	if ( null !== $avif ) {
		return $avif;
	}

	$accept = isset( $_SERVER['HTTP_ACCEPT'] ) ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_ACCEPT'] ) ) ) : '';
	$avif   = ( false !== strpos( $accept, 'image/avif' ) )
		&& is_readable( get_stylesheet_directory() . '/assets/images/remotive-mark.avif' );

	return $avif;
}

/**
 * URL of the hero mark in the best format this request accepts.
 *
 * @return string
 */
function remotive_hero_mark_url() {
	$file = remotive_hero_mark_is_avif() ? 'remotive-mark.avif' : 'remotive-mark.png';

	return get_stylesheet_directory_uri() . '/assets/images/' . $file;
}

/**
 * Make the desktop hero watermark discoverable from the initial document and
 * high priority even though its visual treatment remains a CSS background.
 */
function remotive_preload_hero_mark( $resources ) {
	// Every Saira weight that sets above-the-fold text: 400 body copy,
	// 600 navigation and eyebrows, 700 subheads and buttons, 900 display
	// headings. All four were being discovered only after the font CSS
	// parsed, which put them at the end of the critical chain; preloading
	// hoists them into the initial document so they fetch in parallel
	// with it. The metric overrides in critical.css already make the
	// pre-swap layout non-shifting. (Footer column headings use Space
	// Grotesk instead, as of v1.66.5, but that's a handful of short
	// below-the-fold labels — not worth its own preload entry.)
	foreach ( array( '400', '600', '700', '900' ) as $weight ) {
		$font = '/assets/fonts/saira/saira-latin-' . $weight . '-normal.woff2';

		if ( is_readable( get_stylesheet_directory() . $font ) ) {
			$resources[] = array(
				'href'        => get_stylesheet_directory_uri() . $font,
				'as'          => 'font',
				'type'        => 'font/woff2',
				'crossorigin' => 'anonymous',
			);
		}
	}

	if ( is_front_page() ) {
		$resources[] = array(
			'href'          => remotive_hero_mark_url(),
			'as'            => 'image',
			'type'          => remotive_hero_mark_is_avif() ? 'image/avif' : 'image/png',
			'media'         => '(min-width: 601px)',
			'fetchpriority' => 'high',
		);
	}

	return $resources;
}
add_filter( 'wp_preload_resources', 'remotive_preload_hero_mark' );

/**
 * The hero mark URL is chosen from the Accept header and inlined into the
 * document, so the document itself varies by Accept. Now that the header logo
 * uses a <picture> element with AVIF+PNG sources (see
 * remotive_custom_logo_markup() below), only the hero mark CSS background
 * still negotiates via Accept — and only on the front page where the mark
 * appears. The filter remains so the front-page hero mark doesn't poison
 * other pages in a shared cache.
 */
function remotive_vary_on_accept( $headers ) {
	if ( is_front_page() && ! is_admin() && remotive_hero_mark_is_avif() ) {
		$headers['Vary'] = isset( $headers['Vary'] ) ? $headers['Vary'] . ', Accept' : 'Accept';
	}

	return $headers;
}
add_filter( 'wp_headers', 'remotive_vary_on_accept' );

/**
 * Replace the custom-logo <img> with a <picture> element so AVIF vs PNG is
 * decided by the browser rather than the server's Accept header.
 *
 * The previous approach (remotive_custom_logo_image_attributes()) read
 * HTTP_ACCEPT server-side and pointed the <img> src/srcset at either the AVIF
 * or the PNG files. That varied the HTML document by Accept header, which
 * required Vary: Accept on every cached response — but the filter was only
 * adding it on the front page, leaving every other page (including all those
 * with a header logo) unprotected. A shared cache hitting /services/ without
 * Vary: Accept could return the AVIF-referencing document to a client that
 * can't decode AVIF. Using <picture> moves the negotiation entirely to the
 * browser and removes the server-side Accept dependency from the document,
 * so the HTML is identical regardless of the requesting client.
 *
 * The WordPress logo attachment stays in place for everything else that
 * reads it (og:image, REST API, etc.). Only the visible header rendering
 * changes.
 */
function remotive_custom_logo_markup( $html ) {
	if ( is_admin() ) {
		return $html;
	}

	$base    = get_stylesheet_directory_uri() . '/assets/images/remotive-logo-';
	$dir     = get_stylesheet_directory() . '/assets/images/remotive-logo-';
	$widths  = array( 56, 112, 168 );

	// Build AVIF srcset — used in <source type="image/avif"> if all files exist.
	$avif_set = array();
	$png_set  = array();

	foreach ( $widths as $w ) {
		if ( is_readable( $dir . $w . '.avif' ) ) {
			$avif_set[] = $base . $w . '.avif ' . $w . 'w';
		}
		if ( is_readable( $dir . $w . '.png' ) ) {
			$png_set[] = $base . $w . '.png ' . $w . 'w';
		}
	}

	if ( ! $png_set ) {
		// No theme-supplied logos at all — leave WordPress's output untouched.
		return $html;
	}

	// Extract the home URL and existing alt from whatever WordPress produced
	// so we don't lose them.
	$home_url = esc_url( home_url( '/' ) );
	preg_match( '/alt="([^"]*)"/', $html, $alt_match );
	$alt = isset( $alt_match[1] ) ? esc_attr( $alt_match[1] ) : '';

	$picture = '<picture>';

	if ( $avif_set ) {
		$picture .= '<source type="image/avif" srcset="' . esc_attr( implode( ', ', $avif_set ) ) . '" sizes="56px">';
	}

	$picture .= '<img'
		. ' src="' . esc_url( $base . '56.png' ) . '"'
		. ' srcset="' . esc_attr( implode( ', ', $png_set ) ) . '"'
		. ' sizes="56px"'
		. ' width="56" height="43"'
		. ' alt="' . $alt . '"'
		. ' class="custom-logo"'
		. ' loading="eager"'
		. ' decoding="sync"'
		. '>';

	$picture .= '</picture>';

	// Preserve the anchor WordPress wraps the logo in.
	return '<a href="' . $home_url . '" class="custom-logo-link" rel="home" aria-current="page">' . $picture . '</a>';
}
add_filter( 'get_custom_logo', 'remotive_custom_logo_markup' );

// The old image-attributes filter is superseded by the full-markup filter
// above. Kept as a named no-op so any code that still references the hook
// name doesn't error.
function remotive_custom_logo_image_attributes( $attributes ) {
	return $attributes;
}
add_filter( 'get_custom_logo_image_attributes', 'remotive_custom_logo_image_attributes' );

/**
 * Preconnect only when another component has actually queued Google Sign-In.
 */
function remotive_google_signin_resource_hint( $urls, $relation_type ) {
	if ( 'preconnect' !== $relation_type ) {
		return $urls;
	}

	// Queued handles catch a properly enqueued client. Registered but
	// unqueued handles catch one enqueued later in the render, and the
	// filter lets a site force the hint when its plugin injects a raw
	// script tag, which never appears in either list and is why the
	// origin went unpreconnected in production.
	$needs_hint = (bool) apply_filters( 'remotive_preconnect_google_accounts', false );

	if ( ! $needs_hint ) {
		$scripts = wp_scripts();
		$handles = array_unique( array_merge( (array) $scripts->queue, array_keys( (array) $scripts->registered ) ) );

		foreach ( $handles as $handle ) {
			$src = isset( $scripts->registered[ $handle ] ) ? $scripts->registered[ $handle ]->src : null;

			if ( is_string( $src ) && false !== strpos( $src, 'accounts.google.com/' ) ) {
				$needs_hint = true;
				break;
			}
		}
	}

	if ( $needs_hint ) {
		$urls[] = array(
			'href'        => 'https://accounts.google.com',
			'crossorigin' => 'anonymous',
		);
	}

	return $urls;
}
add_filter( 'wp_resource_hints', 'remotive_google_signin_resource_hint', 10, 2 );

/**
 * Blog (archive/single) and secondary page templates' assets — only
 * loaded where actually used, not on every page. Per the theme's own
 * security/performance audit principle of conditional enqueuing (see
 * changelog.md v1.8.0): most visits never touch these templates, so
 * there's no reason to ship their CSS/JS everywhere.
 */
function remotive_conditional_enqueue_assets() {
	$is_blog = is_home() || is_singular( 'post' ) || is_archive() || is_search();

	// Any page except the front page uses styles from this file: the seven
	// custom templates registered in theme.json, and also the default
	// page.html template, whose .rm-page classes live here too — a
	// hardcoded template list silently missed those ordinary pages.
	$is_secondary_page = is_page() && ! is_front_page();
	// The photo gallery moved to the team page in 1.56.0; the lightbox has
	// to follow it, or the gallery loads without its viewer.
	$is_gallery_page   = is_page_template( 'page-team' );

	if ( ! $is_blog && ! $is_secondary_page ) {
		return;
	}

	$css_path = get_stylesheet_directory() . '/assets/css/blog-and-about.css';
	wp_enqueue_style(
		'remotive-blog-and-about',
		get_stylesheet_directory_uri() . '/assets/css/blog-and-about.css',
		array( 'remotive-style' ),
		file_exists( $css_path ) ? filemtime( $css_path ) : '1.0.0'
	);

	if ( ! $is_gallery_page ) {
		return;
	}

	// Lightbox only. The parallax layer belonged to the old about-page
	// hero, which was replaced with the standard page hero, so its script
	// had nothing left to move.
	foreach ( array( 'lightbox' ) as $handle_suffix ) {
		$js_path = get_stylesheet_directory() . '/assets/js/' . $handle_suffix . '.js';
		wp_enqueue_script(
			'remotive-' . $handle_suffix,
			get_stylesheet_directory_uri() . '/assets/js/' . $handle_suffix . '.js',
			array(),
			file_exists( $js_path ) ? filemtime( $js_path ) : '1.0.0',
			true
		);
		wp_script_add_data( 'remotive-' . $handle_suffix, 'strategy', 'defer' );
	}
}
add_action( 'wp_enqueue_scripts', 'remotive_conditional_enqueue_assets', 21 );

/**
 * Load the same component CSS inside the block editor so the canvas
 * matches the front end (drop caps, plate hovers, etc. won't be live
 * in the editor, but colours/type/spacing will match).
 */
function remotive_editor_assets() {
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/remotive.css' );
}
add_action( 'after_setup_theme', 'remotive_editor_assets' );

/**
 * Explicit theme supports + translation loading.
 *
 * Twenty Twenty-Five (the parent) already declares title-tag and
 * automatic-feed-links, and both calls are idempotent — but declaring them
 * here too means this child theme doesn't silently lose either support if
 * the parent theme is ever swapped or changes its own setup. Cheap
 * insurance, not redundant busywork.
 */
function remotive_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );

	// Child theme's own translation files (if any are added later) live in
	// wp-content/themes/remotive/languages/, not the parent's directory —
	// hence get_stylesheet_directory(), not get_template_directory().
	load_theme_textdomain( 'remotive', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'remotive_theme_setup' );

/**
 * One-time setup: sideload the bundled Re:Motive mark as the site's
 * custom logo, and the square lockup as the site icon (favicon), but
 * only if nothing has been set already and only once — so it never
 * overwrites a choice made later in wp-admin.
 */
function remotive_bootstrap_branding() {
	if ( get_option( 'remotive_branding_done' ) ) {
		return;
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	if ( ! get_theme_mod( 'custom_logo' ) ) {
		$logo_id = remotive_import_theme_image( '/assets/images/remotive-mark.png', 'Re:Motive Media logo' );
		if ( $logo_id ) {
			set_theme_mod( 'custom_logo', $logo_id );
		}
	}

	if ( ! get_option( 'site_icon' ) ) {
		$icon_id = remotive_import_theme_image( '/assets/images/remotive-lockup-square.jpg', 'Re:Motive Media icon' );
		if ( $icon_id ) {
			update_option( 'site_icon', $icon_id );
		}
	}

	update_option( 'remotive_branding_done', 1 );
}
add_action( 'after_switch_theme', 'remotive_bootstrap_branding' );

/**
 * Copy the bundled db-error.php / maintenance.php / php-error.php into
 * wp-content/ so they actually fire — WordPress core hardcodes those
 * three paths (WP_CONTENT_DIR root) and never looks inside a theme
 * folder for them, so shipping them in drop-ins/ alone does nothing on
 * its own. Runs on activation AND on every theme update (not gated by
 * a "done once" flag like the branding bootstrap above), so an edit to
 * one of these files in a future theme version actually reaches the
 * live site instead of the stale copy sitting untouched forever.
 *
 * Never overwrites a file that isn't ours: each bundled copy carries a
 * "Remotive Media db-error drop-in" style signature comment on its
 * second line, and copying is skipped if the file that's already there
 * doesn't have it — protects a hand-edited or third-party drop-in from
 * being silently clobbered.
 */
// Runs from remotive_version_sync() in inc/site-setup.php — once per theme
// version, on activation and after any file upload.
function remotive_install_error_dropins() {
	$files = array( 'db-error.php', 'maintenance.php', 'php-error.php' );

	foreach ( $files as $file ) {
		$src = get_stylesheet_directory() . '/drop-ins/' . $file;
		$dest = WP_CONTENT_DIR . '/' . $file;

		if ( ! is_readable( $src ) ) {
			continue;
		}

		if ( file_exists( $dest ) ) {
			$existing = file_get_contents( $dest, false, null, 0, 200 );
			if ( false === strpos( $existing, 'Remotive Media' ) ) {
				continue; // Someone else's drop-in — leave it alone.
			}
		}

		if ( ! is_writable( WP_CONTENT_DIR ) ) {
			continue;
		}

		copy( $src, $dest );
	}
}

/**
 * Merge any team members present in the code defaults but missing from
 * the site's already-saved `remotive_theme_options` option.
 *
 * Shipping a new default in remotive_theme_option_defaults() only
 * affects a FRESH install — once WordPress has a saved value for an
 * option key, remotive_get_theme_option() returns the saved value and
 * never falls back to the code default again for that key. A person
 * added to the code's default 'team' array in a later theme version
 * therefore never appears on an existing site without this: it merges
 * by slug (never overwrites an existing member's name/role/bio, never
 * reorders or removes anyone already saved) and only appends people
 * it has not offered before — so a member removed on purpose through
 * the settings screen stays removed across later releases.
 */
function remotive_sync_team_roster() {
	$defaults = remotive_theme_option_defaults();
	$slugs    = wp_list_pluck( $defaults['team'], 'slug' );
	$offered  = get_option( 'remotive_team_offered', array() );
	$offered  = is_array( $offered ) ? $offered : array();
	$saved    = get_option( 'remotive_theme_options', array() );

	// Nothing saved yet — a fresh install reads the code defaults directly.
	// Still record what has been offered, so someone removed later through
	// the settings screen is not put back by a future release.
	if ( empty( $saved ) || ! isset( $saved['team'] ) || ! is_array( $saved['team'] ) ) {
		update_option( 'remotive_team_offered', $slugs, false );
		return;
	}

	$existing = wp_list_pluck( $saved['team'], 'slug' );
	$changed  = false;

	foreach ( $defaults['team'] as $member ) {
		// Already on the roster, or offered in an earlier release and since
		// removed on purpose — either way, leave it exactly as it is.
		if ( in_array( $member['slug'], $existing, true ) || in_array( $member['slug'], $offered, true ) ) {
			continue;
		}

		$saved['team'][] = $member;
		$changed         = true;
	}

	if ( $changed ) {
		remotive_update_options_raw( $saved );
	}

	update_option( 'remotive_team_offered', $slugs, false );
}

/**
 * Copy a bundled theme image into the media library and return its
 * attachment ID, without fetching anything over the network.
 *
 * $relative_path is only ever called with hardcoded literals from
 * remotive_bootstrap_branding() above — never user input — so there's no
 * live path-traversal vector today. The realpath()-based containment
 * check below is defense-in-depth regardless: it guarantees the resolved
 * path can never leave the theme directory even if this function is ever
 * called differently in the future, rather than relying on "nothing
 * calls it unsafely right now" as the only protection.
 */
function remotive_import_theme_image( $relative_path, $title ) {
	$theme_dir = realpath( get_stylesheet_directory() );
	$file_path = realpath( get_stylesheet_directory() . $relative_path );

	if ( ! $theme_dir || ! $file_path || strpos( $file_path, $theme_dir ) !== 0 ) {
		return 0;
	}

	if ( ! file_exists( $file_path ) ) {
		return 0;
	}

	$filetype = wp_check_filetype( basename( $file_path ) );
	$upload   = wp_upload_bits( basename( $file_path ), null, file_get_contents( $file_path ) );

	if ( ! empty( $upload['error'] ) ) {
		return 0;
	}

	$attachment = array(
		'post_mime_type' => $filetype['type'],
		'post_title'      => $title,
		'post_content'    => '',
		'post_status'     => 'inherit',
	);

	$attachment_id = wp_insert_attachment( $attachment, $upload['file'] );
	if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		return 0;
	}

	$attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
	wp_update_attachment_metadata( $attachment_id, $attachment_data );

	return $attachment_id;
}
