<?php
/**
 * Remotive Media — progressive WebMCP integration.
 *
 * WebMCP is an experimental browser API. This module exposes a small set of
 * public, read-only site actions and loads a client-side adapter only on the
 * front end. The website remains fully functional when the API is absent.
 */

defined( 'ABSPATH' ) || exit;

/** Resolve a public page URL without assuming the page exists at a fixed slug. */
function remotive_webmcp_page_url( $slug ) {
	$page = get_page_by_path( $slug, OBJECT, 'page' );

	return $page instanceof WP_Post ? get_permalink( $page ) : home_url( '/' . trim( $slug, '/' ) . '/' );
}

/** Register the public search endpoint used by the in-page WebMCP tool. */
function remotive_webmcp_register_rest_routes() {
	register_rest_route(
		'remotive/v1',
		'/search',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'remotive_webmcp_search',
			'permission_callback' => '__return_true',
			'args'                => array(
				'query' => array(
					'required'          => true,
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => static function ( $value ) {
						return is_string( $value ) && '' !== trim( $value ) && strlen( $value ) <= 200;
					},
				),
				'limit' => array(
					'default'           => 5,
					'sanitize_callback' => 'absint',
					'validate_callback' => static function ( $value ) {
						$value = (int) $value;
						return $value >= 1 && $value <= 10;
					},
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'remotive_webmcp_register_rest_routes' );

/** Search published posts and pages and return a deliberately small payload. */
function remotive_webmcp_search( WP_REST_Request $request ) {
	$query = new WP_Query(
		array(
			's'                      => $request->get_param( 'query' ),
			'post_type'              => array( 'post', 'page' ),
			'post_status'            => 'publish',
			// Public and unauthenticated: never return a password-protected post
			// (its excerpt would leak) or a page that is deliberately unlisted.
			'has_password'           => false,
			'post__not_in'           => function_exists( 'remotive_unlisted_page_ids' ) ? remotive_unlisted_page_ids() : array(),
			'posts_per_page'         => (int) $request->get_param( 'limit' ),
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	$results = array();

	foreach ( $query->posts as $post ) {
		$summary   = has_excerpt( $post ) ? $post->post_excerpt : wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 30 );
		$results[] = array(
			'id'      => (int) $post->ID,
			'title'   => html_entity_decode( get_the_title( $post ), ENT_QUOTES, get_bloginfo( 'charset' ) ),
			'url'     => get_permalink( $post ),
			'type'    => get_post_type( $post ),
			'summary' => $summary,
		);
	}

	return rest_ensure_response(
		array(
			'query'   => $request->get_param( 'query' ),
			'count'   => count( $results ),
			'results' => $results,
		)
	);
}

/** Load the browser adapter and pass it same-origin endpoints only. */
function remotive_webmcp_enqueue_assets() {
	$script_path = get_stylesheet_directory() . '/assets/js/webmcp.js';

	wp_enqueue_script(
		'remotive-webmcp',
		get_stylesheet_directory_uri() . '/assets/js/webmcp.js',
		array(),
		file_exists( $script_path ) ? filemtime( $script_path ) : '1.0.0',
		true
	);
	wp_script_add_data( 'remotive-webmcp', 'strategy', 'defer' );

	$config = array(
		'searchEndpoint' => rest_url( 'remotive/v1/search' ),
		'homeUrl'        => home_url( '/' ),
		'servicesUrl'    => remotive_webmcp_page_url( 'services' ),
		'workUrl'        => remotive_webmcp_page_url( 'case-studies' ),
		'contactUrl'     => remotive_webmcp_page_url( 'contact' ),
		'blogUrl'        => get_option( 'page_for_posts' ) ? get_permalink( (int) get_option( 'page_for_posts' ) ) : home_url( '/blog/' ),
	);

	wp_add_inline_script(
		'remotive-webmcp',
		'window.RemotiveWebMCP = ' . wp_json_encode( $config ) . ';',
		'before'
	);
}
add_action( 'wp_enqueue_scripts', 'remotive_webmcp_enqueue_assets', 22 );
