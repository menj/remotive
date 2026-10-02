<?php
/**
 * Serve AVIF where a companion file exists, with the original as fallback.
 *
 * The theme's templates are block markup, so there are no <img> tags to edit
 * by hand: WordPress renders them from the featured image and content
 * blocks. This module wraps those rendered tags in a <picture> element with
 * an AVIF <source> ahead of the untouched <img>, which is the markup-level
 * equivalent of editing the templates and behaves identically.
 *
 * Two rules keep it safe. An AVIF source is added only when the companion
 * file is actually on disk, so nothing ever points at a missing image. And
 * the <img> is passed through byte for byte, so its classes, IDs, sizes,
 * loading and decoding attributes, and any plugin's additions all survive;
 * a browser without AVIF support renders exactly what it rendered before.
 *
 * @package Remotive
 */

defined( 'ABSPATH' ) || exit;

/**
 * The AVIF companion URL for an image URL, when the file exists.
 *
 * Only local files are considered, resolved through the uploads directory
 * or the theme directory. A remote or unrecognised URL returns an empty
 * string rather than a guess.
 *
 * @param string $url Absolute image URL.
 * @return string Companion URL, or '' when there is none.
 */
function remotive_avif_companion_url( $url ) {
	static $cache = array();

	if ( ! is_string( $url ) || '' === $url ) {
		return '';
	}

	if ( isset( $cache[ $url ] ) ) {
		return $cache[ $url ];
	}

	$cache[ $url ] = '';

	if ( ! preg_match( '/\.(png|jpe?g)(\?.*)?$/i', $url ) ) {
		return '';
	}

	$clean = strtok( $url, '?' );
	$path  = '';

	$uploads = wp_get_upload_dir();

	if ( ! empty( $uploads['baseurl'] ) && 0 === strpos( $clean, $uploads['baseurl'] ) ) {
		$path = $uploads['basedir'] . substr( $clean, strlen( $uploads['baseurl'] ) );
	} elseif ( 0 === strpos( $clean, get_stylesheet_directory_uri() ) ) {
		$path = get_stylesheet_directory() . substr( $clean, strlen( get_stylesheet_directory_uri() ) );
	}

	if ( ! $path ) {
		return '';
	}

	$avif_path = preg_replace( '/\.(png|jpe?g)$/i', '.avif', $path );

	if ( $avif_path === $path || ! is_readable( $avif_path ) ) {
		return '';
	}

	$cache[ $url ] = preg_replace( '/\.(png|jpe?g)$/i', '.avif', $clean );

	return $cache[ $url ];
}

/**
 * Translate a srcset to its AVIF companions.
 *
 * All candidates must have companions; a partial set would let the browser
 * pick a width that does not exist as AVIF.
 *
 * @param string $srcset Original srcset attribute value.
 * @return string Companion srcset, or '' when it cannot be completed.
 */
function remotive_avif_companion_srcset( $srcset ) {
	if ( ! is_string( $srcset ) || '' === trim( $srcset ) ) {
		return '';
	}

	$out = array();

	foreach ( explode( ',', $srcset ) as $candidate ) {
		$parts = preg_split( '/\s+/', trim( $candidate ), 2 );

		if ( empty( $parts[0] ) ) {
			return '';
		}

		$avif = remotive_avif_companion_url( $parts[0] );

		if ( ! $avif ) {
			return '';
		}

		$out[] = isset( $parts[1] ) ? $avif . ' ' . $parts[1] : $avif;
	}

	return implode( ', ', $out );
}

/**
 * Wrap one rendered <img> tag in a <picture> with an AVIF source.
 *
 * @param string $html Image markup.
 * @return string Original markup, or the wrapped version.
 */
function remotive_avif_wrap_img( $html ) {
	if ( ! is_string( $html ) || false === strpos( $html, '<img' ) ) {
		return $html;
	}

	// Already inside a picture element, or several images: leave it alone
	// rather than guess at the author's intent.
	if ( false !== stripos( $html, '<picture' ) || substr_count( $html, '<img' ) > 1 ) {
		return $html;
	}

	if ( ! preg_match( '/<img[^>]+>/i', $html, $tag ) ) {
		return $html;
	}

	if ( ! preg_match( '/\ssrc=["\']([^"\']+)["\']/i', $tag[0], $src ) ) {
		return $html;
	}

	$avif_src = remotive_avif_companion_url( $src[1] );

	if ( ! $avif_src ) {
		return $html;
	}

	$attributes = ' srcset="' . esc_attr( $avif_src ) . '"';

	if ( preg_match( '/\ssrcset=["\']([^"\']+)["\']/i', $tag[0], $srcset ) ) {
		$avif_srcset = remotive_avif_companion_srcset( $srcset[1] );

		if ( $avif_srcset ) {
			$attributes = ' srcset="' . esc_attr( $avif_srcset ) . '"';
		}
	}

	if ( preg_match( '/\ssizes=["\']([^"\']+)["\']/i', $tag[0], $sizes ) ) {
		$attributes .= ' sizes="' . esc_attr( $sizes[1] ) . '"';
	}

	$source = '<source' . $attributes . ' type="image/avif">';

	// The img keeps its own markup untouched inside the picture element.
	return str_replace( $tag[0], '<picture>' . $source . $tag[0] . '</picture>', $html );
}

/**
 * Featured images, rendered by the post-featured-image block and by
 * the_post_thumbnail().
 *
 * @param string $html Thumbnail markup.
 * @return string
 */
function remotive_avif_post_thumbnail( $html ) {
	return remotive_avif_wrap_img( $html );
}
add_filter( 'post_thumbnail_html', 'remotive_avif_post_thumbnail' );

/**
 * Images inside post content.
 *
 * @param string $filtered_image The image tag.
 * @return string
 */
function remotive_avif_content_img_tag( $filtered_image ) {
	return remotive_avif_wrap_img( $filtered_image );
}
add_filter( 'wp_content_img_tag', 'remotive_avif_content_img_tag' );
