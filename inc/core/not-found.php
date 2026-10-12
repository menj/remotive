<?php
/**
 * Rescue for addresses that used to work or that tools guess.
 *
 * The server logs for October 2026 showed two families of 404 that a reader
 * would rather not meet:
 *
 * - Date style article addresses (/2026/09/seo-vs-sem/), requested by crawlers
 *   and old links. The article exists under its own permalink.
 * - /favicon.ico and the Apple touch icon names, which browsers and link
 *   previews request by habit. The site icon is set in the theme, so those
 *   requests are sent to it.
 *
 * A match is answered with a 301 to the real address, in the language of the
 * request. Anything else keeps the normal 404 page.
 *
 * @package Remotive
 */

defined( 'ABSPATH' ) || exit;

/**
 * Where an address that returned 404 should go, as a kind and a key.
 * Pure, so it can be tested.
 *
 * @param string $path Request path without the query string, no language prefix.
 * @return array{0:string,1:string}|null array( 'post', slug ) or array( 'icon', '' ); null for no match.
 */
function remotive_not_found_target( $path ) {
	$path = '/' . trim( (string) $path, '/' );

	if ( preg_match( '#^/(?:19|20)\d{2}/(?:0?[1-9]|1[0-2])/([a-z0-9][a-z0-9-]*)$#i', $path, $m ) ) {
		return array( 'post', strtolower( $m[1] ) );
	}

	if ( preg_match( '#^/(?:favicon\.(?:ico|png)|apple-touch-icon(?:-precomposed)?\.png)$#i', $path ) ) {
		return array( 'icon', '' );
	}

	return null;
}

/**
 * Send a matching 404 to the real address.
 */
function remotive_not_found_rescue() {
	if ( ! is_404() || is_admin() ) {
		return;
	}

	$path   = (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '', PHP_URL_PATH ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$target = remotive_not_found_target( $path );

	if ( ! $target ) {
		return;
	}

	$to = '';

	if ( 'post' === $target[0] ) {
		$posts = get_posts( array(
			'name'           => $target[1],
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
		) );

		if ( $posts ) {
			$to = (string) get_permalink( $posts[0] );
		}
	} elseif ( function_exists( 'get_site_icon_url' ) ) {
		$to = (string) get_site_icon_url( 180 );
	}

	if ( '' === $to ) {
		return;
	}

	if ( 'post' === $target[0] && function_exists( 'remotive_i18n_localise_url' ) && function_exists( 'remotive_i18n_lang' ) ) {
		$to = remotive_i18n_localise_url( $to, remotive_i18n_lang() );
	}

	wp_safe_redirect( $to, 301 );
	exit;
}
add_action( 'template_redirect', 'remotive_not_found_rescue', 1 );
