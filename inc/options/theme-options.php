<?php
/**
 * Remotive Media — Theme Options.
 *
 * A single admin page (Appearance → Theme Options) for the handful of
 * things that were previously hardcoded directly in the block templates:
 * contact details, social links, the CTA form endpoint, and which colour
 * mode first-time visitors land on.
 *
 * How values reach the front end: block templates (templates/front-page.html,
 * parts/footer.html) are static files — there's no PHP interpolation inside
 * them. Instead, the templates contain plain-text tokens like
 * __REMOTIVE_CONTACT_EMAIL__, and a `render_block` filter below swaps every
 * token for its live option value on every request, after WordPress has
 * already rendered each block. This works uniformly whether the token sits
 * inside a Paragraph's content, a Navigation Link's url attribute, or a raw
 * Custom HTML block — all three are used across the theme.
 *
 * Tokens are deliberately alphanumeric-plus-underscore only (no braces,
 * colons, or other punctuation), so they pass through esc_url() and any
 * other WordPress sanitisation untouched before being replaced.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default values. These match what was hardcoded in the templates before
 * this options page existed, so activating this file changes nothing on
 * the front end until someone actually edits a setting.
 */
function remotive_theme_option_base_defaults() {
	return array(
		'contact_email'    => 'hello@remotivemedia.asia',
		'address_line_1'   => '11 North Buona Vista Drive',
		'address_line_2'   => '#08-09, The Metropolis',
		'address_line_3'   => 'Singapore 138589',
		'social_instagram' => '#',
		'social_linkedin'  => '#',
		'social_tiktok'    => '#',
		'social_facebook'  => '#',
		'social_x'         => '#',
		'social_youtube'   => '#',
		'social_threads'   => '#',
		// Defaults to the theme's own native handler (inc/forms/cta-form-handler.php)
		// so the form works out of the box with no third-party account
		// needed. Change this in Appearance -> Theme Options -> Call-to-
		// Action to point at a form plugin, Formspree, or a CRM webhook
		// instead — the native handler stays available either way.
		'cta_form_action'  => admin_url( 'admin-post.php' ),
		'default_theme'    => 'dark', // 'dark' | 'light' | 'system'

		// --- Homepage copy. Rendered through the __REMOTIVE_*__ token system,
		// so the block templates stay static and the editable strings live in
		// one place. Defaults match what the templates shipped with. ---
		'hero_eyebrow'     => '',
		'hero_line_1'      => 'Performance marketing, media & SEO',
		'hero_line_2'      => 'built to scale',
		'hero_highlight'   => 'Asian brands.',
		'hero_sub'         => 'We serve as a senior-led independent media team for high-growth B2B and B2C brands, and a powerful modular, search, social, programmatic and SEO extension for agencies without in-house media capabilities. We fix the foundations, get you found and cited, then scale with paid media, turning performance marketing into predictable pipeline growth across Asia and beyond.',
		'hero_cta_primary' => 'Show me a growth plan',
		'hero_cta_second'  => 'View Our Capabilities',
		'hero_reassure'    => '30 minutes · no commitment · reply in three business days',
		'ticker_countries'      => 'Singapore, Malaysia, Thailand, Vietnam, Hong Kong, China',
		'ticker_visible'        => '1',
		'ticker_bg'             => '#1a1a2e',
		'ticker_color'          => '#f7f4ec',
		'ticker_separator'      => 'rgba(255,255,255,0.18)',
		'ticker_size'           => '0.85',
		'ticker_weight'         => '700',
		'ticker_padding_v'      => '0.85',
		'ticker_padding_h'      => '2.4',
		'ticker_speed'          => '26',
		'ticker_direction'      => 'left',
		'ticker_letter_spacing' => '0.10',

		'problem_heading'  => 'Built for brands outgrowing their setup',
		'services_heading' => 'Three Stages, In The Order That Makes Money',
		'why_heading'      => 'Senior, accountable, everywhere you sell',
		'lead_retention_months' => '24',
		'branded_login'    => '0',
		'motion_effects'   => '1',
		'graceful_errors'  => '1',
		'maintenance_mode' => '0',
		'pexels_api_key'       => '',
		'pexels_api_key_clear' => '0',
		'legal_name'       => 'Remotive Media Asia',
		'legal_uen'        => '',
		'branded_login_message' => 'Team access only.',
		'team'             => array(
			array( 'name' => 'Gordan Domlija', 'role' => 'Managing Partner', 'slug' => 'gordan', 'bio' => '', 'home' => '1' ),
			array( 'name' => 'Jazlan Zakirin', 'role' => 'Performance Director', 'slug' => 'jazlan', 'bio' => '', 'home' => '1' ),
			array( 'name' => 'Ally Foo', 'role' => 'Account Director', 'slug' => 'ally', 'bio' => '', 'home' => '0' ),
			array( 'name' => 'Adam Azman', 'role' => 'Paid Search Specialist', 'slug' => 'adam', 'bio' => '', 'home' => '0' ),
			array( 'name' => 'Mohd Elfie Nieshaem', 'role' => 'SEO Specialist', 'slug' => 'elfie', 'bio' => '', 'home' => '0' ),
			array( 'name' => 'Alif Aziz', 'role' => 'Paid Social Specialist', 'slug' => 'alif', 'bio' => '', 'home' => '0' ),
			array( 'name' => 'Nabil Takiyuddin', 'role' => 'Data Analyst', 'slug' => 'nabil', 'bio' => '', 'home' => '0' ),
			array( 'name' => 'Jay Spicer', 'role' => 'Performance Director', 'slug' => 'jay', 'bio' => '', 'home' => '0' ),
			array( 'name' => 'Louie See', 'role' => 'Media Manager', 'slug' => 'louie', 'bio' => '', 'home' => '0' ),
			array( 'name' => 'Freya Angel', 'role' => 'Media Manager', 'slug' => 'freya', 'bio' => '', 'home' => '0' ),
		),
		'work_heading'     => 'Work that moved the number',
		'about_heading'    => 'Independent Spirit. Enterprise Scale.',
		'problem_sub'      => 'The patterns we see most often when a brand has outgrown the setup that got it here.',
		'services_sub'     => 'Fix the foundations, get found and cited, then scale with paid media. Most agencies start at stage three; we start at stage one, which is why stage three works.',
		'why_sub'          => 'What makes Fix, Found, Scale actually work, stated plainly enough to hold us to.',
		'work_sub'         => 'A few engagements where the number moved, with the measurement limits named on each page.',
		'about_sub'        => '20+ senior professionals who run Fix, Found and Scale end to end, not handed off between departments. 50+ markets activated. Full boutique infrastructure across analytics, SEO, programmatic and cross-market audience activation.',

		'cta_heading'      => 'Stop guessing where your growth is going to come from.',
		'cta_sub'          => 'Let us show you exactly where your audiences are active, where the genuine market white space sits, and precisely what it takes for your brand to win. No generic advice. Just hard, regional demand data.',
		'cta_button'       => 'Run a Market Diagnostic',

		// One figure per service block, each from a single named engagement
		// and each linked to the case study that sets out its measurement
		// limits. Deliberately not averages: see remotive_render_stats().
		'stat_1_block'     => '01 · Fix',
		'stat_1_value'     => '745k',
		'stat_1_label'     => 'Addressable audience, from 53k · sports and entertainment',
		'stat_1_case'      => 'cookieless-audience-sports',

		'stat_2_block'     => '02 · Found',
		'stat_2_value'     => '+43.5%',
		'stat_2_label'     => 'Organic search, year on year · FMCG across two markets',
		'stat_2_case'      => 'seo-fmcg-malaysia-singapore',

		'stat_3_block'     => '03 · Scale',
		'stat_3_value'     => '+25–35%',
		'stat_3_label'     => 'Conversion rate, four APAC markets · financial services',
		'stat_3_case'      => 'paid-media-financial-services',
	);
}

/**
 * Every default: the settings above plus the colour scheme (inc/options/colours.php).
 */
function remotive_theme_option_defaults() {
	return array_merge( remotive_theme_option_base_defaults(), remotive_colour_option_defaults() );
}

/**
 * Get one option value, falling back to its default if unset.
 */
function remotive_get_theme_option( $key ) {
	$defaults = remotive_theme_option_defaults();
	$saved    = get_option( 'remotive_theme_options', array() );
	$saved    = is_array( $saved ) ? $saved : array();
	$merged   = wp_parse_args( $saved, $defaults );

	return isset( $merged[ $key ] ) ? $merged[ $key ] : ( $defaults[ $key ] ?? '' );
}

/**
 * Register the setting (needed for options.php to accept the save) and
 * the sanitize callback. Field/section registration via add_settings_section()/
 * add_settings_field() is deliberately NOT used here — this page renders
 * its own tabbed/card markup (see remotive_render_theme_options_page())
 * instead of calling do_settings_sections(), so those calls would just be
 * unused infrastructure for a renderer this page doesn't invoke. The
 * field data they used to hold now lives in remotive_theme_options_tabs(),
 * shared by the renderer below.
 */
function remotive_register_theme_options() {
	register_setting(
		'remotive_theme_options_group',
		'remotive_theme_options',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'remotive_sanitize_theme_options',
			'default'           => remotive_theme_option_defaults(),
		)
	);
}
add_action( 'admin_init', 'remotive_register_theme_options' );

/**
 * Single source of truth for what's on the settings page: which tab each
 * field lives in, its label/type/helper text. Used only by the renderer —
 * remotive_sanitize_theme_options() stays independent and explicit about
 * every key it accepts, on purpose, so a typo here can't silently widen
 * what gets saved.
 */
function remotive_theme_options_base_tabs() {
	return array(
		'homepage' => array(
			'label'       => __( 'Homepage', 'remotive' ),
			'icon'        => 'dashicons-admin-home',
			'description' => __( 'Everything on the front page, in the order it appears: the opening hero, the heading above each section, the three-figure band, and the team grid. These were four separate tabs until v1.79.1, which meant editing one page meant moving between four of them.', 'remotive' ),
			'groups'      => array(
				array(
					'label'       => __( 'Hero', 'remotive' ),
					'description' => __( 'The opening block. The headline is split into three parts so the last can carry the brand colour.', 'remotive' ),
					'fields'      => array(

				'hero_eyebrow'     => array(
					'label' => __( 'Eyebrow (optional)', 'remotive' ),
					'type'  => 'text',
				),
				'hero_line_1'      => array(
					'label' => __( 'Headline — line 1', 'remotive' ),
					'type'  => 'text',
				),
				'hero_line_2'      => array(
					'label' => __( 'Headline — line 2', 'remotive' ),
					'type'  => 'text',
				),
				'hero_highlight'   => array(
					'label' => __( 'Headline — highlighted words', 'remotive' ),
					'type'  => 'text',
				),
				'hero_sub'         => array(
					'label' => __( 'Sub-heading', 'remotive' ),
					'type'  => 'text',
				),
				'hero_cta_primary' => array(
					'label' => __( 'Primary button label', 'remotive' ),
					'type'  => 'text',
				),
				'hero_cta_second'  => array(
					'label' => __( 'Secondary button label', 'remotive' ),
					'type'  => 'text',
				),
				'hero_reassure'    => array(
					'label' => __( 'Reassurance line', 'remotive' ),
					'type'  => 'text',
				),
					),
				),
				array(
					'label'       => __( 'Section headings', 'remotive' ),
					'description' => __( 'The heading and standfirst above each homepage section. A section keeps its layout; only the wording changes.', 'remotive' ),
					'fields'      => array(

				'problem_heading'  => array(
					'label' => __( 'The problem we solve', 'remotive' ),
					'type'  => 'text',
				),
				'problem_sub'      => array(
					'label'  => __( 'Problem description', 'remotive' ),
					'type'   => 'textarea',
					'helper' => __( 'The line under the heading. Every section on the site carries one; leave it blank only if the heading truly stands alone.', 'remotive' ),
				),
				'services_heading' => array(
					'label' => __( 'What we do', 'remotive' ),
					'type'  => 'text',
				),
				'services_sub'     => array(
					'label'  => __( 'Services description', 'remotive' ),
					'type'   => 'textarea',
					'helper' => __( 'The line under the heading. Every section on the site carries one; leave it blank only if the heading truly stands alone.', 'remotive' ),
				),
				'why_heading'      => array(
					'label' => __( 'Why Re:Motive', 'remotive' ),
					'type'  => 'text',
				),
				'why_sub'          => array(
					'label'  => __( 'Why Re:Motive description', 'remotive' ),
					'type'   => 'textarea',
					'helper' => __( 'The line under the heading. Every section on the site carries one; leave it blank only if the heading truly stands alone.', 'remotive' ),
				),
				'work_heading'     => array(
					'label' => __( 'Proof / work', 'remotive' ),
					'type'  => 'text',
				),
				'work_sub'         => array(
					'label'  => __( 'Case studies description', 'remotive' ),
					'type'   => 'textarea',
					'helper' => __( 'The line under the heading. Every section on the site carries one; leave it blank only if the heading truly stands alone.', 'remotive' ),
				),
				'about_heading'    => array(
					'label' => __( 'About / team', 'remotive' ),
					'type'  => 'text',
				),
				'about_sub'        => array(
					'label'  => __( 'Team description', 'remotive' ),
					'type'   => 'textarea',
					'helper' => __( 'The line under the heading. Every section on the site carries one; leave it blank only if the heading truly stands alone.', 'remotive' ),
				),
					),
				),
				array(
					'label'       => __( 'Closing call to action', 'remotive' ),
					'description' => __( 'The final banner before the footer, above the lead-capture form.', 'remotive' ),
					'fields'      => array(

				'cta_heading' => array(
					'label' => __( 'Heading', 'remotive' ),
					'type'  => 'text',
				),
				'cta_sub'     => array(
					'label' => __( 'Sub-heading', 'remotive' ),
					'type'  => 'textarea',
				),
				'cta_button'  => array(
					'label' => __( 'Button label', 'remotive' ),
					'type'  => 'text',
				),
					),
				),
				array(
					'label'       => __( 'Numbers', 'remotive' ),
					'description' => __( 'The three-figure band between the work and about sections. Keep values to four or five characters. Each should be traceable to a named case study.', 'remotive' ),
					'fields'      => array(

				'stat_1_value' => array(
					'label' => __( 'Figure 1 — value', 'remotive' ),
					'type'  => 'text',
				),
				'stat_1_label' => array(
					'label' => __( 'Figure 1 — label', 'remotive' ),
					'type'  => 'text',
				),
				'stat_2_value' => array(
					'label' => __( 'Figure 2 — value', 'remotive' ),
					'type'  => 'text',
				),
				'stat_2_label' => array(
					'label' => __( 'Figure 2 — label', 'remotive' ),
					'type'  => 'text',
				),
				'stat_3_value' => array(
					'label' => __( 'Figure 3 — value', 'remotive' ),
					'type'  => 'text',
				),
				'stat_3_label' => array(
					'label' => __( 'Figure 3 — label', 'remotive' ),
					'type'  => 'text',
				),
					),
				),
				array(
					'label'       => __( 'Team', 'remotive' ),
					'description' => __( 'Members shown in both team grids and in the structured data.', 'remotive' ),
					'fields'      => array(

				'team' => array(
					'label' => __( 'Members', 'remotive' ),
					'type'  => 'team-repeater',
				),
					),
				),
			),
		),
		'contact' => array(
			'label'       => __( 'Business details', 'remotive' ),
			'icon'        => 'dashicons-email-alt',
			'description' => __( 'The email address, postal address and social profiles. These appear in the site footer, on the Contact page, in the blog sidebar, and in the site\'s structured data, so changing one here changes it everywhere. A social field left blank hides that icon rather than linking nowhere.', 'remotive' ),
			'fields'      => array(
				'contact_email'    => array(
					'label' => __( 'Contact email', 'remotive' ),
					'type'  => 'email',
				),
				'address_line_1'   => array(
					'label' => __( 'Address — line 1', 'remotive' ),
					'type'  => 'text',
				),
				'address_line_2'   => array(
					'label' => __( 'Address — line 2', 'remotive' ),
					'type'  => 'text',
				),
				'address_line_3'   => array(
					'label' => __( 'Address — line 3', 'remotive' ),
					'type'  => 'text',
				),
				'legal_name'       => array(
					'label'  => __( 'Registered company name', 'remotive' ),
					'type'   => 'text',
					'helper' => __( 'Used in the footer copyright line. This is the legal entity, which may differ from the brand name used elsewhere on the site.', 'remotive' ),
				),
				'legal_uen'        => array(
					'label'  => __( 'Company registration number (UEN)', 'remotive' ),
					'type'   => 'text',
					'helper' => __( 'Shown in brackets after the company name in the footer. Singapore\'s Companies Act section 144 requires the registered name and number on a company\'s business communications and publications, and a website footer is the usual place for it. Leave blank to omit it entirely.', 'remotive' ),
				),
				'social_instagram' => array(
					'label' => __( 'Instagram URL', 'remotive' ),
					'type'  => 'url',
				),
				'social_linkedin'  => array(
					'label' => __( 'LinkedIn URL', 'remotive' ),
					'type'  => 'url',
				),
				'social_tiktok'    => array(
					'label' => __( 'TikTok URL', 'remotive' ),
					'type'  => 'url',
				),
				'social_facebook'  => array(
					'label' => __( 'Facebook URL', 'remotive' ),
					'type'  => 'url',
				),
				'social_x'         => array(
					'label' => __( 'X (Twitter) URL', 'remotive' ),
					'type'  => 'url',
				),
				'social_youtube'   => array(
					'label' => __( 'YouTube URL', 'remotive' ),
					'type'  => 'url',
				),
				'social_threads'   => array(
					'label' => __( 'Threads URL', 'remotive' ),
					'type'  => 'url',
				),
			),
		),
		'cta'     => array(
			'label'       => __( 'Call-to-Action', 'remotive' ),
			'icon'        => 'dashicons-megaphone',
			/* translators: %s: the contact email address the form emails to */
			'description' => sprintf(
				__( 'Where the homepage email-capture form submits to. By default, submissions email %s directly (the theme\'s built-in handler — no third-party account needed). Change this to a form plugin endpoint, a Formspree-style URL, or a CRM webhook if you\'d rather use one of those instead.', 'remotive' ),
				'<code>' . esc_html( remotive_get_theme_option( 'contact_email' ) ) . '</code>'
			),
			'fields'      => array(
				'cta_form_action' => array(
					'label'  => __( 'Form submission URL', 'remotive' ),
					'type'   => 'text',
					'helper' => __( 'Leave as the default admin-post.php URL to use the built-in handler, or paste a different endpoint to use instead.', 'remotive' ),
				),
			),
		),
		'ticker' => array(
			'label'       => __( 'Country ticker', 'remotive' ),
			'icon'        => 'dashicons-arrow-right-alt',
			'description' => __( 'The scrolling band of market names below the homepage hero. Adjust countries, colours, size and speed here.', 'remotive' ),
			'fields'      => array(
				'ticker_visible'        => array(
					'label'        => __( 'Show ticker', 'remotive' ),
					'type'         => 'toggle',
					'toggle_label' => __( 'Display the country ticker on the homepage', 'remotive' ),
					'helper'       => __( 'When off, the band is hidden entirely. All settings are preserved while hidden.', 'remotive' ),
				),
				'ticker_countries'      => array(
					'label'  => __( 'Countries', 'remotive' ),
					'type'   => 'text',
					'helper' => __( 'Comma-separated list of market names. At least two entries are required for the scroll to loop smoothly; fewer than two hides the band.', 'remotive' ),
				),
				'ticker_bg'             => array(
					'label'  => __( 'Background colour', 'remotive' ),
					'type'   => 'color',
					'helper' => __( 'The fill colour of the ticker band. Default: Charcoal (#1a1a2e).', 'remotive' ),
				),
				'ticker_color'          => array(
					'label'  => __( 'Text colour', 'remotive' ),
					'type'   => 'color',
					'helper' => __( 'The colour of the country names. Default: Ivory (#f7f4ec).', 'remotive' ),
				),
				'ticker_separator'      => array(
					'label'  => __( 'Separator colour', 'remotive' ),
					'type'   => 'text',
					'helper' => __( 'Colour of the vertical line between items. Accepts a hex colour or an rgba() value for transparency. Default: rgba(255,255,255,0.18).', 'remotive' ),
				),
				'ticker_size'           => array(
					'label'  => __( 'Font size (rem)', 'remotive' ),
					'type'   => 'text',
					'helper' => __( 'Size of the country names as a rem value (0.5–2.5). Default: 0.85.', 'remotive' ),
				),
				'ticker_weight'         => array(
					'label'   => __( 'Font weight', 'remotive' ),
					'type'    => 'select',
					'options' => array(
						'400' => __( '400 — Regular', 'remotive' ),
						'500' => __( '500 — Medium', 'remotive' ),
						'600' => __( '600 — Semibold', 'remotive' ),
						'700' => __( '700 — Bold', 'remotive' ),
						'800' => __( '800 — Extrabold', 'remotive' ),
						'900' => __( '900 — Black', 'remotive' ),
					),
				),
				'ticker_letter_spacing' => array(
					'label'  => __( 'Letter spacing (em)', 'remotive' ),
					'type'   => 'text',
					'helper' => __( 'Tracking applied to the country names (0.00–0.50). Default: 0.10.', 'remotive' ),
				),
				'ticker_padding_v'      => array(
					'label'  => __( 'Vertical padding (rem)', 'remotive' ),
					'type'   => 'text',
					'helper' => __( 'Top and bottom padding inside each item (0.2–4.0). Controls the band height. Default: 0.85.', 'remotive' ),
				),
				'ticker_padding_h'      => array(
					'label'  => __( 'Horizontal padding (rem)', 'remotive' ),
					'type'   => 'text',
					'helper' => __( 'Left and right padding inside each item (0.5–8.0). Controls spacing between country names. Default: 2.4.', 'remotive' ),
				),
				'ticker_speed'          => array(
					'label'  => __( 'Scroll speed (seconds)', 'remotive' ),
					'type'   => 'text',
					'helper' => __( 'Seconds for one full loop (4–120). Lower is faster. Default: 26. The speed scales proportionally if you add or remove countries.', 'remotive' ),
				),
				'ticker_direction'      => array(
					'label'   => __( 'Scroll direction', 'remotive' ),
					'type'    => 'select',
					'options' => array(
						'left'  => __( 'Left (default)', 'remotive' ),
						'right' => __( 'Right', 'remotive' ),
					),
				),
			),
		),
		'integrations' => array(
			'label'       => __( 'Integrations', 'remotive' ),
			'icon'        => 'dashicons-admin-network',
			'description' => __( 'Keys for outside services. They are stored in this site\'s database, never printed into a page, and never shown again after saving.', 'remotive' ),
			'fields'      => array(
				'pexels_api_key'       => array(
					'label'  => __( 'Pexels API key', 'remotive' ),
					'type'   => 'secret',
					'helper' => __( 'Used for finding free photography on Pexels. Paste a key to save it; leave the box empty to keep the one already saved. The front end of the site does not call Pexels, so nothing here affects page speed.', 'remotive' ),
				),
				'pexels_api_key_clear' => array(
					'label'        => __( 'Remove saved key', 'remotive' ),
					'type'         => 'toggle',
					'toggle_label' => __( 'Delete the saved Pexels key when I save', 'remotive' ),
				),
			),
		),
		'display' => array(
			'label'       => __( 'Site behaviour', 'remotive' ),
			'icon'        => 'dashicons-admin-appearance',
			'description' => __( "Which colour mode first-time visitors see. Returning visitors who've used the toggle always see their own saved choice regardless of this setting.", 'remotive' ),
			'fields'      => array(
				'lead_retention_months' => array(
					'label'  => __( 'Keep enquiries for', 'remotive' ),
					'type'   => 'text',
					'helper' => __( 'Months to keep stored enquiries before they are deleted automatically. Your privacy policy has to state a period, and a period nothing enforces is worse than none. Set 0 to keep them indefinitely.', 'remotive' ),
				),
				'branded_login' => array(
					'label'        => __( 'Branded login screen', 'remotive' ),
					'type'         => 'toggle',
					'toggle_label' => __( 'Use the site\'s branding on wp-login.php', 'remotive' ),
					'helper'       => __( 'Off by default. When on, the login screen uses the site logo, colours and typeface instead of the WordPress default. This changes appearance only: it does not alter how anyone signs in, and it does not add login security. If a login-branding plugin is already active, that plugin keeps control and this setting does nothing.', 'remotive' ),
				),
				'branded_login_message' => array(
					'label'  => __( 'Login screen message', 'remotive' ),
					'type'   => 'text',
					'helper' => __( 'One line shown above the login form. Leave blank for none.', 'remotive' ),
				),
				'maintenance_mode' => array(
					'label'        => __( 'Maintenance mode', 'remotive' ),
					'type'         => 'toggle',
					'toggle_label' => __( 'Show a "back shortly" page to visitors who are not logged in', 'remotive' ),
					'helper'       => __( 'Off by default. When on, logged-out visitors get a 503 "back shortly" page that search engines treat as temporary. Administrators and editors still see the live site, and nothing is deleted or unpublished. Switch it off here to bring the site back.', 'remotive' ),
				),
				'graceful_errors' => array(
					'label'        => __( 'Graceful error handling', 'remotive' ),
					'type'         => 'toggle',
					'toggle_label' => __( 'Keep PHP notices and warnings off the page', 'remotive' ),
					'helper'       => __( 'On by default. PHP notices, warnings and deprecation messages are captured and written to the error log instead of being printed into the page. This matters beyond tidiness: a printed notice is output, and once output has been sent no header can be set for the rest of the request, so a single notice from one plugin cascades into failed redirects and cookies. Administrators see a summary panel; visitors see nothing. Fatal errors are always handled separately and show the branded error page.', 'remotive' ),
				),
				'motion_effects' => array(
					'label'        => __( 'Scroll motion', 'remotive' ),
					'type'         => 'toggle',
					'toggle_label' => __( 'Fade and lift sections as they scroll into view', 'remotive' ),
					'helper'       => __( 'On by default. Section headings, cards and stats rise gently into place as you scroll, and the hero mark drifts behind the content. Only opacity and transform are animated, so nothing shifts position and page layout is unaffected. Visitors whose device is set to reduce motion never see any of it regardless of this setting.', 'remotive' ),
				),
				'default_theme' => array(
					'label'   => __( 'Default colour mode', 'remotive' ),
					'type'    => 'visual-select',
					'options' => array(
						'dark'   => array(
							'label' => __( 'Dark', 'remotive' ),
							'desc'  => __( 'The CMYK "Registration" direction', 'remotive' ),
						),
						'light'  => array(
							'label' => __( 'Light', 'remotive' ),
							'desc'  => __( 'The Remotive Media Asia brand system', 'remotive' ),
						),
						'system' => array(
							'label' => __( 'System', 'remotive' ),
							'desc'  => __( "Match the visitor's device", 'remotive' ),
						),
					),
				),
			),
		),
	);
}

/**
 * The tabs, with Colours (inc/options/colours.php) placed before Integrations.
 */
function remotive_theme_options_tabs() {
	$tabs = array();

	foreach ( remotive_theme_options_base_tabs() as $key => $tab ) {
		if ( 'integrations' === $key ) {
			$tabs['colours'] = remotive_colours_tab();
		}
		$tabs[ $key ] = $tab;
	}

	return $tabs;
}


function remotive_render_text_field( $key, $type ) {
	$value = remotive_get_theme_option( $key );

	if ( 'textarea' === $type ) {
		printf(
			'<textarea id="remotive_field_%1$s" name="remotive_theme_options[%1$s]" rows="3">%2$s</textarea>',
			esc_attr( $key ),
			esc_textarea( $value )
		);
		return;
	}

	if ( 'secret' === $type ) {
		// Never echo the stored value back into the page.
		printf(
			'<input type="password" id="remotive_field_%1$s" name="remotive_theme_options[%1$s]" value="" autocomplete="new-password" spellcheck="false" placeholder="%2$s" />',
			esc_attr( $key ),
			esc_attr( '' !== (string) $value ? __( 'Saved. Paste a new key to replace it.', 'remotive' ) : __( 'Not set', 'remotive' ) )
		);
		return;
	}

	$type = in_array( $type, array( 'email', 'url' ), true ) ? $type : 'text';
	printf(
		'<input type="%1$s" id="remotive_field_%2$s" name="remotive_theme_options[%2$s]" value="%3$s" />',
		esc_attr( $type ),
		esc_attr( $key ),
		esc_attr( $value )
	);
}

/**
 * The team repeater: one row per member, a hidden template row cloned by
 * admin-theme-options.js for Add member, and a Remove button per row that
 * simply drops the row from the form, so it never reaches the sanitizer.
 */
function remotive_render_team_repeater() {
	// Through the getter, so rows saved before the leadership flag existed
	// show their shipped default instead of an unticked box.
	$team = remotive_team_members();

	echo '<div class="rm-admin__team" id="rmTeamRepeater">';

	$row = function ( $i, $member, $template = false ) {
		$name = $template ? '' : ( $member['name'] ?? '' );
		$role = $template ? '' : ( $member['role'] ?? '' );
		$slug = $template ? '' : ( $member['slug'] ?? '' );
		$bio  = $template ? '' : ( $member['bio'] ?? '' );
		$lead = ! $template && '1' === ( $member['home'] ?? '0' ) ? ' checked' : '';
		$idx  = $template ? '__INDEX__' : (string) $i;

		printf(
			'<fieldset class="rm-admin__team-row%1$s"%2$s>' .
			'<legend class="screen-reader-text">%3$s</legend>' .
			'<label>%4$s <input type="text" name="remotive_theme_options[team][%5$s][name]" value="%6$s" /></label>' .
			'<label>%7$s <input type="text" name="remotive_theme_options[team][%5$s][role]" value="%8$s" /></label>' .
			'<label>%9$s <input type="text" name="remotive_theme_options[team][%5$s][slug]" value="%10$s" /></label>' .
			'<label class="rm-admin__team-bio">%11$s <textarea rows="2" name="remotive_theme_options[team][%5$s][bio]">%12$s</textarea></label>' .
			'<label class="rm-admin__team-lead"><input type="checkbox" name="remotive_theme_options[team][%5$s][home]" value="1"%14$s /> %15$s</label>' .
			'<button type="button" class="button-link-delete rm-admin__team-remove">%13$s</button>' .
			'</fieldset>',
			$template ? ' rm-admin__team-row--template' : '',
			$template ? ' disabled hidden' : '',
			esc_html__( 'Team member', 'remotive' ),
			esc_html__( 'Name', 'remotive' ),
			esc_attr( $idx ),
			esc_attr( $name ),
			esc_html__( 'Role', 'remotive' ),
			esc_attr( $role ),
			esc_html__( 'Photo slug', 'remotive' ),
			esc_attr( $slug ),
			esc_html__( 'Bio (About page)', 'remotive' ),
			esc_textarea( $bio ),
			esc_html__( 'Remove', 'remotive' ),
			$lead,
			esc_html__( 'Leadership: show on the homepage and list first on the Team page', 'remotive' )
		);
	};

	foreach ( array_values( $team ) as $i => $member ) {
		$row( $i, $member );
	}

	$row( 0, array(), true );

	echo '<p><button type="button" class="button" id="rmTeamAdd">' . esc_html__( 'Add member', 'remotive' ) . '</button></p>';
	echo '</div>';
}

function remotive_render_visual_select_field( $key, $options ) {
	$value = remotive_get_theme_option( $key );
	echo '<div class="rm-admin__visual-grid" role="radiogroup" aria-labelledby="remotive_field_' . esc_attr( $key ) . '_label">';
	foreach ( $options as $option_key => $option ) {
		$is_active = $value === $option_key;
		printf(
			'<label class="rm-admin__visual-card%1$s"><input type="radio" name="remotive_theme_options[%2$s]" value="%3$s" %4$s /><span class="rm-admin__visual-swatch rm-admin__visual-swatch--%3$s"></span><span class="rm-admin__visual-title">%5$s</span><span class="rm-admin__visual-desc">%6$s</span></label>',
			$is_active ? ' is-active' : '',
			esc_attr( $key ),
			esc_attr( $option_key ),
			checked( $value, $option_key, false ),
			esc_html( $option['label'] ),
			esc_html( $option['desc'] )
		);
	}
	echo '</div>';
}

/**
 * One field row: label on the left (desktop) / stacked above (mobile),
 * control on the right. Shared by every field type on the page.
 */
function remotive_render_field_row( $key, $field ) {
	$field_id = 'remotive_field_' . $key;
	echo '<div class="rm-admin__row">';

	if ( $field['type'] === 'toggle' ) {
		printf(
			'<label class="rm-admin__toggle"><input type="checkbox" id="%1$s" name="remotive_theme_options[%2$s]" value="1" %3$s /><span class="rm-admin__toggle-track" aria-hidden="true"><span class="rm-admin__toggle-thumb"></span></span><span class="rm-admin__toggle-text">%4$s</span></label>',
			esc_attr( $field_id ),
			esc_attr( $key ),
			checked( '1', (string) remotive_get_theme_option( $key ), false ),
			esc_html( $field['toggle_label'] ?? __( 'Enabled', 'remotive' ) )
		);
		echo '</div>';
		return;
	}

	if ( $field['type'] === 'contrast' ) {
		echo '<span class="rm-admin__row-label">' . esc_html( $field['label'] ) . '</span><div>';
		remotive_render_contrast_table( $field['mode'] );
		echo '</div></div>';
		return;
	}

	if ( $field['type'] === 'team-repeater' ) {
		remotive_render_team_repeater();
		echo '</div>';
		return;
	}

	if ( $field['type'] === 'visual-select' ) {
		echo '<span class="rm-admin__row-label" id="' . esc_attr( $field_id ) . '_label">' . esc_html( $field['label'] ) . '</span>';
		echo '<div>';
		remotive_render_visual_select_field( $key, $field['options'] );
		echo '</div>';
	} elseif ( $field['type'] === 'select' ) {
		printf(
			'<label class="rm-admin__row-label" for="%1$s">%2$s</label>',
			esc_attr( $field_id ),
			esc_html( $field['label'] )
		);
		echo '<div>';
		$current = (string) remotive_get_theme_option( $key );
		echo '<select id="' . esc_attr( $field_id ) . '" name="remotive_theme_options[' . esc_attr( $key ) . ']">';
		foreach ( $field['options'] as $val => $label ) {
			printf(
				'<option value="%1$s"%2$s>%3$s</option>',
				esc_attr( $val ),
				selected( $current, (string) $val, false ),
				esc_html( $label )
			);
		}
		echo '</select>';
		if ( ! empty( $field['helper'] ) ) {
			echo '<p class="rm-admin__row-helper">' . esc_html( $field['helper'] ) . '</p>';
		}
		echo '</div>';
	} elseif ( $field['type'] === 'color' ) {
		printf(
			'<label class="rm-admin__row-label" for="%1$s">%2$s</label>',
			esc_attr( $field_id ),
			esc_html( $field['label'] )
		);
		echo '<div>';
		printf(
			'<input type="color" id="%1$s" name="remotive_theme_options[%1$s]" value="%2$s" />',
			esc_attr( $key ),
			esc_attr( (string) remotive_get_theme_option( $key ) )
		);
		if ( ! empty( $field['helper'] ) ) {
			echo '<p class="rm-admin__row-helper">' . esc_html( $field['helper'] ) . '</p>';
		}
		echo '</div>';
	} else {
		printf(
			'<label class="rm-admin__row-label" for="%1$s">%2$s</label>',
			esc_attr( $field_id ),
			esc_html( $field['label'] )
		);
		echo '<div>';
		remotive_render_text_field( $key, $field['type'] );
		if ( ! empty( $field['helper'] ) ) {
			echo '<p class="rm-admin__row-helper">' . esc_html( $field['helper'] ) . '</p>';
		}
		echo '</div>';
	}

	echo '</div>';
}

/**
 * Sanitize on save. Unknown keys are dropped; every known key is coerced
 * to a safe type regardless of what's submitted.
 */
function remotive_sanitize_theme_options( $input ) {
	$defaults = remotive_theme_option_defaults();
	$input    = is_array( $input ) ? $input : array();
	$clean    = array();

	$clean['contact_email']    = sanitize_email( $input['contact_email'] ?? $defaults['contact_email'] );
	$clean['legal_name']       = sanitize_text_field( $input['legal_name'] ?? $defaults['legal_name'] );
	$clean['legal_uen']        = sanitize_text_field( $input['legal_uen'] ?? $defaults['legal_uen'] );
	$clean['address_line_1']   = sanitize_text_field( $input['address_line_1'] ?? $defaults['address_line_1'] );
	$clean['address_line_2']   = sanitize_text_field( $input['address_line_2'] ?? $defaults['address_line_2'] );
	$clean['address_line_3']   = sanitize_text_field( $input['address_line_3'] ?? $defaults['address_line_3'] );
	$clean['social_instagram'] = remotive_sanitize_optional_url( $input['social_instagram'] ?? $defaults['social_instagram'] );
	$clean['social_linkedin']  = remotive_sanitize_optional_url( $input['social_linkedin'] ?? $defaults['social_linkedin'] );
	$clean['social_tiktok']    = remotive_sanitize_optional_url( $input['social_tiktok'] ?? $defaults['social_tiktok'] );
	$clean['social_facebook']  = remotive_sanitize_optional_url( $input['social_facebook'] ?? $defaults['social_facebook'] );
	$clean['social_x']         = remotive_sanitize_optional_url( $input['social_x'] ?? $defaults['social_x'] );
	$clean['social_youtube']   = remotive_sanitize_optional_url( $input['social_youtube'] ?? $defaults['social_youtube'] );
	$clean['social_threads']   = remotive_sanitize_optional_url( $input['social_threads'] ?? $defaults['social_threads'] );
	$clean['cta_form_action']  = remotive_sanitize_optional_url( $input['cta_form_action'] ?? $defaults['cta_form_action'] );

	// Homepage copy: plain text only, so a single sanitizer covers them all.
	$text_keys = array(
		'hero_eyebrow', 'hero_line_1', 'hero_line_2', 'hero_highlight', 'hero_sub',
		'hero_cta_primary', 'hero_cta_second', 'hero_reassure',
		'problem_heading', 'services_heading', 'why_heading', 'work_heading', 'about_heading',
		'cta_heading', 'cta_button',
		'stat_1_value', 'stat_1_label', 'stat_1_block', 'stat_1_case',
		'stat_2_value', 'stat_2_label', 'stat_2_block', 'stat_2_case',
		'stat_3_value', 'stat_3_label', 'stat_3_block', 'stat_3_case',
	);

	foreach ( $text_keys as $text_key ) {
		$value = $input[ $text_key ] ?? $defaults[ $text_key ];
		$value = sanitize_text_field( $value );

		// An empty heading would leave a hole in the page, so fall back to the
		// shipped default rather than render nothing. The eyebrow is genuinely
		// optional, so it is allowed to be blank.
		if ( '' === trim( $value ) && 'hero_eyebrow' !== $text_key ) {
			$value = $defaults[ $text_key ];
		}

		$clean[ $text_key ] = $value;
	}

	// Team members: add is a new row, edit is a changed row, delete is the
	// row's Remove button (the row never reaches the POST) or a cleared
	// name. Rows are stored in the order submitted, capped at twelve.
	$clean['team'] = array();

	if ( isset( $input['team'] ) && is_array( $input['team'] ) ) {
		foreach ( array_values( $input['team'] ) as $member ) {
			if ( ! is_array( $member ) ) {
				continue;
			}

			$name = sanitize_text_field( $member['name'] ?? '' );

			if ( '' === trim( $name ) ) {
				continue; // A cleared name deletes the member.
			}

			$clean['team'][] = array(
				'name' => $name,
				'role' => sanitize_text_field( $member['role'] ?? '' ),
				'slug' => sanitize_key( $member['slug'] ?? '' ),
				'bio'  => sanitize_textarea_field( $member['bio'] ?? '' ),
				// An unticked checkbox submits nothing, so absence means off.
				'home' => ( isset( $member['home'] ) && '1' === (string) $member['home'] ) ? '1' : '0',
			);

			if ( count( $clean['team'] ) >= 12 ) {
				break;
			}
		}
	} else {
		$clean['team'] = $defaults['team'];
	}

	// An unchecked checkbox submits nothing, so absence means off.
	$clean['branded_login']         = ( isset( $input['branded_login'] ) && '1' === (string) $input['branded_login'] ) ? '1' : '0';
	$clean['motion_effects']        = ( isset( $input['motion_effects'] ) && '1' === (string) $input['motion_effects'] ) ? '1' : '0';
	// Secret: blank keeps the saved key, a valid key replaces it, and the
	// toggle removes it. Only letters and digits are accepted.
	$key_in = isset( $input['pexels_api_key'] ) ? trim( sanitize_text_field( wp_unslash( $input['pexels_api_key'] ) ) ) : '';
	if ( isset( $input['pexels_api_key_clear'] ) && '1' === (string) $input['pexels_api_key_clear'] ) {
		$clean['pexels_api_key'] = '';
	} elseif ( '' !== $key_in && preg_match( '/^[A-Za-z0-9]{20,120}$/', $key_in ) ) {
		$clean['pexels_api_key'] = $key_in;
	} else {
		$clean['pexels_api_key'] = (string) remotive_get_theme_option( 'pexels_api_key' );
	}
	$clean['pexels_api_key_clear']  = '0';
	$clean['maintenance_mode']      = ( isset( $input['maintenance_mode'] ) && '1' === (string) $input['maintenance_mode'] ) ? '1' : '0';
	$clean['graceful_errors']       = ( isset( $input['graceful_errors'] ) && '1' === (string) $input['graceful_errors'] ) ? '1' : '0';
	$clean['branded_login_message'] = sanitize_text_field( $input['branded_login_message'] ?? $defaults['branded_login_message'] );

	foreach ( array( 'problem_sub', 'services_sub', 'why_sub', 'work_sub', 'about_sub', 'cta_sub' ) as $sub_key ) {
		$clean[ $sub_key ] = sanitize_textarea_field( $input[ $sub_key ] ?? $defaults[ $sub_key ] );
	}

	// Retention in months. Zero keeps enquiries indefinitely, which is a
	// deliberate choice a site owner has to make rather than a default.
	$clean['lead_retention_months'] = (string) max( 0, min( 120, (int) ( $input['lead_retention_months'] ?? $defaults['lead_retention_months'] ) ) );

	$theme_mode              = $input['default_theme'] ?? $defaults['default_theme'];
	$clean['default_theme']  = in_array( $theme_mode, array( 'dark', 'light', 'system' ), true ) ? $theme_mode : 'dark';

	// ---- Ticker ----------------------------------------------------------
	$clean['ticker_visible']   = ( isset( $input['ticker_visible'] ) && '1' === (string) $input['ticker_visible'] ) ? '1' : '0';
	$clean['ticker_countries'] = sanitize_text_field( $input['ticker_countries'] ?? $defaults['ticker_countries'] );
	$clean['ticker_bg']        = sanitize_hex_color( $input['ticker_bg'] ?? $defaults['ticker_bg'] ) ?? $defaults['ticker_bg'];
	$clean['ticker_color']     = sanitize_hex_color( $input['ticker_color'] ?? $defaults['ticker_color'] ) ?? $defaults['ticker_color'];

	// Separator accepts hex or rgba — sanitize_hex_color rejects rgba, so
	// allow it through only if it looks like a safe rgba() string.
	$sep_raw = trim( (string) ( $input['ticker_separator'] ?? $defaults['ticker_separator'] ) );
	if ( preg_match( '/^rgba\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*[\d.]+\s*\)$/', $sep_raw ) ) {
		$clean['ticker_separator'] = $sep_raw;
	} else {
		$clean['ticker_separator'] = sanitize_hex_color( $sep_raw ) ?? $defaults['ticker_separator'];
	}

	// Numeric CSS values: each clamped to a sensible range.
	$clean['ticker_size']           = (string) round( max( 0.5, min( 2.5, (float) ( $input['ticker_size']           ?? $defaults['ticker_size'] ) ) ), 2 );
	$clean['ticker_padding_v']      = (string) round( max( 0.2, min( 4.0, (float) ( $input['ticker_padding_v']      ?? $defaults['ticker_padding_v'] ) ) ), 2 );
	$clean['ticker_padding_h']      = (string) round( max( 0.5, min( 8.0, (float) ( $input['ticker_padding_h']      ?? $defaults['ticker_padding_h'] ) ) ), 2 );
	$clean['ticker_speed']          = (string) max( 4, min( 120, (int)   ( $input['ticker_speed']          ?? $defaults['ticker_speed'] ) ) );
	$clean['ticker_letter_spacing'] = (string) round( max( 0.0, min( 0.5, (float) ( $input['ticker_letter_spacing'] ?? $defaults['ticker_letter_spacing'] ) ) ), 3 );

	$allowed_weights = array( '400', '500', '600', '700', '800', '900' );
	$clean['ticker_weight']    = in_array( (string) ( $input['ticker_weight'] ?? $defaults['ticker_weight'] ), $allowed_weights, true )
		? (string) ( $input['ticker_weight'] ?? $defaults['ticker_weight'] )
		: $defaults['ticker_weight'];

	$clean['ticker_direction'] = in_array( (string) ( $input['ticker_direction'] ?? $defaults['ticker_direction'] ), array( 'left', 'right' ), true )
		? (string) ( $input['ticker_direction'] ?? $defaults['ticker_direction'] )
		: 'left';

	remotive_sanitize_colour_options( $input, $clean );

	if ( empty( $clean['contact_email'] ) ) {
		$clean['contact_email'] = $defaults['contact_email'];
		add_settings_error( 'remotive_theme_options', 'invalid_email', __( 'Contact email looked invalid — kept the previous value.', 'remotive' ) );
	}

	return $clean;
}

/**
 * "#" is a deliberate placeholder (not yet wired up), so allow it through
 * as-is rather than rejecting it as an invalid URL.
 */
function remotive_sanitize_optional_url( $value ) {
	$value = trim( (string) $value );
	if ( $value === '' || $value === '#' ) {
		return $value === '' ? '#' : $value;
	}
	return esc_url_raw( $value );
}

/**
 * Admin page under Appearance.
 */
function remotive_add_theme_options_page() {
	$hook = add_theme_page(
		__( 'Theme Options', 'remotive' ),
		__( 'Theme Options', 'remotive' ),
		'edit_theme_options',
		'remotive-theme-options',
		'remotive_render_theme_options_page'
	);

	// Load the admin CSS/JS only on this exact page, not every wp-admin
	// screen — remotive_admin_enqueue_assets() checks against this hook.
	add_action( 'load-' . $hook, function () {
		add_action( 'admin_enqueue_scripts', 'remotive_admin_enqueue_assets' );
	} );
}
add_action( 'admin_menu', 'remotive_add_theme_options_page' );

function remotive_admin_enqueue_assets() {
	wp_enqueue_style( 'dashicons' );

	$css_path = get_stylesheet_directory() . '/assets/css/admin-theme-options.css';
	wp_enqueue_style(
		'remotive-admin-theme-options',
		get_stylesheet_directory_uri() . '/assets/css/admin-theme-options.css',
		array( 'dashicons' ),
		file_exists( $css_path ) ? filemtime( $css_path ) : '1.0.0'
	);

	$js_path = get_stylesheet_directory() . '/assets/js/admin-theme-options.js';
	wp_enqueue_script(
		'remotive-admin-theme-options',
		get_stylesheet_directory_uri() . '/assets/js/admin-theme-options.js',
		array(),
		file_exists( $js_path ) ? filemtime( $js_path ) : '1.0.0',
		true
	);
}

function remotive_render_theme_options_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$tabs        = remotive_theme_options_tabs();
	$tab_keys    = array_keys( $tabs );
	$first_tab   = $tab_keys[0];
	$theme_data  = wp_get_theme();
	?>
	<div class="wrap rm-admin">

		<div class="rm-admin__header">
			<span class="rm-admin__header-icon" aria-hidden="true">
				<picture>
					<source srcset="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/remotive-logo-admin.avif' ); ?>" type="image/avif">
					<img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/remotive-logo-admin.png' ); ?>" alt="" width="34" height="26">
				</picture>
			</span>
			<div class="rm-admin__header-text">
				<h1><?php esc_html_e( 'Theme Options', 'remotive' ); ?></h1>
				<p><?php esc_html_e( 'Site-specific settings for the Remotive Media theme.', 'remotive' ); ?></p>
			</div>
			<span class="rm-admin__version">v<?php echo esc_html( $theme_data->get( 'Version' ) ); ?></span>
		</div>

		<?php settings_errors( 'remotive_theme_options' ); ?>

		<?php if ( isset( $_GET['remotive_restored'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<?php $rm_restored = sanitize_text_field( rawurldecode( wp_unslash( $_GET['remotive_restored'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<?php if ( '0' === $rm_restored ) : ?>
				<div class="notice notice-error is-dismissible">
					<p><?php esc_html_e( 'That item could not be restored. It may no longer ship with the theme.', 'remotive' ); ?></p>
				</div>
			<?php else : ?>
				<div class="notice notice-success is-dismissible">
					<p>
						<?php
						/* translators: %s: restored item title. */
						printf( esc_html__( 'Restored to the shipped version: %s.', 'remotive' ), esc_html( $rm_restored ) );
						?>
					</p>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( isset( $_GET['remotive_setup'] ) && 'done' === $_GET['remotive_setup'] ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible">
				<p>
					<?php
					$rm_created = isset( $_GET['created'] ) ? absint( $_GET['created'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$rm_updated = isset( $_GET['updated'] ) ? absint( $_GET['updated'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					printf(
						/* translators: 1: pages created, 2: pages updated. */
						esc_html__( 'Site setup finished. %1$d page(s) created, %2$d template assignment(s) filled in.', 'remotive' ),
						$rm_created,
						$rm_updated
					);
					?>
				</p>
			</div>
		<?php endif; ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'remotive_theme_options_group' ); ?>

			<?php
			/*
			 * Two panels are not settings fields: the site-setup card
			 * (Shipped content, Menus, Site pages) and the enquiries card.
			 * Both contain their own <form> elements, so they cannot be
			 * nested inside this settings form — they are rendered after
			 * it closes, below.
			 *
			 * Until v1.79.2 they simply sat beneath the whole tabbed area,
			 * which meant they stayed on screen no matter which tab was
			 * selected and read as though they were repeated in every tab.
			 * They are now proper tabs: the buttons join the tablist here,
			 * and their panels carry the same rm-admin__panel markup and
			 * remotive_panel_* ids, so the existing tab script — which
			 * resolves panels with getElementById() from aria-controls —
			 * shows and hides them like any other panel regardless of
			 * where they sit in the DOM.
			 */
			$rm_card_tabs = array(
				'setup'     => array(
					'label' => __( 'Site setup', 'remotive' ),
					'icon'  => 'dashicons-admin-tools',
				),
				'enquiries' => array(
					'label' => __( 'Enquiries', 'remotive' ),
					'icon'  => 'dashicons-email',
				),
			);
			if ( ! function_exists( 'remotive_render_leads_card' ) ) {
				unset( $rm_card_tabs['enquiries'] );
			}
			$rm_all_tabs = $tabs + $rm_card_tabs;
			?>
			<div class="rm-admin__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Theme Options sections', 'remotive' ); ?>">
				<?php foreach ( $rm_all_tabs as $tab_key => $tab ) : ?>
					<button
						type="button"
						role="tab"
						id="remotive_tab_<?php echo esc_attr( $tab_key ); ?>"
						aria-controls="remotive_panel_<?php echo esc_attr( $tab_key ); ?>"
						aria-selected="<?php echo $tab_key === $first_tab ? 'true' : 'false'; ?>"
						tabindex="<?php echo $tab_key === $first_tab ? '0' : '-1'; ?>"
						class="rm-admin__tab"
					>
						<span class="dashicons <?php echo esc_attr( $tab['icon'] ); ?>" aria-hidden="true"></span>
						<?php echo esc_html( $tab['label'] ); ?>
					</button>
				<?php endforeach; ?>
			</div>

			<?php foreach ( $tabs as $tab_key => $tab ) : ?>
				<div
					class="rm-admin__panel<?php echo $tab_key === $first_tab ? ' is-active' : ''; ?>"
					id="remotive_panel_<?php echo esc_attr( $tab_key ); ?>"
					role="tabpanel"
					aria-labelledby="remotive_tab_<?php echo esc_attr( $tab_key ); ?>"
					tabindex="0"
				>
					<div class="rm-admin__card">
						<p class="rm-admin__card-desc"><?php echo wp_kses( $tab['description'], array( 'code' => array() ) ); ?></p>
						<?php
						/*
						 * A tab may declare 'groups' instead of a flat 'fields'
						 * list. Consolidating the four homepage tabs into one
						 * (v1.79.1) put 25 fields on a single panel, which is
						 * coherent as a mental model — "I am editing the
						 * homepage" — but unreadable as one undifferentiated
						 * run of inputs. Groups restore the section boundaries
						 * that the separate tabs used to provide, without
						 * making the owner hunt across tabs to edit one page.
						 *
						 * Tabs that still use a flat 'fields' array render
						 * exactly as before, so this is additive.
						 */
						if ( ! empty( $tab['groups'] ) ) :
							foreach ( $tab['groups'] as $group ) :
								?>
								<div class="rm-admin__group">
									<h3 class="rm-admin__group-title"><?php echo esc_html( $group['label'] ); ?></h3>
									<?php if ( ! empty( $group['description'] ) ) : ?>
										<p class="rm-admin__group-desc"><?php echo esc_html( $group['description'] ); ?></p>
									<?php endif; ?>
									<?php foreach ( $group['fields'] as $field_key => $field ) : ?>
										<?php remotive_render_field_row( $field_key, $field ); ?>
									<?php endforeach; ?>
								</div>
								<?php
							endforeach;
						else :
							foreach ( $tab['fields'] as $field_key => $field ) :
								remotive_render_field_row( $field_key, $field );
							endforeach;
						endif;
						?>
					</div>
				</div>
			<?php endforeach; ?>

			<?php
			/*
			 * The Save button belongs to the settings form, so it is
			 * meaningless on the two card tabs — those manage their own
			 * state through their own forms. It is hidden there by the tab
			 * script rather than moved, so the settings tabs keep the
			 * standard WordPress submit markup and its styling.
			 */
			?>
			<div class="rm-admin__submit">
				<?php submit_button(); ?>
			</div>
		</form>

		<div
			class="rm-admin__panel"
			id="remotive_panel_setup"
			role="tabpanel"
			aria-labelledby="remotive_tab_setup"
			tabindex="0"
		>
			<?php remotive_render_site_setup_card(); ?>
		</div>

		<?php if ( function_exists( 'remotive_render_leads_card' ) ) : ?>
			<div
				class="rm-admin__panel"
				id="remotive_panel_enquiries"
				role="tabpanel"
				aria-labelledby="remotive_tab_enquiries"
				tabindex="0"
			>
				<?php remotive_render_leads_card(); ?>
			</div>
		<?php endif; ?>

		<div class="rm-admin__footer">
			<span><?php esc_html_e( 'See docs/ssot.md and readme.md in the theme folder for full documentation.', 'remotive' ); ?></span>
			<a href="https://menj.blog" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Built by MENJ', 'remotive' ); ?></a>
		</div>

	</div>
	<?php
}

/**
 * Replace __REMOTIVE_*__ tokens in rendered block output with the live
 * option values. Runs on every block's output, every request — cheap,
 * since it's a handful of str_replace calls against short strings.
 */
/**
 * The saved team roster with empty rows dropped; the single source for the
 * grids and the Organization schema's employee list.
 *
 * @return array<int,array{name:string,role:string,slug:string,bio:string}>
 */
function remotive_team_members() {
	$team = remotive_get_theme_option( 'team' );
	$team = is_array( $team ) ? $team : array();
	$out  = array();

	// A roster saved before the leadership flag existed has no 'home' key on
	// any row. Those members take the shipped default for their slug rather
	// than all reading as "not a leader", which would empty the homepage.
	$shipped = array();

	foreach ( remotive_theme_option_defaults()['team'] as $default ) {
		$shipped[ $default['slug'] ] = $default['home'] ?? '0';
	}

	foreach ( $team as $member ) {
		if ( ! is_array( $member ) || '' === trim( $member['name'] ?? '' ) ) {
			continue;
		}

		$slug = (string) ( $member['slug'] ?? '' );
		$home = array_key_exists( 'home', $member ) ? $member['home'] : ( $shipped[ $slug ] ?? '0' );

		$out[] = array(
			'name' => (string) $member['name'],
			'role' => (string) ( $member['role'] ?? '' ),
			'slug' => $slug,
			'bio'  => (string) ( $member['bio'] ?? '' ),
			'home' => '1' === (string) $home ? '1' : '0',
		);
	}

	return $out;
}

/**
 * Columns for the Team page grid: whichever of 3, 4 or 5 leaves the last row
 * fullest, so ten people sit as two rows of five rather than 4 + 4 + 2, and
 * eleven do not strand one tile on a row of five. A tie goes to the wider
 * grid. The roster is editable, so this follows its size instead of a number
 * that would be right only until the next person is added.
 *
 * @param int $count Number of members shown.
 * @return int 3, 4 or 5.
 */
function remotive_team_columns( $count ) {
	// An empty roster has no last row to fill; keep the stylesheet default.
	if ( $count < 1 ) {
		return 4;
	}

	$best = 4;
	$fill = -1.0;

	foreach ( array( 3, 4, 5 ) as $cols ) {
		$last = $count % $cols;
		$last = 0 === $last ? $cols : $last;
		$rate = $last / $cols;

		if ( $rate >= $fill ) { // >= so a tie goes to the wider grid.
			$fill = $rate;
			$best = $cols;
		}
	}

	return $best;
}

/**
 * A count as a word: 10 -> "ten" / "Ten". Copy that says how many people
 * there are cannot be hardcoded when the roster is editable; it goes stale
 * the day someone is added. Past twenty it falls back to digits.
 */
function remotive_number_word( $n, $capital = false ) {
	$words = array( 'zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten',
		'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen', 'twenty' );
	$word  = $words[ $n ] ?? (string) $n;

	return $capital ? ucfirst( $word ) : $word;
}

/**
 * Member figures for a team grid, escaped here at output. The wrapper div
 * (.rm-team / .rm-team--full) stays in the template; this renders only the
 * figures inside it, teaser without bios, About grid with them.
 *
 * @param bool $with_bios Include each member's bio paragraph.
 * @return string
 */
/**
 * A short leadership row leaves most of a four-column grid empty, and two
 * portraits in the left half read as unfinished. The space goes to the
 * prompt that answers "where is everyone else": a tile in the row itself,
 * spanning the columns the portraits do not use.
 *
 * Only for fewer than four shown (four or more fill the row, and the line
 * under it, remotive_render_team_more(), carries the prompt instead).
 *
 * @param int $shown How many leaders the row is already showing.
 * @return string
 */
function remotive_render_team_tile( $shown ) {
	$others = count( remotive_team_members() ) - $shown;

	if ( $shown >= 4 || $others < 1 ) {
		return '';
	}

	return '<a class="rm-team__member rm-team__tile" style="--rm-span:' . (int) ( 4 - $shown ) . '" href="' . esc_url( home_url( '/team/' ) ) . '">' .
		'<span class="rm-team__tile-count">+' . (int) $others . '</span>' .
		'<span class="rm-team__tile-label">' . esc_html( _n( 'more specialist, running their own discipline', 'more specialists, each running their own discipline', $others, 'remotive' ) ) . '</span>' .
		'<span class="rm-team__tile-cta">' . esc_html__( 'Meet the whole team', 'remotive' ) . ' &rarr;</span>' .
		'</a>';
}

/**
 * The line under the homepage leadership row: how many more people there are
 * and where to find them. Empty when the homepage already shows everyone.
 *
 * @return string
 */
function remotive_render_team_more() {
	$all    = remotive_team_members();
	$shown  = count( array_filter( $all, function ( $m ) {
		return '1' === $m['home'];
	} ) );
	$shown  = $shown ? $shown : min( 4, count( $all ) );
	$others = count( $all ) - $shown;

	// Nothing more to say, or a short row that already has its tile.
	if ( $others < 1 || $shown < 4 ) {
		return '';
	}

	return '<p class="rm-team__more">' . esc_html( sprintf(
		/* translators: %s: number of further team members, as a word. */
		_n( 'Plus %s more specialist, each running their own discipline.', 'Plus %s more specialists, each running their own discipline.', $others, 'remotive' ),
		remotive_number_word( $others )
	) ) . ' <a href="' . esc_url( home_url( '/team/' ) ) . '">' . esc_html__( 'Meet the whole team', 'remotive' ) . ' &rarr;</a></p>';
}

/**
 * Per-member portrait CSS, generated from the roster for every slug whose
 * AVIF actually ships in assets/team/. This is what makes the Team tab's
 * promise true: a new member is a roster row plus one image file, with no
 * stylesheet edit. Attached as an inline style to the main
 * stylesheet so it participates in the same load path, print excluded as
 * team photos collapse on paper anyway.
 *
 * @return string CSS, possibly empty.
 */
function remotive_team_portrait_css() {
	$dir  = get_stylesheet_directory() . '/assets/team/';
	$uri  = get_stylesheet_directory_uri() . '/assets/team/';
	$grad = 'linear-gradient(135deg,var(--rm-line),var(--wp--preset--color--paper-2))';
	$css  = '';

	foreach ( remotive_team_members() as $member ) {
		$slug = $member['slug'];

		if ( ! $slug || ! is_readable( $dir . $slug . '.avif' ) ) {
			continue;
		}

		$sel  = '.rm-team__photo[data-person="' . $slug . '"]';

		// The file's modified time is the cache key. A replaced portrait keeps
		// its filename, so without this a browser or CDN that already holds the
		// old one keeps showing it, and replacing a photo would look like it had
		// not worked. The URL changes exactly when the file does.
		$base = $uri . rawurlencode( $slug ) . '.avif?v=' . (int) filemtime( $dir . $slug . '.avif' );
		$css .= $sel . '{background:url(' . $base . ') center bottom/cover no-repeat,' . $grad . ';}';
		$css .= $sel . ' span{display:none;}';
	}

	return $css;
}

function remotive_render_team_markup( $with_bios, $scope = 'all' ) {
	$html    = '';
	$members = remotive_team_members();
	$leaders = array_values( array_filter( $members, function ( $m ) {
		return '1' === $m['home'];
	} ) );

	if ( 'home' === $scope ) {
		// The homepage introduces the leadership; the Team page holds
		// everyone. If nobody is flagged, show the first four rather than a
		// heading over an empty grid.
		$members = $leaders ? $leaders : array_slice( $members, 0, 4 );
	} elseif ( $leaders ) {
		// Team page: leadership first, then everyone else in roster order.
		$members = array_merge(
			$leaders,
			array_values( array_filter( $members, function ( $m ) {
				return '1' !== $m['home'];
			} ) )
		);
	}

	foreach ( $members as $member ) {
		$person = $member['slug'] ? ' data-person="' . esc_attr( $member['slug'] ) . '"' : '';
		$bio    = '';

		if ( $with_bios && '' !== trim( $member['bio'] ) ) {
			$bio = '<p class="rm-team__bio">' . esc_html( $member['bio'] ) . '</p>';
		}

		// The span is the no-portrait fallback: the per-slug CSS from
		// remotive_team_portrait_css() hides it whenever a portrait file
		// exists. It used to say the literal word "Photo", which read as
		// unfinished placeholder content on any member whose portrait
		// hadn't been supplied yet. Branded initials on the gradient are
		// a deliberate visual state instead — never a fabricated
		// likeness, never an apology.
		$words    = preg_split( '/\s+/', trim( $member['name'] ) );
		$initials = '';
		if ( $words && '' !== $words[0] ) {
			$initials .= mb_strtoupper( mb_substr( $words[0], 0, 1 ) );
			if ( count( $words ) > 1 ) {
				$initials .= mb_strtoupper( mb_substr( end( $words ), 0, 1 ) );
			}
		}

		$html .= '<figure class="rm-team__member">' .
			'<div class="rm-team__photo"' . $person . '><span>' . esc_html( $initials ) . '</span></div>' .
			'<figcaption>' .
			'<strong class="rm-team__name">' . esc_html( $member['name'] ) . '</strong>' .
			'<span class="rm-team__role">' . esc_html( $member['role'] ) . '</span>' .
			$bio .
			'</figcaption>' .
			'</figure>';
	}

	if ( 'home' === $scope ) {
		$html .= remotive_render_team_tile( count( $members ) );
	}

	return $html;
}

/**
 * Render every configured social icon, and only configured ones.
 *
 * Previously each of the 7 icons was static markup in the templates,
 * always rendered, with __REMOTIVE_SOCIAL_*__ tokens filling in just the
 * href. An unconfigured field defaults to '#' (see
 * remotive_theme_option_defaults()), so a site that hasn't filled in, say,
 * Threads yet still showed a clickable Threads icon that went nowhere —
 * exactly the dead click target the Theme Options screen's own helper
 * text promises won't happen ("blank hides the icon"). Rendering the
 * whole set server-side, skipping anything blank or still '#', is what
 * actually keeps that promise; a per-icon token can only fill in an
 * href, not remove the anchor around it. remotive_schema_same_as() in
 * inc/content/schema-markup.php runs the identical not-empty / not-'#' /
 * valid-URL check for the same reason, on the same seven keys.
 *
 * @return string Anchor markup for configured platforms, in a fixed
 *                order, or '' if none are configured.
 */
function remotive_render_social_links() {
	$platforms = array(
		'social_facebook'  => array(
			'label' => 'Facebook',
			'icon'  => '<svg fill="currentColor" role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M9.101 23.691v-7.98H6.627v-3.667h2.474v-1.58c0-4.085 1.848-5.978 5.858-5.978.401 0 .955.042 1.468.103a8.68 8.68 0 0 1 1.141.195v3.325a8.623 8.623 0 0 0-.653-.036 26.805 26.805 0 0 0-.733-.008c-.707 0-1.259.096-1.675.309a1.686 1.686 0 0 0-.679.622c-.258.42-.374.995-.374 1.752v1.296h3.919l-.386 1.848-.287 1.42-.226.399h-3.62v8.98h-3.53z"/></svg>',
		),
		'social_instagram' => array(
			'label' => 'Instagram',
			'icon'  => '<svg fill="currentColor" role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M7.0301.084c-1.2768.0602-2.1487.264-2.911.5634-.7888.3075-1.4575.72-2.1228 1.3877-.6652.6677-1.075 1.3368-1.3802 2.127-.2954.7638-.4956 1.6365-.552 2.914-.0564 1.2775-.0689 1.6882-.0626 4.947.0062 3.2586.0206 3.6671.0825 4.9473.061 1.2765.264 2.1482.5635 2.9107.308.7889.72 1.4573 1.388 2.1228.6679.6655 1.3365 1.0743 2.1285 1.38.7632.295 1.6361.4961 2.9134.552 1.2773.056 1.6884.069 4.9462.0627 3.2578-.0062 3.668-.0207 4.9478-.0814 1.28-.0607 2.147-.2652 2.9098-.5633.7889-.3086 1.4578-.72 2.1228-1.3881.665-.6682 1.0745-1.3378 1.3795-2.1284.2957-.7632.4966-1.636.552-2.9124.056-1.2809.0692-1.6898.063-4.948-.0063-3.2583-.021-3.6668-.0817-4.9465-.0607-1.2797-.264-2.1487-.5633-2.9117-.3084-.7889-.72-1.4568-1.3876-2.1228C21.2982 1.33 20.628.9208 19.8378.6165 19.074.321 18.2017.1197 16.9244.0645 15.6471.0093 15.236-.005 11.977.0014 8.718.0076 8.31.0215 7.0301.0839m.1402 21.6932c-1.17-.0509-1.8053-.2453-2.2287-.408-.5606-.216-.96-.4771-1.3819-.895-.422-.4178-.6811-.8186-.9-1.378-.1644-.4234-.3624-1.058-.4171-2.228-.0595-1.2645-.072-1.6442-.079-4.848-.007-3.2037.0053-3.583.0607-4.848.05-1.169.2456-1.805.408-2.2282.216-.5613.4762-.96.895-1.3816.4188-.4217.8184-.6814 1.3783-.9003.423-.1651 1.0575-.3614 2.227-.4171 1.2655-.06 1.6447-.072 4.848-.079 3.2033-.007 3.5835.005 4.8495.0608 1.169.0508 1.8053.2445 2.228.408.5608.216.96.4754 1.3816.895.4217.4194.6816.8176.9005 1.3787.1653.4217.3617 1.056.4169 2.2263.0602 1.2655.0739 1.645.0796 4.848.0058 3.203-.0055 3.5834-.061 4.848-.051 1.17-.245 1.8055-.408 2.2294-.216.5604-.4763.96-.8954 1.3814-.419.4215-.8181.6811-1.3783.9-.4224.1649-1.0577.3617-2.2262.4174-1.2656.0595-1.6448.072-4.8493.079-3.2045.007-3.5825-.006-4.848-.0608M16.953 5.5864A1.44 1.44 0 1 0 18.39 4.144a1.44 1.44 0 0 0-1.437 1.4424M5.8385 12.012c.0067 3.4032 2.7706 6.1557 6.173 6.1493 3.4026-.0065 6.157-2.7701 6.1506-6.1733-.0065-3.4032-2.771-6.1565-6.174-6.1498-3.403.0067-6.156 2.771-6.1496 6.1738M8 12.0077a4 4 0 1 1 4.008 3.9921A3.9996 3.9996 0 0 1 8 12.0077"/></svg>',
		),
		'social_x'         => array(
			'label' => 'X',
			'icon'  => '<svg fill="currentColor" role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
		),
		'social_youtube'   => array(
			'label' => 'YouTube',
			'icon'  => '<svg fill="currentColor" role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>',
		),
		'social_threads'   => array(
			'label' => 'Threads',
			'icon'  => '<svg fill="currentColor" role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12.186 24h-.007c-3.581-.024-6.334-1.205-8.184-3.509C2.35 18.44 1.5 15.586 1.472 12.01v-.017c.03-3.579.879-6.43 2.525-8.482C5.845 1.205 8.6.024 12.18 0h.014c2.746.02 5.043.725 6.826 2.098 1.677 1.29 2.858 3.13 3.509 5.467l-2.04.569c-1.104-3.96-3.898-5.984-8.304-6.015-2.91.022-5.11.936-6.54 2.717-1.34 1.669-2.033 4.06-2.06 7.106.027 3.046.72 5.437 2.06 7.106 1.43 1.781 3.63 2.695 6.54 2.717 2.623-.02 4.358-.631 5.8-2.04 1.647-1.61 1.618-3.593 1.088-4.759-.313-.692-.877-1.27-1.618-1.686-.192 1.352-.622 2.446-1.284 3.239-.886 1.058-2.146 1.636-3.75 1.741-.936.06-1.874-.08-2.65-.454-.83-.4-1.454-1.055-1.716-1.816-.394-1.142-.086-2.522.774-3.512.86-.99 2.31-1.585 4.16-1.585.44 0 .863.043 1.263.128-.055-.578-.245-1.04-.567-1.375-.395-.409-1.017-.62-1.848-.62h-.012c-.858.003-1.578.24-2.208.729l-1.302-1.548c.94-.762 2.033-1.148 3.253-1.148h.02c1.4 0 2.535.36 3.377 1.07.842.71 1.34 1.75 1.478 3.09.09.09.176.184.256.281.68.828.909 1.836.678 2.996-.376 1.891-1.99 3.037-4.795 3.406-1.4.183-2.723-.116-3.727-.836a3.526 3.526 0 0 1-1.457-2.204c-.153-.702-.058-1.417.276-2.058-.5-.207-.92-.55-1.222-1.007-.508-.767-.51-1.752-.007-2.634.596-1.04 1.802-1.706 3.32-1.826.163-.013.323-.02.48-.02z"/></svg>',
		),
		'social_linkedin'  => array(
			'label' => 'LinkedIn',
			'icon'  => '<!--! Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0) --><svg fill="currentColor" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M100.28 448H7.4V148.9h92.88zM53.79 108.1C24.09 108.1 0 83.5 0 53.8a53.79 53.79 0 0 1 107.58 0c0 29.7-24.1 54.3-53.79 54.3zM447.9 448h-92.68V302.4c0-34.7-.7-79.2-48.29-79.2-48.29 0-55.69 37.7-55.69 76.7V448h-92.78V148.9h89.08v40.8h1.3c12.4-23.5 42.69-48.3 87.88-48.3 94 0 111.28 61.9 111.28 142.3V448z"/></svg>',
		),
		'social_tiktok'    => array(
			'label' => 'TikTok',
			'icon'  => '<svg fill="currentColor" role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07"/></svg>',
		),
	);

	$html = '';
	foreach ( $platforms as $key => $platform ) {
		$url = trim( (string) remotive_get_theme_option( $key ) );
		if ( '' === $url || '#' === $url || false === filter_var( $url, FILTER_VALIDATE_URL ) ) {
			continue;
		}
		$html .= sprintf(
			'<a class="rm-social-link" href="%1$s" aria-label="%2$s (opens in new tab)" target="_blank" rel="noopener noreferrer">%3$s</a>',
			esc_url( $url ),
			esc_attr( $platform['label'] ),
			$platform['icon']
		);
	}

	return $html;
}

/**
 * Generate the inline CSS that applies Theme Options ticker settings to
 * the live ticker band.
 *
 * Uses CSS custom properties scoped to .rm-ticker so existing base rules
 * remain the fallback and every property is overridable from a single
 * computed block. The animation duration scales with the speed option;
 * direction is implemented by reversing the keyframe translation target
 * and swapping the flex direction on the track, which keeps the
 * IntersectionObserver pause logic untouched.
 *
 * Returns '' when the ticker is hidden, so no CSS is injected at all.
 *
 * @return string
 */
function remotive_ticker_css() {
	if ( '1' !== (string) remotive_get_theme_option( 'ticker_visible' ) ) {
		// Band hidden — suppress the band entirely.
		return '.rm-ticker-band { display:none !important; }';
	}

	$bg        = remotive_get_theme_option( 'ticker_bg' );
	$color     = remotive_get_theme_option( 'ticker_color' );
	$sep       = remotive_get_theme_option( 'ticker_separator' );
	$size      = (float) remotive_get_theme_option( 'ticker_size' );
	$weight    = remotive_get_theme_option( 'ticker_weight' );
	$tracking  = (float) remotive_get_theme_option( 'ticker_letter_spacing' );
	$pad_v     = (float) remotive_get_theme_option( 'ticker_padding_v' );
	$pad_h     = (float) remotive_get_theme_option( 'ticker_padding_h' );
	$speed     = (int)   remotive_get_theme_option( 'ticker_speed' );
	$direction = remotive_get_theme_option( 'ticker_direction' );

	// Scale animation duration by country count so perceived speed stays
	// consistent regardless of how many items the track contains: more
	// countries = longer track = proportionally longer duration.
	$countries = array_filter( array_map( 'trim', explode( ',', remotive_get_theme_option( 'ticker_countries' ) ) ) );
	$count     = max( 1, count( $countries ) );
	$base      = max( 4, $speed );
	// The default (26s at 5 countries) is the reference point.
	$duration  = round( $base * ( $count / 5 ), 2 );

	$translate = 'right' === $direction ? '50%' : '-50%';
	$flex_dir  = 'right' === $direction ? 'row-reverse' : 'row';

	// Only emit colours when they differ from the defaults. The defaults
	// are a fixed navy band on cream text, which is correct in light mode
	// and wrong in dark: emitting them unconditionally overrode the
	// stylesheet's mode-aware rule and produced a navy band on the navy
	// dark-mode page, visible only as floating text and dividers. Leaving
	// them out lets .rm-ticker's own ink/paper rule invert with the mode
	// as it was designed to. An owner who has deliberately picked colours
	// still gets exactly what they picked, in both modes.
	$defaults = remotive_theme_option_defaults();
	$css      = '';

	if ( $bg !== $defaults['ticker_bg'] || $color !== $defaults['ticker_color'] ) {
		$css .= '.rm-ticker{';
		$css .= 'background:' . esc_attr( $bg ) . ';';
		$css .= 'color:' . esc_attr( $color ) . ';';
		$css .= '}';
	}

	$css .= '.rm-ticker__track{';
	$css .= 'animation-duration:' . $duration . 's;';
	$css .= 'flex-direction:' . esc_attr( $flex_dir ) . ';';
	$css .= '}';

	$css .= '@keyframes rm-scroll{';
	$css .= 'from{transform:translateX(0);}';
	$css .= 'to{transform:translateX(' . esc_attr( $translate ) . ');}';
	$css .= '}';

	$css .= '.rm-ticker__item{';
	$css .= 'font-size:' . $size . 'rem;';
	$css .= 'font-weight:' . esc_attr( $weight ) . ';';
	$css .= 'letter-spacing:' . $tracking . 'em;';
	$css .= 'padding:' . $pad_v . 'rem ' . $pad_h . 'rem;';
	// Same reasoning as the band colours above: the default separator is a
	// translucent white, which is invisible against the white band dark
	// mode is supposed to render. Omitting it at the default lets the
	// stylesheet's currentColor-derived divider follow the text colour in
	// either mode.
	if ( $sep !== $defaults['ticker_separator'] ) {
		$css .= 'border-right:1px solid ' . esc_attr( $sep ) . ';';
	}
	$css .= '}';

	return $css;
}

/**
 * Render the ticker strip's inner HTML from the configured country list.
 *
 * The seamless infinite-scroll illusion requires the list to repeat enough
 * times that the CSS animation never reveals a gap. Four repetitions cover
 * any reasonable viewport width at any font size; the CSS animation translates
 * by exactly 50% (two repetitions' worth), so the loop point is invisible.
 *
 * Each country name is output as a plain text node inside a <span> — no HTML
 * allowed, sanitize_text_field() already stripped tags in the sanitizer. The
 * entire strip is aria-hidden="true" in the template (decorative, not
 * content), so there is nothing to translate here.
 *
 * @return string Ready-to-inject HTML, or '' when the option is empty.
 */
function remotive_render_ticker() {
	if ( '1' !== (string) remotive_get_theme_option( 'ticker_visible' ) ) {
		return '';
	}

	$raw = remotive_get_theme_option( 'ticker_countries' );

	$items = array_filter(
		array_map( 'trim', explode( ',', $raw ) ),
		static function( $v ) { return '' !== $v; }
	);

	if ( count( $items ) < 2 ) {
		// A single item or empty list can't loop: hide the band silently.
		return '';
	}

	// Build one repetition, then clone it three more times so the CSS
	// animation always has content to scroll into.
	$one = '';
	foreach ( $items as $item ) {
		$one .= '<span class="rm-ticker__item">' . esc_html( $item ) . '</span>';
	}

	return $one . $one . $one . $one;
}


/**
 * Build the footer copyright line.
 *
 * The year was hardcoded as "2026" in parts/footer.html, which would have
 * silently gone stale on 1 January and stayed wrong until somebody noticed.
 * date_i18n() rather than date(): the year is rendered in the site's locale,
 * which matters for locales that do not use the Gregorian calendar.
 *
 * The name is the registered entity, not the brand mark. The footer
 * copyright line is the one place on the site where the legal name belongs
 * — "Re:Motive Media" is the brand and is correct everywhere else.
 *
 * The UEN is included when set. Singapore's Companies Act section 144
 * requires a company's registered name and registration number on its
 * business communications and publications, and a website footer is where
 * that is conventionally satisfied. Clearing the field omits it, since the
 * requirement does not apply to every entity that might use this theme.
 *
 * @return string Escaped, ready to splice into rendered block output.
 */
function remotive_copyright_line() {
	$name = trim( (string) remotive_get_theme_option( 'legal_name' ) );
	$uen  = trim( (string) remotive_get_theme_option( 'legal_uen' ) );
	$year = date_i18n( 'Y' );

	if ( '' === $name ) {
		$name = get_bloginfo( 'name' );
	}

	$entity = esc_html( $name );

	if ( '' !== $uen ) {
		/* translators: %s: company registration number */
		$entity .= ' ' . sprintf( esc_html__( '(UEN %s)', 'remotive' ), esc_html( $uen ) );
	}

	return sprintf(
		/* translators: 1: year, 2: company name, possibly followed by a registration number */
		esc_html__( '&copy; %1$s %2$s. All rights reserved.', 'remotive' ),
		esc_html( $year ),
		$entity
	);
}

/**
 * Legal links for the footer bottom bar.
 *
 * These already appear in the footer's Company column, but the bottom bar
 * is where visitors and regulators look for them, and under the PDPA a
 * privacy policy has to be readily accessible. Rendered from the real
 * pages so a renamed or missing page drops its link rather than 404ing.
 *
 * @return string
 */
function remotive_footer_legal_links() {
	$links = array(
		'privacy' => __( 'Privacy Policy', 'remotive' ),
		'terms'   => __( 'Terms of Service', 'remotive' ),
	);

	$out = array();

	foreach ( $links as $slug => $label ) {
		$page = get_page_by_path( $slug, OBJECT, 'page' );

		if ( ! $page || 'publish' !== $page->post_status ) {
			continue;
		}

		$out[] = '<a href="' . esc_url( get_permalink( $page->ID ) ) . '">' . esc_html( $label ) . '</a>';
	}

	return $out ? '<span class="rm-footer__legal">' . implode( '<span aria-hidden="true"> · </span>', $out ) . '</span>' : '';
}

function remotive_replace_theme_option_tokens( $block_content, $block ) {
	static $map = null;

	if ( $map === null ) {
		// "Sanitize on input, escape on output" — remotive_sanitize_theme_options()
		// already sanitizes every value before it's stored (sanitize_email,
		// sanitize_text_field, esc_url_raw as appropriate per field — see
		// that function). esc_url_raw() is the *storage* form, deliberately
		// less aggressive than esc_url(), which is the correct function for
		// injecting a URL directly into rendered HTML. Re-escaping here,
		// right before these values are spliced into already-rendered block
		// output, means this filter doesn't depend on the storage-time
		// sanitizer being (or staying) sufficient on its own — each stage
		// does its own job, independently.
		$map = array(
			'__REMOTIVE_CONTACT_EMAIL__'    => esc_html( remotive_get_theme_option( 'contact_email' ) ),
			// Homepage copy. esc_html() throughout: these are plain strings
			// spliced into rendered block output, never markup.
			'__REMOTIVE_HERO_EYEBROW__'     => esc_html( remotive_get_theme_option( 'hero_eyebrow' ) ),
			'__REMOTIVE_HERO_LINE_1__'      => esc_html( remotive_get_theme_option( 'hero_line_1' ) ),
			'__REMOTIVE_HERO_LINE_2__'      => esc_html( remotive_get_theme_option( 'hero_line_2' ) ),
			'__REMOTIVE_HERO_HIGHLIGHT__'   => esc_html( remotive_get_theme_option( 'hero_highlight' ) ),
			'__REMOTIVE_HERO_SUB__'         => esc_html( remotive_get_theme_option( 'hero_sub' ) ),
			'__REMOTIVE_HERO_CTA_PRIMARY__' => esc_html( remotive_get_theme_option( 'hero_cta_primary' ) ),
			'__REMOTIVE_HERO_CTA_SECOND__'  => esc_html( remotive_get_theme_option( 'hero_cta_second' ) ),
			'__REMOTIVE_HERO_REASSURE__'    => esc_html( remotive_get_theme_option( 'hero_reassure' ) ),
			'__REMOTIVE_TICKER__'           => remotive_render_ticker(),
			'__REMOTIVE_PROBLEM_HEADING__'  => esc_html( remotive_get_theme_option( 'problem_heading' ) ),
			'__REMOTIVE_SERVICES_HEADING__' => esc_html( remotive_get_theme_option( 'services_heading' ) ),
			'__REMOTIVE_WHY_HEADING__'      => esc_html( remotive_get_theme_option( 'why_heading' ) ),
			'__REMOTIVE_WORK_HEADING__'     => esc_html( remotive_get_theme_option( 'work_heading' ) ),
			'__REMOTIVE_ABOUT_HEADING__'    => esc_html( remotive_get_theme_option( 'about_heading' ) ),
			'__REMOTIVE_STAT_1_VALUE__'     => esc_html( remotive_get_theme_option( 'stat_1_value' ) ),
			'__REMOTIVE_STAT_1_LABEL__'     => esc_html( remotive_get_theme_option( 'stat_1_label' ) ),
			'__REMOTIVE_STAT_2_VALUE__'     => esc_html( remotive_get_theme_option( 'stat_2_value' ) ),
			'__REMOTIVE_STAT_2_LABEL__'     => esc_html( remotive_get_theme_option( 'stat_2_label' ) ),
			'__REMOTIVE_STAT_3_VALUE__'     => esc_html( remotive_get_theme_option( 'stat_3_value' ) ),
			'__REMOTIVE_STAT_3_LABEL__'     => esc_html( remotive_get_theme_option( 'stat_3_label' ) ),
			'__REMOTIVE_ADDRESS_LINE_1__'   => esc_html( remotive_get_theme_option( 'address_line_1' ) ),
			'__REMOTIVE_ADDRESS_LINE_2__'   => esc_html( remotive_get_theme_option( 'address_line_2' ) ),
			'__REMOTIVE_ADDRESS_LINE_3__'   => esc_html( remotive_get_theme_option( 'address_line_3' ) ),
			'__REMOTIVE_SOCIAL_LINKS__'    => remotive_render_social_links(),
			'__REMOTIVE_CTA_FORM_ACTION__'  => esc_url( remotive_get_theme_option( 'cta_form_action' ) ),
			// Not admin-configurable like the CTA's — see
			// inc/forms/about-form-handler.php's docblock for why this one
			// always uses the native handler directly.
			'__REMOTIVE_ABOUT_FORM_ACTION__' => esc_url( admin_url( 'admin-post.php' ) ),
			// Same reasoning as the About form's — always the native
			// handler, no admin-configurable override.
			'__REMOTIVE_CONTACT_FORM_ACTION__' => esc_url( admin_url( 'admin-post.php' ) ),
			// wp_nonce_field() with $echo=false returns the HTML string
			// instead of printing it — needed here since block templates
			// can't run PHP directly (see readme.md's "Theme Options"
			// section for why this token-substitution mechanism exists
			// at all). Safe to compute once per request: nonces are
			// deterministic per user+action+time-window, not random per
			// call, so every block containing this token gets the same
			// (correct) value. Not escaped here — it's WP core's own
			// trusted output, already safe HTML.
			// Team grids: markup, not plain text, so not esc_html'd here —
			// remotive_render_team_markup() escapes every member field at
			// the point it is spliced into its own trusted wrapper.
			'__REMOTIVE_PROBLEM_SUB__'      => esc_html( remotive_get_theme_option( 'problem_sub' ) ),
			'__REMOTIVE_SERVICES_SUB__'     => esc_html( remotive_get_theme_option( 'services_sub' ) ),
			'__REMOTIVE_WHY_SUB__'          => esc_html( remotive_get_theme_option( 'why_sub' ) ),
			'__REMOTIVE_WORK_SUB__'         => esc_html( remotive_get_theme_option( 'work_sub' ) ),
			'__REMOTIVE_ABOUT_SUB__'        => esc_html( remotive_get_theme_option( 'about_sub' ) ),
			'__REMOTIVE_CTA_HEADING__'      => esc_html( remotive_get_theme_option( 'cta_heading' ) ),
			'__REMOTIVE_CTA_SUB__'          => esc_html( remotive_get_theme_option( 'cta_sub' ) ),
			'__REMOTIVE_CTA_BUTTON__'       => esc_html( remotive_get_theme_option( 'cta_button' ) ),
			'__REMOTIVE_TEAM_TEASER__'      => remotive_render_team_markup( false, 'home' ),
			'__REMOTIVE_TEAM_MORE__'        => remotive_render_team_more(),
			'__REMOTIVE_TEAM_FULL__'        => remotive_render_team_markup( true ),
			'__REMOTIVE_TEAM_COUNT__'       => remotive_number_word( count( remotive_team_members() ), true ),
			'__REMOTIVE_TEAM_COLS__'        => (string) remotive_team_columns( count( remotive_team_members() ) ),
			'__REMOTIVE_CTA_NONCE_FIELD__'  => wp_nonce_field( 'remotive_cta_submit', 'remotive_cta_nonce', true, false ),
			'__REMOTIVE_ABOUT_NONCE_FIELD__' => wp_nonce_field( 'remotive_about_submit', 'remotive_about_nonce', true, false ),
			'__REMOTIVE_CONTACT_NONCE_FIELD__' => wp_nonce_field( 'remotive_contact_submit', 'remotive_contact_nonce', true, false ),
			// Already escaped inside their own builders — see the notes there.
			'__REMOTIVE_COPYRIGHT__'        => remotive_copyright_line(),
			'__REMOTIVE_LEGAL_LINKS__'      => remotive_footer_legal_links(),
		);

		/**
		 * Allow other modules to register token replacements.
		 *
		 * Values added here are spliced into rendered block output as-is,
		 * so a filter that adds one is responsible for escaping it — the
		 * same contract the entries above follow. Used by
		 * inc/content/feature-grids.php, which returns markup rather than a
		 * plain string and therefore cannot be escaped at this stage.
		 *
		 * @param array $map Token => replacement.
		 */
		$map = apply_filters( 'remotive_theme_option_tokens', $map );
	}

	if ( strpos( $block_content, '__REMOTIVE_' ) === false ) {
		return $block_content;
	}

	return strtr( $block_content, $map );
}
add_filter( 'render_block', 'remotive_replace_theme_option_tokens', 10, 2 );

/**
 * Tell the front end which mode first-time visitors should see. The
 * toggle script (assets/js/remotive.js) reads window.remotiveThemeOptions
 * and falls back to 'dark' if this never runs for some reason. This data
 * is also duplicated into an early <head> script by
 * remotive_prevent_theme_flash() below — that early copy is what actually
 * prevents the flash; this footer copy exists so remotive.js's own
 * logic (wiring up the click handler, keeping aria-pressed in sync) has
 * the same values available without needing to parse anything out of the
 * page itself. (Fixed in 1.65.7: this was attached to the nonexistent
 * 'remotive-theme-toggle' handle — a leftover from before the front-end
 * scripts were consolidated into one remotive-front bundle — so it never
 * actually printed, and remotive.js's own defaultTheme logic silently
 * always fell back to 'dark'.)
 */
function remotive_inline_theme_option_data() {
	$default_theme = remotive_get_theme_option( 'default_theme' );

	wp_add_inline_script(
		'remotive-front',
		'window.remotiveThemeOptions = ' . wp_json_encode(
			array(
				'defaultTheme' => $default_theme,
				'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
			)
		) . ';',
		'before'
	);
}
add_action( 'wp_enqueue_scripts', 'remotive_inline_theme_option_data', 21 );

/**
 * Prevent a flash of the wrong colour mode on load.
 *
 * assets/js/remotive.js is enqueued in the footer (correct, standard
 * practice — it shouldn't block rendering). But that means a returning
 * visitor whose saved choice differs from the server-rendered default
 * mode sees one frame of the *wrong* mode before the footer script runs
 * and corrects it — a real, visible flash, previously documented in
 * docs/upgrading.md as a known trade-off rather than fixed.
 *
 * The actual fix has to run before first paint, which means a small
 * inline <script> directly in <head> — there's no way to defer this to
 * an external, cacheable file without reintroducing the same race. Kept
 * deliberately tiny (a handful of statements, no dependencies) so the
 * render-blocking cost stays negligible. Mirrors — but doesn't replace —
 * the resolution logic in remotive.js, which still needs to run
 * later to wire up the toggle button itself.
 */
function remotive_prevent_theme_flash() {
	$default_theme = remotive_get_theme_option( 'default_theme' );
	?>
<script>
(function(){
	try{
		var saved = localStorage.getItem('remotive-theme');
		var mode = (saved === 'light' || saved === 'dark') ? saved : <?php echo wp_json_encode( $default_theme ); ?>;
		if (mode === 'system') {
			mode = (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) ? 'light' : 'dark';
		}
		document.documentElement.setAttribute('data-theme', mode === 'light' ? 'light' : 'dark');
	}catch(e){}
})();
</script>
	<?php
}
add_action( 'wp_head', 'remotive_prevent_theme_flash', 1 );

/**
 * Put data-theme on <html> from the server.
 *
 * The inline script above sets it from the saved choice before first paint,
 * but without JavaScript nothing set it, and the stylesheets that key off
 * `html[data-theme]` (the Saira typeface in particular) never applied, so those
 * visitors got the fallback fonts. The server now sends the site's default
 * mode ('system' becomes dark, since the server cannot know); the script still
 * overrides it for a visitor with a saved choice or a light-mode device.
 */
function remotive_html_data_theme( $output ) {
	if ( is_admin() || false !== strpos( $output, 'data-theme' ) ) {
		return $output;
	}

	$default = remotive_get_theme_option( 'default_theme' );

	return $output . ' data-theme="' . ( 'light' === $default ? 'light' : 'dark' ) . '"';
}
add_filter( 'language_attributes', 'remotive_html_data_theme' );
