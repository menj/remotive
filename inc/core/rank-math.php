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
		return false;
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
