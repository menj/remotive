<?php
/**
 * Rank Math SEO compatibility.
 *
 * Written against Rank Math SEO 1.0.279. When the plugin is active it takes
 * over three things the theme also handles, and it does so by discarding the
 * theme's versions:
 *
 *  - Robots: the plugin calls remove_all_filters( 'wp_robots' ) on the front
 *    end, so anything hooked there is gone. The plugin's own
 *    'rank_math/frontend/robots' filter is the only one that counts.
 *  - Canonical: the plugin removes core's rel_canonical, so the core
 *    'get_canonical_url' filter never runs. 'rank_math/frontend/canonical'
 *    is the one that counts.
 *  - Sitemap: the plugin builds its own sitemap (core's is switched off), and
 *    leaves out only posts whose rank_math_robots meta says noindex, which
 *    the runtime noindex rules for the landing and confirmation pages never
 *    write. 'rank_math/sitemap/entry' lets the theme drop them.
 *
 * Each filter below is inert when Rank Math is not installed, because the
 * plugin's hooks are never fired.
 *
 * Schema is handled separately in inc/content/schema-markup.php.
 *
 * @package Remotive
 */

defined( 'ABSPATH' ) || exit;

/**
 * The generic confirmation page stays out of search results.
 *
 * Mirrors remotive_thanks_noindex() (which Rank Math discards). The landing
 * confirmation page (audit-requested) is a landing-template page and gets
 * noindex + nofollow from inc/landing/landing-pages.php instead, so it is
 * left alone here.
 *
 * @param array $robots Rank Math robots, e.g. array( 'index' => 'index', 'follow' => 'follow' ).
 * @return array
 */
function remotive_rank_math_thanks_robots( $robots ) {
	if ( function_exists( 'remotive_is_thanks_page' ) && remotive_is_thanks_page()
		&& ! ( function_exists( 'remotive_is_landing_page' ) && remotive_is_landing_page() ) ) {
		$robots           = (array) $robots;
		$robots['index'] = 'noindex';
		$robots['follow'] = 'follow';
	}

	return $robots;
}
add_filter( 'rank_math/frontend/robots', 'remotive_rank_math_thanks_robots' );

/**
 * Each landing-page language keeps its own canonical URL.
 *
 * Without this the plugin points every language at the English page, which
 * tells crawlers the Malay and Chinese versions are duplicates of it.
 *
 * @param string $canonical Canonical URL.
 * @return string
 */
function remotive_rank_math_canonical( $canonical ) {
	if ( function_exists( 'remotive_lp_canonical' ) && function_exists( 'remotive_is_landing_page' ) && remotive_is_landing_page() ) {
		$post = get_queried_object();

		return remotive_lp_canonical( $canonical, $post );
	}

	return $canonical;
}
add_filter( 'rank_math/frontend/canonical', 'remotive_rank_math_canonical' );

/**
 * Drop the unlisted pages (landing and confirmation pages, see
 * remotive_unlisted_page_ids()) from the plugin's XML sitemap.
 *
 * @param array|false $url  Sitemap entry (array with 'loc').
 * @param string      $type Entry type.
 * @param object      $post The post, when $type is 'post'.
 * @return array|false False makes the plugin skip the entry.
 */
function remotive_rank_math_sitemap_entry( $url, $type = '', $post = null ) {
	if ( 'post' === $type && is_object( $post ) && in_array( (int) $post->ID, remotive_unlisted_page_ids(), true ) ) {
		// A landing page switched to index in Rank Math's Advanced tab belongs in the sitemap; the confirmation pages never do.
		$is_landing = in_array( (int) $post->ID, remotive_lp_page_ids(), true ) && ! remotive_lp_is_confirmation_page( $post->ID );

		if ( ! $is_landing || remotive_lp_robots_policy( $post->ID )['noindex'] ) {
			return false;
		}
	}

	return $url;
}
add_filter( 'rank_math/sitemap/entry', 'remotive_rank_math_sitemap_entry', 10, 3 );

/**
 * One <title>, not two.
 *
 * Rank Math moves WordPress's classic title tag into its own head output, but
 * a block theme's templates add a second one (`_block_template_render_title_tag`)
 * that Rank Math does not know about, so every page printed the title twice.
 * Both read the same text, because Rank Math supplies it through
 * `pre_get_document_title`; only the extra tag is removed, and only when Rank
 * Math has really taken the title over. Runs before the tag is printed
 * (wp_head priority 1). Without Rank Math nothing changes.
 */
function remotive_rank_math_single_title() {
	if ( class_exists( 'RankMath' ) && has_action( 'rank_math/head', '_wp_render_title_tag' ) ) {
		remove_action( 'wp_head', '_block_template_render_title_tag', 1 );
	}
}
add_action( 'wp_head', 'remotive_rank_math_single_title', 0 );

/**
 * A meta description for a category archive that has none.
 *
 * Rank Math prints nothing when the category has no description, and an archive without one shows
 * whatever Google picks from the page. The category's own description is used when there is one,
 * otherwise a sentence naming the category and the site.
 *
 * @param string $description Rank Math's description.
 * @return string
 */
function remotive_rank_math_archive_description( $description ) {
	if ( '' !== trim( (string) $description ) || ! is_category() ) {
		return $description;
	}

	$term = get_queried_object();

	if ( ! $term || empty( $term->name ) ) {
		return $description;
	}

	if ( ! empty( $term->description ) ) {
		return wp_strip_all_tags( $term->description );
	}

	/* translators: 1: category name, 2: site name */
	return sprintf( __( 'Articles on %1$s from %2$s: what works for brands in Asia, and what to fix first.', 'remotive' ), $term->name, get_bloginfo( 'name' ) );
}
add_filter( 'rank_math/frontend/description', 'remotive_rank_math_archive_description', 20 );

/**
 * A social image for a page that has none of its own: the site logo.
 *
 * Rank Math prints og:image only from a featured image, the content or its own default setting, and
 * the default was never set, so every page but the six posts with a lead image shared with no picture.
 * `remotive_default_social_image` returns the URL to use instead of the logo.
 *
 * @param string $url The image Rank Math found, or ''.
 * @return string
 */
function remotive_rank_math_social_image( $url ) {
	if ( '' !== trim( (string) $url ) ) {
		return $url;
	}

	$fallback = '';
	$logo_id  = (int) get_theme_mod( 'custom_logo' );

	if ( $logo_id ) {
		$fallback = (string) wp_get_attachment_image_url( $logo_id, 'full' );
	}

	if ( '' === $fallback ) {
		$fallback = get_stylesheet_directory_uri() . '/assets/images/remotive-logo-landscape.png';
	}

	return (string) apply_filters( 'remotive_default_social_image', $fallback );
}
add_filter( 'rank_math/opengraph/facebook/image', 'remotive_rank_math_social_image', 20 );
add_filter( 'rank_math/opengraph/twitter/image', 'remotive_rank_math_social_image', 20 );


/**
 * Whether Rank Math's breadcrumbs are switched on (Rank Math → General Settings → Breadcrumbs).
 *
 * @return bool
 */
function remotive_rank_math_breadcrumbs_on() {
	$on = function_exists( 'rank_math_get_breadcrumbs' )
		&& is_callable( array( '\\RankMath\\Helper', 'get_settings' ) )
		&& (bool) \RankMath\Helper::get_settings( 'general.breadcrumbs' );

	/**
	 * Whether the visible breadcrumb trail prints.
	 *
	 * @param bool $on Rank Math's own setting.
	 */
	return (bool) apply_filters( 'remotive_rank_math_breadcrumbs', $on );
}

/**
 * A visible breadcrumb trail above the page title, from Rank Math's own breadcrumbs, while its setting
 * is on. The structured data carries a BreadcrumbList on every inner page regardless; this is the
 * trail a visitor sees. Not on the front page, the one-page layout, the ad landing pages or a
 * confirmation page, and only above the level-one title of the page itself, never a card's title.
 *
 * @param string $content Rendered block.
 * @param array  $block   Parsed block.
 * @return string
 */
function remotive_rank_math_breadcrumbs_before_title( $content, $block ) {
	if ( ! remotive_rank_math_breadcrumbs_on() || is_front_page() || is_404() || is_search() ) {
		return $content;
	}

	$name = isset( $block['blockName'] ) ? $block['blockName'] : '';

	if ( 'core/post-title' === $name && ( ! is_singular() || 1 !== (int) ( $block['attrs']['level'] ?? 2 ) || ! in_the_loop() ) ) {
		return $content;
	}

	if ( 'core/query-title' === $name && ! ( is_archive() || is_home() ) ) {
		return $content;
	}

	if ( ( function_exists( 'remotive_agency_front' ) && remotive_agency_front() )
		|| ( function_exists( 'remotive_is_landing_page' ) && remotive_is_landing_page() )
		|| ( function_exists( 'remotive_is_thanks_page' ) && remotive_is_thanks_page() ) ) {
		return $content;
	}

	$trail = rank_math_get_breadcrumbs( array(
		'wrap_before' => '<nav class="rm-breadcrumbs rank-math-breadcrumb" aria-label="' . esc_attr__( 'Breadcrumb', 'remotive' ) . '"><p>',
		'wrap_after'  => '</p></nav>',
	) );

	return '' === (string) $trail ? $content : $trail . $content;
}
add_filter( 'render_block_core/post-title', 'remotive_rank_math_breadcrumbs_before_title', 10, 2 );
add_filter( 'render_block_core/query-title', 'remotive_rank_math_breadcrumbs_before_title', 10, 2 );
