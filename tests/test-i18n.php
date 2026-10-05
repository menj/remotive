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
function is_front_page() { return false; }
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

// The one page of the brief is a landing page, which has its own copy for every
// language, so the site's ordinary pages are all off until switched on.
t_eq( remotive_i18n_default_live_pages(), array(), 'no ordinary page is live by default' );
foreach ( array( 'ms', 'zh-hans', 'zh-hant' ) as $code ) {
	foreach ( array( '', 'team', 'services', 'contact', 'faq', 'case-studies' ) as $page ) {
		t_ok( ! remotive_i18n_available( $code, $page ), "$page is not live in $code until switched on" );
	}
	t_ok( ! remotive_i18n_available( $code, null ), "no page, not live in $code" );
}
t_ok( remotive_i18n_available( 'en', 'anything' ), 'English is always live' );

// With nothing translated live there is no switcher, and no link that would only redirect back.
$GLOBALS['remotive_i18n_lang'] = 'en';
t_eq( remotive_i18n_render_switcher(), '', 'no switcher while no other language is live' );

// The dictionaries: the same pages everywhere, each with a title and description.
$keys = array();
foreach ( array( 'ms', 'zh-hans', 'zh-hant' ) as $code ) {
	$data = require dirname( __DIR__ ) . '/inc/i18n/' . $code . '.php';
	t_ok( isset( $data['seo'][''] ), "$code has a home page entry" );
	foreach ( $data['seo'] as $page => $fields ) {
		t_ok( ! empty( $fields['title'] ) && ! empty( $fields['description'] ), "$code '$page' has a title and description" );
	}
	$keys[ $code ] = array_keys( $data['seo'] );
	sort( $keys[ $code ] );
	t_ok( count( $data['strings'] ) > 500, "$code has a full set of strings" );
}
t_eq( $keys['ms'], $keys['zh-hans'], 'Malay and Simplified cover the same pages' );
t_eq( $keys['ms'], $keys['zh-hant'], 'Malay and Traditional cover the same pages' );

t_done( 'i18n' );
