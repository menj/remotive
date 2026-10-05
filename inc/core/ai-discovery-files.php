<?php
/**
 * AI Discovery Files plugin (llms.txt and friends), 2.2.2: compatibility.
 *
 * What the plugin does that touches the theme:
 *  - It lists every published page, from get_pages(), in llms.txt, llms.html and
 *    ai.json. The landing pages and the confirmation pages are for ads only and
 *    must not be advertised to AI systems, so they are taken out of that list.
 *    The theme already hides them from get_pages() on the front end; this also
 *    covers a file built from wp-admin (the plugin caches what it builds), where
 *    that hiding is off.
 *  - It serves its files on template_redirect and appends to robots.txt at
 *    priority 20; the theme's own robots.txt line is at 99, so both stay.
 *  - It knows WPML and Polylang only. The theme's own language versions
 *    (/ms/, /zh-hans/, /zh-hant/) are not in its lists, which is correct for
 *    llms.txt (one list, English addresses; every page declares its translations
 *    with hreflang and in /sitemap-languages.xml).
 *
 * Without the plugin nothing here runs.
 *
 * @package Remotive
 */

defined( 'ABSPATH' ) || exit;

/**
 * Take the unlisted pages (landing and confirmation pages) out of the page list
 * the plugin hands its templates.
 *
 * @param array  $data      Collected data; $data['pages'] is a list of array( 'title' => ..., 'url' => ... ).
 * @param string $file_slug File being built.
 * @return array
 */
function remotive_aidf_template_data( $data, $file_slug = '' ) {
	if ( ! is_array( $data ) || empty( $data['pages'] ) || ! is_array( $data['pages'] ) || ! function_exists( 'remotive_unlisted_page_ids' ) ) {
		return $data;
	}

	$hide = array();

	foreach ( remotive_unlisted_page_ids() as $id ) {
		$hide[] = untrailingslashit( (string) get_permalink( $id ) );
		$hide[] = untrailingslashit( home_url( '/?page_id=' . (int) $id ) );
	}

	$hide = array_filter( $hide );

	$data['pages'] = array_values( array_filter( $data['pages'], function ( $page ) use ( $hide ) {
		$url = is_array( $page ) && isset( $page['url'] ) ? untrailingslashit( (string) $page['url'] ) : '';

		return '' === $url || ! in_array( $url, $hide, true );
	} ) );

	return $data;
}
add_filter( 'aidf_template_data', 'remotive_aidf_template_data', 10, 2 );

/**
 * Once, drop the plugin's cached files so a copy built before this existed, which
 * may still list a landing page, is rebuilt.
 */
function remotive_aidf_flush_once() {
	if ( '1' === get_option( 'remotive_aidf_flushed' ) || ! class_exists( 'AIDF_File_Cache' ) || ! method_exists( 'AIDF_File_Cache', 'invalidate' ) ) {
		return;
	}

	AIDF_File_Cache::invalidate();
	update_option( 'remotive_aidf_flushed', '1', false );
}
add_action( 'init', 'remotive_aidf_flush_once', 30 );
