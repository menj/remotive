<?php
/**
 * Minimal stand-ins for the WordPress functions the theme's plain logic calls,
 * so the tests run with `php tests/test-*.php` and no WordPress. Only what the
 * tested code touches is stubbed; anything else fails loudly as an undefined
 * function, which is the point.
 *
 * Tests set $GLOBALS['T'] to steer the stubs (options, query vars, page).
 */

define( 'ABSPATH', dirname( __DIR__ ) . '/' );
define( 'OBJECT', 'OBJECT' );

$GLOBALS['T']       = array(
	'options'   => array(),
	'query_var' => '',
	'slug'      => 'seo-audit',
	'get'       => array(),
	'admin'     => false,
	'errors'    => array(),
);
$GLOBALS['filters'] = array();

function t_reset() {
	$GLOBALS['T'] = array(
		'options'   => array(),
		'query_var' => '',
		'slug'      => 'seo-audit',
		'get'       => array(),
		'admin'     => false,
		'errors'    => array(),
	);
	$_GET         = array();
}

$GLOBALS['t_fail'] = 0;
$GLOBALS['t_pass'] = 0;

function t_ok( $cond, $label ) {
	if ( $cond ) {
		$GLOBALS['t_pass']++;
		return;
	}
	$GLOBALS['t_fail']++;
	fwrite( STDERR, "FAIL: $label\n" );
}

function t_eq( $actual, $expected, $label ) {
	t_ok( $actual === $expected, $label . ' (expected ' . var_export( $expected, true ) . ', got ' . var_export( $actual, true ) . ')' );
}

function t_done( $name ) {
	echo "$name: {$GLOBALS['t_pass']} passed, {$GLOBALS['t_fail']} failed\n";
	exit( $GLOBALS['t_fail'] ? 1 : 0 );
}

// Escaping and text.
function __( $s ) { return $s; }
function esc_html__( $s ) { return htmlspecialchars( $s ); }
function esc_attr__( $s ) { return htmlspecialchars( $s ); }
function esc_html( $s ) { return htmlspecialchars( (string) $s ); }
function esc_attr( $s ) { return htmlspecialchars( (string) $s ); }
function esc_url( $s ) { return $s; }
function esc_url_raw( $s ) { return $s; }
function number_format_i18n( $n, $d = 0 ) { return number_format( $n, $d ); }
function sanitize_key( $s ) { return strtolower( preg_replace( '/[^a-z0-9_\-]/i', '', $s ) ); }
function sanitize_text_field( $s ) { return trim( strip_tags( (string) $s ) ); }
function sanitize_hex_color( $c ) { return preg_match( '/^#([A-Fa-f0-9]{3}){1,2}$/', (string) $c ) ? $c : ''; }
function wp_unslash( $s ) { return $s; }
function wp_json_encode( $d, $f = 0 ) { return json_encode( $d, $f ); }
function is_serialized( $s ) { return is_string( $s ) && preg_match( '/^[aOsibd]:/', $s ); }

// Hooks: record, never run.
function add_action( $h, $cb ) { $GLOBALS['filters'][ $h ][] = $cb; }
function add_filter( $h, $cb ) { $GLOBALS['filters'][ $h ][] = $cb; }
function add_rewrite_rule( $r, $q, $w ) { $GLOBALS['T']['rewrite'][] = array( $r, $q, $w ); }
function add_settings_error( $a, $code, $msg, $type = 'error' ) { $GLOBALS['T']['errors'][] = array( $code, $type, $msg ); }

// Options and request state.
function get_option( $k, $d = false ) { return $GLOBALS['T']['options'][ $k ] ?? $d; }
function get_query_var( $k ) { return $GLOBALS['T']['query_var']; }
function is_admin() { return $GLOBALS['T']['admin']; }
function is_page() { return true; }
function is_page_template() { return $GLOBALS['T']['template'] ?? true; }
function get_queried_object_id() { return 1; }
function get_queried_object() { return (object) array( 'ID' => 1 ); }
function get_posts() { return $GLOBALS['T']['landing_ids'] ?? array( 5, 6 ); }
function get_post_field() { return $GLOBALS['T']['slug']; }
function get_page_by_path( $s ) { return (object) array( 'ID' => 5 ); }
function get_permalink() { return 'https://example.com/' . $GLOBALS['T']['slug'] . '/'; }
function home_url( $p = '' ) { return 'https://example.com' . $p; }
function admin_url( $p = '' ) { return 'https://example.com/wp-admin/' . $p; }
function user_trailingslashit( $s ) { return rtrim( $s, '/' ) . '/'; }
function get_stylesheet_directory() { return dirname( __DIR__ ); }
function get_stylesheet_directory_uri() { return 'https://example.com/wp-content/themes/remotive'; }
function wp_create_nonce() { return 'NONCE'; }
function nocache_headers() {}
