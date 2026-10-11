<?php
/**
 * The agency home page keeps its translated versions: the page is rendered, then
 * run through the real language layer for each language.
 * Run: php tests/test-agency-i18n.php
 */

require __DIR__ . '/bootstrap.php';

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
function get_post() { return (object) array( 'post_modified_gmt' => '2026-10-01 10:00:00' ); }
function is_front_page() { return true; }
function is_home() { return false; }
function is_category() { return false; }
function is_tag() { return false; }
function is_author() { return false; }
function is_date() { return false; }
function is_paged() { return false; }
function is_singular() { return false; }
function untrailingslashit( $s ) { return rtrim( $s, '/\\' ); }
function sanitize_textarea_field( $s ) { return trim( strip_tags( (string) $s ) ); }
function get_post_status( $p ) { return 'publish'; }
function wp_parse_args( $a, $d = array() ) { return array_merge( $d, (array) $a ); }
function wp_kses( $s ) { return $s; }
function _n( $a, $b, $n ) { return 1 === $n ? $a : $b; }

require dirname( __DIR__ ) . '/inc/i18n/i18n.php';
require dirname( __DIR__ ) . '/inc/options/colours.php';
require dirname( __DIR__ ) . '/inc/options/agency-layout.php';
require dirname( __DIR__ ) . '/inc/options/theme-options.php';
require dirname( __DIR__ ) . '/inc/landing/landing-copy.php';
require dirname( __DIR__ ) . '/inc/landing/landing-pages.php';
require dirname( __DIR__ ) . '/inc/landing/agency-page.php';

$GLOBALS['T']['options']['remotive_theme_options'] = array( 'agency_mode' => '1' );

// The default words, in the order they are written on the page.
$d     = remotive_agency_option_defaults();
$words = array( $d['agency_line_2'], $d['agency_lead'], $d['agency_form_title'], $d['agency_form_intro'], $d['agency_cta'], $d['agency_fine'], $d['agency_services_heading'], $d['agency_services_lead'], $d['agency_final_heading'], $d['agency_final_lead'] );
for ( $i = 1; $i <= 6; $i++ ) {
	$words[] = $d[ 'agency_svc_' . $i . '_title' ];
	$words[] = $d[ 'agency_svc_' . $i . '_desc' ];
}

foreach ( array( 'ms' => 'ms', 'zh-hans' => 'zh-hans', 'zh-hant' => 'zh-hant' ) as $code => $unused ) {
	$GLOBALS['remotive_i18n_lang'] = $code;
	$html    = remotive_agency_render();
	$missing = array();
	$out     = remotive_i18n_translate_body( $html, $code, $missing );

	// Every default sentence on the page has a translation.
	// Every English sentence the page ships with must have been translated, so none may be reported as missing.
	// (The form's own labels are written in the language already, and the language links keep their names.)
	$bad = array_values( array_intersect( array_map( 'remotive_i18n_norm', $words ), array_map( 'remotive_i18n_norm', array_keys( $missing ) ) ) );
	t_eq( $bad, array(), "$code: nothing on the agency page is left untranslated" );

	foreach ( array( 'Your name', 'Work email' ) as $english ) {
		t_ok( false === strpos( $out, '>' . $english ), "$code: the form label '$english' is translated" );
	}

	t_ok( false === strpos( $out, 'built to scale Asian brands' ), "$code: the headline's second line is translated" );
	t_ok( false === strpos( $out, 'senior-led independent media team' ), "$code: the introduction is translated" );
	t_eq( substr_count( $out, '<form ' ), 2, "$code: both forms are still there" );
	t_ok( false !== strpos( $out, 'name="lp_lang"' ), "$code: the form carries its language" );
	t_ok( false !== strpos( $out, 'data-lang="' . ( 'ms' === $code ? 'ms' : ( 'zh-hans' === $code ? 'zh' : 'zht' ) ) . '"' ), "$code: the page declares its language" );

	// The language links.
	$nav = remotive_agency_lang_nav();
	foreach ( array( 'https://example.com/', 'https://example.com/ms/', 'https://example.com/zh-hans/', 'https://example.com/zh-hant/' ) as $href ) {
		t_ok( false !== strpos( $nav, 'href="' . $href . '"' ), "$code: the language links include $href" );
	}
	t_eq( substr_count( $nav, 'aria-current="true"' ), 1, "$code: exactly one language is marked as current" );
}

// English: no translation, and the links are all there.
$GLOBALS['remotive_i18n_lang'] = 'en';
t_ok( false !== strpos( remotive_agency_lang_nav(), 'hreflang="ms-MY"' ), 'English: the page links to the Malay version' );

// A language switched off in Theme Options leaves the links.
$GLOBALS['T']['options']['remotive_theme_options'] = array( 'agency_mode' => '1', 'lang_ms' => '0' );
t_ok( false === strpos( remotive_agency_lang_nav(), '/ms/' ) && false !== strpos( remotive_agency_lang_nav(), '/zh-hans/' ), 'a language that is off drops out of the links' );

// The home page keeps every language, with the other pages switched off or not.
foreach ( array( '1', '0' ) as $hide ) {
	$GLOBALS['T']['options']['remotive_theme_options'] = array( 'agency_mode' => '1', 'agency_hide_pages' => $hide );
	foreach ( array( 'ms', 'zh-hans', 'zh-hant' ) as $code ) {
		t_ok( remotive_agency_i18n_available( true, $code, '' ), "the home page keeps its $code version (other pages hidden: $hide)" );
	}
}

// With the other pages switched off, their language versions go; the kept pages keep theirs.
$GLOBALS['T']['slug'] = 'privacy'; // the stubs answer every page with this address, so it stands for the kept legal page
$GLOBALS['T']['options']['remotive_theme_options'] = array( 'agency_mode' => '1', 'agency_hide_pages' => '1' );
// (The tests' apply_filters() does not run filters, so the agency filter is called directly.)
t_ok( ! remotive_agency_i18n_available( true, 'ms', 'team' ), 'the Team page has no Malay version while the other pages are hidden' );
t_ok( ! remotive_agency_i18n_available( true, 'zh-hans', 'services' ), 'Services has no Chinese version while the other pages are hidden' );
t_ok( remotive_agency_i18n_available( true, 'ms', 'privacy' ), 'the privacy page keeps its Malay version' );
t_ok( ! remotive_agency_i18n_available( false, 'ms', 'privacy' ), 'a page that has no translation still has none' );

// Off: nothing changes for any page.
$GLOBALS['T']['options']['remotive_theme_options'] = array();
t_ok( remotive_agency_i18n_available( true, 'ms', 'team' ), 'with the switch off, the Team page keeps its Malay version' );
$GLOBALS['T']['options']['remotive_theme_options'] = array( 'agency_mode' => '1', 'agency_hide_pages' => '0' );
t_ok( remotive_agency_i18n_available( true, 'ms', 'team' ), 'with the other pages left live, the Team page keeps its Malay version' );

t_done( 'agency-i18n' );
