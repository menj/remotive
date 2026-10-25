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
function apply_filters( $h, $v ) { return $v; }
function remove_action( $hook = '', $cb = '', $prio = 10 ) { $GLOBALS['t_actions'][] = $hook . '|' . $cb; }
function has_action( $hook, $cb ) { return ! empty( $GLOBALS['t_has'][ $hook . '|' . $cb ] ); }
function is_user_logged_in() { return $GLOBALS['T']['logged_in'] ?? false; }
function is_wp_error( $x ) { return $x instanceof WP_Error; }
class WP_Error {
	public $errors = array();
	public function __construct( $code = '', $message = '' ) {
		if ( '' !== $code ) {
			$this->errors[ $code ][] = $message;
		}
	}
	public function add( $code, $message ) { $this->errors[ $code ][] = $message; }
	public function remove( $code ) { unset( $this->errors[ $code ] ); }
	public function get_error_codes() { return array_keys( $this->errors ); }
}
function add_action( $h, $cb, $prio = 10 ) { $GLOBALS['filters'][ $h ][] = $cb; $GLOBALS['priorities'][ $h . '|' . ( is_string( $cb ) ? $cb : 'closure' ) ] = $prio; }
function add_filter( $h, $cb, $prio = 10 ) { $GLOBALS['filters'][ $h ][] = $cb; $GLOBALS['priorities'][ $h . '|' . ( is_string( $cb ) ? $cb : 'closure' ) ] = $prio; }
function add_rewrite_rule( $r, $q, $w ) { $GLOBALS['T']['rewrite'][] = array( $r, $q, $w ); }
function add_settings_error( $a, $code, $msg, $type = 'error' ) { $GLOBALS['T']['errors'][] = array( $code, $type, $msg ); }

// Options and request state.
function get_option( $k, $d = false ) { return $GLOBALS['T']['options'][ $k ] ?? $d; }
function update_option( $k, $v ) { $GLOBALS['T']['options'][ $k ] = $v; return true; }
function get_query_var( $k ) { return $GLOBALS['T']['query_var']; }
function is_admin() { return $GLOBALS['T']['admin']; }
function is_page() { return true; }
function is_page_template() { return $GLOBALS['T']['template'] ?? true; }
function get_queried_object_id() { return 1; }
function get_queried_object() { return (object) array( 'ID' => 1 ); }
function current_user_can() { return $GLOBALS['T']['can_edit'] ?? false; }
function get_post_meta( $id, $key = '', $single = false ) { return $GLOBALS['T']['meta'][ $id ][ $key ] ?? ''; }
function update_post_meta( $id, $key, $value ) { $GLOBALS['T']['meta'][ $id ][ $key ] = $value; return true; }
function get_posts() { return $GLOBALS['T']['landing_ids'] ?? array( 5, 6 ); }
function get_post_field() { return $GLOBALS['T']['slug']; }
function get_page_by_path( $s ) { return ! empty( $GLOBALS['T']['no_page'] ) ? null : (object) array( 'ID' => 5, 'post_status' => $GLOBALS['T']['page_status'] ?? 'publish', 'post_modified_gmt' => '2026-10-01 10:00:00' ); }
function get_permalink() { return 'https://example.com/' . $GLOBALS['T']['slug'] . '/'; }
function home_url( $p = '' ) { return 'https://example.com' . $p; }
function admin_url( $p = '' ) { return 'https://example.com/wp-admin/' . $p; }
function user_trailingslashit( $s ) { return rtrim( $s, '/' ) . '/'; }
function get_stylesheet_directory() { return dirname( __DIR__ ); }
function get_stylesheet_directory_uri() { return 'https://example.com/wp-content/themes/remotive'; }
function wp_create_nonce() { return 'NONCE'; }
function nocache_headers() {}

// Transients: a plain array, enough for the setup lock.
if ( ! function_exists( 'get_transient' ) ) {
	function get_transient( $k ) { return $GLOBALS['T']['transients'][ $k ] ?? false; }
	function set_transient( $k, $v, $ttl = 0 ) { $GLOBALS['T']['transients'][ $k ] = $v; return true; }
	function delete_transient( $k ) { unset( $GLOBALS['T']['transients'][ $k ] ); return true; }
}
if ( ! defined( 'MINUTE_IN_SECONDS' ) ) { define( 'MINUTE_IN_SECONDS', 60 ); }
if ( ! function_exists( 'is_front_page' ) ) { function is_front_page() { return $GLOBALS['T']['front'] ?? false; } }
if ( ! function_exists( 'esc_textarea' ) ) { function esc_textarea( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); } }
if ( ! function_exists( 'add_meta_box' ) ) { function add_meta_box() {} }
if ( ! function_exists( 'wp_parse_url' ) ) { function wp_parse_url( $u, $c = -1 ) { return parse_url( $u, $c ); } }
if ( ! function_exists( 'wp_installing' ) ) { function wp_installing() { return false; } }
if ( ! function_exists( 'maybe_serialize' ) ) { function maybe_serialize( $v ) { return is_scalar( $v ) ? $v : serialize( $v ); } }
if ( ! function_exists( 'get_bloginfo' ) ) { function get_bloginfo( $k = '' ) { return 'Re:Motive Media'; } }
if ( ! function_exists( 'is_category' ) ) { function is_category() { return $GLOBALS['T']['category'] ?? false; } }
if ( ! function_exists( 'wp_strip_all_tags' ) ) { function wp_strip_all_tags( $s ) { return strip_tags( $s ); } }
