<?php
/**
 * Article page helpers (templates/single.html, assets/css/article.css).
 *
 * Reading time on the date line, and a related-posts query that leaves out
 * the article being read. Nothing here changes stored content.
 *
 * @package Remotive
 */

defined( 'ABSPATH' ) || exit;

/**
 * Minutes needed to read a post, at 220 words a minute, never less than one.
 *
 * @param int|WP_Post|null $post Post, or null for the current post.
 * @return int
 */
function remotive_reading_minutes( $post = null ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return 1;
	}

	$text  = wp_strip_all_tags( strip_shortcodes( (string) $post->post_content ) );
	$words = str_word_count( $text );

	return max( 1, (int) ceil( $words / 220 ) );
}

/**
 * Adds "N min read" after the date on the article header.
 *
 * @param string $content Rendered block.
 * @param array  $block   Parsed block.
 * @return string
 */
function remotive_article_reading_time( $content, $block ) {
	$class = isset( $block['attrs']['className'] ) ? (string) $block['attrs']['className'] : '';

	if ( ! is_singular( 'post' ) || false === strpos( $class, 'rm-art-date' ) || '' === trim( (string) $content ) ) {
		return $content;
	}

	$minutes = remotive_reading_minutes();
	/* translators: %d: whole minutes. */
	$label = sprintf( _n( '%d min read', '%d min read', $minutes, 'remotive' ), $minutes );

	return preg_replace(
		'#</div>\s*$#',
		'<span class="rm-art-read">' . esc_html( $label ) . '</span></div>',
		$content,
		1
	);
}
add_filter( 'render_block_core/post-date', 'remotive_article_reading_time', 10, 2 );

/**
 * A Query Loop with the class "rm-related" skips the article being read.
 *
 * @param array    $query Query vars.
 * @param WP_Block $block The Query Loop block.
 * @return array
 */
function remotive_related_query_vars( $query, $block ) {
	$class = isset( $block->parsed_block['attrs']['className'] ) ? (string) $block->parsed_block['attrs']['className'] : '';

	if ( false !== strpos( $class, 'rm-related' ) && is_singular( 'post' ) ) {
		$query['post__not_in'] = array_merge( (array) ( $query['post__not_in'] ?? array() ), array( get_the_ID() ) );
	}

	return $query;
}
add_filter( 'query_loop_block_query_vars', 'remotive_related_query_vars', 10, 2 );

/**
 * The address a share button opens, for the article being rendered: WhatsApp, LinkedIn or X.
 *
 * Built on the server so the links are real before assets/js/article.js runs (it sets the same
 * addresses) and for a reader without it. Outside an article the address is the service's own front.
 *
 * @param string $network 'whatsapp', 'linkedin' or 'x'.
 * @return string
 */
function remotive_share_link( $network ) {
	$url   = is_singular() ? (string) get_permalink() : home_url( '/' );
	$title = is_singular() ? html_entity_decode( wp_strip_all_tags( get_the_title() ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) : get_bloginfo( 'name' );

	switch ( $network ) {
		case 'whatsapp':
			return 'https://wa.me/?text=' . rawurlencode( $title . ' ' . $url );
		case 'linkedin':
			return 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $url );
		case 'x':
			return 'https://x.com/intent/post?text=' . rawurlencode( $title ) . '&url=' . rawurlencode( $url );
	}

	return $url;
}
