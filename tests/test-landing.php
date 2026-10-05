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
t_ok( ! empty( $r['noindex'] ) && ! empty( $r['nofollow'] ), 'robots noindex, nofollow' );

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
