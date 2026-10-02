<?php
/**
 * Remotive Media — One-page prototype (English / Bahasa Melayu / 简体中文).
 *
 * Templates/page-one-page.html holds the whole page; this file only loads its
 * stylesheet and script on pages that use it. The language switcher is
 * client-side (?lang=en|ms|zh, then the saved choice, then the browser
 * language); the server always renders English, so the page reads fully
 * without JavaScript.
 */

defined( 'ABSPATH' ) || exit;

function remotive_one_page_assets() {
	if ( ! is_page_template( 'page-one-page' ) ) {
		return;
	}

	$css = get_stylesheet_directory() . '/assets/css/one-page.css';
	$js  = get_stylesheet_directory() . '/assets/js/one-page.js';

	wp_enqueue_style(
		'remotive-one-page',
		get_stylesheet_directory_uri() . '/assets/css/one-page.css',
		array( 'remotive-style' ),
		file_exists( $css ) ? filemtime( $css ) : '1.0.0'
	);

	wp_enqueue_script(
		'remotive-one-page',
		get_stylesheet_directory_uri() . '/assets/js/one-page.js',
		array(),
		file_exists( $js ) ? filemtime( $js ) : '1.0.0',
		true
	);
	wp_script_add_data( 'remotive-one-page', 'strategy', 'defer' );
}
add_action( 'wp_enqueue_scripts', 'remotive_one_page_assets', 22 );
