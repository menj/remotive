<?php
/**
 * Language layer: which pages are live in which language, URLs, dictionaries.
 * Run: php tests/test-i18n.php
 */

require __DIR__ . '/bootstrap.php';

// A database with no saved translations, so only the shipped dictionaries count.
class T_Wpdb {
	public $prefix = 'wp_';
	public function suppress_errors( $x = null ) { return false; }
	public function prepare( $q ) { return $q; }
	public function get_results( $q, $o = null ) { return array(); }
	public function get_charset_collate() { return ''; }
}
$GLOBALS['wpdb'] = new T_Wpdb();

function get_theme_file_path( $p ) { return dirname( __DIR__ ) . '/' . $p; }
function wp_parse_url( $u, $c = -1 ) { return parse_url( $u, $c ); }
function wp_strip_all_tags( $s ) { return strip_tags( $s ); }
function get_current_user_id() { return 1; }
function is_front_page() { return $GLOBALS['t_front'] ?? false; }
function is_home() { return false; }
function is_paged() { return false; }
function is_singular() { return false; }

require dirname( __DIR__ ) . '/inc/i18n/i18n.php';
require dirname( __DIR__ ) . '/inc/i18n/i18n-admin.php';

// Prefixes and tags: Chinese is told apart by script, not by country.
$langs = remotive_i18n_languages();
t_eq( array_column( $langs, 'prefix' ), array( '', 'ms', 'zh-hans', 'zh-hant' ), 'language prefixes' );
t_eq( array_column( $langs, 'hreflang' ), array( 'en', 'ms-MY', 'zh-Hans', 'zh-Hant' ), 'hreflang tags' );
t_eq( array_column( $langs, 'native' ), array( 'English', 'Bahasa Melayu', '简体中文', '繁體中文' ), 'switcher names are written in the language' );

// URLs.
t_eq( remotive_i18n_url( 'ms', '' ), 'https://example.com/ms/', 'Malay home' );
t_eq( remotive_i18n_url( 'zh-hans', 'team' ), 'https://example.com/zh-hans/team/', 'Simplified team page' );
t_eq( remotive_i18n_url( 'en', 'team' ), 'https://example.com/team/', 'English keeps its address' );

// The main pages are live in every language; the rest is off until switched on.
$live = remotive_i18n_default_live_pages();
foreach ( array( '', 'services', 'services/seo', 'case-studies', 'about', 'team', 'blog', 'contact', 'faq', 'privacy', 'terms' ) as $page ) {
	t_ok( in_array( $page, $live, true ), "'$page' is a default live page" );
}
foreach ( array( 'ms', 'zh-hans', 'zh-hant' ) as $code ) {
	foreach ( array( '', 'services', 'about', 'contact' ) as $page ) {
		t_ok( remotive_i18n_available( $code, $page ), "'$page' is live in $code" );
	}
	foreach ( array( '2026/09/seo-vs-sem', 'case-studies/ecommerce-seo-footwear' ) as $page ) {
		t_ok( ! remotive_i18n_available( $code, $page ), "'$page' (an article, an unlisted case study) is off in $code until switched on" );
	}
	t_ok( ! remotive_i18n_available( $code, null ), "no page, not live in $code" );
}
t_ok( remotive_i18n_available( 'en', 'anything' ), 'English is always live' );

// The header switcher: four languages, the Chinese ones written in characters.
$GLOBALS['remotive_i18n_lang'] = 'en';
$GLOBALS['t_front']            = true;
$nav = remotive_i18n_render_switcher( true );
t_ok( false !== strpos( $nav, 'rm-lang--nav' ), 'header switcher has its own class' );
foreach ( array( '>EN<', '>BM<', '>简体<', '>繁體<' ) as $label ) {
	t_ok( false !== strpos( $nav, $label ), "header switcher shows $label" );
}
foreach ( array( 'https://example.com/ms/', 'https://example.com/zh-hans/', 'https://example.com/zh-hant/' ) as $href ) {
	t_ok( false !== strpos( $nav, 'href="' . $href . '"' ), "header switcher links to $href" );
}
t_ok( false !== strpos( remotive_i18n_render_switcher(), '>Bahasa Melayu<' ), 'the footer switcher keeps the full names' );
$GLOBALS['t_front'] = false;

// A page with no translation of its own (an article, an archive) still offers each language's home page.
$fallback = remotive_i18n_render_switcher( true );
t_ok( false !== strpos( $fallback, 'href="https://example.com/ms/"' ), 'pages without a translation link to the Malay home page' );
t_ok( false !== strpos( $fallback, 'href="https://example.com/zh-hant/"' ), 'and to the Traditional Chinese home page' );

t_done( 'i18n' );
