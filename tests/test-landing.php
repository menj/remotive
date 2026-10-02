<?php
/**
 * Landing pages: language from the URL, URLs, hreflang, <html lang>, robots.
 * Run: php tests/test-landing.php
 */

require __DIR__ . '/bootstrap.php';
require dirname( __DIR__ ) . '/inc/landing/landing-pages.php';

// Language comes from the URL's language directory only.
$cases = array(
	''      => 'en',
	'ms'    => 'ms',
	'zh-cn' => 'zh',
	'zh-tw' => 'zht',
	'fr'    => 'en',
);
foreach ( $cases as $prefix => $expected ) {
	t_reset();
	$GLOBALS['T']['query_var'] = $prefix;
	t_eq( remotive_lp_requested_lang(), $expected, "language for '$prefix'" );
}

t_reset();
$GLOBALS['T']['query_var'] = 'zh-tw';
t_eq( remotive_lp_lang_index(), 3, 'Traditional Chinese is copy index 3' );

// URLs are path-based, never ?lang=.
t_reset();
t_eq( remotive_lp_url( 'seo-audit', 'ms' ), 'https://example.com/ms/seo-audit/', 'Malay URL' );
t_eq( remotive_lp_url( 'seo-audit', 'zht' ), 'https://example.com/zh-tw/seo-audit/', 'Traditional URL' );
foreach ( array_keys( remotive_lp_languages() ) as $key ) {
	t_ok( false === strpos( remotive_lp_url( 'seo-audit', $key ), '?' ), "no query string for $key" );
}

// hreflang: every language plus x-default, each its own URL.
t_reset();
$GLOBALS['T']['slug'] = 'seo-audit';
ob_start();
remotive_lp_hreflang();
$out = ob_get_clean();
foreach ( array( 'en', 'ms', 'zh-Hans', 'zh-Hant', 'x-default' ) as $tag ) {
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
$GLOBALS['T']['query_var'] = 'zh-cn';
t_eq( remotive_lp_html_lang( 'lang="en-US"' ), 'lang="zh-Hans"', 'html lang replaced' );
t_eq( remotive_lp_html_lang( '' ), ' lang="zh-Hans"', 'html lang added when absent' );
$GLOBALS['T']['admin'] = true;
t_eq( remotive_lp_html_lang( 'lang="en-US"' ), 'lang="en-US"', 'admin untouched' );

// Robots: noindex + nofollow; language URLs are not redirected to the English one.
t_reset();
$r = remotive_lp_robots( array( 'index' => true ) );
t_ok( ! empty( $r['noindex'] ) && ! empty( $r['nofollow'] ), 'robots noindex, nofollow' );
t_eq( remotive_lp_keep_language_url( 'https://example.com/x/' ), 'https://example.com/x/', 'English URL may redirect' );
$GLOBALS['T']['query_var'] = 'ms';
t_eq( remotive_lp_keep_language_url( 'https://example.com/x/' ), false, 'language URL never redirected' );

t_done( 'landing' );
