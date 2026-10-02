<?php
/**
 * Publish the theme's shipped content on activation.
 *
 * The theme ships 23 written items — four Insights articles, six service
 * pages and thirteen case studies. Handing those over as an import file meant the
 * site was half-built until someone remembered to run the importer, so they
 * are created here instead, as part of the same setup pass that creates the
 * core pages.
 *
 * Rules this follows, in order of importance:
 *
 * - Create once, then never touch it again. Every item is matched by slug
 *   first; if a post or page with that slug exists, it is skipped entirely,
 *   whatever state it is in. Edits made in wp-admin always survive.
 * - Remember what was seeded. A slug is recorded on creation, so an item the
 *   site owner later deletes stays deleted rather than reappearing at the
 *   next setup run.
 * - Publish, don't draft. The point is a site that is complete on activation.
 * - Parents are resolved by slug at run time, so service pages land under
 *   /services/ and case studies under /case-studies/ without hard-coded IDs.
 *
 * @package Remotive
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/content-seed-data.php';

const REMOTIVE_SEEDED = 'remotive_seeded_content';

/**
 * Find an existing post or page by slug, in any status.
 *
 * get_page_by_path() only finds published items, which would let the seeder
 * recreate something sitting in drafts or trash. This looks wider.
 *
 * @param string $slug Post slug.
 * @param string $type Post type.
 * @return WP_Post|null
 */
function remotive_seed_find( $slug, $type ) {
	$found = get_posts(
		array(
			'name'             => $slug,
			'post_type'        => $type,
			'post_status'      => 'any',
			'numberposts'      => 1,
			'suppress_filters' => false,
		)
	);

	return $found ? $found[0] : null;
}

/**
 * Attach a featured image from the theme's bundled seed images.
 *
 * Copies the file into the uploads directory so it behaves like any other
 * media item and survives a theme update. Silently does nothing if the file
 * is missing or the copy fails — a missing image is not worth failing setup
 * over.
 *
 * @param int    $post_id Post to attach to.
 * @param string $file    File name inside assets/seed-images/.
 * @param string $alt     Alt text.
 */
function remotive_seed_attach_image( $post_id, $file, $alt ) {
	if ( ! $file ) {
		return;
	}

	$source = get_stylesheet_directory() . '/assets/seed-images/' . $file;

	if ( ! is_readable( $source ) ) {
		return;
	}

	$uploads = wp_upload_dir();

	// Bail before touching wp-admin includes: on a failed uploads directory
	// there is nothing to attach to, and requiring those files outside an
	// admin request is both pointless and fragile.
	if ( ! empty( $uploads['error'] ) || empty( $uploads['path'] ) ) {
		return;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$target = trailingslashit( $uploads['path'] ) . wp_unique_filename( $uploads['path'], $file );

	if ( ! copy( $source, $target ) ) {
		return;
	}

	// Copy the AVIF companion alongside the PNG under the same basename.
	// Nothing references it directly: inc/core/avif.php looks for exactly this
	// sibling when it wraps an image tag, so the attachment stays an
	// ordinary PNG for every plugin and export that reads it.
	$source_avif = preg_replace( '/\.png$/i', '.avif', $source );
	$target_avif = preg_replace( '/\.png$/i', '.avif', $target );

	if ( $source_avif !== $source && is_readable( $source_avif ) ) {
		copy( $source_avif, $target_avif );
	}

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/png',
			'post_title'     => sanitize_file_name( pathinfo( $file, PATHINFO_FILENAME ) ),
			'post_status'    => 'inherit',
		),
		$target,
		$post_id
	);

	if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		return;
	}

	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $target ) );
	update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
	set_post_thumbnail( $post_id, $attachment_id );
}

/**
 * Apply an item's template, SEO meta, terms and image to a post.
 *
 * Shared by the activation seeder and the per-item restore action so the
 * two can never drift apart.
 *
 * @param int   $post_id      Post to apply to.
 * @param array $item         Seed item definition.
 * @param bool  $attach_image Whether to attach the bundled image. Restore
 *                            passes false when a thumbnail already exists,
 *                            to avoid duplicating media on every click.
 */
function remotive_seed_apply_extras( $post_id, $item, $attach_image = true ) {
	if ( ! empty( $item['template'] ) ) {
		update_post_meta( $post_id, '_wp_page_template', $item['template'] );
	}

	// Rank Math reads these directly; harmless if the plugin is absent,
	// and already correct if it is installed later.
	foreach ( array( 'rm_title' => 'rank_math_title', 'rm_desc' => 'rank_math_description', 'rm_kw' => 'rank_math_focus_keyword' ) as $from => $meta_key ) {
		if ( ! empty( $item[ $from ] ) ) {
			update_post_meta( $post_id, $meta_key, $item[ $from ] );
		}
	}

	if ( ! empty( $item['category'] ) ) {
		list( $cat_name, $cat_slug ) = $item['category'];
		$term = term_exists( $cat_slug, 'category' );

		if ( ! $term ) {
			$term = wp_insert_term( $cat_name, 'category', array( 'slug' => $cat_slug ) );
		}

		if ( ! is_wp_error( $term ) ) {
			wp_set_post_terms( $post_id, array( (int) $term['term_id'] ), 'category' );
		}
	}

	if ( ! empty( $item['tags'] ) ) {
		wp_set_post_terms( $post_id, $item['tags'], 'post_tag' );
	}

	if ( $attach_image && ! empty( $item['image'] ) ) {
		remotive_seed_attach_image( $post_id, $item['image'], $item['title'] );
	}
}

/**
 * Create the shipped content.
 *
 * @return string[] Notes on what was created, for the setup screen.
 */
function remotive_seed_run() {
	$seeded = (array) get_option( REMOTIVE_SEEDED, array() );
	$notes  = array();

	foreach ( remotive_seed_content() as $item ) {
		$key = $item['type'] . ':' . $item['slug'];

		// Seeded once already — even if the site owner has since deleted it.
		if ( in_array( $key, $seeded, true ) ) {
			continue;
		}

		// Something with this slug exists: never overwrite it.
		if ( remotive_seed_find( $item['slug'], $item['type'] ) ) {
			$seeded[] = $key;
			continue;
		}

		$args = array(
			'post_title'   => $item['title'],
			'post_name'    => $item['slug'],
			'post_content' => $item['content'],
			'post_excerpt' => $item['excerpt'],
			'post_status'  => 'publish',
			'post_type'    => $item['type'],
			'post_author'  => remotive_seed_author(),
		);

		if ( ! empty( $item['parent'] ) ) {
			$parent = get_page_by_path( $item['parent'], OBJECT, 'page' );

			if ( $parent ) {
				$args['post_parent'] = $parent->ID;
			}
		}

		$post_id = wp_insert_post( $args, true );

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		remotive_seed_apply_extras( $post_id, $item );

		$seeded[] = $key;
		/* translators: %s: item title. */
		$notes[]  = sprintf( __( '%s: published', 'remotive' ), $item['title'] );
	}

	update_option( REMOTIVE_SEEDED, array_values( array_unique( $seeded ) ) );

	// Resolve internal cross-links now that all posts exist.
	// Seeded content uses /blog/{slug}/ paths, which only work under a
	// matching permalink structure. Rewrite every __REMOTIVE_BLOG_LINK_{slug}__
	// placeholder to the actual permalink — which get_permalink() resolves
	// regardless of how WordPress is configured. Templates use the same
	// placeholder approach for their static link text.
	remotive_resolve_seed_post_links();

	return $notes;
}

/**
 * Rewrite hardcoded /blog/{slug}/ links in seeded post content to real
 * permalinks, and replace __REMOTIVE_LINK_{slug}__ placeholders used in
 * templates.
 *
 * Called after the main seed loop so all target posts are guaranteed to
 * exist. Safe to call repeatedly: get_permalink() always wins, and a link
 * that already uses a real permalink passes filter_var( $url, FILTER_VALIDATE_URL )
 * unchanged.
 *
 * Only updates posts whose content still contains an assumed /blog/ prefix.
 * That set is normally the just-seeded posts on a fresh install; subsequent
 * calls are cheap because get_posts() returns nothing when no matches exist.
 */
function remotive_resolve_seed_post_links() {
	// Collect the slugs of every seeded blog post so we can look them up.
	$data  = remotive_seed_content();
	$slugs = array();
	foreach ( $data as $item ) {
		if ( 'post' === $item['type'] ) {
			$slugs[] = $item['slug'];
		}
	}

	if ( ! $slugs ) {
		return;
	}

	// Build a map: /blog/{slug}/ → real permalink, for every slug whose
	// post currently exists. Non-existent targets are left untouched rather
	// than replaced with a broken link.
	$link_map = array();
	foreach ( $slugs as $slug ) {
		$post = get_posts( array(
			'name'             => $slug,
			'post_type'        => 'post',
			'post_status'      => 'publish',
			'numberposts'      => 1,
			'suppress_filters' => false,
		) );

		if ( $post ) {
			$permalink = get_permalink( $post[0]->ID );
			if ( $permalink ) {
				// Map both the /blog/ prefix and a site-root prefix since
				// some earlier installs may have stored either.
				$link_map[ '/blog/' . $slug . '/' ] = $permalink;
			}
		}
	}

	if ( ! $link_map ) {
		return;
	}

	// Find all published posts and pages that still contain a raw /blog/ link.
	$pattern = implode( '|', array_map( 'preg_quote', array_keys( $link_map ) ) );

	$posts_to_update = get_posts( array(
		'post_type'      => array( 'post', 'page' ),
		'post_status'    => 'publish',
		'posts_per_page' => 200,
		'fields'         => 'ids',
		// No s= here: a regex search isn't available through WP_Query,
		// so we load all and filter below.
	) );

	foreach ( $posts_to_update as $id ) {
		$post = get_post( $id );

		if ( ! $post || ! preg_match( '/\/blog\//', $post->post_content ) ) {
			continue;
		}

		$new_content = str_replace(
			array_keys( $link_map ),
			array_values( $link_map ),
			$post->post_content
		);

		if ( $new_content !== $post->post_content ) {
			wp_update_post( array(
				'ID'           => $id,
				'post_content' => $new_content,
			) );
		}
	}
}

/**
 * Author for seeded content: the first administrator, falling back to any
 * existing user. Content with no valid author is invisible in the admin list
 * tables, which looks like a bug.
 *
 * @return int
 */
function remotive_seed_author() {
	$admins = get_users(
		array(
			'role'    => 'administrator',
			'number'  => 1,
			'orderby' => 'ID',
			'fields'  => 'ID',
		)
	);

	if ( $admins ) {
		return (int) $admins[0];
	}

	$any = get_users( array( 'number' => 1, 'fields' => 'ID' ) );

	return $any ? (int) $any[0] : 0;
}

/**
 * How many seed items are still missing, for the setup screen.
 *
 * @return array{total:int,live:int}
 */
function remotive_seed_status() {
	$total = 0;
	$live  = 0;

	foreach ( remotive_seed_content() as $item ) {
		$total++;
		$post = remotive_seed_find( $item['slug'], $item['type'] );

		if ( $post && 'publish' === $post->post_status ) {
			$live++;
		}
	}

	return array( 'total' => $total, 'live' => $live );
}

/**
 * Replace one item with its shipped version, on explicit request only.
 *
 * The activation seeder never rewrites anything; this is the one path that
 * does, and it runs only from the Theme Options button behind a nonce and a
 * capability check. If the item exists in any status its title, content and
 * excerpt are overwritten and it is republished; if it was deleted it is
 * recreated. Either way the item ends up exactly as shipped.
 *
 * @param string $key Item key in type:slug form.
 * @return string|false The restored item's title, or false when the key is
 *                      unknown or the write failed.
 */
function remotive_seed_restore( $key ) {
	$target = null;

	foreach ( remotive_seed_content() as $item ) {
		if ( $item['type'] . ':' . $item['slug'] === $key ) {
			$target = $item;
			break;
		}
	}

	if ( ! $target ) {
		return false;
	}

	$existing = remotive_seed_find( $target['slug'], $target['type'] );

	$args = array(
		'post_title'   => $target['title'],
		'post_name'    => $target['slug'],
		'post_content' => $target['content'],
		'post_excerpt' => $target['excerpt'],
		'post_status'  => 'publish',
		'post_type'    => $target['type'],
	);

	if ( ! empty( $target['parent'] ) ) {
		$parent = get_page_by_path( $target['parent'], OBJECT, 'page' );

		if ( $parent ) {
			$args['post_parent'] = $parent->ID;
		}
	}

	if ( $existing ) {
		$args['ID'] = $existing->ID;
		$post_id    = wp_update_post( wp_slash( $args ), true );
	} else {
		$args['post_author'] = remotive_seed_author();
		$post_id             = wp_insert_post( wp_slash( $args ), true );
	}

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		return false;
	}

	remotive_seed_apply_extras( $post_id, $target, ! has_post_thumbnail( $post_id ) );

	// Record it as seeded, so a restored-after-deletion item is tracked
	// the same way as one created on activation.
	$seeded = (array) get_option( REMOTIVE_SEEDED, array() );

	if ( ! in_array( $key, $seeded, true ) ) {
		$seeded[] = $key;
		update_option( REMOTIVE_SEEDED, array_values( $seeded ) );
	}

	return $target['title'];
}
