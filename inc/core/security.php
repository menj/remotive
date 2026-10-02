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
   0. CLIENT ADDRESS
   ======================================================================== */

/**
 * Proxy address ranges whose forwarded-client header is believed.
 *
 * Cloudflare's published ranges (https://www.cloudflare.com/ips/), as of
 * 2026-10. A request is only treated as coming through Cloudflare when its
 * connecting address (REMOTE_ADDR, which a visitor cannot forge) is inside
 * one of these, so a visitor cannot claim an address by sending the header
 * themselves. A site behind a different proxy adds its ranges with this
 * filter; if Cloudflare changes its ranges the effect is only that the
 * connecting address is used, as before.
 *
 * @return string[] CIDR ranges.
 */
function remotive_trusted_proxy_ranges() {
	return (array) apply_filters(
		'remotive_trusted_proxy_ranges',
		array(
			'173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22',
			'141.101.64.0/18', '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20',
			'197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
			'104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
			'2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32',
			'2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32',
		)
	);
}

/**
 * Whether an IPv4 or IPv6 address falls inside a CIDR range.
 *
 * @param string $ip   Address.
 * @param string $cidr Range, e.g. 173.245.48.0/20.
 * @return bool
 */
function remotive_ip_in_range( $ip, $cidr ) {
	if ( false === strpos( $cidr, '/' ) ) {
		return false;
	}

	list( $subnet, $bits ) = explode( '/', $cidr, 2 );

	$ip_bin     = @inet_pton( $ip ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	$subnet_bin = @inet_pton( $subnet ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	$bits       = (int) $bits;

	// Different families never match; a malformed value never matches.
	if ( false === $ip_bin || false === $subnet_bin || strlen( $ip_bin ) !== strlen( $subnet_bin ) || $bits < 0 || $bits > 8 * strlen( $ip_bin ) ) {
		return false;
	}

	$bytes = intdiv( $bits, 8 );
	if ( substr( $ip_bin, 0, $bytes ) !== substr( $subnet_bin, 0, $bytes ) ) {
		return false;
	}

	$rest = $bits % 8;
	if ( 0 === $rest ) {
		return true;
	}

	$mask = ( 0xFF << ( 8 - $rest ) ) & 0xFF;

	return ( ord( $ip_bin[ $bytes ] ) & $mask ) === ( ord( $subnet_bin[ $bytes ] ) & $mask );
}

/**
 * The address the rate limits and the enquiry record treat as the visitor's.
 *
 * REMOTE_ADDR is the only value a visitor cannot forge, so it is the default.
 * Behind Cloudflare it is Cloudflare's address, which would make every
 * visitor share one login lockout and one form quota, so when the connection
 * really is from a Cloudflare range the CF-Connecting-IP header it sets is
 * used instead (see remotive_trusted_proxy_ranges()). Other setups can map the
 * address with the 'remotive_client_ip' filter.
 *
 * Anything that is not a valid IP address comes back as an empty string.
 *
 * @return string
 */
function remotive_client_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

	if ( '' !== $ip && ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
		$forwarded = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );

		if ( false !== filter_var( $forwarded, FILTER_VALIDATE_IP ) ) {
			foreach ( remotive_trusted_proxy_ranges() as $range ) {
				if ( remotive_ip_in_range( $ip, $range ) ) {
					$ip = $forwarded;
					break;
				}
			}
		}
	}

	$ip = (string) apply_filters( 'remotive_client_ip', $ip );

	return false !== filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '';
}


/* ========================================================================
   1. LOGIN RATE LIMITING
   ======================================================================== */

/**
 * Track failed login attempts per IP.
 */
function remotive_track_login_failure( $username ) {
	$ip = remotive_client_ip();
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

	$ip = remotive_client_ip();
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
	$ip = remotive_client_ip();
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
 * Whether a route or request string addresses the REST users collection.
 *
 * Decodes up to twice, so %2F and %252F spellings are caught as well.
 *
 * @param string $value A REST route or a request URI.
 * @return bool
 */
function remotive_is_users_rest_route( $value ) {
	$value = rawurldecode( rawurldecode( (string) $value ) );

	return (bool) preg_match( '#/wp/v2/users(?![A-Za-z0-9_-])#i', $value );
}

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

	// The route WordPress resolved, plus the raw request, because the route can
	// also arrive as ?rest_route=%2Fwp%2Fv2%2Fusers, which a plain match on
	// the URL misses.
	$candidates = array();
	if ( isset( $GLOBALS['wp'] ) && is_object( $GLOBALS['wp'] ) && ! empty( $GLOBALS['wp']->query_vars['rest_route'] ) ) {
		$candidates[] = (string) $GLOBALS['wp']->query_vars['rest_route'];
	}
	if ( isset( $_SERVER['REQUEST_URI'] ) ) {
		$candidates[] = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
	}

	foreach ( $candidates as $candidate ) {
		if ( remotive_is_users_rest_route( $candidate ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to list users.', 'remotive' ),
				array( 'status' => 401 )
			);
		}
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
	// A name that exists redirects and one that does not used to return a 404,
	// which told a visitor which usernames are real. Redirect on the request
	// itself (?author=, ?author_name=, /author/name/) whether or not it matched.
	if ( isset( $_GET['author'] ) || isset( $_GET['author_name'] ) || is_author() || '' !== (string) get_query_var( 'author_name' ) || '' !== (string) get_query_var( 'author' ) ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'remotive_block_author_enumeration' );


/**
 * Keep the users sitemap out: core lists every author's archive URL, which
 * includes the username slug.
 *
 * @param object|false $provider Sitemap provider.
 * @param string       $name     Provider name.
 * @return object|false
 */
function remotive_remove_users_sitemap( $provider, $name ) {
	return 'users' === $name ? false : $provider;
}
add_filter( 'wp_sitemaps_add_provider', 'remotive_remove_users_sitemap', 10, 2 );

/**
 * One message for every failed sign-in, so the login form does not say whether
 * the username or the password was the wrong half.
 *
 * Lost-password messages are core's and are left as they are.
 *
 * @param WP_Error $errors Errors for the login screen.
 * @return WP_Error
 */
function remotive_generic_login_errors( $errors ) {
	if ( ! is_wp_error( $errors ) ) {
		return $errors;
	}

	$codes = array( 'invalid_username', 'invalid_email', 'incorrect_password' );

	if ( array_intersect( $codes, $errors->get_error_codes() ) ) {
		foreach ( $codes as $code ) {
			$errors->remove( $code );
		}
		$errors->add( 'remotive_login_failed', __( '<strong>Error:</strong> The username or password is incorrect.', 'remotive' ) );
	}

	return $errors;
}
add_filter( 'wp_login_errors', 'remotive_generic_login_errors' );


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
