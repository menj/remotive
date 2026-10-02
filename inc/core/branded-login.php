<?php
/**
 * Branded login screen.
 *
 * Replaces the WordPress logo and styling on wp-login.php with the site's
 * own. Presentation only: this module deliberately contains no
 * authentication, rate limiting, URL rewriting or other hardening, because
 * those belong in a must-use plugin that keeps working when the theme is
 * switched or fails. If the site already runs such a plugin, this module
 * stands aside rather than fighting it for the same filters.
 *
 * Disabled by default. Enable it in Appearance -> Theme Options -> Display.
 *
 * @package Remotive
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the branded login should run for this request.
 *
 * Three conditions, all of which must hold:
 *
 * 1. The option is on. It ships off, so an existing site sees no change
 *    until someone chooses this.
 * 2. No login-branding plugin is already doing the job. The bundled MU
 *    plugin defines remotive_mu_branded_login(); if that is present it owns
 *    the login screen, and duplicating its filters would double the header
 *    text and fight over the same styles.
 * 3. The theme's logo asset is actually on disk, since a branded screen
 *    with a missing logo is worse than the default one.
 *
 * @return bool
 */
function remotive_branded_login_active() {
	static $active = null;

	if ( null !== $active ) {
		return $active;
	}

	$active = false;

	if ( '1' !== (string) remotive_get_theme_option( 'branded_login' ) ) {
		return false;
	}

	if ( function_exists( 'remotive_mu_branded_login' ) ) {
		return false;
	}

	$active = is_readable( get_stylesheet_directory() . '/assets/images/remotive-logo-168.png' );

	return $active;
}

/**
 * Load the login stylesheet and pass it the logo URL.
 */
function remotive_branded_login_assets() {
	if ( ! remotive_branded_login_active() ) {
		return;
	}

	$path = get_stylesheet_directory() . '/assets/css/login.css';

	wp_enqueue_style(
		'remotive-login',
		get_stylesheet_directory_uri() . '/assets/css/login.css',
		array(),
		file_exists( $path ) ? filemtime( $path ) : '1.0.0'
	);

	// The only value that cannot live in the stylesheet, since it depends on
	// the install's own URL. AVIF where the request accepts it, as elsewhere.
	$logo = function_exists( 'remotive_hero_mark_is_avif' ) && remotive_hero_mark_is_avif()
		? 'remotive-logo-168.avif'
		: 'remotive-logo-168.png';

	wp_add_inline_style(
		'remotive-login',
		':root{--rm-login-logo:url("' . esc_url( get_stylesheet_directory_uri() . '/assets/images/' . $logo ) . '");}'
	);
}
add_action( 'login_enqueue_scripts', 'remotive_branded_login_assets' );

/**
 * Point the login logo at the site rather than wordpress.org.
 *
 * @param string $url Default URL.
 * @return string
 */
function remotive_branded_login_header_url( $url ) {
	return remotive_branded_login_active() ? home_url( '/' ) : $url;
}
add_filter( 'login_headerurl', 'remotive_branded_login_header_url' );

/**
 * Use the site name as the logo's accessible name.
 *
 * @param string $text Default text.
 * @return string
 */
function remotive_branded_login_header_text( $text ) {
	return remotive_branded_login_active() ? get_bloginfo( 'name' ) : $text;
}
add_filter( 'login_headertext', 'remotive_branded_login_header_text' );

/**
 * A short line above the form explaining whose login this is.
 *
 * Only added to the login form itself: the password reset and registration
 * screens use the same filter, and the line would be wrong there.
 *
 * @param string $message Existing message markup.
 * @return string
 */
function remotive_branded_login_message( $message ) {
	if ( ! remotive_branded_login_active() ) {
		return $message;
	}

	$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : 'login'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( 'login' !== $action ) {
		return $message;
	}

	$subtitle = remotive_get_theme_option( 'branded_login_message' );

	if ( '' === trim( (string) $subtitle ) ) {
		return $message;
	}

	return '<p class="remotive-login-subtitle">' . esc_html( $subtitle ) . '</p>' . $message;
}
add_filter( 'login_message', 'remotive_branded_login_message' );
