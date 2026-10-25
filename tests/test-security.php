<?php
/**
 * Security hardening: client address, REST users route, generic login errors,
 * enquiry length caps. Run: php tests/test-security.php
 */

require __DIR__ . '/bootstrap.php';
require dirname( __DIR__ ) . '/inc/core/security.php';
require dirname( __DIR__ ) . '/inc/forms/lead-form-handler.php';

// Client address: REMOTE_ADDR, validated; junk becomes empty.
$_SERVER['REMOTE_ADDR'] = '203.0.113.9';
t_eq( remotive_client_ip(), '203.0.113.9', 'IPv4 accepted' );
$_SERVER['REMOTE_ADDR'] = '2001:db8::1';
t_eq( remotive_client_ip(), '2001:db8::1', 'IPv6 accepted' );
$_SERVER['REMOTE_ADDR'] = 'not-an-ip';
t_eq( remotive_client_ip(), '', 'invalid address rejected' );
unset( $_SERVER['REMOTE_ADDR'] );
t_eq( remotive_client_ip(), '', 'missing address is empty' );

// Behind Cloudflare: the forwarded address is believed only from a Cloudflare range.
$_SERVER['REMOTE_ADDR']             = '172.68.10.5';   // inside 172.64.0.0/13
$_SERVER['HTTP_CF_CONNECTING_IP']   = '198.51.100.23';
t_eq( remotive_client_ip(), '198.51.100.23', 'forwarded address used from a Cloudflare range' );
$_SERVER['REMOTE_ADDR']             = '2606:4700::1111';
t_eq( remotive_client_ip(), '198.51.100.23', 'forwarded address used from a Cloudflare IPv6 range' );
$_SERVER['REMOTE_ADDR']             = '203.0.113.9';   // not Cloudflare: the header is a forgery
t_eq( remotive_client_ip(), '203.0.113.9', 'forged header ignored from an ordinary address' );
$_SERVER['REMOTE_ADDR']             = '172.68.10.5';
$_SERVER['HTTP_CF_CONNECTING_IP']   = 'junk, 1.2.3.4';
t_eq( remotive_client_ip(), '172.68.10.5', 'invalid forwarded value ignored' );
$_SERVER['REMOTE_ADDR']             = '172.80.0.1';    // just outside 172.64.0.0/13
$_SERVER['HTTP_CF_CONNECTING_IP']   = '198.51.100.23';
t_eq( remotive_client_ip(), '172.80.0.1', 'address just outside the range is not trusted' );
unset( $_SERVER['HTTP_CF_CONNECTING_IP'] );

t_ok( remotive_ip_in_range( '173.245.63.255', '173.245.48.0/20' ), 'top of a /20 is inside' );
t_ok( ! remotive_ip_in_range( '173.245.64.0', '173.245.48.0/20' ), 'next address is outside' );
t_ok( ! remotive_ip_in_range( '2606:4700::1', '173.245.48.0/20' ), 'IPv6 never matches an IPv4 range' );
t_ok( ! remotive_ip_in_range( '1.2.3.4', 'garbage' ), 'malformed range never matches' );

// The users route in every spelling, never other routes.
foreach ( array(
	'/wp-json/wp/v2/users',
	'/wp-json/wp/v2/users/1',
	'/wp-json/wp/v2/users?per_page=100',
	'/?rest_route=/wp/v2/users',
	'/?rest_route=%2Fwp%2Fv2%2Fusers',
	'/?rest_route=%252Fwp%252Fv2%252Fusers',
	'/wp/v2/USERS',
) as $uri ) {
	t_ok( remotive_is_users_rest_route( $uri ), "users route blocked: $uri" );
}
foreach ( array( '/wp-json/wp/v2/posts', '/wp-json/wp/v2/users-extra', '/wp-json/remotive/v1/search', '/' ) as $uri ) {
	t_ok( ! remotive_is_users_rest_route( $uri ), "other route allowed: $uri" );
}

// Filter: anonymous blocked on the encoded form, logged-in left alone.
t_reset();
$_SERVER['REQUEST_URI'] = '/?rest_route=%2Fwp%2Fv2%2Fusers';
t_ok( remotive_block_user_enumeration_rest( null ) instanceof WP_Error, 'anonymous encoded users request blocked' );
$GLOBALS['T']['logged_in'] = true;
t_eq( remotive_block_user_enumeration_rest( null ), null, 'logged-in user not blocked' );
$GLOBALS['T']['logged_in'] = false;
$_SERVER['REQUEST_URI'] = '/wp-json/wp/v2/posts';
t_eq( remotive_block_user_enumeration_rest( null ), null, 'posts route not blocked' );

// Login errors: wrong user and wrong password read the same.
$a = new WP_Error( 'invalid_username', 'Unknown username.' );
$b = new WP_Error( 'incorrect_password', 'Wrong password.' );
$a = remotive_generic_login_errors( $a );
$b = remotive_generic_login_errors( $b );
t_eq( $a->errors, $b->errors, 'unknown user and wrong password give the same error' );
t_eq( $a->get_error_codes(), array( 'remotive_login_failed' ), 'only the generic code remains' );
$other = remotive_generic_login_errors( new WP_Error( 'remotive_login_locked', 'Locked.' ) );
t_eq( $other->get_error_codes(), array( 'remotive_login_locked' ), 'lockout message left as it was' );

// Users sitemap removed, others kept.
t_eq( remotive_remove_users_sitemap( 'p', 'users' ), false, 'users sitemap removed' );
t_eq( remotive_remove_users_sitemap( 'p', 'posts' ), 'p', 'posts sitemap kept' );

// Enquiry length caps, by characters not bytes.
t_eq( remotive_limit_text( str_repeat( 'a', 300 ), REMOTIVE_LEAD_MAX_NAME ), str_repeat( 'a', 200 ), 'long name cut' );
t_eq( remotive_limit_text( str_repeat( '中', 6000 ), REMOTIVE_LEAD_MAX_MESSAGE ), str_repeat( '中', 5000 ), 'long multibyte message cut by characters' );
t_eq( remotive_limit_text( 'short', 200 ), 'short', 'short text untouched' );

// Only the path and the rest_route argument name a route: a search for the users path on another route is
// an ordinary request.
t_eq( remotive_rest_route_candidates( '/wp-json/wp/v2/posts?search=%2Fwp%2Fv2%2Fusers' ), array( '/wp-json/wp/v2/posts' ), 'the rest of the query string is not a route' );
t_eq( remotive_rest_route_candidates( '/?rest_route=%2Fwp%2Fv2%2Fusers&per_page=5' ), array( '/', '/wp/v2/users' ), 'the rest_route argument is a route' );
t_reset();
$_SERVER['REQUEST_URI'] = '/wp-json/wp/v2/posts?search=/wp/v2/users';
t_eq( remotive_block_user_enumeration_rest( null ), null, 'a posts search mentioning the users path is allowed' );
$_SERVER['REQUEST_URI'] = '/wp-json/wp/v2/users';
t_ok( remotive_block_user_enumeration_rest( null ) instanceof WP_Error, 'the users path is still blocked' );

// Dispatch: the embedded author request of ?_embed goes through here, not through the authentication filter.
class T_Request { public $route; public function __construct( $r ) { $this->route = $r; } public function get_route() { return $this->route; } }
t_ok( remotive_block_user_enumeration_dispatch( null, null, new T_Request( '/wp/v2/users/1' ) ) instanceof WP_Error, 'an embedded users request is blocked at dispatch' );
t_eq( remotive_block_user_enumeration_dispatch( null, null, new T_Request( '/wp/v2/posts' ) ), null, 'other routes dispatch as normal' );
t_eq( remotive_block_user_enumeration_dispatch( 'SET', null, new T_Request( '/wp/v2/users' ) ), 'SET', 'an earlier result is respected' );
$GLOBALS['T']['logged_in'] = true;
t_eq( remotive_block_user_enumeration_dispatch( null, null, new T_Request( '/wp/v2/users' ) ), null, 'a logged-in user is not blocked at dispatch' );
$GLOBALS['T']['logged_in'] = false;
t_ok( in_array( 'remotive_block_user_enumeration_dispatch', $GLOBALS['filters']['rest_pre_dispatch'] ?? array(), true ), 'the dispatch filter is registered' );

// The author redirect must run before redirect_canonical() (priority 10), which otherwise turns ?author=1
// into /author/<login>/ first.
t_eq( $GLOBALS['priorities']['template_redirect|remotive_block_author_enumeration'] ?? null, 0, 'the author redirect runs ahead of redirect_canonical' );

// Application passwords: blocked unless the Theme Options switch is turned off.
t_reset();
t_eq( remotive_filter_app_passwords( true ), false, 'application passwords are refused by default' );
$GLOBALS['T']['options']['remotive_theme_options'] = array( 'motion_effects' => '1' );
t_eq( remotive_filter_app_passwords( true ), false, 'and with options saved by an older version that never had the switch' );
$GLOBALS['T']['options']['remotive_theme_options'] = array( 'block_app_passwords' => '0' );
t_eq( remotive_filter_app_passwords( true ), true, 'switching the block off allows them' );
t_eq( remotive_filter_app_passwords( false ), false, 'but cannot allow what WordPress itself refuses' );
$GLOBALS['T']['options']['remotive_theme_options'] = array( 'block_app_passwords' => '1' );
t_eq( remotive_filter_app_passwords( true ), false, 'switched back on' );
t_ok( in_array( 'remotive_filter_app_passwords', $GLOBALS['filters']['wp_is_application_passwords_available'] ?? array(), true ), 'the filter is registered' );

t_done( 'security' );
