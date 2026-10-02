<?php
/**
 * Remotive Media — homepage results band.
 *
 * The three-figure band between the proof section and the team section.
 *
 * Why this file exists
 * --------------------
 * The band previously rendered three bare numbers with a two-word label and
 * nothing else: no eyebrow, no heading, no basis, no link. It was the only
 * section on the page without a head, which is why it read as orphaned. The
 * values it carried at one point — an average ROAS, a year-one organic
 * figure, a client-retention percentage — could not be derived from any of
 * the thirteen case studies the theme ships:
 *
 *   - Exactly one engagement reports ROAS, "above 400%", and that case's own
 *     honest-read section says platform-reported ROAS flatters. One number
 *     is not an average.
 *   - The only genuine year-on-year organic figure in the body of work is
 *     +43.5%. The larger percentages are month-two or month-four movements,
 *     and their own cases flag them as large percentages on small absolute
 *     numbers.
 *   - Retention is not mentioned in any case study at all.
 *
 * Unsourced aggregates sitting directly beneath case studies that state
 * their own measurement limits is the one inconsistency that undermines the
 * rest of the page. So each figure here now comes from a single named
 * engagement and links to the case study where its limits are set out.
 *
 * The band is also the connective tissue it was missing: one figure per
 * service block, in the same order as "What we do", so the section reads as
 * that section proved rather than as three numbers with no parent.
 *
 * @package Remotive
 */

defined( 'ABSPATH' ) || exit;

/**
 * Resolve a case study slug to its permalink.
 *
 * Case studies are children of the Case Studies page, so the lookup uses
 * the full path. get_page_by_path() rather than a built URL, for the same
 * reason the feature grids resolve posts by slug: a hardcoded path assumes
 * a permalink structure, and that assumption is what had seeded content
 * 404ing before v1.66.6.
 *
 * @param string $slug
 * @return string Empty when the case study does not exist.
 */
function remotive_stat_case_url( $slug ) {
	$slug = trim( (string) $slug );

	if ( '' === $slug ) {
		return '';
	}

	$page = get_page_by_path( 'case-studies/' . $slug, OBJECT, 'page' );

	if ( ! $page || 'publish' !== $page->post_status ) {
		return '';
	}

	return (string) get_permalink( $page->ID );
}

/**
 * Render the three-figure results band.
 *
 * Each figure is a link when its case study resolves, and plain markup when
 * it does not — a missing case study drops the link rather than emitting one
 * that 404s, and nothing looks clickable that is not.
 *
 * @return string
 */
function remotive_render_stats() {
	$accents = array( 'cyan', 'magenta', 'accent-3' );
	$out     = '<div class="rm-stats-grid">';

	for ( $i = 1; $i <= 3; $i++ ) {
		$block = remotive_get_theme_option( 'stat_' . $i . '_block' );
		$value = remotive_get_theme_option( 'stat_' . $i . '_value' );
		$label = remotive_get_theme_option( 'stat_' . $i . '_label' );
		$url   = remotive_stat_case_url( remotive_get_theme_option( 'stat_' . $i . '_case' ) );

		if ( '' === trim( (string) $value ) ) {
			continue;
		}

		$accent = $accents[ $i - 1 ];
		$tag    = $url ? 'a' : 'div';
		$href   = $url ? ' href="' . esc_url( $url ) . '"' : '';
		$linked = $url ? ' rm-stat--linked' : '';

		$out .= '<' . $tag . ' class="rm-stat rm-stat--' . esc_attr( $accent ) . $linked . '"' . $href . '>';

		if ( '' !== trim( (string) $block ) ) {
			$out .= '<span class="rm-stat__block">' . esc_html( $block ) . '</span>';
		}

		$out .= '<span class="rm-stat__value">' . esc_html( $value ) . '</span>'
			. '<span class="rm-stat__label">' . esc_html( $label ) . '</span>';

		if ( $url ) {
			$out .= '<span class="rm-stat__cue" aria-hidden="true">'
				. esc_html__( 'Read the case', 'remotive' )
				. '<span class="rm-link-arrow">&rarr;</span></span>';
		}

		$out .= '</' . $tag . '>';
	}

	return $out . '</div>';
}

/**
 * Token for the band.
 *
 * @param array $tokens
 * @return array
 */
function remotive_stats_tokens( $tokens ) {
	$tokens['__REMOTIVE_STATS__'] = remotive_render_stats();

	return $tokens;
}
add_filter( 'remotive_theme_option_tokens', 'remotive_stats_tokens' );
