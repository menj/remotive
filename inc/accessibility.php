<?php
/**
 * Accessibility corrections that belong in PHP rather than markup.
 *
 * The theme's templates are block markup, so a defect in what a core block
 * renders cannot be fixed by editing a template. These filters correct the
 * rendered output at its source, which keeps the fix in one place for every
 * template that uses the block.
 *
 * @package Remotive
 */

defined( 'ABSPATH' ) || exit;

/**
 * Give linked featured images an accessible name.
 *
 * The post-featured-image block is rendered with isLink enabled on the blog,
 * archive and search templates. When the attachment has no alt text, which is
 * the normal case for a decorative card image, the resulting link has no
 * discernible text at all: a screen-reader user hears an unlabelled link with
 * no way to know where it goes. WCAG 2.4.4 and 4.1.2 both fail.
 *
 * The fix sets the alt text to the post title, which is the honest
 * description of where the link goes. It is applied only when the alt is
 * genuinely empty, so a real description entered in the media library always
 * wins, and only when the image is a post thumbnail, so images placed in
 * content keep their intended decorative status.
 *
 * @param array   $attr       Image attributes.
 * @param WP_Post $attachment Attachment post object.
 * @param string  $size       Requested size.
 * @return array
 */
function remotive_featured_image_alt( $attr, $attachment, $size ) {
	unset( $size );

	if ( ! empty( $attr['alt'] ) ) {
		return $attr;
	}

	$parent = isset( $attachment->post_parent ) ? (int) $attachment->post_parent : 0;

	// In a query loop the current post is the reliable owner; fall back to
	// the attachment's parent outside the loop.
	$post_id = get_the_ID();

	if ( ! $post_id || ! has_post_thumbnail( $post_id ) || get_post_thumbnail_id( $post_id ) !== $attachment->ID ) {
		$post_id = $parent;
	}

	if ( ! $post_id ) {
		return $attr;
	}

	$title = get_the_title( $post_id );

	if ( '' === trim( (string) $title ) ) {
		return $attr;
	}

	$attr['alt'] = $title;

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'remotive_featured_image_alt', 10, 3 );

/**
 * Mark the current page in the navigation.
 *
 * Core adds the current-menu-item class, which is visual only. Exposing
 * aria-current lets assistive technology announce which item is the current
 * page (WCAG 4.1.2), matching what sighted users read from the underline.
 *
 * @param string $output Navigation link block markup.
 * @param array  $block  Parsed block.
 * @return string
 */
function remotive_nav_aria_current( $output, $block ) {
	unset( $block );

	if ( false === strpos( $output, 'current-menu-item' ) || false !== strpos( $output, 'aria-current' ) ) {
		return $output;
	}

	return preg_replace( '/(<a\b)/', '$1 aria-current="page"', $output, 1 );
}
add_filter( 'render_block_core/navigation-link', 'remotive_nav_aria_current', 10, 2 );
