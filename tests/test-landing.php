<?php
/**
 * Landing pages: language from the URL, URLs, hreflang, <html lang>, robots.
 * Run: php tests/test-landing.php
 */

require __DIR__ . '/bootstrap.php';
// Stands in for the language layer, which reads the URL's language directory.
function remotive_i18n_lang() {
	return $GLOBALS['remotive_i18n_lang'] ?? 'en';
}

require dirname( __DIR__ ) . '/inc/landing/landing-pages.php';

// Language comes from the URL's language directory only.
$cases = array(
	'en'      => 'en',
	'ms'      => 'ms',
	'zh-hans' => 'zh',
	'zh-hant' => 'zht',
	'zh-cn'   => 'en', // the retired prefix is redirected before it gets here
	'fr'      => 'en',
);
foreach ( $cases as $prefix => $expected ) {
	t_reset();
	$GLOBALS['remotive_i18n_lang'] = $prefix;
	t_eq( remotive_lp_requested_lang(), $expected, "language for '$prefix'" );
}

t_reset();
$GLOBALS['remotive_i18n_lang'] = 'zh-hant';
t_eq( remotive_lp_lang_index(), 3, 'Traditional Chinese is copy index 3' );

// URLs are path-based, never ?lang=.
t_reset();
t_eq( remotive_lp_url( 'seo-audit', 'ms' ), 'https://example.com/ms/seo-audit/', 'Malay URL' );
t_eq( remotive_lp_url( 'seo-audit', 'zht' ), 'https://example.com/zh-hant/seo-audit/', 'Traditional URL' );
t_eq( remotive_lp_url( 'seo-audit', 'zh' ), 'https://example.com/zh-hans/seo-audit/', 'Simplified URL' );
foreach ( array_keys( remotive_lp_languages() ) as $key ) {
	t_ok( false === strpos( remotive_lp_url( 'seo-audit', $key ), '?' ), "no query string for $key" );
}

// hreflang: every language plus x-default, each its own URL.
t_reset();
$GLOBALS['T']['slug'] = 'seo-audit';
ob_start();
remotive_lp_hreflang();
$out = ob_get_clean();
foreach ( array( 'en', 'ms-MY', 'zh-Hans', 'zh-Hant', 'x-default' ) as $tag ) {
	t_ok( false !== strpos( $out, 'hreflang="' . $tag . '"' ), "hreflang $tag present" );
}
t_eq( substr_count( $out, '<link' ), 5, 'five hreflang links' );

// A page that is not a landing page prints nothing.
t_reset();
$GLOBALS['T']['slug'] = 'about';
ob_start();
remotive_lp_hreflang();
t_eq( ob_get_clean(), '', 'no hreflang off the landing pages' );

// <html lang> follows the URL.
t_reset();
$GLOBALS['remotive_i18n_lang'] = 'zh-hans';
t_eq( remotive_lp_html_lang( 'lang="en-US"' ), 'lang="zh-Hans"', 'html lang replaced' );
t_eq( remotive_lp_html_lang( '' ), ' lang="zh-Hans"', 'html lang added when absent' );
$GLOBALS['T']['admin'] = true;
t_eq( remotive_lp_html_lang( 'lang="en-US"' ), 'lang="en-US"', 'admin untouched' );

// Robots: noindex + nofollow.
t_reset();
$r = remotive_lp_robots( array( 'index' => true ) );
t_ok( ! empty( $r['noindex'] ) && ! empty( $r['nofollow'] ), 'robots noindex, nofollow by default (nothing chosen in Rank Math)' );

// Rank Math's Advanced tab controls it once a choice is saved.
t_reset();
$GLOBALS['T']['meta'][1]['rank_math_robots'] = array( 'index' );
$r = remotive_lp_robots( array( 'noindex' => true, 'nofollow' => true, 'max-image-preview' => 'large' ) );
t_ok( empty( $r['noindex'] ) && empty( $r['nofollow'] ) && 'large' === $r['max-image-preview'], 'index chosen in Rank Math: no noindex, no nofollow, other directives kept' );
$GLOBALS['T']['meta'][1]['rank_math_robots'] = array( 'index', 'nofollow' );
$r = remotive_lp_robots( array() );
t_ok( empty( $r['noindex'] ) && ! empty( $r['nofollow'] ), 'index + nofollow chosen: only nofollow' );
t_eq( remotive_lp_robots_policy( 1 ), array( 'noindex' => false, 'nofollow' => true ), 'policy follows the Rank Math choice' );

// Seeding fills only a page with no choice, and never overwrites one.
t_reset();
$GLOBALS['T']['meta'][1]['_wp_page_template'] = 'page-landing';
remotive_lp_seed_robots( 1 );
t_eq( $GLOBALS['T']['meta'][1]['rank_math_robots'], array( 'noindex', 'nofollow' ), 'default written to Rank Math meta' );
$GLOBALS['T']['meta'][1]['rank_math_robots'] = array( 'index' );
remotive_lp_seed_robots( 1 );
t_eq( $GLOBALS['T']['meta'][1]['rank_math_robots'], array( 'index' ), 'a saved choice is never overwritten' );

// Nobody from the main site can enter a landing page; ads, typed addresses and bookmarks can.
t_reset();
if ( ! function_exists( 'wp_parse_url' ) ) {
	function wp_parse_url( $u, $c = -1 ) { return parse_url( $u, $c ); }
}
$in = function ( $server ) { return remotive_lp_is_internal_navigation( $server, 'example.com' ); };
t_ok( $in( array( 'HTTP_REFERER' => 'https://example.com/services/' ) ), 'a click from a main-site page is turned back' );
t_ok( $in( array( 'HTTP_REFERER' => 'https://www.example.com/' ) ), 'a click from the home page (www) is turned back' );
t_ok( $in( array( 'HTTP_REFERER' => 'https://example.com/ms/about/?x=1' ) ), 'a click from a translated page is turned back' );
t_ok( $in( array( 'HTTP_SEC_FETCH_SITE' => 'same-origin' ) ), 'same-origin with the Referer stripped is turned back' );
t_ok( $in( array( 'HTTP_SEC_FETCH_SITE' => 'same-site' ) ), 'same-site is turned back' );
t_ok( ! $in( array( 'HTTP_REFERER' => 'https://www.google.com/', 'HTTP_SEC_FETCH_SITE' => 'cross-site' ) ), 'an ad or search click gets in' );
t_ok( ! $in( array( 'HTTP_REFERER' => 'https://l.facebook.com/', 'HTTP_SEC_FETCH_SITE' => 'cross-site' ) ), 'a social click gets in' );
t_ok( ! $in( array( 'HTTP_SEC_FETCH_SITE' => 'none' ) ), 'a typed address or bookmark gets in' );
t_ok( ! $in( array() ), 'no headers at all (an app, curl) gets in' );
t_ok( ! $in( array( 'HTTP_REFERER' => 'https://example.com/seo-audit/', 'HTTP_SEC_FETCH_SITE' => 'same-origin' ) ), 'a landing page to itself (reload) gets in' );
t_ok( ! $in( array( 'HTTP_REFERER' => 'https://example.com/ms/google-ads-management/', 'HTTP_SEC_FETCH_SITE' => 'same-origin' ) ), 'language buttons between landing pages work' );
t_ok( ! $in( array( 'HTTP_REFERER' => 'https://example.com/zh-hans/paid-social-advertising', 'HTTP_SEC_FETCH_SITE' => 'same-origin' ) ), 'the form redirect to the thank-you page works (referer is the landing page)' );
$in_sub = function ( $server ) { return remotive_lp_is_internal_navigation( $server, 'example.com', '/site' ); };
t_ok( ! $in_sub( array( 'HTTP_REFERER' => 'https://example.com/site/ms/seo-audit/', 'HTTP_SEC_FETCH_SITE' => 'same-origin' ) ), 'subdirectory install: a landing page under the folder still gets in from another landing page' );
t_ok( $in_sub( array( 'HTTP_REFERER' => 'https://example.com/site/about/', 'HTTP_SEC_FETCH_SITE' => 'same-origin' ) ), 'subdirectory install: a main-site page is still turned back' );
t_ok( $in( array( 'HTTP_REFERER' => 'https://example.com/seo-audit-guide/', 'HTTP_SEC_FETCH_SITE' => 'same-origin' ) ), 'a main-site page whose address merely starts like a landing page is turned back' );

// The AI Discovery Files plugin: its page list never carries a landing or confirmation page, even when built from wp-admin.
t_reset();
require_once dirname( __DIR__ ) . '/inc/core/ai-discovery-files.php';
if ( ! function_exists( 'untrailingslashit' ) ) {
	function untrailingslashit( $s ) { return rtrim( (string) $s, '/' ); }
}
$GLOBALS['T']['slug'] = 'seo-audit';
$data = array( 'pages' => array(
	array( 'title' => 'About', 'url' => 'https://example.com/about' ),
	array( 'title' => 'SEO landing', 'url' => 'https://example.com/seo-audit' ),
) );
$out = remotive_aidf_template_data( $data, 'llms-txt' );
t_eq( array_column( $out['pages'], 'title' ), array( 'About' ), 'a landing page is removed from the plugin page list' );
t_eq( remotive_aidf_template_data( array( 'x' => 1 ) ), array( 'x' => 1 ), 'data without pages passes through' );

// Button labels are readable: Chinese in characters, never a code like ZH-CN.
$labels = array_column( remotive_lp_languages(), 2 );
t_eq( $labels, array( 'EN', 'BM', '简体', '繁體' ), 'switcher labels' );

// The language layer leaves landing pages alone (no translation, no redirect to English).
t_reset();
$GLOBALS['T']['slug'] = 'seo-audit';
t_eq( remotive_lp_skip_translator( true ), false, 'translator skipped on a landing page' );
$GLOBALS['T']['slug'] = 'about';
t_eq( remotive_lp_skip_translator( true ), true, 'translator keeps other pages' );

// Unlisted pages: the landing pages plus the confirmation pages, no duplicates.
t_reset();
$GLOBALS['T']['landing_ids'] = array( 11, 12, 5 );
$ids = remotive_unlisted_page_ids();
t_ok( in_array( 11, $ids, true ) && in_array( 12, $ids, true ), 'landing pages unlisted' );
t_eq( count( $ids ), count( array_unique( $ids ) ), 'no duplicate ids' );


// ---- The "How did you hear about us?" field ----
foreach ( remotive_lp_source_options() as $key => $label ) {
	t_eq( count( $label ), 4, "source answer '$key' has all four languages" );
	t_ok( '' !== trim( implode( '', $label ) ) && ! in_array( '', $label, true ), "source answer '$key' has no empty language" );
}
t_eq( array_keys( remotive_lp_source_options() ), array( 'search', 'ai', 'linkedin', 'social', 'referral', 'event', 'email', 'other' ), 'the answers are the eight sources, Other last' );
foreach ( array( 'en' => 'Search engine (Google, Bing)', 'ms' => 'Enjin carian', 'zh-hans' => '搜索引擎', 'zh-hant' => '搜尋引擎' ) as $lang => $needle ) {
	$GLOBALS['remotive_i18n_lang'] = $lang;
	$html = remotive_lp_source_field( 'rm-lp-top' );
	t_ok( false !== strpos( $html, $needle ), "$lang: the field lists the sources in the page language" );
	t_ok( false !== strpos( $html, '<select id="rm-lp-top-source" name="source">' ) && false !== strpos( $html, 'for="rm-lp-top-source"' ), "$lang: the label is tied to the select" );
	t_ok( false === strpos( $html, ' required' ), "$lang: the field is optional" );
}
t_ok( 8 + 1 === substr_count( remotive_lp_source_field( 'x' ), '<option' ), 'one option per source plus the prompt' );


// ---- The language links are never rewritten by the translator ----
foreach ( array( 'landing-pages.php', 'agency-page.php' ) as $file ) {
	$code = (string) file_get_contents( dirname( __DIR__ ) . '/inc/landing/' . $file );
	preg_match_all( '#<nav class="rm-lp__langs"[^>]*>#', $code, $navs );
	t_ok( count( $navs[0] ) >= 1, "$file has a language nav" );
	foreach ( $navs[0] as $nav ) {
		t_ok( false !== strpos( $nav, 'translate="no"' ), "$file: the language nav is marked translate=\"no\", so the language layer leaves its links alone" );
	}
}


// ---- The confirmation page's language links keep the service ----
if ( ! function_exists( 'add_query_arg' ) ) {
	function add_query_arg( $args, $url ) { return $url . ( false === strpos( $url, '?' ) ? '?' : '&' ) . http_build_query( $args ); }
}
$GLOBALS['remotive_i18n_lang'] = 'zh-hans';
$nav = remotive_lp_lang_nav( REMOTIVE_LP_THANKS_SLUG, array( 'service' => 'seo-audit' ) );
t_eq( substr_count( $nav, 'service=seo-audit' ), count( remotive_lp_enabled_languages() ), 'every language link on the confirmation page carries the service' );
t_ok( false === strpos( remotive_lp_lang_nav( REMOTIVE_LP_THANKS_SLUG ), 'service=' ), 'with no service the links are plain' );
t_ok( false === strpos( $nav, 'from=' ), 'the language links never carry from=, so a language switch cannot fire the lead conversion a second time' );

// ---- The confirmation URL is only used while the page can take a visitor ----
t_reset();
t_eq( remotive_lp_thanks_url( 'ms' ), 'https://example.com/ms/audit-requested/', 'a published confirmation page is used' );
$GLOBALS['T']['page_status'] = 'draft';
t_eq( remotive_lp_thanks_url( 'ms' ), '', 'an unpublished one is not, so the handler falls back instead of sending the visitor to a 404' );
$GLOBALS['T']['no_page'] = true;
t_eq( remotive_lp_thanks_url( 'en' ), '', 'nor a deleted one' );
t_reset();
t_ok( ! remotive_lp_page_usable( null ) && ! remotive_lp_page_usable( (object) array( 'post_status' => 'trash' ) ) && remotive_lp_page_usable( (object) array( 'post_status' => 'publish' ) ), 'usable means present and published' );

// ---- The logo links home only where asked ----
t_ok( false === strpos( remotive_lp_logo(), '<a ' ), 'the ad pages keep the logo unlinked' );
$linked = remotive_lp_logo( 'https://example.com/' );
t_ok( false !== strpos( $linked, 'class="rm-lp__home" href="https://example.com/"' ) && false !== strpos( $linked, 'aria-label=' ), 'a legal page gets a labelled link home' );

t_done( 'landing' );
