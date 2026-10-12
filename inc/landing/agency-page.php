<?php
/**
 * Remotive Media: the single-page agency layout.
 *
 * The settings and the on/off switch are in inc/options/agency-layout.php
 * (Appearance -> Theme Options -> Agency layout). With the switch off, nothing
 * here does anything. With it on:
 *
 * - The home page is swapped for the landing-page template and renders one
 *   scrolling agency page in the format of the ad landing pages: a contact form
 *   above the fold and another at the bottom, then the services and the team.
 *   There is no menu, no ticker and no reassurance line.
 * - The page keeps its translated versions (/ms/, /zh-hans/, /zh-hant/): the words
 *   go through the same language layer as the rest of the site, so hreflang,
 *   canonical, the language sitemap and Tools → Translations all apply, and the
 *   page has its own language links.
 * - If "Send the other pages to the home page" is also on, the rest of the
 *   public site redirects to the home page (a temporary 302), and drops out of
 *   the sitemaps and the language versions. The ad landing pages, their
 *   confirmation pages and the legal pages stay. Nothing is deleted.
 *
 * Switch it off and every page, template and setting is as it was.
 *
 * @package Remotive
 */

defined( 'ABSPATH' ) || exit;

/** Slug used for the agency page in forms and tracking. */
const REMOTIVE_AGENCY_SLUG = 'agency';

/**
 * Whether the agency layout switch is on.
 *
 * @return bool
 */
function remotive_agency_mode() {
	return '1' === (string) remotive_get_theme_option( 'agency_mode' );
}

/**
 * Whether the other pages are being sent to the home page.
 *
 * @return bool
 */
function remotive_agency_hides_pages() {
	return remotive_agency_mode() && '1' === (string) remotive_get_theme_option( 'agency_hide_pages' );
}

/**
 * Whether this request is the home page showing the agency layout.
 *
 * @return bool
 */
function remotive_agency_front() {
	return remotive_agency_mode() && ! is_admin() && is_front_page() && ! is_paged();
}

/**
 * The agency page as a landing "service", so the shared form and the enquiry
 * handler treat it like the other landing pages. English only.
 *
 * @return array<string,array<int,string>>
 */
function remotive_agency_service() {
	$four = function ( $text ) {
		return array( $text, $text, $text, $text );
	};

	return array(
		'label'      => $four( 'Agency (home page)' ),
		'cta'        => $four( (string) remotive_get_theme_option( 'agency_cta' ) ),
		'fine'       => $four( (string) remotive_get_theme_option( 'agency_fine' ) ),
		'site_label' => array( 'Your website', 'Laman web anda', '您的网站', '您的網站' ),
	);
}

/**
 * One landing service by slug: an ad landing page's, or the agency page's.
 *
 * @param string $slug Service slug.
 * @return array|null
 */
function remotive_lp_service( $slug ) {
	if ( REMOTIVE_AGENCY_SLUG === $slug ) {
		return remotive_agency_service();
	}

	$all = remotive_landing_services();

	return isset( $all[ $slug ] ) ? $all[ $slug ] : null;
}

/**
 * Whether a request should be sent to the home page. Pure, so it can be tested.
 *
 * @param array $c Flags: mode, hide, front, content (a page, post, archive,
 *                 search or the posts page), landing, legal, preview.
 * @return bool
 */
function remotive_agency_should_redirect( array $c ) {
	if ( empty( $c['mode'] ) || empty( $c['hide'] ) ) {
		return false;
	}

	if ( ! empty( $c['front'] ) || ! empty( $c['landing'] ) || ! empty( $c['legal'] ) || ! empty( $c['preview'] ) ) {
		return false;
	}

	return ! empty( $c['content'] );
}

/**
 * Send the pages that are switched off to the home page.
 */
function remotive_agency_maybe_redirect() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || is_robots() || is_feed() || is_404() ) {
		return;
	}

	$is_page = is_page();

	$send = remotive_agency_should_redirect( array(
		'mode'    => remotive_agency_mode(),
		'hide'    => '1' === (string) remotive_get_theme_option( 'agency_hide_pages' ),
		'front'   => is_front_page(),
		'content' => is_singular() || is_home() || is_archive() || is_search() || is_attachment(),
		'landing' => $is_page && is_page_template( REMOTIVE_LANDING_TEMPLATE ),
		'legal'   => $is_page && is_page_template( 'page-legal' ),
		'preview' => is_preview(),
	) );

	if ( ! $send ) {
		return;
	}

	nocache_headers();
	wp_safe_redirect( home_url( '/' ), 302 );
	exit;
}
add_action( 'template_redirect', 'remotive_agency_maybe_redirect', 0 );

/**
 * Render the home page with the landing-page template (a bare canvas that
 * prints the page below) while the layout is on.
 *
 * @param string $template Template file chosen by WordPress.
 * @return string
 */
function remotive_agency_swap_template( $template ) {
	if ( ! remotive_agency_front() || ! function_exists( 'get_block_template' ) ) {
		return $template;
	}

	$landing = get_block_template( get_stylesheet() . '//' . REMOTIVE_LANDING_TEMPLATE, 'wp_template' );

	if ( $landing && ! empty( $landing->content ) ) {
		global $_wp_current_template_id, $_wp_current_template_content;

		$_wp_current_template_id      = $landing->id;
		$_wp_current_template_content = $landing->content;
	}

	return $template;
}
add_filter( 'template_include', 'remotive_agency_swap_template', 99 );

/**
 * Fill the landing token for the agency home page.
 *
 * @param array $tokens Token => replacement.
 * @return array
 */
function remotive_agency_token( $tokens ) {
	if ( remotive_agency_front() ) {
		$tokens['__REMOTIVE_LANDING__'] = remotive_agency_render();
	}

	return $tokens;
}
add_filter( 'remotive_theme_option_tokens', 'remotive_agency_token', 20 );

/**
 * English paths ('privacy', 'terms') of the pages that stay while the others are
 * switched off, so their language versions stay too.
 *
 * @return string[]
 */
function remotive_agency_kept_keys() {
	$base = rtrim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
	$keys = array();

	foreach ( remotive_agency_kept_page_ids() as $id ) {
		$path = (string) wp_parse_url( (string) get_permalink( $id ), PHP_URL_PATH );

		if ( '' !== $base && 0 === strpos( $path, $base ) ) {
			$path = substr( $path, strlen( $base ) );
		}

		$keys[] = trim( $path, '/' );
	}

	return $keys;
}

/**
 * Which pages have language versions while the agency layout is on. The home
 * page keeps all of its languages. With the other pages switched off, they
 * have none, except the pages that stay (the legal pages).
 *
 * @param bool   $available Whether the page exists in the language.
 * @param string $lang      Language code.
 * @param string $key       English path of the page, '' for the home page.
 * @return bool
 */
function remotive_agency_i18n_available( $available, $lang, $key ) {
	if ( ! remotive_agency_hides_pages() || '' === (string) $key ) {
		return $available;
	}

	return $available && in_array( (string) $key, remotive_agency_kept_keys(), true );
}
add_filter( 'remotive_i18n_available', 'remotive_agency_i18n_available', 10, 3 );

/**
 * Ids of the pages that keep working while the others are switched off: the
 * home page and the legal pages. (Landing pages are handled by their own rules.)
 *
 * @return int[]
 */
function remotive_agency_kept_page_ids() {
	$ids = array( (int) get_option( 'page_on_front' ) );

	$legal = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => 20,
		'fields'         => 'ids',
		'meta_key'       => '_wp_page_template',
		'meta_value'     => 'page-legal',
		'no_found_rows'  => true,
	) );

	return array_values( array_filter( array_map( 'intval', array_merge( $ids, $legal ) ) ) );
}

/**
 * Rank Math's sitemap: only the kept pages.
 *
 * @param array|false $url  Sitemap entry.
 * @param string      $type Entry type.
 * @param object      $post The post, when $type is 'post'.
 * @return array|false
 */
function remotive_agency_rank_math_entry( $url, $type = '', $post = null ) {
	if ( ! $url || ! remotive_agency_hides_pages() ) {
		return $url;
	}

	if ( 'post' !== $type || ! is_object( $post ) ) {
		return false; // Terms, users and the rest are not reachable.
	}

	$is_landing = 'page' === $post->post_type && REMOTIVE_LANDING_TEMPLATE === get_post_meta( (int) $post->ID, '_wp_page_template', true );

	if ( $is_landing || in_array( (int) $post->ID, remotive_agency_kept_page_ids(), true ) ) {
		return $url;
	}

	return false;
}
add_filter( 'rank_math/sitemap/entry', 'remotive_agency_rank_math_entry', 12, 3 );

/**
 * WordPress's own sitemap: pages only, and only the kept ones.
 *
 * @param array  $args      Query arguments.
 * @param string $post_type Post type.
 * @return array
 */
function remotive_agency_core_sitemap_args( $args, $post_type ) {
	if ( ! remotive_agency_hides_pages() || 'page' !== $post_type ) {
		return $args;
	}

	$args['post__in'] = remotive_agency_kept_page_ids() ?: array( 0 );

	return $args;
}
add_filter( 'wp_sitemaps_posts_query_args', 'remotive_agency_core_sitemap_args', 12, 2 );

/**
 * @param array $post_types Post types in the core sitemap.
 * @return array
 */
function remotive_agency_core_sitemap_types( $post_types ) {
	if ( remotive_agency_hides_pages() ) {
		unset( $post_types['post'] );
	}

	return $post_types;
}
add_filter( 'wp_sitemaps_post_types', 'remotive_agency_core_sitemap_types' );

/**
 * @param array $taxonomies Taxonomies in the core sitemap.
 * @return array
 */
function remotive_agency_core_sitemap_taxonomies( $taxonomies ) {
	return remotive_agency_hides_pages() ? array() : $taxonomies;
}
add_filter( 'wp_sitemaps_taxonomies', 'remotive_agency_core_sitemap_taxonomies' );

/**
 * @param object|false $provider Sitemap provider.
 * @param string       $name     Provider name.
 * @return object|false
 */
function remotive_agency_core_sitemap_provider( $provider, $name ) {
	return ( remotive_agency_hides_pages() && 'users' === $name ) ? false : $provider;
}
add_filter( 'wp_sitemaps_add_provider', 'remotive_agency_core_sitemap_provider', 10, 2 );

/**
 * The AI Discovery Files plugin lists only the pages that still exist.
 *
 * @param array  $data      Template data.
 * @param string $file_slug File being built.
 * @return array
 */
function remotive_agency_aidf_data( $data, $file_slug = '' ) {
	if ( ! remotive_agency_hides_pages() || ! is_array( $data ) || empty( $data['pages'] ) || ! is_array( $data['pages'] ) ) {
		return $data;
	}

	$keep = array( untrailingslashit( home_url( '/' ) ) );

	foreach ( remotive_agency_kept_page_ids() as $id ) {
		$keep[] = untrailingslashit( (string) get_permalink( $id ) );
	}

	$data['pages'] = array_values( array_filter( $data['pages'], function ( $page ) use ( $keep ) {
		$url = is_array( $page ) && isset( $page['url'] ) ? untrailingslashit( (string) $page['url'] ) : '';

		return '' === $url || in_array( $url, $keep, true );
	} ) );

	return $data;
}
add_filter( 'aidf_template_data', 'remotive_agency_aidf_data', 20, 2 );

/**
 * Initials for a portrait with no photograph.
 *
 * @param string $name Full name.
 * @return string
 */
function remotive_agency_initials( $name ) {
	$words    = preg_split( '/\s+/', trim( $name ) );
	$initials = '';

	if ( $words && '' !== $words[0] ) {
		$initials .= mb_strtoupper( mb_substr( $words[0], 0, 1 ) );

		if ( count( $words ) > 1 ) {
			$initials .= mb_strtoupper( mb_substr( end( $words ), 0, 1 ) );
		}
	}

	return $initials;
}

/**
 * The text of a field as paragraphs: a blank line starts a new one.
 *
 * @param string $text   Plain text.
 * @param string $class  Class for each paragraph.
 * @return string
 */
function remotive_agency_paragraphs( $text, $class ) {
	$out = '';

	foreach ( preg_split( '/\R{2,}/', trim( (string) $text ) ) as $para ) {
		if ( '' !== trim( $para ) ) {
			$out .= '<p class="' . esc_attr( $class ) . '">' . esc_html( trim( $para ) ) . '</p>';
		}
	}

	return $out;
}

/**
 * Which ad landing page each service card links to, by card number. The three
 * services without a landing page have no entry, so their cards carry no link.
 * The `remotive_agency_service_pages` filter can change the list.
 *
 * @return array<int,string> Card number => landing page slug.
 */
function remotive_agency_service_pages() {
	return (array) apply_filters( 'remotive_agency_service_pages', array(
		1 => 'seo-audit',
		2 => 'google-ads-management',
		3 => 'paid-social-advertising',
	) );
}

/**
 * The link at the foot of a service card: the landing page's own button text,
 * in the language of the page, pointing at that language's version. Nothing if
 * the switch is off, the card has no landing page, or the page is not published.
 *
 * @param int $card Card number, 1 to 6.
 * @return string
 */
function remotive_agency_service_link( $card ) {
	$pages = remotive_agency_service_pages();

	if ( '1' !== (string) remotive_get_theme_option( 'agency_link_services' ) || empty( $pages[ $card ] ) ) {
		return '';
	}

	$slug     = (string) $pages[ $card ];
	$services = remotive_landing_services();
	$page     = get_page_by_path( $slug, OBJECT, 'page' );

	if ( ! isset( $services[ $slug ] ) || ! $page || 'publish' !== get_post_status( $page ) ) {
		return '';
	}

	return '<a class="rm-ag__go" href="' . esc_url( remotive_lp_url( $slug, remotive_lp_requested_lang() ) ) . '">'
		. remotive_lp_t( $services[ $slug ]['cta'] ) . ' <span aria-hidden="true">&rarr;</span></a>';
}

/**
 * The questions that are filled in, as array( question, answer ) pairs.
 *
 * @return array<int,array{0:string,1:string}>
 */
function remotive_agency_faq_items() {
	$items = array();

	for ( $i = 1; $i <= 6; $i++ ) {
		$q = trim( (string) remotive_get_theme_option( 'agency_faq_' . $i . '_q' ) );
		$a = trim( (string) remotive_get_theme_option( 'agency_faq_' . $i . '_a' ) );

		if ( '' !== $q && '' !== $a ) {
			$items[] = array( $q, $a );
		}
	}

	return $items;
}

/**
 * The questions section: the same accordion the ad landing pages use.
 *
 * @return string
 */
function remotive_agency_faq_html() {
	if ( '1' !== (string) remotive_get_theme_option( 'agency_show_faq' ) || ! remotive_agency_faq_items() ) {
		return '';
	}

	$out = '';

	foreach ( remotive_agency_faq_items() as $qa ) {
		$out .= '<details class="rm-lp__q"><summary>' . esc_html( $qa[0] ) . '</summary><p>' . esc_html( $qa[1] ) . '</p></details>';
	}

	return '<section class="rm-lp__section rm-lp__faq rm-ag__faq" id="questions" aria-labelledby="rm-ag-faq">'
		. '<h2 id="rm-ag-faq">' . esc_html( (string) remotive_get_theme_option( 'agency_faq_heading' ) ) . '</h2>'
		. '<div class="rm-lp__faqs">' . $out . '</div></section>';
}

/**
 * FAQPage structured data for the home page, built from the same items as the
 * visible questions so the two cannot drift apart. The language layer translates
 * it on the translated versions, as it does for the FAQ page.
 */
function remotive_agency_faq_schema() {
	if ( ! remotive_agency_front() || '1' !== (string) remotive_get_theme_option( 'agency_show_faq' ) ) {
		return;
	}

	$entities = array();

	foreach ( remotive_agency_faq_items() as $qa ) {
		$entities[] = array(
			'@type'          => 'Question',
			'name'           => $qa[0],
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $qa[1],
			),
		);
	}

	if ( ! $entities ) {
		return;
	}

	echo '<script type="application/ld+json" id="remotive-agency-faq">' . wp_json_encode( array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => $entities,
	), JSON_HEX_TAG | JSON_HEX_AMP ) . '</script>' . "\n";
}
add_action( 'wp_head', 'remotive_agency_faq_schema', 7 );

/**
 * The services section.
 *
 * @return string
 */
function remotive_agency_services_html() {
	if ( '1' !== (string) remotive_get_theme_option( 'agency_show_services' ) ) {
		return '';
	}

	$cards = '';
	$n     = 0;

	for ( $i = 1; $i <= 6; $i++ ) {
		$title = trim( (string) remotive_get_theme_option( 'agency_svc_' . $i . '_title' ) );

		if ( '' === $title ) {
			continue;
		}

		++$n;
		$desc   = trim( (string) remotive_get_theme_option( 'agency_svc_' . $i . '_desc' ) );
		$cards .= '<li class="rm-ag__card"><span class="rm-ag__num" aria-hidden="true">' . esc_html( str_pad( (string) $n, 2, '0', STR_PAD_LEFT ) ) . '</span>'
			. '<h3>' . esc_html( $title ) . '</h3>'
			. ( '' !== $desc ? '<p>' . esc_html( $desc ) . '</p>' : '' )
			. remotive_agency_service_link( $i )
			. '</li>';
	}

	if ( '' === $cards ) {
		return '';
	}

	$lead = trim( (string) remotive_get_theme_option( 'agency_services_lead' ) );

	return '<section class="rm-lp__section rm-ag__services" id="services" aria-labelledby="rm-ag-services">'
		. '<h2 id="rm-ag-services">' . esc_html( (string) remotive_get_theme_option( 'agency_services_heading' ) ) . '</h2>'
		. ( '' !== $lead ? '<p class="rm-lp__lead">' . esc_html( $lead ) . '</p>' : '' )
		. '<ul class="rm-ag__cards">' . $cards . '</ul></section>';
}

/**
 * The team section, with small portraits.
 *
 * @return string
 */
function remotive_agency_team_html() {
	if ( '1' !== (string) remotive_get_theme_option( 'agency_show_team' ) ) {
		return '';
	}

	$members = remotive_team_members();

	if ( ! $members ) {
		return '';
	}

	$sizes = remotive_agency_team_sizes();
	$size  = (string) remotive_get_theme_option( 'agency_team_size' );
	$px    = isset( $sizes[ $size ] ) ? $sizes[ $size ] : $sizes['sm'];

	$people = '';

	foreach ( $members as $member ) {
		// The portrait picture itself comes from remotive_team_portrait_css(), keyed on data-person.
		$people .= '<li class="rm-ag__person">'
			. '<div class="rm-team__photo rm-ag__photo"' . ( $member['slug'] ? ' data-person="' . esc_attr( $member['slug'] ) . '"' : '' ) . '><span>' . esc_html( remotive_agency_initials( $member['name'] ) ) . '</span></div>'
			. '<strong class="rm-ag__name">' . esc_html( $member['name'] ) . '</strong>'
			. ( '' !== $member['role'] ? '<span class="rm-ag__role">' . esc_html( $member['role'] ) . '</span>' : '' )
			. '</li>';
	}

	return '<section class="rm-lp__section rm-ag__team" id="team" aria-labelledby="rm-ag-team">'
		. '<h2 id="rm-ag-team">' . esc_html( (string) remotive_get_theme_option( 'agency_team_heading' ) ) . '</h2>'
		. '<ul class="rm-ag__people" style="--ag-photo:' . (int) $px . 'px">' . $people . '</ul></section>';
}

/**
 * The footer: who we are, how to reach us, where to follow us and the legal
 * pages, from the business details in Theme Options (so it matches the rest of
 * the site), with a way back to the form. There are no links to the pages that
 * the layout switches off.
 *
 * @return string
 */
function remotive_agency_footer_html() {
	$brand = '<div class="rm-ag__foot-brand">' . remotive_lp_logo()
		. '<p>' . esc_html__( 'Full-funnel digital marketing for brands who would rather be seen than shout. Registered in Singapore, working across APAC.', 'remotive' ) . '</p></div>';

	$email = trim( (string) remotive_get_theme_option( 'contact_email' ) );
	$lines = array();

	foreach ( array( 'address_line_1', 'address_line_2', 'address_line_3' ) as $key ) {
		$line = trim( (string) remotive_get_theme_option( $key ) );

		if ( '' !== $line ) {
			$lines[] = esc_html( $line );
		}
	}

	$contact = '';

	if ( '' !== $email || $lines ) {
		$contact = '<div class="rm-ag__foot-col"><h2>' . esc_html__( 'Contact', 'remotive' ) . '</h2>'
			. ( '' !== $email ? '<p><a class="rm-ag__mail" href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a></p>' : '' )
			. ( $lines ? '<p class="rm-ag__address">' . implode( '<br>', $lines ) . '</p>' : '' )
			. '</div>';
	}

	$social_links = remotive_render_social_links();
	$social       = '' !== $social_links
		? '<div class="rm-ag__foot-col"><h2>' . esc_html__( 'Follow us', 'remotive' ) . '</h2><div class="rm-ag__socials">' . $social_links . '</div></div>'
		: '';

	$items = '';

	foreach ( array( 'privacy' => __( 'Privacy Policy', 'remotive' ), 'terms' => __( 'Terms of Service', 'remotive' ) ) as $path => $label ) {
		$page = get_page_by_path( $path, OBJECT, 'page' );

		if ( $page && 'publish' === get_post_status( $page ) ) {
			$items .= '<li><a href="' . esc_url( get_permalink( $page->ID ) ) . '">' . esc_html( $label ) . '</a></li>';
		}
	}

	$legal = '' !== $items ? '<div class="rm-ag__foot-col"><h2>' . esc_html__( 'Legal', 'remotive' ) . '</h2><ul class="rm-ag__legal">' . $items . '</ul></div>' : '';

	return '<footer class="rm-ag__foot">'
		. '<div class="rm-ag__foot-grid">' . $brand . $contact . $social . $legal . '</div>'
		. '<div class="rm-ag__foot-bottom"><p>' . remotive_copyright_line() . '</p>'
		. '<a class="rm-ag__top" href="#main">' . esc_html__( 'Back to top', 'remotive' ) . ' <span aria-hidden="true">&uarr;</span></a></div>'
		. '</footer>';
}

/**
 * The language links in the bar: one per language the site offers for the home
 * page, each pointing at that language's own home page. The campaign
 * parameters are added to them by assets/js/landing.js, as on the ad pages.
 *
 * @return string
 */
function remotive_agency_lang_nav() {
	$links   = '';
	$current = remotive_lp_requested_lang();
	$count   = 0;

	foreach ( remotive_lp_enabled_languages() as $key => $lang ) {
		if ( '' !== $lang[0] && function_exists( 'remotive_i18n_available' ) && ! remotive_i18n_available( $lang[0], '' ) ) {
			continue;
		}

		++$count;
		$url    = '' === $lang[0] ? home_url( '/' ) : user_trailingslashit( home_url( '/' . $lang[0] ) );
		$links .= sprintf(
			'<a class="rm-lp__lang" href="%1$s" hreflang="%2$s" lang="%2$s" aria-label="%3$s" title="%3$s"%4$s>%5$s</a>',
			esc_url( $url ),
			esc_attr( $lang[1] ),
			esc_attr( $lang[3] ),
			$current === $key ? ' aria-current="true"' : '',
			esc_html( $lang[2] )
		);
	}

	return $count > 1 ? '<nav class="rm-lp__langs" aria-label="Language / Bahasa / 语言 / 語言">' . $links . '</nav>' : '';
}

/**
 * The agency page.
 *
 * @return string
 */
function remotive_agency_render() {
	$o = function ( $key ) {
		return (string) remotive_get_theme_option( $key );
	};

	$cta     = $o( 'agency_cta' );
	$eyebrow = trim( $o( 'agency_eyebrow' ) );

	$footer = remotive_agency_footer_html();

	return '<div class="rm-lp rm-ag" data-lang="' . esc_attr( remotive_lp_requested_lang() ) . '" data-service="' . esc_attr( REMOTIVE_AGENCY_SLUG ) . '">'
		. '<header class="rm-lp__bar">' . remotive_lp_logo() . remotive_agency_lang_nav() . '</header>'
		. '<main id="main" class="rm-lp__main">'
		. '<section class="rm-lp__hero"><div class="rm-lp__copy">'
		. ( '' !== $eyebrow ? '<p class="rm-lp__eyebrow">' . esc_html( $eyebrow ) . '</p>' : '' )
		. '<h1>' . esc_html( $o( 'agency_line_1' ) ) . '<br> ' . esc_html( $o( 'agency_line_2' ) ) . '</h1>'
		. remotive_agency_paragraphs( $o( 'agency_lead' ), 'rm-lp__lead' )
		. '</div>'
		. '<div class="rm-lp__card" id="rm-lp-start"><h2>' . esc_html( $o( 'agency_form_title' ) ) . '</h2>'
		. '<p class="rm-lp__intro">' . esc_html( $o( 'agency_form_intro' ) ) . '</p>'
		. remotive_lp_form( REMOTIVE_AGENCY_SLUG, 'top' ) . '</div></section>'
		. remotive_agency_services_html()
		. remotive_agency_team_html()
		. remotive_agency_faq_html()
		. '<section class="rm-lp__section rm-lp__final" aria-labelledby="rm-lp-final"><div class="rm-lp__final-copy">'
		. '<h2 id="rm-lp-final">' . esc_html( $o( 'agency_final_heading' ) ) . '</h2>'
		. remotive_agency_paragraphs( $o( 'agency_final_lead' ), 'rm-lp__lead' ) . '</div>'
		. '<div class="rm-lp__card">' . remotive_lp_form( REMOTIVE_AGENCY_SLUG, 'bottom' ) . '</div></section>'
		. '</main>'
		. '<a class="rm-lp__sticky" href="#rm-lp-start">' . esc_html( $cta ) . '</a>'
		. $footer
		. '</div>';
}

/**
 * Stylesheets and script for the agency home page: the landing-page ones, plus
 * the file for the services and team sections.
 */
function remotive_agency_assets() {
	if ( ! remotive_agency_front() ) {
		return;
	}

	$dir = get_stylesheet_directory();
	$uri = get_stylesheet_directory_uri();

	foreach ( array(
		'remotive-landing' => array( '/assets/css/landing.css', array( 'remotive-style' ) ),
		'remotive-agency'  => array( '/assets/css/agency.css', array( 'remotive-landing' ) ),
	) as $handle => $file ) {
		wp_enqueue_style( $handle, $uri . $file[0], $file[1], file_exists( $dir . $file[0] ) ? filemtime( $dir . $file[0] ) : '1.0.0' );
	}

	wp_enqueue_script( 'remotive-landing', $uri . '/assets/js/landing.js', array(), file_exists( $dir . '/assets/js/landing.js' ) ? filemtime( $dir . '/assets/js/landing.js' ) : '1.0.0', true );
	wp_script_add_data( 'remotive-landing', 'strategy', 'defer' );
}
add_action( 'wp_enqueue_scripts', 'remotive_agency_assets', 22 );
