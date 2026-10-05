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

t_done( 'landing' );
