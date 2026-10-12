<?php
/**
 * Remotive Media: the single-page agency layout, as a switch.
 *
 * Appearance -> Theme Options -> Agency layout. Off by default, in which case
 * nothing about the theme changes. When it is on, the home page is a one-page
 * agency landing page in the same format as the ad landing pages (a contact
 * form above the fold and another at the bottom, no menu, no ticker), and,
 * if "Send the other pages to the home page" is also on, the rest of the
 * site stops being reachable. Switch it off and everything is back.
 *
 * This file holds the settings: their defaults, the tab and the sanitiser. The
 * page itself, and the routing that goes with the switch, are in
 * inc/landing/agency-page.php.
 *
 * @package Remotive
 */

defined( 'ABSPATH' ) || exit;

/**
 * The six services shown on the agency page: default title and description.
 * The wording comes from the service pages the site already has.
 *
 * @return array<int,array{0:string,1:string}>
 */
function remotive_agency_default_services() {
	return array(
		array( 'SEO & GEO / AIO / AEO', 'Search engine optimisation plus generative, AI and answer engine visibility, one organic discipline.' ),
		array( 'Paid Media', 'Full-funnel paid media: Google, Meta, LinkedIn and programmatic at 0% media markup, judged on CPA and ROAS.' ),
		array( 'Social Media & Influencer', 'Paid social, creators and content across Meta, TikTok and LinkedIn, measured on outcomes, never reach.' ),
		array( 'Content & Creative', 'Content strategy and performance creative: assets built to rank, get cited by AI engines, and convert.' ),
		array( 'Email & Lifecycle Automation', 'Enterprise marketing automation: email and WhatsApp lifecycle flows, AI assistants and CRM integration.' ),
		array( 'Analytics, Data & Attribution', 'Tracking, dashboards and attribution that connect ad spend to revenue: decisions from data, not gut feel.' ),
	);
}

/**
 * The six questions on the agency page: default question and answer. The answers
 * come from the site's own FAQ, rewritten in the house writing style.
 *
 * @return array<int,array{0:string,1:string}>
 */
function remotive_agency_default_faq() {
	return array(
		array( 'Do I have to buy every service?', 'No. Take one service or all six, and change the mix when your quarter changes. Some clients start with search alone and add paid media once demand shows up. Others hand us everything in month 1.' ),
		array( 'How quickly will I see results?', 'It depends on the channel and on where you start. Paid media can move within days, because you buy the placement. Search takes longer: in our case studies the organic gains arrived between 1 and 12 months in, and each one states its starting position. Anyone who promises a fixed timeline before seeing your site is guessing.' ),
		array( 'Do you work with agencies as well as brands?', 'Yes. We white-label for creative and PR agencies that need performance or search capability without hiring for it. The terms match any other engagement: senior specialists on the work, and reporting you can put your own name on.' ),
		array( 'Which markets do you cover?', 'Singapore and Malaysia hold most of our work. We also run campaigns in Thailand, Indonesia, the Philippines, Vietnam and Hong Kong. The company is registered in Singapore and the team works in the region.' ),
		array( 'What do you report on?', 'Funded accounts, qualified leads and pipeline value. Those are the numbers a marketing lead answers for internally, so we hold ourselves to them. Channel metrics still appear, below the commercial ones.' ),
		array( 'What are GEO, AIO and AEO, and do I need them?', 'They are three names for one shift: buyers increasingly ask an AI engine before they ask a search engine, and most brands never appear in the answer. Generative engine optimisation (GEO), AI optimisation (AIO) and answer engine optimisation (AEO) all describe work aimed at that surface. Whether you need it depends on whether your buyers research that way. Your own analytics can answer that.' ),
	);
}

/**
 * The four working steps. Defaults for the "How we work" section.
 *
 * @return array<int,array{0:string,1:string}>
 */
function remotive_agency_default_process() {
	return array(
		array( 'Audit', 'We review your site, tracking, accounts and competitors, and list what holds growth back.' ),
		array( 'Fix', 'We repair the foundations first: site structure, tracking and the pages that carry the sale.' ),
		array( 'Scale', 'With the base sound, we grow search, paid media and content together under one plan.' ),
		array( 'Report', 'Each month you receive the numbers that matter, what changed and what happens next.' ),
	);
}

/**
 * The six symptoms listed in the "Signs it is time to talk" section.
 *
 * @return string[]
 */
function remotive_agency_default_signs() {
	return array(
		'Traffic is flat or falling, and nobody can say why.',
		'Ad spend keeps rising, and so does the cost per lead.',
		'Lead volume swings from month to month.',
		'Reports show activity with no link to revenue.',
		'Competitors appear in AI answers and your brand does not.',
		'Search, paid and social run as separate projects.',
	);
}

/**
 * Defaults for every agency option.
 *
 * @return array<string,string>
 */
function remotive_agency_option_defaults() {
	$out = array(
		'agency_mode'            => '0',
		'agency_hide_pages'      => '1',
		'agency_eyebrow'         => '',
		'agency_line_1'          => 'Performance marketing, media & SEO',
		'agency_line_2'          => 'built to scale Asian brands, globally.',
		'agency_lead'            => 'We are a senior-led independent media team for high-growth B2B and B2C brands. We provide a powerful modular, search, social, programmatic and SEO extension for brands and agencies without in-house media capabilities. We fix the foundations, get you found and cited, then scale with paid media, turning performance marketing into predictable pipeline growth across Asia and beyond.',
		'agency_form_title'      => 'Talk to the team',
		'agency_form_intro'      => 'Tell us about your brand and what you want to grow. We reply within three business days.',
		'agency_cta'             => 'Send message',
		'agency_fine'            => 'We reply within three business days. No mailing list.',
		'agency_show_services'   => '1',
		'agency_services_heading' => 'What we do',
		'agency_services_lead'   => 'Fix the foundations, get found and cited, then scale with paid media.',
		'agency_link_services'  => '1',
		'agency_show_process'    => '1',
		'agency_process_heading' => 'How we work',
		'agency_process_lead'    => 'Four steps, in this order.',
		'agency_show_signs'      => '1',
		'agency_signs_heading'   => 'Signs it is time to talk',
		'agency_signs_lead'      => 'If several of these sound familiar, the problem usually sits in the system, and more activity will not fix it.',
		'agency_show_faq'        => '1',
		'agency_faq_heading'     => 'Common questions',
		'agency_show_team'       => '1',
		'agency_team_heading'    => 'The team',
		'agency_team_size'       => 'sm',
		'agency_final_heading'   => 'Let us talk about your growth.',
		'agency_final_lead'      => 'Send us a few details and a senior member of the team will reply within three business days.',
	);

	foreach ( remotive_agency_default_process() as $i => $step ) {
		$out[ 'agency_proc_' . ( $i + 1 ) . '_title' ] = $step[0];
		$out[ 'agency_proc_' . ( $i + 1 ) . '_desc' ]  = $step[1];
	}

	foreach ( remotive_agency_default_signs() as $i => $sign ) {
		$out[ 'agency_sign_' . ( $i + 1 ) ] = $sign;
	}

	foreach ( remotive_agency_default_faq() as $i => $qa ) {
		$out[ 'agency_faq_' . ( $i + 1 ) . '_q' ] = $qa[0];
		$out[ 'agency_faq_' . ( $i + 1 ) . '_a' ] = $qa[1];
	}

	foreach ( remotive_agency_default_services() as $i => $svc ) {
		$out[ 'agency_svc_' . ( $i + 1 ) . '_title' ] = $svc[0];
		$out[ 'agency_svc_' . ( $i + 1 ) . '_desc' ]  = $svc[1];
	}

	return $out;
}

/**
 * Portrait size choices on the agency page, as pixels.
 *
 * @return array<string,int>
 */
function remotive_agency_team_sizes() {
	return array(
		'xs' => 48,
		'sm' => 64,
		'md' => 88,
	);
}

/**
 * The Agency layout tab.
 *
 * @return array<string,mixed>
 */
function remotive_agency_tab() {
	$services = array();

	foreach ( remotive_agency_default_services() as $i => $unused ) {
		$n = $i + 1;

		$services[ 'agency_svc_' . $n . '_title' ] = array(
			/* translators: %d: service number, 1 to 6 */
			'label' => sprintf( __( 'Service %d: title', 'remotive' ), $n ),
			'type'  => 'text',
		);
		$services[ 'agency_svc_' . $n . '_desc' ]  = array(
			/* translators: %d: service number, 1 to 6 */
			'label' => sprintf( __( 'Service %d: description', 'remotive' ), $n ),
			'type'  => 'textarea',
		);
	}

	$process = array();

	foreach ( remotive_agency_default_process() as $i => $unused ) {
		$n = $i + 1;

		$process[ 'agency_proc_' . $n . '_title' ] = array(
			/* translators: %d: step number, 1 to 4 */
			'label' => sprintf( __( 'Step %d: title', 'remotive' ), $n ),
			'type'  => 'text',
		);
		$process[ 'agency_proc_' . $n . '_desc' ]  = array(
			/* translators: %d: step number, 1 to 4 */
			'label' => sprintf( __( 'Step %d: description', 'remotive' ), $n ),
			'type'  => 'textarea',
		);
	}

	$signs = array();

	foreach ( remotive_agency_default_signs() as $i => $unused ) {
		$signs[ 'agency_sign_' . ( $i + 1 ) ] = array(
			/* translators: %d: sign number, 1 to 6 */
			'label' => sprintf( __( 'Sign %d', 'remotive' ), $i + 1 ),
			'type'  => 'text',
		);
	}

	$faq = array();

	foreach ( remotive_agency_default_faq() as $i => $unused ) {
		$n = $i + 1;

		$faq[ 'agency_faq_' . $n . '_q' ] = array(
			/* translators: %d: question number, 1 to 6 */
			'label' => sprintf( __( 'Question %d', 'remotive' ), $n ),
			'type'  => 'text',
		);
		$faq[ 'agency_faq_' . $n . '_a' ] = array(
			/* translators: %d: question number, 1 to 6 */
			'label' => sprintf( __( 'Answer %d', 'remotive' ), $n ),
			'type'  => 'textarea',
		);
	}

	return array(
		'label'       => __( 'Agency layout', 'remotive' ),
		'icon'        => 'dashicons-layout',
		'description' => __( 'An alternative home page: one scrolling agency landing page in the format of the ad landing pages, with a contact form at the top and another at the bottom. It is off by default. Switching it on changes only the home page (and, if you choose, hides the other pages); every other page, template and setting is kept, so switching it off puts the site back exactly as it was. The wording is English; the Malay and Chinese versions of the page are translated through Tools → Translations, so after you change an English sentence, update its translation there (until then that sentence shows in English in those languages).', 'remotive' ),
		'groups'      => array(
			array(
				'label'       => __( 'Switch', 'remotive' ),
				'description' => __( 'Turn the layout on or off here.', 'remotive' ),
				'fields'      => array(
					'agency_mode'       => array(
						'label'        => __( 'Single-page agency layout', 'remotive' ),
						'type'         => 'toggle',
						'toggle_label' => __( 'Use the single-page agency layout for the home page', 'remotive' ),
						'helper'       => __( 'Off by default. When on, the home page is the agency landing page below: no menu, no ticker, a contact form above the fold and at the bottom. Switch it off to bring the usual home page back.', 'remotive' ),
					),
					'agency_hide_pages' => array(
						'label'        => __( 'Other pages', 'remotive' ),
						'type'         => 'toggle',
						'toggle_label' => __( 'Send the other pages to the home page while the layout is on', 'remotive' ),
						'helper'       => __( 'On by default. About, Team, Services, Case Studies, Insights, Contact, FAQ and every other page redirect to the home page (a temporary 302, so switching the layout off restores them), and they leave the sitemaps and the language versions (the legal pages keep theirs). The ad landing pages, their thank-you pages and the legal pages (privacy, terms) stay reachable. Nothing is deleted. Turn this off to keep the other pages live next to the agency home page.', 'remotive' ),
					),
				),
			),
			array(
				'label'       => __( 'Opening: headline, introduction and form', 'remotive' ),
				'description' => __( 'What sits above the first scroll. The form is on the right on a desktop and straight under the headline on a phone.', 'remotive' ),
				'fields'      => array(
					'agency_eyebrow'    => array(
						'label'  => __( 'Eyebrow (optional)', 'remotive' ),
						'type'   => 'text',
						'helper' => __( 'A short line above the headline. Leave blank for none.', 'remotive' ),
					),
					'agency_line_1'     => array(
						'label' => __( 'Headline: line 1', 'remotive' ),
						'type'  => 'text',
					),
					'agency_line_2'     => array(
						'label' => __( 'Headline: line 2', 'remotive' ),
						'type'  => 'text',
					),
					'agency_lead'       => array(
						'label' => __( 'Introduction', 'remotive' ),
						'type'  => 'textarea',
					),
					'agency_form_title' => array(
						'label' => __( 'Form title', 'remotive' ),
						'type'  => 'text',
					),
					'agency_form_intro' => array(
						'label' => __( 'Form introduction', 'remotive' ),
						'type'  => 'text',
					),
					'agency_cta'        => array(
						'label' => __( 'Button label', 'remotive' ),
						'type'  => 'text',
					),
					'agency_fine'       => array(
						'label'  => __( 'Line under the button', 'remotive' ),
						'type'   => 'text',
						'helper' => __( 'The small print under each form. A link to the privacy policy follows it.', 'remotive' ),
					),
				),
			),
			array(
				'label'       => __( 'Services', 'remotive' ),
				'description' => __( 'Six cards that cover everything the agency does. Leave a title blank to hide that card.', 'remotive' ),
				'fields'      => array_merge(
					array(
						'agency_show_services'    => array(
							'label'        => __( 'Services section', 'remotive' ),
							'type'         => 'toggle',
							'toggle_label' => __( 'Show the services section', 'remotive' ),
						),
						'agency_link_services'    => array(
							'label'        => __( 'Landing page links', 'remotive' ),
							'type'         => 'toggle',
							'toggle_label' => __( 'Link each service card to its ready-made landing page', 'remotive' ),
							'helper'       => __( 'On by default. SEO links to the SEO audit page, Paid Media to the Google Ads management page and Social Media to the paid social page. The other three services have no landing page yet, so their cards carry no link. A visitor who arrives from this home page is let through; the pages stay closed to every other click from the site.', 'remotive' ),
						),
						'agency_services_heading' => array(
							'label' => __( 'Heading', 'remotive' ),
							'type'  => 'text',
						),
						'agency_services_lead'    => array(
							'label' => __( 'Introduction', 'remotive' ),
							'type'  => 'textarea',
						),
					),
					$services
				),
			),
			array(
				'label'       => __( 'How we work', 'remotive' ),
				'description' => __( 'Four numbered steps that show how an engagement runs. Leave a title blank to hide a step.', 'remotive' ),
				'fields'      => array_merge(
					array(
						'agency_show_process'    => array(
							'label'        => __( 'Process section', 'remotive' ),
							'type'         => 'toggle',
							'toggle_label' => __( 'Show the four steps', 'remotive' ),
						),
						'agency_process_heading' => array(
							'label' => __( 'Heading', 'remotive' ),
							'type'  => 'text',
						),
						'agency_process_lead'    => array(
							'label' => __( 'Introduction', 'remotive' ),
							'type'  => 'text',
						),
					),
					$process
				),
			),
			array(
				'label'       => __( 'Signs', 'remotive' ),
				'description' => __( 'A checklist of symptoms a visitor recognises, placed above the team. Leave a line blank to hide it.', 'remotive' ),
				'fields'      => array_merge(
					array(
						'agency_show_signs'    => array(
							'label'        => __( 'Signs section', 'remotive' ),
							'type'         => 'toggle',
							'toggle_label' => __( 'Show the checklist', 'remotive' ),
						),
						'agency_signs_heading' => array(
							'label' => __( 'Heading', 'remotive' ),
							'type'  => 'text',
						),
						'agency_signs_lead'    => array(
							'label' => __( 'Introduction', 'remotive' ),
							'type'  => 'textarea',
						),
					),
					$signs
				),
			),
			array(
				'label'       => __( 'Questions', 'remotive' ),
				'description' => __( 'Six questions in an accordion, with matching FAQ data for search engines. Leave a question blank to hide it.', 'remotive' ),
				'fields'      => array_merge(
					array(
						'agency_show_faq'    => array(
							'label'        => __( 'Questions section', 'remotive' ),
							'type'         => 'toggle',
							'toggle_label' => __( 'Show the questions section', 'remotive' ),
						),
						'agency_faq_heading' => array(
							'label' => __( 'Heading', 'remotive' ),
							'type'  => 'text',
						),
					),
					$faq
				),
			),
			array(
				'label'       => __( 'Team', 'remotive' ),
				'description' => __( 'Everyone in the team list (Homepage tab), with small portraits.', 'remotive' ),
				'fields'      => array(
					'agency_show_team'    => array(
						'label'        => __( 'Team section', 'remotive' ),
						'type'         => 'toggle',
						'toggle_label' => __( 'Show the team', 'remotive' ),
					),
					'agency_team_heading' => array(
						'label' => __( 'Heading', 'remotive' ),
						'type'  => 'text',
					),
					'agency_team_size'    => array(
						'label'   => __( 'Portrait size', 'remotive' ),
						'type'    => 'select',
						'options' => array(
							'xs' => __( 'Extra small (48 px)', 'remotive' ),
							'sm' => __( 'Small (64 px)', 'remotive' ),
							'md' => __( 'Medium (88 px)', 'remotive' ),
						),
					),
				),
			),
			array(
				'label'       => __( 'Closing form', 'remotive' ),
				'description' => __( 'The band at the bottom, with the second contact form.', 'remotive' ),
				'fields'      => array(
					'agency_final_heading' => array(
						'label' => __( 'Heading', 'remotive' ),
						'type'  => 'text',
					),
					'agency_final_lead'    => array(
						'label' => __( 'Introduction', 'remotive' ),
						'type'  => 'textarea',
					),
				),
			),
		),
	);
}

/**
 * Validate the agency options from a form submission into $clean.
 *
 * @param array $input Raw submitted options.
 * @param array $clean Sanitised options being built (modified).
 */
function remotive_sanitize_agency_options( $input, &$clean ) {
	$defaults = remotive_agency_option_defaults();

	// A checkbox that is not ticked submits nothing, so absence means off.
	foreach ( array( 'agency_mode', 'agency_hide_pages', 'agency_show_services', 'agency_link_services', 'agency_show_process', 'agency_show_signs', 'agency_show_faq', 'agency_show_team' ) as $key ) {
		$clean[ $key ] = ( isset( $input[ $key ] ) && '1' === (string) $input[ $key ] ) ? '1' : '0';
	}

	// Optional text may be blank. Everything else falls back to its default,
	// so a cleared field can never leave a hole in the page.
	$optional = array( 'agency_eyebrow' => true );
	$text     = array( 'agency_eyebrow', 'agency_line_1', 'agency_line_2', 'agency_form_title', 'agency_form_intro', 'agency_cta', 'agency_fine', 'agency_services_heading', 'agency_process_heading', 'agency_process_lead', 'agency_signs_heading', 'agency_faq_heading', 'agency_team_heading', 'agency_final_heading' );
	$area     = array( 'agency_lead', 'agency_services_lead', 'agency_signs_lead', 'agency_final_lead' );

	foreach ( $text as $key ) {
		$value = sanitize_text_field( $input[ $key ] ?? $defaults[ $key ] );
		$clean[ $key ] = ( '' === trim( $value ) && ! isset( $optional[ $key ] ) ) ? $defaults[ $key ] : $value;
	}

	foreach ( $area as $key ) {
		$value = sanitize_textarea_field( $input[ $key ] ?? $defaults[ $key ] );
		$clean[ $key ] = '' === trim( $value ) ? $defaults[ $key ] : $value;
	}

	// A service with no title is hidden, so these may be blank (a description alone is never shown).
	foreach ( array_keys( remotive_agency_default_services() ) as $i ) {
		$n = $i + 1;

		$clean[ 'agency_svc_' . $n . '_title' ] = sanitize_text_field( $input[ 'agency_svc_' . $n . '_title' ] ?? $defaults[ 'agency_svc_' . $n . '_title' ] );
		$clean[ 'agency_svc_' . $n . '_desc' ]  = sanitize_textarea_field( $input[ 'agency_svc_' . $n . '_desc' ] ?? $defaults[ 'agency_svc_' . $n . '_desc' ] );
	}

	// A step with no title and a sign with no text are hidden, so these may be blank.
	foreach ( array_keys( remotive_agency_default_process() ) as $i ) {
		$n = $i + 1;

		$clean[ 'agency_proc_' . $n . '_title' ] = sanitize_text_field( $input[ 'agency_proc_' . $n . '_title' ] ?? $defaults[ 'agency_proc_' . $n . '_title' ] );
		$clean[ 'agency_proc_' . $n . '_desc' ]  = sanitize_textarea_field( $input[ 'agency_proc_' . $n . '_desc' ] ?? $defaults[ 'agency_proc_' . $n . '_desc' ] );
	}

	foreach ( array_keys( remotive_agency_default_signs() ) as $i ) {
		$clean[ 'agency_sign_' . ( $i + 1 ) ] = sanitize_text_field( $input[ 'agency_sign_' . ( $i + 1 ) ] ?? $defaults[ 'agency_sign_' . ( $i + 1 ) ] );
	}

	// A question with no text is hidden, so these may be blank.
	foreach ( array_keys( remotive_agency_default_faq() ) as $i ) {
		$n = $i + 1;

		$clean[ 'agency_faq_' . $n . '_q' ] = sanitize_text_field( $input[ 'agency_faq_' . $n . '_q' ] ?? $defaults[ 'agency_faq_' . $n . '_q' ] );
		$clean[ 'agency_faq_' . $n . '_a' ] = sanitize_textarea_field( $input[ 'agency_faq_' . $n . '_a' ] ?? $defaults[ 'agency_faq_' . $n . '_a' ] );
	}

	$size                     = (string) ( $input['agency_team_size'] ?? $defaults['agency_team_size'] );
	$clean['agency_team_size'] = isset( remotive_agency_team_sizes()[ $size ] ) ? $size : $defaults['agency_team_size'];
}
