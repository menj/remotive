<?php
/**
 * A card with no featured image still gets a picture area.
 *
 * The Insights, archive and search cards use the post-featured-image block, which
 * prints nothing when a post has no image, leaving the card with a bare text
 * block next to its neighbours' photographs. This prints a branded gradient of
 * the same shape instead (assets/css/blog-and-about.css), so a new article an
 * editor publishes without an image does not look broken. A post with an image
 * is untouched.
 *
 * @package Remotive
 */

defined( 'ABSPATH' ) || exit;

/**
 * @param string $content Rendered block.
 * @param array  $block   Parsed block.
 * @return string
 */
function remotive_post_image_fallback( $content, $block ) {
	if ( '' !== trim( (string) $content ) || empty( $block['attrs']['className'] ) || false === strpos( (string) $block['attrs']['className'], 'rm-post-card__image' ) ) {
		return $content;
	}

	$link = get_permalink();

	return '<div class="wp-block-post-featured-image rm-post-card__image rm-post-card__image--empty">'
		. ( $link ? '<a href="' . esc_url( $link ) . '" tabindex="-1" aria-hidden="true"></a>' : '' )
		. '</div>';
}
add_filter( 'render_block_core/post-featured-image', 'remotive_post_image_fallback', 10, 2 );
