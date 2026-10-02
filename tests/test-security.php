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

t_done( 'security' );
