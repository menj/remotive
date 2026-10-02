<?php
/**
 * Remotive Media — homepage feature grids.
 *
 * The "problem we solve" and "why Re:Motive" grids. Both were sixteen
 * hand-written <div>s of heading-plus-paragraph inside a wp:html block:
 * no icons, no links, and nothing to distinguish one point from the next.
 * Each was a dead end — a visitor reading "Invisible to AI search" and
 * wanting to know what is done about it had nowhere to go.
 *
 * Defined as data here and rendered through a token, for the same reason
 * the ticker and the social icons are: sixteen inline SVGs pasted into a
 * block template cannot be edited without hand-editing HTML, and the
 * destination of each point is a maintenance concern rather than content.
 *
 * Icons are inline SVG rather than an icon font or sprite sheet. Inline
 * costs no extra request, inherits currentColor so the plate-colour
 * rotation in CSS works without duplicating each icon per colour, and
 * carries aria-hidden so a screen reader reads the heading rather than
 * announcing decoration.
 *
 * @package Remotive
 */

defined( 'ABSPATH' ) || exit;

/**
 * Icon paths, keyed by name.
 *
 * Deliberately simple geometric strokes at a 24-unit grid, matching the
 * plate-and-registration language of the rest of the theme rather than a
 * generic icon set. All use fill:none and stroke:currentColor so one path
 * serves every accent colour.
 *
 * @return array<string,string>
 */
function remotive_feature_icon_paths() {
	return array(
		// Problems
		'stretched'   => '<path d="M6 3h12M6 21h12M8 3v3.5a4 4 0 0 0 1.6 3.2L12 12l-2.4 2.3A4 4 0 0 0 8 17.5V21M16 3v3.5a4 4 0 0 1-1.6 3.2L12 12l2.4 2.3a4 4 0 0 1 1.6 3.2V21"/>',
		'siloed'      => '<rect x="2.5" y="4" width="7.5" height="7"/><rect x="14" y="13" width="7.5" height="7"/><path d="M10 7.5h2.5M11.5 7.5v9h2.5" stroke-dasharray="2 2.4"/>',
		'costs'       => '<path d="M3 20h18M6 20V9M11 20V5M16 20v-7"/><path d="M14.5 4.5 19 3l-1.2 4.4"/>',
		'attribution' => '<path d="M10.5 13.5 6.8 17.2a3.6 3.6 0 0 1-5.1-5.1l2.6-2.6"/><path d="M13.5 10.5l3.7-3.7a3.6 3.6 0 0 1 5.1 5.1l-2.6 2.6"/><path d="M9 15l6-6" stroke-dasharray="2 2.5"/>',
		'sprawl'      => '<circle cx="12" cy="12" r="2.2"/><circle cx="4" cy="5" r="2"/><circle cx="20" cy="5" r="2"/><circle cx="4" cy="19" r="2"/><circle cx="20" cy="19" r="2"/><path d="M5.5 6.4 10 10.4M18.5 6.4 14 10.4M5.5 17.6 10 13.6M18.5 17.6 14 13.6" stroke-dasharray="2 2.4"/>',
		'invisible'   => '<path d="M2 12s3.6-6.5 10-6.5c1.6 0 3 .4 4.2 1M22 12s-3.6 6.5-10 6.5c-1.7 0-3.2-.5-4.4-1.1"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/><path d="M3 3l18 18"/>',
		'dashboards'  => '<rect x="2.5" y="4" width="19" height="16" rx="1.5"/><path d="M6 15.5l3-3 2.5 2 3.5-4.5"/><path d="M17 15.5l3 3M20 15.5l-3 3"/>',
		'idle'        => '<path d="M2.5 6.5h13v8h-13z"/><path d="m2.5 7 6.5 4.5L15.5 7"/><circle cx="18.5" cy="16" r="4.5"/><path d="M18.5 14v2.2l1.5 1"/>',

		// Whys
		'target'      => '<circle cx="12" cy="12" r="8.5"/><circle cx="12" cy="12" r="4.5"/><circle cx="12" cy="12" r="1"/>',
		'plug'        => '<path d="M9 3v5M15 3v5"/><path d="M6.5 8h11v3.5a5.5 5.5 0 0 1-11 0z"/><path d="M12 17v4"/>',
		'globe'       => '<circle cx="12" cy="12" r="8.5"/><path d="M3.5 12h17"/><path d="M12 3.5c2.2 2.4 3.3 5.3 3.3 8.5S14.2 18.1 12 20.5c-2.2-2.4-3.3-5.3-3.3-8.5S9.8 5.9 12 3.5z"/>',
		'discovery'   => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="M15.2 15.2 21 21"/><path d="M10.5 7.5l.9 2.1 2.1.9-2.1.9-.9 2.1-.9-2.1-2.1-.9 2.1-.9z"/>',
		'modular'     => '<rect x="3" y="3" width="7.5" height="7.5"/><rect x="13.5" y="3" width="7.5" height="7.5"/><rect x="3" y="13.5" width="7.5" height="7.5"/><path d="M13.5 17.25h7.5M17.25 13.5v7.5"/>',
		'funnel'      => '<path d="M2.5 4h19l-7.2 8.4V20l-4.6-2.4v-5.2z"/>',
		'measurement' => '<path d="M3 7h18v10H3z"/><path d="M7 7v3.5M11 7v5M15 7v3.5M19 7v5"/>',
		'proof'       => '<path d="M12 2.5 20 6v6c0 4.4-3.2 8.2-8 9.5-4.8-1.3-8-5.1-8-9.5V6z"/><path d="m8.5 12 2.5 2.5 4.5-5"/>',
	);
}

/**
 * The eight "problem we solve" points.
 *
 * Destinations are blog posts only, deliberately — not services or case
 * studies. This grid sits above the Services and Proof sections, both of
 * which exist to send visitors to those pages; linking there from here
 * pre-empts the sections that do that job, and because search engines
 * generally credit only the first anchor text pointing at a given URL
 * from a page, a /services/analytics/ link here also spends that page's
 * first-link priority on the words "Analytics" in a problem card rather
 * than on the Services section's own, better-matched anchor.
 *
 * A problem statement raises a question, so the useful answer is an
 * article that addresses it, not a product page that assumes the reader
 * has already decided. Items with no honest editorial match stay
 * unlinked rather than being pointed at a loosely-related post to fill
 * the slot — remotive_render_feature_grid() renders those as plain
 * cards.
 *
 * @return array[]
 */
function remotive_problem_items() {
	return array(
		array(
			'icon'  => 'stretched',
			'title' => __( 'For brands', 'remotive' ),
			'text'  => __( 'An experienced, independent on-demand media team without the agency overhead.', 'remotive' ),
		),
		array(
			'icon'  => 'plug',
			'title' => __( 'For agencies', 'remotive' ),
			'text'  => __( 'A powerful, modular business extension without having to build the capability in-house.', 'remotive' ),
		),
		array(
			'icon'  => 'attribution',
			'title' => __( 'Weak attribution', 'remotive' ),
			'text'  => __( 'Teams cannot prove which channels open accounts and first orders.', 'remotive' ),
		),
		array(
			'icon'  => 'sprawl',
			'title' => __( 'Agency sprawl', 'remotive' ),
			'text'  => __( 'Point agencies each own a channel, and no one owns the number.', 'remotive' ),
			'post'  => 'sem-services-singapore',
			'cta'   => __( 'What SEM includes', 'remotive' ),
		),
		array(
			'icon'  => 'invisible',
			'title' => __( 'Invisible to AI search', 'remotive' ),
			'text'  => __( 'Buyers ask AI engines first, and most brands never appear in the answer.', 'remotive' ),
			'post'  => 'seo-friendly-web-design',
			'cta'   => __( 'What search reads', 'remotive' ),
		),
		array(
			'icon'  => 'dashboards',
			'title' => __( 'Dashboards nobody acts on', 'remotive' ),
			'text'  => __( 'Reporting describes last month and decides nothing.', 'remotive' ),
		),
		array(
			'icon'  => 'idle',
			'title' => __( 'Lists left idle', 'remotive' ),
			'text'  => __( 'Email lists sit unmailed while paid buys the same audience twice.', 'remotive' ),
			'post'  => 'facebook-advertising-malaysia',
			'cta'   => __( 'Paid vs owned audiences', 'remotive' ),
		),
	);
}

/**
 * The eight "why Re:Motive" points.
 *
 * @return array[]
 */
function remotive_why_items() {
	return array(
		array(
			'icon'  => 'target',
			'title' => __( 'Commercial accountability', 'remotive' ),
			'text'  => __( 'We report funded accounts, qualified leads and pipeline value. That is the number that matters.', 'remotive' ),
		),
		array(
			'icon'  => 'plug',
			'title' => __( 'A team that plugs into yours', 'remotive' ),
			'text'  => __( 'Senior specialists working inside your team, or white-label for creative and PR agencies.', 'remotive' ),
		),
		array(
			'icon'  => 'globe',
			'title' => __( 'Regional footprint', 'remotive' ),
			'text'  => __( 'Multi-market delivery across Asia, adapted to local regulation and platform.', 'remotive' ),
		),
		array(
			'icon'  => 'discovery',
			'title' => __( 'Next-gen discovery', 'remotive' ),
			'text'  => __( 'You show up in classic search and in AI answers, through SEO and GEO.', 'remotive' ),
		),
		array(
			'icon'  => 'modular',
			'title' => __( 'Modular by design', 'remotive' ),
			'text'  => __( 'Take one capability or the full stack, and scale as quarters change.', 'remotive' ),
		),
		array(
			'icon'  => 'funnel',
			'title' => __( 'Full-funnel scope', 'remotive' ),
			'text'  => __( 'One team across search, paid, social, content, email and analytics, so nothing falls between vendors.', 'remotive' ),
		),
		array(
			'icon'  => 'measurement',
			'title' => __( 'Honest measurement', 'remotive' ),
			'text'  => __( 'Attribution reported as a model, platform numbers labelled as platform numbers.', 'remotive' ),
		),
		array(
			'icon'  => 'proof',
			'title' => __( 'Proof before promises', 'remotive' ),
			'text'  => __( 'Case studies with the numbers shown, including what they do and do not prove.', 'remotive' ),
			'href'  => '/case-studies/',
			'cta'   => __( 'Case studies', 'remotive' ),
		),
	);
}

/**
 * Render one inline SVG icon.
 *
 * aria-hidden and focusable="false": the icon repeats what the heading
 * beside it already says, and IE-era SVGs are focusable by default in some
 * assistive tech, which would add sixteen empty tab stops to the homepage.
 *
 * @param string $name Key from remotive_feature_icon_paths().
 * @return string
 */
function remotive_feature_icon( $name ) {
	$paths = remotive_feature_icon_paths();

	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}

	return '<svg class="rm-feature__icon" viewBox="0 0 24 24" width="26" height="26"'
		. ' fill="none" stroke="currentColor" stroke-width="1.75"'
		. ' stroke-linecap="round" stroke-linejoin="round"'
		. ' aria-hidden="true" focusable="false">'
		. $paths[ $name ]
		. '</svg>';
}

/**
 * Resolve a blog post slug to its real permalink.
 *
 * Items reference posts by slug rather than by path because /blog/{slug}/
 * only resolves under one permalink structure — the same assumption that
 * had seeded content 404ing before v1.66.6. get_permalink() is correct
 * whatever the site is configured to use, including subdirectory installs.
 *
 * Returns '' when the post does not exist, which drops the link rather
 * than emitting one that 404s.
 *
 * @param string $slug
 * @return string
 */
function remotive_feature_post_url( $slug ) {
	$posts = get_posts( array(
		'name'             => $slug,
		'post_type'        => 'post',
		'post_status'      => 'publish',
		'numberposts'      => 1,
		'suppress_filters' => false,
	) );

	return $posts ? (string) get_permalink( $posts[0]->ID ) : '';
}

/**
 * Render one feature grid.
 *
 * Not every point links. A problem statement raises a question a link can
 * answer — "Invisible to AI search" wants "so what do you do about it" —
 * whereas the reassurance points in the Why grid are statements, not
 * questions, and linking all of them read as mechanical. Items are also
 * deduplicated by destination: search engines generally credit only the
 * first anchor text pointing at a given URL from a page, so four links to
 * Analytics passed no more signal than one while making the grid feel
 * padded to a reader who kept landing on the same page.
 *
 * A linked item renders as an <a> with the whole card as the target, so
 * the hit area matches what the hover state implies. An unlinked item
 * renders as a <div> with no cue and no hover lift, so nothing looks
 * clickable that is not.
 *
 * The trailing cue is aria-hidden: the link's accessible name already
 * comes from the heading and body text, and "Analytics right arrow"
 * appended to that is noise.
 *
 * @param array[] $items    From remotive_problem_items() / remotive_why_items().
 * @param string  $variant  'problem' or 'why', used for the block class only.
 * @return string
 */
function remotive_render_feature_grid( $items, $variant ) {
	$out = '<div class="rm-features rm-features--' . esc_attr( $variant ) . '">';

	foreach ( $items as $i => $item ) {
		$url = '';

		if ( ! empty( $item['post'] ) ) {
			$url = remotive_feature_post_url( $item['post'] );
		} elseif ( ! empty( $item['href'] ) ) {
			$url = home_url( $item['href'] );
		}

		$has_link = ( '' !== $url && ! empty( $item['cta'] ) );

		// Index drives the accent-colour rotation and the pulse stagger in
		// CSS, so neither needs sixteen hand-written nth-child rules.
		$style = ' style="--rm-feature-i:' . (int) $i . '"';

		$out .= $has_link
			? '<a class="rm-feature rm-feature--linked" href="' . esc_url( $url ) . '"' . $style . '>'
			: '<div class="rm-feature"' . $style . '>';

		$out .= '<span class="rm-feature__mark">' . remotive_feature_icon( $item['icon'] ) . '</span>'
			. '<h3 class="rm-feature__title">' . esc_html( $item['title'] ) . '</h3>'
			. '<p class="rm-feature__text">' . esc_html( $item['text'] ) . '</p>';

		if ( $has_link ) {
			$out .= '<span class="rm-feature__cue" aria-hidden="true">'
				. esc_html( $item['cta'] )
				. '<span class="rm-link-arrow">&rarr;</span>'
				. '</span>';
		}

		$out .= $has_link ? '</a>' : '</div>';
	}

	return $out . '</div>';
}

/**
 * Token values for the two grids.
 *
 * Registered through the same render_block token map the ticker and social
 * icons use — see remotive_replace_theme_option_tokens().
 *
 * @param array $tokens
 * @return array
 */
function remotive_feature_grid_tokens( $tokens ) {
	$tokens['__REMOTIVE_PROBLEMS__'] = remotive_render_feature_grid( remotive_problem_items(), 'problem' );
	$tokens['__REMOTIVE_WHYS__']     = remotive_render_feature_grid( remotive_why_items(), 'why' );

	return $tokens;
}
add_filter( 'remotive_theme_option_tokens', 'remotive_feature_grid_tokens' );
