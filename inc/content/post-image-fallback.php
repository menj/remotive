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
	$class = isset( $block['attrs']['className'] ) ? (string) $block['attrs']['className'] : '';

	if ( '' !== trim( (string) $content ) || '' === $class ) {
		return $content;
	}

	// The card image, and the small square beside each Recent posts entry in the sidebar.
	if ( false !== strpos( $class, 'rm-post-card__image' ) ) {
		$out = 'wp-block-post-featured-image rm-post-card__image rm-post-card__image--empty';
	} elseif ( false !== strpos( $class, 'rm-sidebar__recent-thumb' ) ) {
		$out = 'wp-block-post-featured-image rm-sidebar__recent-thumb rm-sidebar__recent-thumb--empty';
	} else {
		return $content;
	}

	$link = get_permalink();

	return '<div class="' . esc_attr( $out ) . '">'
		// The card title links to the same page, so this link is a duplicate target; it carries the title
		// as its name rather than being an empty anchor, and stays out of the tab order.
		. ( $link ? '<a href="' . esc_url( $link ) . '" tabindex="-1" aria-label="' . esc_attr( get_the_title() ) . '"></a>' : '' )
		. '</div>';
}
add_filter( 'render_block_core/post-featured-image', 'remotive_post_image_fallback', 10, 2 );
