<?php
/**
 * Keeps the shipped content live and current.
 *
 * Three jobs, none of which touches text an owner has written:
 *
 *  1. Publish. When the theme is activated, every shipped item ends up published: a draft, a pending or
 *     scheduled copy is published, a copy in the Trash is restored, and an item that was deleted for good
 *     is created again.
 *  2. Replace what is outdated. Each time the theme brings new shipped text, a copy that is still exactly
 *     what the theme wrote earlier is replaced by the new text. A copy an editor has changed is left alone
 *     ("Restore" in Theme Options still puts it back on request). Whether a copy is untouched is known from
 *     a ledger of fingerprints (md5) the theme keeps of what it wrote, so no list has to be maintained by
 *     hand for future releases.
 *  3. Remove what is gone. A shipped item the theme no longer ships, and WordPress's own sample post, sample
 *     page and the empty "Uncategorized" category, are removed when they are untouched. Removal goes through
 *     the Trash, except the sample content, which has no value to keep.
 *
 * Runs in the admin, for a person allowed to publish pages: once on the first admin load after activation
 * or after new theme files arrive, and from the "Set up" button in Theme Options. Never on a visitor's request.
 */

defined( 'ABSPATH' ) || exit;

/** Option row: fingerprints of the text the theme last wrote for each shipped item, keyed "type:slug". */
const REMOTIVE_SEED_LEDGER = 'remotive_seed_ledger';

/** Option row: set on activation so the next admin load publishes every shipped item. */
const REMOTIVE_FORCE_PUBLISH = 'remotive_force_publish';

/** Option row: the theme version whose content has been synced. */
const REMOTIVE_CONTENT_SYNCED = 'remotive_content_synced';

/**
 * Fingerprints of earlier shipped text for items that changed after they had been published, so a copy
 * from that release counts as untouched. The ledger covers every release from this one on; this list only
 * bridges the releases before it. Each entry holds only the fields that changed: c content, t title,
 * x excerpt, rt/rd/rk the Rank Math title, description and focus keyword.
 *
 * @return array<string,array<int,array<string,string>>>
 */
function remotive_content_history() {
	return array(
		'page:seo-fmcg-malaysia-singapore' => array(
			array( 'c' => '60f0fb3d13ee8540a32e7bb985a85b4d' ),
		),
		'page:b2b-seo-industrial-supplier' => array(
			array(
				'x'  => 'c8105f16404cbc2214d8b034aa58a254',
				'rd' => '1d41f3a45f69d8d6e7a7eb28c7eda159',
			),
		),
	);
}

/** The Rank Math meta keys, by the fingerprint field that tracks each. */
function remotive_content_meta_map() {
	return array(
		'rt' => array( 'rm_title', 'rank_math_title' ),
		'rd' => array( 'rm_desc', 'rank_math_description' ),
		'rk' => array( 'rm_kw', 'rank_math_focus_keyword' ),
	);
}

/**
 * Fingerprints of one shipped item.
 *
 * @param array $item A shipped item.
 * @return array<string,string>
 */
function remotive_content_snapshot( array $item ) {
	$snap = array(
		'c' => md5( (string) ( $item['content'] ?? '' ) ),
		't' => md5( (string) ( $item['title'] ?? '' ) ),
		'x' => md5( (string) ( $item['excerpt'] ?? '' ) ),
	);

	foreach ( remotive_content_meta_map() as $field => $map ) {
		$snap[ $field ] = md5( (string) ( $item[ $map[0] ] ?? '' ) );
	}

	return $snap;
}

/**
 * The fingerprints that mean "the theme wrote this" for one field of one item: what the ledger recorded,
 * what the theme ships now, and what earlier releases shipped.
 *
 * @param string $field   c, t, x, rt, rd or rk.
 * @param string $key     type:slug.
 * @param array  $ledger  The whole ledger.
 * @param array  $current Fingerprints of the item as shipped now.
 * @return string[]
 */
function remotive_content_known( $field, $key, array $ledger, array $current ) {
	$known = array( $current[ $field ] );

	if ( isset( $ledger[ $key ][ $field ] ) ) {
		$known[] = $ledger[ $key ][ $field ];
	}

	foreach ( remotive_content_history()[ $key ] ?? array() as $old ) {
		if ( isset( $old[ $field ] ) ) {
			$known[] = $old[ $field ];
		}
	}

	return $known;
}

/**
 * What to do with a copy of a shipped item that is already on the site.
 *
 * Pure, so every case can be tested without WordPress.
 *
 * @param array  $stored  Fingerprints of the copy on the site (c, t, x), with the content put back into
 *                        the form it was seeded in.
 * @param string $key     type:slug.
 * @param array  $ledger  The whole ledger.
 * @param array  $current Fingerprints of the item as shipped now.
 * @return string 'current' (already the shipped text), 'refresh' (untouched older text, replace it) or
 *                'edited' (an editor changed it, leave it).
 */
function remotive_content_decide( array $stored, $key, array $ledger, array $current ) {
	if ( $stored['c'] === $current['c'] && $stored['t'] === $current['t'] && $stored['x'] === $current['x'] ) {
		return 'current';
	}

	foreach ( array( 'c', 't', 'x' ) as $field ) {
		if ( ! in_array( $stored[ $field ], remotive_content_known( $field, $key, $ledger, $current ), true ) ) {
			return 'edited';
		}
	}

	return 'refresh';
}

/**
 * Record what the theme wrote for one item.
 *
 * @param string $key  type:slug.
 * @param array  $item The shipped item.
 */
function remotive_content_ledger_set( $key, array $item ) {
	$ledger         = (array) get_option( REMOTIVE_SEED_LEDGER, array() );
	$ledger[ $key ] = remotive_content_snapshot( $item );

	update_option( REMOTIVE_SEED_LEDGER, $ledger, false );
}

/**
 * Fingerprints of the copy of an item that is on the site now.
 *
 * @param WP_Post $post       The post.
 * @param string  $base       Path the site lives under.
 * @param array   $permalinks Permalinks of the seeded posts.
 * @return array<string,string>
 */
function remotive_content_stored( $post, $base, array $permalinks ) {
	return array(
		'c' => md5( remotive_seed_content_from_site( (string) $post->post_content, $base, $permalinks ) ),
		't' => md5( (string) $post->post_title ),
		'x' => md5( (string) $post->post_excerpt ),
	);
}

/**
 * The post for an item that sits in the Trash, or null.
 *
 * @param array $item A shipped item.
 * @return WP_Post|null
 */
function remotive_content_find_trashed( array $item ) {
	$args = array(
		'name'             => $item['slug'] . '__trashed',
		'post_type'        => $item['type'],
		'post_status'      => 'trash',
		'numberposts'      => 1,
		'suppress_filters' => false,
	);

	// A child is only ever its declared parent's child: with that page missing, or the copy in the Trash
	// under another parent, nothing is matched.
	if ( '' !== (string) ( $item['parent'] ?? '' ) ) {
		$parent = get_page_by_path( $item['parent'], OBJECT, 'page' );

		if ( ! $parent ) {
			return null;
		}

		$args['post_parent'] = (int) $parent->ID;
	}

	$found = get_posts( $args );

	if ( ! $found ) {
		$args['name'] = $item['slug'];
		$found        = get_posts( $args );
	}

	return $found ? $found[0] : null;
}

/**
 * Fill in the parts of a shipped item that sit beside its text: the Rank Math fields and the image.
 *
 * An empty field is filled, one still holding earlier shipped text is replaced, and anything an editor
 * wrote stays. The image is attached only when the post has none.
 *
 * @param WP_Post $post    The post.
 * @param array   $item    The shipped item.
 * @param string  $key     type:slug.
 * @param array   $ledger  The whole ledger.
 * @param array   $current Fingerprints of the item as shipped now.
 * @return int Fields and images changed.
 */
function remotive_content_apply_fields( $post, array $item, $key, array $ledger, array $current ) {
	$changed = 0;

	foreach ( remotive_content_meta_map() as $field => $map ) {
		$new = (string) ( $item[ $map[0] ] ?? '' );
		$cur = (string) get_post_meta( $post->ID, $map[1], true );

		if ( '' !== $new && $cur !== $new && ( '' === $cur || in_array( md5( $cur ), remotive_content_known( $field, $key, $ledger, $current ), true ) ) ) {
			update_post_meta( $post->ID, $map[1], $new );
			++$changed;
		}
	}

	if ( ! empty( $item['image'] ) && ! has_post_thumbnail( $post->ID ) ) {
		remotive_seed_attach_image( $post->ID, $item['image'], $item['title'] );

		if ( has_post_thumbnail( $post->ID ) ) {
			++$changed;
		}
	}

	return $changed;
}

/**
 * Bring the shipped content up: publish it, replace what is outdated, remove what is gone.
 *
 * @param bool $publish_all Publish every shipped item, restoring and recreating as needed. True on
 *                          activation and from the "Set up" button; false when only new files arrived.
 * @return array{published:int,created:int,refreshed:int,retired:int}
 */
function remotive_sync_shipped_content( $publish_all = false ) {
	$out = array(
		'published' => 0,
		'created'   => 0,
		'refreshed' => 0,
		'retired'   => 0,
	);

	if ( ! function_exists( 'remotive_seed_content' ) ) {
		return $out;
	}

	global $wpdb;

	$items      = remotive_seed_content();
	$ledger     = (array) get_option( REMOTIVE_SEED_LEDGER, array() );
	$base       = remotive_seed_base_path();
	$permalinks = remotive_seed_post_permalinks();
	$shipped    = array();
	$recreated  = 0;

	foreach ( $items as $item ) {
		$key = $item['type'] . ':' . $item['slug'];

		$shipped[ $key ] = true;
		$current         = remotive_content_snapshot( $item );
		$post            = remotive_seed_find( $item['slug'], $item['type'], $item['parent'] ?? '' );

		if ( ! $post && $publish_all ) {
			$trashed = remotive_content_find_trashed( $item );

			if ( $trashed && wp_untrash_post( $trashed->ID ) ) {
				$post = get_post( $trashed->ID );
			} elseif ( function_exists( 'remotive_seed_create' ) ) {
				$post_id = remotive_seed_create( $item );

				if ( $post_id ) {
					$ledger[ $key ] = $current;
					++$out['created'];
					++$recreated;
					continue;
				}
			}
		}

		if ( ! $post ) {
			continue;
		}

		if ( $publish_all && 'publish' !== $post->post_status ) {
			$args = array(
				'ID'          => $post->ID,
				'post_status' => 'publish',
			);

			if ( 'future' === $post->post_status ) {
				$args['post_date']     = current_time( 'mysql' );
				$args['post_date_gmt'] = current_time( 'mysql', true );
				$args['edit_date']     = true;
			}

			if ( wp_update_post( wp_slash( $args ) ) ) {
				++$out['published'];
				$post = get_post( $post->ID );
			}
		}

		$decision = remotive_content_decide( remotive_content_stored( $post, $base, $permalinks ), $key, $ledger, $current );

		if ( 'current' === $decision ) {
			// The text is already the shipped text; the fields beside it may still be missing or old.
			if ( remotive_content_apply_fields( $post, $item, $key, $ledger, $current ) ) {
				++$out['refreshed'];
			}

			$ledger[ $key ] = $current;
			continue;
		}

		if ( 'refresh' !== $decision || ! remotive_seed_blocks_balanced( $item['content'] ?? '' ) ) {
			continue;
		}

		// Compare and swap: the WHERE clause holds the text that was just checked, so a copy an editor
		// saved since then is not overwritten (no row matches).
		$rows = $wpdb->update(
			$wpdb->posts,
			array(
				'post_title'        => $item['title'],
				'post_content'      => remotive_seed_content_to_site( $item['content'], $base ),
				'post_excerpt'      => $item['excerpt'],
				'post_modified'     => current_time( 'mysql' ),
				'post_modified_gmt' => current_time( 'mysql', true ),
			),
			array(
				'ID'           => $post->ID,
				'post_title'   => $post->post_title,
				'post_content' => $post->post_content,
				'post_excerpt' => $post->post_excerpt,
			)
		);

		if ( 1 !== (int) $rows ) {
			continue;
		}

		clean_post_cache( $post->ID );
		remotive_content_apply_fields( get_post( $post->ID ), $item, $key, $ledger, $current );

		$ledger[ $key ] = $current;
		++$out['refreshed'];
	}

	// Items the theme wrote earlier and no longer ships: out, if nobody has edited them.
	foreach ( array_diff_key( $ledger, $shipped ) as $key => $old ) {
		list( $type, $slug ) = array_pad( explode( ':', $key, 2 ), 2, '' );

		unset( $ledger[ $key ] );

		if ( ! in_array( $type, array( 'post', 'page' ), true ) || '' === $slug ) {
			continue;
		}

		$found = get_posts(
			array(
				'name'             => $slug,
				'post_type'        => $type,
				'post_status'      => 'any',
				'numberposts'      => 1,
				'suppress_filters' => false,
			)
		);

		if ( ! $found ) {
			continue;
		}

		$stored = remotive_content_stored( $found[0], $base, $permalinks );

		if ( $stored['c'] === ( $old['c'] ?? '' ) && $stored['t'] === ( $old['t'] ?? '' ) && $stored['x'] === ( $old['x'] ?? '' ) && wp_trash_post( $found[0]->ID ) ) {
			++$out['retired'];
		}
	}

	update_option( REMOTIVE_SEED_LEDGER, $ledger, false );

	if ( ( $out['refreshed'] || $recreated ) && function_exists( 'remotive_resolve_seed_post_links' ) ) {
		remotive_resolve_seed_post_links(); // The new text carries /blog/ links; point them at the real permalinks again.
	}

	return $out;
}

/**
 * Whether a post is still exactly what WordPress put there at install: never edited, so deleting it
 * loses nothing.
 *
 * @param WP_Post|null $post     The post.
 * @param string       $title    Its default title.
 * @param string       $fragment A phrase from its default text.
 * @return bool
 */
function remotive_is_untouched_sample( $post, $title, $fragment ) {
	return $post instanceof WP_Post
		&& $post->post_title === $title
		&& false !== strpos( (string) $post->post_content, $fragment )
		&& $post->post_modified_gmt === $post->post_date_gmt;
}

/**
 * Remove WordPress's sample post, sample page and the empty "Uncategorized" category.
 *
 * Only copies nobody has edited, and the category only when no post of any status uses it. The default
 * category has to be something, so the first category the theme ships becomes the new default.
 *
 * @return int Things removed.
 */
function remotive_remove_wp_sample_content() {
	$removed = 0;
	$samples = array(
		array( 'hello-world', 'post', 'Hello world!', 'Welcome to WordPress' ),
		array( 'sample-page', 'page', 'Sample Page', 'This is an example page' ),
	);

	foreach ( $samples as $sample ) {
		$post = get_page_by_path( $sample[0], OBJECT, $sample[1] );

		if ( remotive_is_untouched_sample( $post, $sample[2], $sample[3] ) && wp_delete_post( $post->ID, true ) ) {
			++$removed;
		}
	}

	$term    = get_term_by( 'slug', 'uncategorized', 'category' );
	$default = (int) get_option( 'default_category' );

	if ( ! $term || (int) $term->term_id !== $default ) {
		return $removed;
	}

	$users = get_posts(
		array(
			'category'         => (int) $term->term_id,
			'post_status'      => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ),
			'numberposts'      => 1,
			'fields'           => 'ids',
			'suppress_filters' => false,
		)
	);

	if ( $users ) {
		return $removed;
	}

	foreach ( remotive_seed_content() as $item ) {
		if ( 'post' !== $item['type'] || empty( $item['category'][1] ) ) {
			continue;
		}

		$new = get_term_by( 'slug', $item['category'][1], 'category' );

		if ( $new && (int) $new->term_id !== (int) $term->term_id ) {
			update_option( 'default_category', (int) $new->term_id );

			if ( true === wp_delete_term( (int) $term->term_id, 'category' ) ) {
				++$removed;
			} else {
				update_option( 'default_category', (int) $term->term_id );
			}

			break;
		}
	}

	return $removed;
}

/** Activation: the next admin load publishes every shipped item and clears the sample content. */
add_action(
	'after_switch_theme',
	static function () {
		update_option( REMOTIVE_FORCE_PUBLISH, 1, false );
		delete_option( REMOTIVE_CONTENT_SYNCED );
	},
	5
);

/**
 * The automatic pass: on the first admin load after activation or after new theme files arrive, by a
 * person allowed to publish pages.
 */
function remotive_content_sync_on_admin() {
	if ( ! is_admin() || wp_doing_ajax() || wp_installing() || ! current_user_can( 'publish_pages' ) || ! function_exists( 'remotive_sync_shipped_content' ) ) {
		return;
	}

	// The Set up button (admin-post.php) runs the same work itself and reports it; running it here first
	// would leave that report at zero.
	if ( isset( $GLOBALS['pagenow'] ) && 'admin-post.php' === $GLOBALS['pagenow'] ) {
		return;
	}

	$version = (string) wp_get_theme( get_stylesheet() )->get( 'Version' );
	$force   = (bool) get_option( REMOTIVE_FORCE_PUBLISH );
	$first   = false === get_option( REMOTIVE_CONTENT_SYNCED );

	if ( ! $force && get_option( REMOTIVE_CONTENT_SYNCED ) === $version ) {
		return;
	}

	// One request at a time: two admin loads at once would both seed the same missing items. The loser
	// leaves the version unrecorded and the next admin load runs it.
	if ( function_exists( 'remotive_setup_lock_acquire' ) && ! remotive_setup_lock_acquire() ) {
		return;
	}

	try {
		if ( function_exists( 'remotive_seed_run' ) ) {
			remotive_seed_run(); // Items the site has never had.
		}

		remotive_sync_shipped_content( $force );

		if ( $force || $first ) {
			remotive_remove_wp_sample_content();
		}
	} catch ( \Throwable $e ) {
		error_log( sprintf( '[remotive] content sync failed: %s', $e->getMessage() ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}

	// Recorded even after a failure, so one broken item does not retry on every admin load.
	update_option( REMOTIVE_CONTENT_SYNCED, $version, false );
	delete_option( REMOTIVE_FORCE_PUBLISH );

	if ( function_exists( 'remotive_setup_lock_release' ) ) {
		remotive_setup_lock_release();
	}
}
add_action( 'admin_init', 'remotive_content_sync_on_admin', 30 );
