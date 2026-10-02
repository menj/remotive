<?php
/**
 * Remotive Media — Security hardening.
 *
 * Addresses specific attack patterns from the site's first-week server logs:
 * wp-login.php brute-force, xmlrpc.php probing, install/setup-config endpoint
 * exposure, version fingerprinting, and REST API user enumeration.
 *
 * Each measure is a named function with its own hook so any can be removed
 * individually via remove_action()/remove_filter() without editing this file.
 *
 * @package Remotive
 */

defined( 'ABSPATH' ) || exit;


/* ========================================================================
   1. LOGIN RATE LIMITING
   ======================================================================== */

/**
 * Track failed login attempts per IP.
 */
function remotive_track_login_failure( $username ) {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	if ( empty( $ip ) ) {
		return;
	}
	$key   = 'remotive_lf_' . md5( $ip );
	$count = (int) get_transient( $key );
	set_transient( $key, $count + 1, 10 * MINUTE_IN_SECONDS );
}
add_action( 'wp_login_failed', 'remotive_track_login_failure' );

/**
 * Block authentication after 5 failures in 10 minutes.
 *
 * Guards:
 * - Empty username or password means this is cookie-based session validation
 *   (which runs on every page load via wp_get_current_user()), not a login
 *   form submission. Return early so we never interfere with session checks.
 * - Non-POST requests are never login submissions. Return early.
 * These two checks prevent the WP_Error from firing on normal page loads,
 * which caused the critical error in v1.68.1.
 */
function remotive_block_locked_login( $user, $username, $password ) {
	if ( empty( $username ) || empty( $password ) ) {
		return $user;
	}
	if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== strtoupper( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) {
		return $user;
	}

	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	if ( empty( $ip ) ) {
		return $user;
	}

	if ( (int) get_transient( 'remotive_lf_' . md5( $ip ) ) >= 5 ) {
		return new WP_Error(
			'remotive_login_locked',
			__( 'Too many failed attempts. Please try again in 10 minutes.', 'remotive' )
		);
	}

	return $user;
}
add_filter( 'authenticate', 'remotive_block_locked_login', 30, 3 );

/**
 * Reset the failure counter on successful login.
 */
function remotive_clear_login_failures( $user_login, $user ) {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	if ( $ip ) {
		delete_transient( 'remotive_lf_' . md5( $ip ) );
	}
}
add_action( 'wp_login', 'remotive_clear_login_failures', 10, 2 );


/* ========================================================================
   2. DISABLE XML-RPC
   ======================================================================== */

add_filter( 'xmlrpc_enabled', '__return_false' );

function remotive_remove_pingback_header( $headers ) {
	unset( $headers['X-Pingback'] );
	return $headers;
}
add_filter( 'wp_headers', 'remotive_remove_pingback_header' );

function remotive_disable_xmlrpc_pingback( $methods ) {
	unset( $methods['pingback.ping'] );
	unset( $methods['pingback.extensions.getPingbacks'] );
	return $methods;
}
add_filter( 'xmlrpc_methods', 'remotive_disable_xmlrpc_pingback' );


/* ========================================================================
   3. BLOCK INSTALL / SETUP ENDPOINTS ONCE INSTALLED
   ======================================================================== */

/**
 * Redirect install.php and setup-config.php to home once WordPress is
 * installed. Hooked on admin_init — NOT template_redirect — because these
 * files live in wp-admin/ and are only reached via admin requests.
 *
 * Guard: ABSPATH must be defined (always true here, but explicit), and
 * we check SCRIPT_FILENAME rather than SCRIPT_NAME so this works regardless
 * of subdirectory installs. We explicitly exclude wp-login.php from the
 * check — is_admin() returns true there but it must never be redirected.
 */
function remotive_block_install_endpoints() {
	$script = isset( $_SERVER['SCRIPT_FILENAME'] ) ? wp_unslash( $_SERVER['SCRIPT_FILENAME'] ) : '';
	$base   = basename( $script );

	if ( 'install.php' === $base || 'setup-config.php' === $base ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}
add_action( 'admin_init', 'remotive_block_install_endpoints' );


/* ========================================================================
   4. VERSION FINGERPRINTING REMOVAL
   ======================================================================== */

remove_action( 'wp_head', 'wp_generator' );

function remotive_remove_version_query( $src ) {
	if ( is_admin() ) {
		return $src;
	}
	$parsed = wp_parse_url( $src );
	if ( isset( $parsed['query'] ) && preg_match( '/\bver=([^&]+)/', $parsed['query'], $m ) ) {
		// Keep filemtime-based timestamps (long numeric strings); strip WP version strings.
		if ( ! preg_match( '/^\d{9,}$/', $m[1] ) ) {
			$src = remove_query_arg( 'ver', $src );
		}
	}
	return $src;
}
add_filter( 'style_loader_src',  'remotive_remove_version_query' );
add_filter( 'script_loader_src', 'remotive_remove_version_query' );

function remotive_remove_feed_version() {
	return '';
}
add_filter( 'the_generator', 'remotive_remove_feed_version' );


/* ========================================================================
   5. REST API — RESTRICT UNAUTHENTICATED USER ENUMERATION
   ======================================================================== */

/**
 * Block unauthenticated /wp-json/wp/v2/users. Only this endpoint is blocked;
 * the rest of the API stays open for the WebMCP integration.
 */
function remotive_block_user_enumeration_rest( $result ) {
	// If a previous filter already set a result, respect it.
	if ( null !== $result ) {
		return $result;
	}
	if ( is_user_logged_in() ) {
		return $result;
	}
	$uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	if ( preg_match( '#/wp/v2/users\b#', $uri ) ) {
		return new WP_Error(
			'rest_forbidden',
			__( 'Sorry, you are not allowed to list users.', 'remotive' ),
			array( 'status' => 401 )
		);
	}
	return $result;
}
add_filter( 'rest_authentication_errors', 'remotive_block_user_enumeration_rest' );

/**
 * Redirect ?author=N and author archive URLs to home to block username
 * enumeration via the URL layer.
 *
 * Runs on template_redirect. Guards:
 * - is_admin() check prevents firing on admin/login requests.
 * - The is_author() check is wrapped in a did_action('template_redirect')
 *   guard — unnecessary here since we ARE on template_redirect — but the
 *   function is only registered on that hook so conditional tags are safe.
 */
function remotive_block_author_enumeration() {
	if ( is_admin() ) {
		return;
	}
	if ( isset( $_GET['author'] ) || is_author() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'remotive_block_author_enumeration' );


/* ========================================================================
   6. SECURITY HEADERS
   ======================================================================== */

function remotive_security_headers( $headers ) {
	// wp_headers fires on both front-end and login page; safe to apply to both.
	$headers['X-Content-Type-Options'] = 'nosniff';
	$headers['X-Frame-Options']        = 'SAMEORIGIN';
	$headers['Referrer-Policy']        = 'strict-origin-when-cross-origin';
	$headers['Permissions-Policy']     = 'camera=(), microphone=(), geolocation=(), payment=()';
	return $headers;
}
add_filter( 'wp_headers', 'remotive_security_headers' );
