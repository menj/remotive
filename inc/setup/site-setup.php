<?php
/**
 * One-time site setup for the Remotive Media theme.
 *
 * The custom page templates (page-about, page-services, page-contact,
 * page-case-studies) are self-contained: they carry their own content and do
 * not render post_content. So a Page only has to exist with the right slug
 * and the right template assigned, and it renders complete. This module
 * creates those Pages and wires up the Posts page, which is the part a theme
 * cannot do on its own.
 *
 * It runs automatically once, on theme activation (and on the first admin load
 * after upgrading, for sites where the theme was already active). A stored
 * flag keeps it to a single occurrence, so re-activating the theme will not
 * run it again. The same routine is available as a button in Appearance ->
 * Theme Options for re-checking, or for rebuilding a page that was deleted.
 *
 * Running unattended is safe because the routine only ever adds. A page is
 * matched by slug; if one already exists the routine leaves its title, content
 * and template alone, and only fills in a template assignment that is missing
 * entirely. Nothing is deleted or overwritten, so the worst case on a site
 * that already has these pages is that it finds them and changes nothing.
 *
 * @package Remotive
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The pages the theme's navigation and templates expect to exist.
 *
 * 'template' is the block template slug, stored in the _wp_page_template
 * meta key. An empty string means the page uses the default page.html, which
 * renders the editor content, so those pages need copy written in the editor.
 *
 * @return array<string,array<string,string>> Keyed by slug.
 */
function remotive_required_pages() {
	return array(
		'home'     => array(
			'title'    => __( 'Home', 'remotive' ),
			'template' => '',
			'order'    => 0,
			'in_menu'  => false,
			'note'     => __( 'Front page. templates/front-page.html always wins for the homepage, so this page exists only to be assigned in Settings -> Reading.', 'remotive' ),
			'rm_title' => __( 'Performance Marketing & SEO Agency | Re:Motive', 'remotive' ),
			'rm_desc'  => __( 'Senior specialists running SEO, paid media and analytics across Singapore, Malaysia and Asia. Get a free audit.', 'remotive' ),
			'rm_kw'    => __( 'performance marketing agency singapore', 'remotive' ),
		),
		'services' => array(
			'title'    => __( 'Services', 'remotive' ),
			'template' => 'page-services',
			'order'    => 10,
			'in_menu'  => true,
			'note'     => __( 'Self-contained template. No editor content needed.', 'remotive' ),
			'rm_title' => __( 'Our Services | Re:Motive Media', 'remotive' ),
			'rm_desc'  => __( 'SEO, paid media, social, content, email and analytics: modular capabilities, activate one or all three. See what we do.', 'remotive' ),
			'rm_kw'    => __( 'digital marketing services', 'remotive' ),
		),
		'case-studies' => array(
			'title'    => __( 'Case Studies', 'remotive' ),
			'template' => 'page-case-studies',
			'order'    => 20,
			'in_menu'  => true,
			'note'     => __( 'Self-contained template. Case study cards are placeholders until the custom post type lands.', 'remotive' ),
			'rm_title' => __( 'Case Studies | Re:Motive Media', 'remotive' ),
			'rm_desc'  => __( 'Real client results in SEO, paid media and analytics across Asia, with the measurement limits named on each page.', 'remotive' ),
			'rm_kw'    => __( 'digital marketing case studies', 'remotive' ),
		),
		'about'    => array(
			'title'    => __( 'About', 'remotive' ),
			'template' => 'page-about',
			'order'    => 30,
			'in_menu'  => true,
			'note'     => __( 'Self-contained template: how the company started, why the region, what it stands for, and the contact form.', 'remotive' ),
			'rm_title' => __( 'About Re:Motive Media Asia', 'remotive' ),
			'rm_desc'  => __( 'A Singapore-registered performance marketing and SEO agency built for how Asia actually buys. Meet the team.', 'remotive' ),
			'rm_kw'    => __( 're:motive media asia', 'remotive' ),
		),
		'team'     => array(
			'title'    => __( 'Meet the Team', 'remotive' ),
			'template' => 'page-team',
			'order'    => 35,
			'in_menu'  => true,
			'note'     => __( 'Self-contained template. The roster and portraits come from Theme Options; the gallery is in the template.', 'remotive' ),
			'rm_title' => __( 'Meet the Team | Re:Motive Media', 'remotive' ),
			'rm_desc'  => __( 'Senior specialists who plug into your team: no junior handovers, one number everyone answers for. Meet them here.', 'remotive' ),
			'rm_kw'    => __( 'digital marketing team singapore', 'remotive' ),
		),
		'contact'  => array(
			'title'    => __( 'Contact', 'remotive' ),
			'template' => 'page-contact',
			'order'    => 50,
			'in_menu'  => true,
			'note'     => __( 'Self-contained template, including the contact form.', 'remotive' ),
			'rm_title' => __( 'Contact Re:Motive Media Asia', 'remotive' ),
			'rm_desc'  => __( "Tell us what is not working. We reply within three business days with a view. Get in touch.", 'remotive' ),
			'rm_kw'    => __( 'contact digital marketing agency', 'remotive' ),
		),
		'blog'     => array(
			'title'    => __( 'Insights', 'remotive' ),
			'template' => '',
			'order'    => 40,
			'in_menu'  => true,
			'note'     => __( 'Posts page. Rendered by templates/home.html, so editor content is ignored.', 'remotive' ),
			'rm_title' => __( 'Insights | Re:Motive Media Blog', 'remotive' ),
			'rm_desc'  => __( "What we are seeing in search, paid media and measurement across Singapore, Malaysia and the wider region.", 'remotive' ),
			'rm_kw'    => __( 'digital marketing insights', 'remotive' ),
		),
		'faq'      => array(
			'title'    => __( 'FAQ', 'remotive' ),
			'template' => 'page-faq',
			'order'    => 55,
			'in_menu'  => false,
			'note'     => __( 'Self-contained template. Questions live in the template, and the FAQPage structured data is parsed from them, so the two cannot drift apart.', 'remotive' ),
			'rm_title' => __( 'FAQ | Re:Motive Media', 'remotive' ),
			'rm_desc'  => __( "Answers to what we are usually asked before a first call: scope, results, markets, and measurement.", 'remotive' ),
			'rm_kw'    => __( 'digital marketing agency faq', 'remotive' ),
		),
		'privacy'  => array(
			'title'    => __( 'Privacy Policy', 'remotive' ),
			'template' => 'page-legal',
			'order'    => 60,
			'in_menu'  => false,
			'note'     => __( 'Legal template renders editor content. A draft is in docs/legal-privacy-policy.md: fill in every CONFIRM marker and have it reviewed before publishing.', 'remotive' ),
			'rm_title' => __( 'Privacy Policy | Re:Motive Media Asia', 'remotive' ),
			'rm_desc'  => __( "How Re:Motive Media Asia Pte. Ltd. collects, uses and protects personal data under Singapore's PDPA.", 'remotive' ),
			'rm_kw'    => '',
		),
		'thank-you' => array(
			'title'    => __( 'Thank You', 'remotive' ),
			'template' => 'page-thank-you',
			'order'    => 80,
			'in_menu'  => false,
			'note'     => __( 'Confirmation page every native form redirects to on success. Self-contained template. Kept out of menus and out of search results (noindex), but it must stay published: it is the conversion destination analytics and ad platforms fire on.', 'remotive' ),
			'rm_title' => __( 'Thank You | Re:Motive Media Asia', 'remotive' ),
			'rm_desc'  => __( 'We have your message and will reply within three business days.', 'remotive' ),
			'rm_kw'    => '',
		),
		'seo-audit' => array(
			'title'    => __( 'SEO landing page', 'remotive' ),
			'template' => 'page-landing',
			'order'    => 90,
			'in_menu'  => false,
			'note'     => __( 'Ad landing page for SEO (noindex, nofollow). Copy lives in inc/landing/landing-pages.php. Point ads and social posts here, not the main site.', 'remotive' ),
			'rm_title' => '',
			'rm_desc'  => '',
			'rm_kw'    => '',
		),
		'google-ads-management' => array(
			'title'    => __( 'Google Ads landing page', 'remotive' ),
			'template' => 'page-landing',
			'order'    => 91,
			'in_menu'  => false,
			'note'     => __( 'Ad landing page for Google Ads (noindex, nofollow). Copy lives in inc/landing/landing-pages.php. Point ads and social posts here, not the main site.', 'remotive' ),
			'rm_title' => '',
			'rm_desc'  => '',
			'rm_kw'    => '',
		),
		'paid-social-advertising' => array(
			'title'    => __( 'Paid social landing page', 'remotive' ),
			'template' => 'page-landing',
			'order'    => 92,
			'in_menu'  => false,
			'note'     => __( 'Ad landing page for paid social (noindex, nofollow). Copy lives in inc/landing/landing-pages.php. Point ads and social posts here, not the main site.', 'remotive' ),
			'rm_title' => '',
			'rm_desc'  => '',
			'rm_kw'    => '',
		),
		'audit-requested' => array(
			'title'    => __( 'Audit requested', 'remotive' ),
			'template' => 'page-landing',
			'order'    => 93,
			'in_menu'  => false,
			'note'     => __( 'Confirmation page for the ad landing page forms (noindex, nofollow), in four languages. It is the conversion URL for those forms: keep it published.', 'remotive' ),
			'rm_title' => '',
			'rm_desc'  => '',
			'rm_kw'    => '',
		),
		'terms'    => array(
			'title'    => __( 'Terms of Service', 'remotive' ),
			'template' => 'page-legal',
			'order'    => 70,
			'in_menu'  => false,
			'note'     => __( 'Legal template renders editor content. A draft is in docs/legal-terms-of-service.md: fill in every CONFIRM marker and have it reviewed before publishing.', 'remotive' ),
			'rm_title' => __( 'Terms of Service | Re:Motive Media Asia', 'remotive' ),
			'rm_desc'  => __( 'The terms governing use of the Re:Motive Media Asia website and engagement with our services.', 'remotive' ),
			'rm_kw'    => '',
		),
	);
}

/**
 * Current state of each required page, for the admin panel.
 *
 * @return array<string,array<string,mixed>>
 */
function remotive_site_setup_status() {
	$status = array();

	foreach ( remotive_required_pages() as $slug => $page ) {
		$existing = get_page_by_path( $slug, OBJECT, 'page' );
		$assigned = $existing ? get_post_meta( $existing->ID, '_wp_page_template', true ) : '';

		$status[ $slug ] = array(
			'title'            => $page['title'],
			'note'             => $page['note'],
			'wanted_template'  => $page['template'],
			'exists'           => (bool) $existing,
			'id'               => $existing ? (int) $existing->ID : 0,
			'edit_link'        => $existing ? get_edit_post_link( $existing->ID, 'raw' ) : '',
			'view_link'        => $existing ? get_permalink( $existing->ID ) : '',
			'template_ok'      => $existing && ( '' === $page['template'] || $assigned === $page['template'] ),
			'current_template' => $assigned,
		);
	}

	return $status;
}

/**
 * Create any missing pages, fill in missing template assignments, and point
 * Settings -> Reading at the Home and Insights pages.
 *
 * @return array<string,array<string>> Lists of what was created, updated and skipped.
 */
function remotive_run_site_setup() {
	$created = array();
	$updated = array();
	$skipped = array();

	foreach ( remotive_required_pages() as $slug => $page ) {
		$existing = get_page_by_path( $slug, OBJECT, 'page' );

		if ( $existing ) {
			// Never touch an existing page's title or content. Only fill in a
			// template assignment if it is missing, so a deliberate change
			// made in the editor is preserved. SEO meta follows the same
			// rule: fill in only what Rank Math has never been given a
			// value for, so a title or description already written in the
			// editor is left alone.
			$assigned      = get_post_meta( $existing->ID, '_wp_page_template', true );
			$page_touched  = false;

			if ( $page['template'] && ! $assigned ) {
				update_post_meta( $existing->ID, '_wp_page_template', $page['template'] );
				$page_touched = true;
			}

			foreach ( array( 'rm_title' => 'rank_math_title', 'rm_desc' => 'rank_math_description', 'rm_kw' => 'rank_math_focus_keyword' ) as $from => $meta_key ) {
				if ( empty( $page[ $from ] ) ) {
					continue;
				}
				if ( '' === (string) get_post_meta( $existing->ID, $meta_key, true ) ) {
					update_post_meta( $existing->ID, $meta_key, $page[ $from ] );
					$page_touched = true;
				}
			}

			if ( $page_touched ) {
				/* translators: %s: page title. */
				$updated[] = sprintf( __( '%s: template or SEO meta filled in', 'remotive' ), $page['title'] );
			} else {
				$skipped[] = $page['title'];
			}

			continue;
		}

		$page_id = wp_insert_post(
			array(
				'post_title'   => $page['title'],
				'post_name'    => $slug,
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => '',
				'menu_order'   => $page['order'],
			),
			true
		);

		if ( is_wp_error( $page_id ) ) {
			continue;
		}

		if ( $page['template'] ) {
			update_post_meta( $page_id, '_wp_page_template', $page['template'] );
		}

		// Rank Math reads these directly; harmless if the plugin is absent,
		// and already correct if it is installed later. Same mapping
		// content-seed.php uses for blog posts and case studies, so a page
		// created here and a post seeded there behave identically.
		foreach ( array( 'rm_title' => 'rank_math_title', 'rm_desc' => 'rank_math_description', 'rm_kw' => 'rank_math_focus_keyword' ) as $from => $meta_key ) {
			if ( ! empty( $page[ $from ] ) ) {
				update_post_meta( $page_id, $meta_key, $page[ $from ] );
			}
		}

		$created[] = $page['title'];
	}

	// Point Reading settings at Home and Insights. front-page.html still wins
	// for the homepage either way; this exists so the Posts page resolves at
	// /blog/ and renders through templates/home.html.
	//
	// Only when the site hasn't configured a static front page already:
	// this routine also runs from the manual repair action and (since
	// 1.65.7) from upgrade migrations, and unconditionally rewriting
	// show_on_front / page_on_front / page_for_posts on every run meant
	// "repair" silently reverted a homepage an administrator had
	// deliberately changed. A show_on_front of 'page' with a valid page
	// assigned is an explicit choice, whoever made it — including this
	// routine on a previous run — and gets left alone. Fresh installs
	// (show_on_front 'posts', page_on_front 0) are still configured.
	$home = get_page_by_path( 'home', OBJECT, 'page' );
	$blog = get_page_by_path( 'blog', OBJECT, 'page' );

	$front_configured = 'page' === get_option( 'show_on_front' )
		&& (int) get_option( 'page_on_front' ) > 0
		&& get_post( (int) get_option( 'page_on_front' ) ) instanceof WP_Post;

	if ( ! $front_configured && $home && $blog && (int) $home->ID !== (int) $blog->ID ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', (int) $home->ID );
		update_option( 'page_for_posts', (int) $blog->ID );
	}

	// Built after the pages exist, so every menu item can link by post ID.
	remotive_build_primary_navigation();

	return array(
		'created' => $created,
		'updated' => $updated,
		'skipped' => $skipped,
	);
}

/**
 * MD5 of the title, page text and Rank Math fields of the seeded service and case-study pages, as every earlier
 * release seeded them. The same rule as the articles: remotive_refresh_article_seo() replaces a value only while it
 * still matches one of these, so a page an editor changed keeps their words.
 *
 * @return array[]
 */
function remotive_old_page_copy_hashes() {
	return array(
		'analytics' => array(
			'content' => array( '7e6ceb232ab3148a4c4dd54923631e2c' ),
			'ptitle'  => array( '84e17a3e0220b5ad7e63f343fce20840' ),
			'title'   => array( '1be699eca05fdb3f6d0300b32a218838' ),
			'desc'    => array( '19bb9900367554ec78bea3ffd036340e' ),
			'kw'      => array( '10e0cd432d25914801b5cc355d459427' ),
		),
		'b2b-seo-industrial-supplier' => array(
			'content' => array( 'a9d453cce3cf0a84aff7bba0966858b3' ),
			'ptitle'  => array( '40d71d996a2fadde71742232c0ba29e1' ),
			'title'   => array( 'b0e59adcbff9f0e2640d5b7db141d012' ),
			'desc'    => array( '1d41f3a45f69d8d6e7a7eb28c7eda159' ),
			'kw'      => array( 'fda2e2f9ebc07ef3e006bec25d83dc21' ),
		),
		'content' => array(
			'content' => array( 'b1cb1003066d0c9dc9c559233f497b09' ),
			'ptitle'  => array( '0265f0462a72d43783e8c37771ce66af' ),
			'title'   => array( 'f6d09a9405598cd7ad53692f4c8f0895' ),
			'desc'    => array( 'e03c5007ce32236764bfa0cf94a54e6e' ),
			'kw'      => array( 'a731b7e8c9504e996553fc23940d5df6' ),
		),
		'cookieless-audience-sports' => array(
			'content' => array( '3e45b74b955e628c6a47e18fb198598e' ),
			'ptitle'  => array( '85c9ab0d5d0325eb97d3297488d9aaab' ),
			'title'   => array( '42c1bb7da8ad94809f39d87f6ccaf7cf' ),
			'desc'    => array( '9670bf58f707d28163b5179f897acfe8' ),
			'kw'      => array( '521142505e4227041f2041965a938ade' ),
		),
		'ecommerce-seo-footwear' => array(
			'content' => array( '69f9acbe23c448d7ce9ee2fea7f8db81' ),
			'ptitle'  => array( 'e2d1903f955b2c5835879a3784eeeffd' ),
			'title'   => array( '7269b624abb9e078ca2de7034d71acac' ),
			'desc'    => array( 'bca405259a2b4162b24af71d826d2df7' ),
			'kw'      => array( '27a24982534f1d26ab147e8632d9391f' ),
		),
		'email' => array(
			'content' => array( 'ef955b22e59f2163ee9bc73dbb7afdc5' ),
			'ptitle'  => array( '4e485374bc55d84c6a30bcd28722a589' ),
			'title'   => array( '00d1b9b3d28f05798d047a187b3f14c2' ),
			'desc'    => array( 'd9dcd977956cb1bea2b1d889381bf4e2' ),
			'kw'      => array( 'df882947c6d64eac2c2dab470256b06f' ),
		),
		'healthcare-seo-malaysia' => array(
			'content' => array( '8b5b1828990779764f33924cb5167e98' ),
			'ptitle'  => array( 'c6304b7f29e156658894a631806341f9' ),
			'title'   => array( '4b3d35bd9de3b2e902d1689492957517' ),
			'desc'    => array( '05cf4786a4532a7947c95e6057683db0' ),
			'kw'      => array( 'b6f119e4be3cd378c034aaacc8ead7f1' ),
		),
		'market-entry-trading-platform' => array(
			'content' => array( '0db2bb4f5a947fb2568e14aa4450af04' ),
			'ptitle'  => array( '96e7c2649f300fd7dfe330366626952b' ),
			'title'   => array( 'b2122c911545d817e903fac0d4f414bc' ),
			'desc'    => array( 'f47fa34114f96d94382bec6393dc7de2' ),
			'kw'      => array( 'c89cac1b3b4e61870ba01874a66aa1d3' ),
		),
		'marketplace-launch-skincare' => array(
			'content' => array( 'a1db87ca1b642c376f0724c95aff313c' ),
			'ptitle'  => array( '930397ac5c46b9f21e57fa692668ebce' ),
			'title'   => array( 'd0f99ceb3a0320f792df2168ef835e96' ),
			'desc'    => array( '9827f86188ca6f9c2d9271863c8ab11b' ),
			'kw'      => array( '488c465e0204a2d54970ca006ff9028f' ),
		),
		'paid-media' => array(
			'content' => array( 'df90fc13971226b72029af62459a6bff' ),
			'ptitle'  => array( '0dd442dd603b3c23812b7b40b314ef35' ),
			'title'   => array( '219f4d75838570f211ee8b72748f8cde' ),
			'desc'    => array( 'e586300cb68e4d1b1b1c9c384a2df628' ),
			'kw'      => array( 'c28e5883923c7a0374d13b185db66eaf' ),
		),
		'paid-media-financial-services' => array(
			'content' => array( '0465702330cf85bf9a840ad68df048e8' ),
			'ptitle'  => array( 'ac6e60d26e1e4220787d2c7a4f59840a' ),
			'title'   => array( '5b1b176d7cb8f60287e007ac8052e0f4' ),
			'desc'    => array( '7e19a0ac48effcf24586dbf0e461b1a8' ),
			'kw'      => array( 'cb2c285c25310a735f801ee1ee87d093' ),
		),
		'paid-search-b2b-gifting' => array(
			'content' => array( '7e60b0d9ca152a8c2af6c06c65e847cd' ),
			'ptitle'  => array( 'c3f208ccc0d7ad871c3d88425c9bd076' ),
			'title'   => array( '618416fc047fb3fbf84327f02d2f599c' ),
			'desc'    => array( 'fb9662566ecb51e53560249e09cc8e75' ),
			'kw'      => array( 'fc3294cbca47f0b3aa64e07f4fa6a83f' ),
		),
		'programmatic-advertising-automotive' => array(
			'content' => array( '839121d51295dc615c638c6a47521cc0' ),
			'ptitle'  => array( '5bb13de9d6c52c702112897499d3c20c' ),
			'title'   => array( '3f8b560005ed4d1e10148ceef319fa25' ),
			'desc'    => array( '3eef3ce3397eb93fd17e0c9ef36bdfac' ),
			'kw'      => array( 'f04ae1c04126d5a9b735d09d1b6d2c50' ),
		),
		'seo' => array(
			'content' => array( 'f73265deb960402e5213425f6e4ad486' ),
			'ptitle'  => array( '7c23a086e49f71ce8ae526514543215e' ),
			'title'   => array( '3a973bc293df681b7ad92b692e6dece6' ),
			'desc'    => array( '7ff7930690c92a55b3ecfbc793e694dc' ),
			'kw'      => array( '8324a318bff49e1d7c41a8cc9c0ced85' ),
		),
		'seo-ai-visibility-healthcare' => array(
			'content' => array( '7160ac2222df9b2955cca74b00c3f63e' ),
			'ptitle'  => array( '91e4d16ad819867db289cc1db5bc7809' ),
			'title'   => array( '1060743fc77e439648a1a775bd2eba95' ),
			'desc'    => array( '1a8b697943a07278846ec616b52a4358' ),
			'kw'      => array( '0a588468703884a655f1f686cf5f9122' ),
		),
		'seo-b2b-services' => array(
			'content' => array( '377d43000e9e4f2c734da763897f625f' ),
			'ptitle'  => array( '2806bca9b6ff1de7d864048f3d7d0a7f' ),
			'title'   => array( '3cecccdf56acdb27c4b4c4c81f922d9f' ),
			'desc'    => array( 'edacda9f0d55bc9e44a265b927fb2140' ),
			'kw'      => array( 'edb86960b874bca2aa51e05dec526feb' ),
		),
		'seo-fmcg-malaysia-singapore' => array(
			'content' => array( '0ab5af0fe56359aa52f2b2faa4c890bd' ),
			'ptitle'  => array( '86a35fa312664c32d1ee425e2c94671f' ),
			'title'   => array( 'd02c4d945fc5104b5c7d350ec882aaab' ),
			'desc'    => array( '58d415e9c6898801a2eaa39e71bbd8c5' ),
			'kw'      => array( '6ac0ae2f77a4b0be7b44122ba7044dc8' ),
		),
		'singapore-b2b-portfolio' => array(
			'content' => array( 'f4f2f7748276bc8592d1408718a5894f' ),
			'ptitle'  => array( '964c835c5362ddbb9ec42f16adcf4eed' ),
			'title'   => array( '68c1321c18815666e4fc8d4522b85495' ),
			'desc'    => array( 'b978764d7da36fd623228253fc36cc52' ),
			'kw'      => array( '331b6d778d80a1bac1e928af5173335b' ),
		),
		'social' => array(
			'content' => array( 'f24afaa80f1c493c108b3eb1333cf218' ),
			'ptitle'  => array( '31645cf591ac9b48ac0cd26ef5861f5b' ),
			'title'   => array( '9a7347d5fdcd533922de727c0126f990' ),
			'desc'    => array( 'f737eccb66974c64851f8cf2d2779881' ),
			'kw'      => array( '85daf3f495ba6b453d2a57e056662ed9' ),
		),
		'technical-seo-industrial-automation' => array(
			'content' => array( '6a3a97bd0795ffef61c21b508cb4b1a4' ),
			'ptitle'  => array( 'e10ff0c64da7cdbf5b22f1a17e47d4be' ),
			'title'   => array( '9255491baaf4e268c72164636eb2fcbf' ),
			'desc'    => array( 'ae1b594ca8a39334d404ba2611f5f145' ),
			'kw'      => array( '30d0d8c9222cb326e94f3a6a8a4e0838' ),
		),
	);
}

/**
 * MD5 of the post title, article text and Rank Math fields as every earlier release seeded them.
 *
 * remotive_refresh_article_seo() only replaces a value that still matches
 * its hash, so anything an editor has changed stays as they left it.
 *
 * @return array[]
 */
function remotive_old_article_seo_hashes() {
	return array(
		'seo-friendly-web-design' => array(
			'content' => array( '23559b7a46b685f1c17b6ad1f57e9df8', 'bb602c61d590b5d0830c9d2af2a34d9d', '513bae76e395032cdb7b5e5f8d788c87' ),
			'ptitle'  => array( '9b2bd2870473e386678169c10c673af8' ),
			'title'   => array( '07d6bc1f5688653d170af8c940d22f12' ),
			'desc'    => array( '1074ed3a8a3a19e09f33ef03e831a895', '487352f80c7fe13248871c2ed75fee9d' ),
			'kw'      => array( 'e95ddfa23d0081f56992ca2923a4d1bd' ),
		),
		'sem-services-singapore' => array(
			'content' => array( 'b0143cdf0ef476dfb3ffcf707b00ccab', 'b736b1a3e41f406e9905d71a325bc438', '11b64fb6804b9d6f5a56db9e9076a5dc' ),
			'ptitle'  => array( '8bcbf04f44f0e7408ba9d83c5d8d3086' ),
			'title'   => array( 'b6cfe5195f328d4dfb4414b16607838f', '8078f27d69818acec52a40c53572eaef' ),
			'desc'    => array( '4d858f59eafd3fca38baeba11355a913', '0a14bd396a4dfc041cf69de5ee43ad77' ),
			'kw'      => array( '98dde1cf6bbd40875110fa4432f3b0d7', '4bdcb363ffb6a794fb0676987a6d1763' ),
		),
		'facebook-advertising-malaysia' => array(
			'content' => array( '6c6c80277ff614523649967c189752a3', 'f216ead1a145408457f48401a9bff45f', '4d65ad89a6f2da592a1d053ae75586db' ),
			'ptitle'  => array( 'cf5096d011671d20ad52b89c2ebaf29a' ),
			'title'   => array( 'b6d491de25fd1341cb747a51e37a873d' ),
			'desc'    => array( '7cfb228076e44ddcac8e5b48f8d41815', '34b004c7cd7169444b33a29398375ee4' ),
			'kw'      => array( 'e4535434e1039b46370e422ef9a864f6' ),
		),
		'how-to-choose-an-seo-agency' => array(
			'content' => array( '9a0dc44c8c8f75df5ef6638482924521', 'b841fad50b2edb30ed67d154e2b64a5a', 'fb83c8c72e78ded8c8470580944d890c' ),
			'ptitle'  => array( '1b7cbfd6c41312aec1dd2010599f657a' ),
			'title'   => array( '12ddf28236473f0e4d1faf304249aa78', '35a4207c5565e897a8b63ffdfc66d369' ),
			'desc'    => array( '65668e85d4e2e798ac26922c892b603c', 'd693139db7e1ede33becfba67a3d0e03' ),
			'kw'      => array( 'e10e04e38e72289b7bb74933f06f05cb' ),
		),
		'seo-vs-sem' => array(
			'content' => array( '38d06a6e829754d6630d8a297eb71090', 'a99e6294e4586452238023f46465fb74', 'bb76247d523243dc8ec15055cbe9f6b0' ),
			'ptitle'  => array( '980b52028aebfb5de26c70695401e0da' ),
			'title'   => array( '980b52028aebfb5de26c70695401e0da' ),
			'desc'    => array( '68dee08689f4580f93ab751fe798cebd', 'dedf1e91115025462d6c475e0c1637d4' ),
			'kw'      => array( '8d7d4382ab81dab0ff2724c990fbdba5' ),
		),
		'seo-cost-singapore' => array(
			'content' => array( '8b568bf7f56ca9d837160f9d08541d77', '76eff59351865d12360c2bdab6b0488f', 'f2713be5c149a913e0e60099b76f5db1' ),
			'ptitle'  => array( '0dc638afbe34d215e9b935cac5293349' ),
			'title'   => array( '0dc638afbe34d215e9b935cac5293349' ),
			'desc'    => array( '027bf5c95d20ad33054980097c973644', '139322be304f5cc6d5f8d8ab79924439' ),
			'kw'      => array( '8696e7a29d9dd25b40d163a78f81e708', 'e326a57b6d297d96ead8c0f4c8c36b59' ),
		),
		'seo-services-pricing-malaysia' => array(
			'content' => array( '6fd9009ceff2f4b7eada9d1266910cc7', '5f392a6cc18269defa3b440ffaae39f5', 'b64be0b0f9709fd0afcc4b5d6d470c4b' ),
			'ptitle'  => array( '2d1e9557ea23efde6d519b65fb3150ce' ),
			'title'   => array( '2d1e9557ea23efde6d519b65fb3150ce' ),
			'desc'    => array( 'bef087e9c7c9e6d56c2a89c9867b178e', 'a9ce5036432a8aec46ececbc044e4341' ),
			'kw'      => array( '3fa1c2d2ad0371473e5367146b987dd2', 'b1797e3937cbc5cdd865caee765d0493' ),
		),
	);
}

/**
 * Brings the seeded articles up to the optimised Rank Math fields and text
 * (focus keyword matching the URL, 120 to 160 character descriptions, keyword
 * density above 0.76%, block markup throughout).
 *
 * A field is replaced only when it is empty or still byte-for-byte what the
 * theme seeded earlier. An edited title, description, keyword or body is
 * never touched.
 *
 * @return int Number of fields changed.
 */
function remotive_refresh_article_seo() {
	if ( ! function_exists( 'remotive_seed_content' ) || ! function_exists( 'remotive_seed_content_from_site' ) ) {
		return 0;
	}

	global $wpdb;

	$hashes     = array_merge( remotive_old_article_seo_hashes(), remotive_old_page_copy_hashes() );
	$base       = remotive_seed_base_path();
	$permalinks = remotive_seed_post_permalinks();
	$changed    = 0;
	$failed     = false;

	foreach ( remotive_seed_content() as $item ) {
		if ( ! in_array( $item['type'] ?? '', array( 'post', 'page' ), true ) || empty( $item['slug'] ) || ! isset( $hashes[ $item['slug'] ] ) ) {
			continue;
		}

		$found = remotive_seed_find( $item['slug'], $item['type'], $item['parent'] ?? '' );
		$posts = $found ? array( $found ) : array();

		if ( ! $posts ) {
			continue;
		}

		$post_id = (int) $posts[0]->ID;
		$old     = $hashes[ $item['slug'] ];

		$fields = array(
			'rank_math_title'         => array( 'rm_title', 'title' ),
			'rank_math_description'   => array( 'rm_desc', 'desc' ),
			'rank_math_focus_keyword' => array( 'rm_kw', 'kw' ),
		);

		foreach ( $fields as $meta_key => $map ) {
			$new = (string) ( $item[ $map[0] ] ?? '' );
			$cur = (string) get_post_meta( $post_id, $meta_key, true );

			if ( '' === $new || $cur === $new || ( '' !== $cur && ! in_array( md5( $cur ), (array) $old[ $map[1] ], true ) ) ) {
				continue;
			}

			// Compare and swap, so a value an editor saves after it was read is kept. Only a write that
			// took counts; a database error leaves the schema version where it is and the next admin load tries again.
			if ( '' === $cur ) {
				$rows = add_post_meta( $post_id, $meta_key, $new, true ) ? 1 : 0;
			} else {
				$rows = $wpdb->update( $wpdb->postmeta, array( 'meta_value' => $new ), array( 'post_id' => $post_id, 'meta_key' => $meta_key, 'meta_value' => $cur ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
				wp_cache_delete( $post_id, 'post_meta' );
			}

			if ( false === $rows ) {
				$failed = true;
			} elseif ( 1 === (int) $rows ) {
				++$changed;
			}
		}

		// The post title, in sentence case since 1.117.0. Only a title that is still exactly what an earlier
		// release seeded is replaced; the row is matched on that title, so a title saved meanwhile is kept.
		$title_now = (string) $posts[0]->post_title;

		if ( ! empty( $item['title'] ) && $title_now !== $item['title'] && in_array( md5( $title_now ), (array) $old['ptitle'], true ) ) {
			$rows = $wpdb->update( $wpdb->posts, array( 'post_title' => $item['title'] ), array( 'ID' => $post_id, 'post_title' => $title_now ) );

			if ( false === $rows ) {
				$failed = true;
			} elseif ( 1 === (int) $rows ) {
				clean_post_cache( $post_id );
				++$changed;
			}
		}

		if ( empty( $item['content'] ) ) {
			continue;
		}

		// The stored text has had its /blog/ links turned into permalinks (and a
		// subdirectory site its base path), so it is put back into seeded form
		// before it is compared with the earlier seed hashes.
		$stored = (string) $posts[0]->post_content;

		if ( ! in_array( md5( remotive_seed_content_from_site( $stored, $base, $permalinks ) ), (array) $old['content'], true ) ) {
			continue;
		}

		$new_content = remotive_seed_content_to_site( $item['content'], $base );

		if ( $new_content === $stored ) {
			continue;
		}

		// Compare and swap: the WHERE clause holds the text that was just checked, so
		// a row an editor has saved since then is not overwritten (zero rows match).
		$rows = $wpdb->update(
			$wpdb->posts,
			array(
				'post_content'      => $new_content,
				'post_modified'     => current_time( 'mysql' ),
				'post_modified_gmt' => current_time( 'mysql', true ),
			),
			array(
				'ID'           => $post_id,
				'post_content' => $stored,
			)
		);

		if ( false === $rows ) {
			$failed = true;
		} elseif ( 1 === (int) $rows ) {
			clean_post_cache( $post_id );
			++$changed;
		}
	}

	if ( $changed && function_exists( 'remotive_resolve_seed_post_links' ) ) {
		remotive_resolve_seed_post_links(); // The new text carries /blog/ links; point them at the real permalinks again.
	}

	return $failed ? false : $changed;
}

/**
 * Option flag recording that the automatic pass has already run. Its value is
 * the theme version that ran it, which makes the history readable later.
 */
const REMOTIVE_SETUP_FLAG = 'remotive_site_setup_done';

/**
 * The migration schema version this code knows how to bring a site up to.
 *
 * Separate from the theme version: bump this only when a migration actually
 * needs to run on already-active sites (new required pages, menu changes,
 * seed additions, etc.). Bumping the theme version alone never triggers
 * migrations. This is the value stored in remotive_site_setup_done after
 * all migrations for this release complete successfully.
 */
const REMOTIVE_SETUP_SCHEMA = '1.120.4';

/** Option row that keeps two overlapping admin loads from running the migrations at the same time. */
const REMOTIVE_SETUP_LOCK = 'remotive_setup_lock';

/**
 * Migrations keyed by the schema version they introduce.
 *
 * Each migration runs once on sites whose stored schema version is less than
 * the migration's key. After it completes, the stored version advances to
 * that key. Safe to add new entries here for future releases without touching
 * anything that already ran.
 *
 * @return array<string,callable>
 */
function remotive_migration_registry() {
	return array(
		// 1.65.7: first versioned migration — provisions the two market
		// landing pages (RM-011), builds the three-column footer menu
		// system (RM-006), and seeds any new articles (RM-012).
		// Also migrates the old footer_sitemap location assignment to
		// footer_company; that part runs on admin_init regardless, so
		// we only call the seeder and menu builder here.
		'1.65.7' => function() {
			remotive_run_site_setup();
			remotive_seed_run();
			remotive_build_classic_menus();
		},
		// 1.65.8: rename /work/ to /case-studies/ if the page still lives
		// at the old slug. The required-pages registry has used 'case-studies'
		// since the slug was standardised, but any site activated before that
		// change has the page at /work/ in the database — setup only creates,
		// never renames, so it never fixed the mismatch automatically.
		'1.65.8' => function() {
			remotive_migrate_work_slug();
		},
		// 1.65.9: provisions the thank-you page. Existing sites need it
		// created or every form redirect falls back to the inline
		// confirmation and no conversion URL ever fires.
		'1.65.9' => function() {
			remotive_run_site_setup();
		},
		// 1.66.0: backfill featured images on seeded posts that have none.
		// Three Insights articles were added to the seed data without an
		// 'image' key, so they published without a thumbnail. Seeding only
		// touches an item once, so correcting the data alone would fix new
		// installs and leave every existing site with three blank cards.
		'1.66.0' => function() {
			remotive_backfill_seed_images();
		},
		// 1.66.1: retire the three unsubstantiated homepage figures.
		'1.66.1' => function() {
			remotive_retire_unsourced_stats();
		},
		// 1.75.0: re-run the image backfill. The 1.66.0 entry below already
		// does exactly the right thing, but it ran once and then stopped
		// being reachable: four seed images (seo-cost-singapore,
		// seo-services-pricing-malaysia, seo-vs-sem, and the regenerated
		// how-to-choose-an-seo-agency) were added to assets/seed-images/
		// AFTER 1.66.0 had already completed on live sites. Seeding never
		// revisits an item it has recorded, and the migration registry
		// never replays a version, so those four articles kept publishing
		// with no thumbnail and the Insights archive showed a grid of
		// blank cards next to one that had an image.
		//
		// Safe to run repeatedly by design: remotive_backfill_seed_images()
		// skips any post that already has a thumbnail, so this cannot
		// overwrite an image the owner chose, and re-running it on a site
		// that is already complete is a no-op that attaches nothing.
		'1.75.0' => function() {
			remotive_backfill_seed_images();
		},
		// 1.111.0: the Insights articles get photographs instead of the old
		// generic graphics (or nothing). Only an untouched bundled graphic or a
		// missing image is replaced.
		'1.111.0' => function() {
			remotive_refresh_article_photos();
		},
		// 1.114.0: the seeded articles get Rank Math fields that pass Rank Math's own
		// checks. Only untouched seed values are replaced.
		'1.114.0' => function() {
			return remotive_refresh_article_seo();
		},
		// 1.115.0: the seeded articles are lengthened to 1,000 words and over.
		// Same safe refresh: only text that still matches an earlier seed is replaced.
		'1.115.0' => function() {
			return remotive_refresh_article_seo();
		},
		// 1.117.0: the seeded articles are rewritten to the house writing style (no contractions, sentence-case
		// titles). Same safe refresh: only text that still matches an earlier seed is replaced.
		'1.117.0' => function() {
			return remotive_refresh_article_seo();
		},
		// 1.119.0: the seeded service and case-study pages follow the house writing style too (no contractions,
		// no banned words). The same safe refresh, now for pages as well as articles.
		'1.119.0' => function() {
			return remotive_refresh_article_seo();
		},
		// 1.120.3: light is the default colour mode. A site still on the old default (dark) moves to
		// light once; the choice stays editable in Theme Options -> Site behaviour -> Default colour mode.
		'1.120.3' => 'remotive_migrate_default_colour_mode',
		// 1.120.4: the seeded categories get a description, which is the archive's meta description.
		'1.120.4' => 'remotive_migrate_category_descriptions',
		// 1.90.0: provisions the three ad landing pages (seo-audit,
		// google-ads-management, paid-social-advertising). Registering them in
		// remotive_required_pages() only reaches fresh installs; sites
		// already at an earlier schema skip setup, so without this the
		// pages would never be created. Setup only creates what is missing
		// and never touches an existing page's content.
		'1.90.0' => function() {
			remotive_run_site_setup();
		},
		// 1.92.0: provisions the audit-requested confirmation page that the
		// landing page forms redirect to. Without it the forms would fall
		// back to the shared English thank-you page.
		'1.92.0' => function() {
			remotive_run_site_setup();
		},
		// 1.98.0: the site lists six case studies, not fourteen. The footer
		// menu of an already-set-up site still carries the old links, because
		// the menu builder never rewrites an assigned menu; swap only the
		// dropped items and the "All 14" label, never an administrator's edits.
		'1.98.0' => function() {
			remotive_refresh_case_study_menu();
		},
		// 1.103.1: the contact address is hello@remotivemedia.asia. A site that
		// saved the older .com address keeps receiving enquiries there, because
		// a saved option outranks the shipped default. Replace that one value
		// only; any other address an administrator chose is left alone.
		'1.103.1' => function() {
			remotive_correct_contact_email();
		},
	);
}

/**
 * Create the pages automatically, once — and run any pending migrations.
 *
 * Runs on theme activation, and also on the first admin load after an upgrade
 * for sites where the theme was already active when this shipped.
 *
 * Now versioned: the stored option holds the schema version of the last
 * successful migration rather than just a truthy value. version_compare()
 * decides which migrations still need to run, so an active site receives
 * exactly the changes introduced since its last schema version, in order,
 * without replaying anything it already has.
 */
function remotive_maybe_auto_setup() {
	// wp_insert_post() during an install or a WP-CLI bootstrap can run before
	// rewrite rules and post types are ready.
	if ( wp_installing() ) {
		return;
	}

	$stored   = (string) get_option( REMOTIVE_SETUP_FLAG, '' );
	$required = REMOTIVE_SETUP_SCHEMA;

	// Nothing stored yet: fresh install. Run everything in order.
	$is_fresh = '' === $stored;

	if ( ! $is_fresh && ! version_compare( $stored, $required, '<' ) ) {
		return; // Already at or ahead of the current schema — nothing to do.
	}

	// Two admin loads at once (the first after an upload, say) would both run the migrations and seed every
	// page twice. The second waits for the next load, by which time the stored version has moved on.
	if ( ! remotive_setup_lock_acquire() ) {
		return;
	}

	try {
		remotive_run_pending_migrations( $stored, $required, $is_fresh );
	} finally {
		remotive_setup_lock_release();
	}
}

/**
 * Take the setup lock, or report that another request holds it.
 *
 * One compare-and-swap on the option row: the row is updated from "free" ('0') to this request's
 * token in a single statement, so two requests that both read "free" cannot both win (a read followed
 * by a write, as with a transient, lets both through). A lock older than ten minutes is treated as
 * abandoned by a request that died, and taken over the same way.
 *
 * @return bool Whether this request holds the lock.
 */
function remotive_setup_lock_acquire() {
	global $wpdb;

	$token = (string) time();

	if ( ! is_object( $wpdb ) || ! method_exists( $wpdb, 'update' ) ) {
		// No database layer (the test bootstrap): a plain transient is the best available.
		if ( get_transient( REMOTIVE_SETUP_LOCK ) ) {
			return false;
		}
		set_transient( REMOTIVE_SETUP_LOCK, $token, 10 * MINUTE_IN_SECONDS );
		$GLOBALS['remotive_setup_lock_token'] = $token;

		return true;
	}

	// Make sure the row exists; add_option() is a no-op when it already does.
	add_option( REMOTIVE_SETUP_LOCK, '0', '', 'no' );

	// Read past the object cache: the row may have been changed by the other request moments ago.
	wp_cache_delete( REMOTIVE_SETUP_LOCK, 'options' );
	wp_cache_delete( 'alloptions', 'options' );
	$held = (string) get_option( REMOTIVE_SETUP_LOCK, '0' );
	$free = '0' === $held || '' === $held || (int) $held < time() - 10 * MINUTE_IN_SECONDS;

	if ( ! $free ) {
		return false;
	}

	// Swap only from the value just read: a request that got in between changed it, and the swap fails.
	$rows = $wpdb->update( $wpdb->options, array( 'option_value' => $token ), array( 'option_name' => REMOTIVE_SETUP_LOCK, 'option_value' => $held ) );
	wp_cache_delete( REMOTIVE_SETUP_LOCK, 'options' );
	wp_cache_delete( 'alloptions', 'options' );

	if ( 1 !== (int) $rows ) {
		return false;
	}

	$GLOBALS['remotive_setup_lock_token'] = $token;

	return true;
}

/**
 * Give the setup lock back, if this request still holds it.
 *
 * The release is conditional on the request's own token. A request that ran past the ten-minute
 * takeover and lost the lock to a newer one must not clear that newer owner's lock on its way out.
 */
function remotive_setup_lock_release() {
	global $wpdb;

	$token = isset( $GLOBALS['remotive_setup_lock_token'] ) ? (string) $GLOBALS['remotive_setup_lock_token'] : '';
	unset( $GLOBALS['remotive_setup_lock_token'] );

	if ( '' === $token ) {
		return;
	}

	if ( ! is_object( $wpdb ) || ! method_exists( $wpdb, 'update' ) ) {
		if ( (string) get_transient( REMOTIVE_SETUP_LOCK ) === $token ) {
			delete_transient( REMOTIVE_SETUP_LOCK );
		}

		return;
	}

	$wpdb->update( $wpdb->options, array( 'option_value' => '0' ), array( 'option_name' => REMOTIVE_SETUP_LOCK, 'option_value' => $token ) );
	wp_cache_delete( REMOTIVE_SETUP_LOCK, 'options' );
	wp_cache_delete( 'alloptions', 'options' );
}

/**
 * Run every migration newer than the stored schema, in order, and record each one as it completes.
 *
 * @param string $stored   The stored schema version ('' on a fresh install).
 * @param string $required The current schema version.
 * @param bool   $is_fresh Whether nothing was stored.
 */
function remotive_run_pending_migrations( $stored, $required, $is_fresh ) {
	$migrations = remotive_migration_registry();
	uksort( $migrations, 'version_compare' ); // Chronological order by version number (a plain ksort would put 1.111.0 before 1.66.0).

	foreach ( $migrations as $version => $run ) {
		// Skip migrations the stored schema already covers, but always run
		// everything on a fresh install (stored is '' which sorts below all
		// real versions under version_compare).
		if ( ! $is_fresh && ! version_compare( $stored, $version, '<' ) ) {
			continue;
		}

		// A migration that returns false did not finish. Stop here and leave the
		// stored version behind it, so the next admin load runs it again.
		if ( false === call_user_func( $run ) ) {
			return;
		}

		update_option( REMOTIVE_SETUP_FLAG, $version );
		$stored = $version; // Next loop iteration builds on this.
	}

	// Ensure the option always reflects the final schema even if the
	// migrations array is empty (shouldn't happen, but defensive).
	update_option( REMOTIVE_SETUP_FLAG, $required );
}

// Activation: fires once, for the user who switched to the theme.
add_action( 'after_switch_theme', 'remotive_maybe_auto_setup' );

/**
 * Catch-up pass for installs where the theme was already active before this
 * version, so after_switch_theme will not fire until they switch again.


/**
 * Reset the homepage results figures when they still hold the three
 * unsubstantiated values.
 *
 * Theme Options are user data and a theme has no business overwriting them
 * on a whim. This is the exception, and narrowly drawn.
 *
 * The values "3.2x average ROAS", "+140% organic traffic, year one" and
 * "98% client retention" cannot be derived from any of the thirteen case
 * studies this theme ships. One engagement reports ROAS at all, and that
 * case's own text says platform-reported ROAS flatters; the only genuine
 * year-on-year organic figure in the body of work is +43.5%, so +140% is
 * contradicted rather than merely unsupported; retention appears nowhere.
 *
 * Left alone they would have stayed a claims problem. After v1.72.0 they
 * became a worse one: the band now labels each figure with a service block
 * and links it to a specific case study, so "3.2x average ROAS" carries a
 * "read the case" link to a case study containing no such number. An
 * unsourced claim became a checkable false attribution, which any visitor
 * who clicks discovers immediately.
 *
 * Guarded so it only ever touches those exact strings: any other value,
 * including one the owner has since written, is left untouched and the
 * option is not saved at all. Matching is loose on case and whitespace but
 * strict on substance.
 *
 * @return bool Whether anything was reset.
 */
function remotive_retire_unsourced_stats() {
	$opts = get_option( 'remotive_theme_options' );

	if ( ! is_array( $opts ) ) {
		return false;
	}

	// Substrings that identify the retired claims, matched against the
	// stored value and label together.
	$retired = array(
		'3.2',
		'140',
		'98',
	);

	$labels = array(
		'average roas',
		'organic traffic, year one',
		'client retention',
	);

	$defaults = remotive_theme_option_defaults();
	$changed  = false;

	for ( $i = 1; $i <= 3; $i++ ) {
		$value = isset( $opts[ 'stat_' . $i . '_value' ] ) ? (string) $opts[ 'stat_' . $i . '_value' ] : '';
		$label = isset( $opts[ 'stat_' . $i . '_label' ] ) ? strtolower( trim( (string) $opts[ 'stat_' . $i . '_label' ] ) ) : '';

		// Both the number and the label have to match a retired claim, so a
		// coincidental "98%" of something else is never caught.
		$is_retired = false;

		foreach ( $labels as $k => $needle ) {
			if ( $needle === $label && false !== strpos( $value, $retired[ $k ] ) ) {
				$is_retired = true;
				break;
			}
		}

		if ( ! $is_retired ) {
			continue;
		}

		foreach ( array( 'block', 'value', 'label', 'case' ) as $part ) {
			$key = 'stat_' . $i . '_' . $part;
			if ( isset( $defaults[ $key ] ) ) {
				$opts[ $key ] = $defaults[ $key ];
			}
		}

		$changed = true;
	}

	if ( $changed ) {
		// Not update_option(): that runs the settings sanitiser, which reads a missing checkbox as "off"
		// and would switch off every default-on option an older saved array does not hold yet.
		remotive_update_options_raw( $opts );
	}

	return $changed;
}

/**
 * Attach featured images to seeded posts that are missing one.
 *
 * Only fills gaps: a post that already has a thumbnail is skipped, so a
 * hand-picked image is never overwritten by the bundled one. Matched by
 * slug, and silently skips anything whose file is absent.
 *
 * @return int Number of images attached.
 */
function remotive_backfill_seed_images() {
	if ( ! function_exists( 'remotive_seed_content' ) || ! function_exists( 'remotive_seed_attach_image' ) ) {
		return 0;
	}

	$attached = 0;

	foreach ( remotive_seed_content() as $item ) {
		if ( empty( $item['image'] ) || empty( $item['slug'] ) ) {
			continue;
		}

		$type  = ( 'post' === ( $item['type'] ?? '' ) ) ? 'post' : 'page';
		$posts = get_posts( array(
			'name'             => $item['slug'],
			'post_type'        => $type,
			'post_status'      => 'publish',
			'numberposts'      => 1,
			'suppress_filters' => false,
		) );

		if ( ! $posts ) {
			continue;
		}

		$post_id = $posts[0]->ID;

		// Never replace an image somebody chose deliberately.
		if ( has_post_thumbnail( $post_id ) ) {
			continue;
		}

		remotive_seed_attach_image( $post_id, $item['image'], $item['title'] ?? '' );

		if ( has_post_thumbnail( $post_id ) ) {
			$attached++;
		}
	}

	return $attached;
}

/**
 * MD5 of each bundled article graphic as it shipped before v1.111.0
 * (assets/seed-images/<slug>.png). A thumbnail whose file is byte-for-byte one of
 * these is the untouched bundled graphic; anything else was chosen by someone.
 *
 * @return array<string,string> Article slug => md5.
 */
function remotive_old_seed_graphic_hashes() {
	return array(
		'seo-friendly-web-design'       => '5b944cd9dfc2e2bfeaf4c7de697eee9e',
		'sem-services-singapore'        => 'f6b24e679d5fcc564b21a72e4bda84bb',
		'facebook-advertising-malaysia' => '60422c98b90e1ce50882e4c3a3f44472',
		'how-to-choose-an-seo-agency'   => '9cc33faa32067f3a8cba84bed2d1a992',
		'seo-vs-sem'                    => 'efb2bbdb3c783a701219b678021b2bde',
		'seo-cost-singapore'            => 'd5810dcac3f1e9feb7343bb2b6a0e38d',
		'seo-services-pricing-malaysia' => 'ff648261b28503a7d8c05f62581c29c1',
	);
}

/**
 * Swap the old generic article graphics for the photographs.
 *
 * Before v1.111.0 each Insights article carried a bundled gradient graphic with
 * its title on it, or none at all. Now an article gets a Pexels photo
 * (assets/seed-images/<slug>.jpg). This sets the photo only when the article has
 * no thumbnail, or its thumbnail file is byte-for-byte the old bundled graphic
 * (compared by content, not by name, so an image an owner uploaded is never
 * taken for it). Nothing is deleted: the old graphic stays in the media library.
 *
 * @return int Number of articles whose image was set or replaced.
 */
function remotive_refresh_article_photos() {
	if ( ! function_exists( 'remotive_seed_content' ) || ! function_exists( 'remotive_seed_attach_image' ) ) {
		return 0;
	}

	$hashes  = remotive_old_seed_graphic_hashes();
	$changed = 0;

	foreach ( remotive_seed_content() as $item ) {
		if ( 'post' !== ( $item['type'] ?? '' ) || empty( $item['image'] ) || empty( $item['slug'] ) ) {
			continue;
		}

		$posts = get_posts( array(
			'name'             => $item['slug'],
			'post_type'        => 'post',
			'post_status'      => 'publish',
			'numberposts'      => 1,
			'suppress_filters' => false,
		) );

		if ( ! $posts ) {
			continue;
		}

		$post_id = (int) $posts[0]->ID;
		$thumb   = (int) get_post_thumbnail_id( $post_id );

		if ( $thumb ) {
			$file = (string) get_attached_file( $thumb );

			if ( ! isset( $hashes[ $item['slug'] ] ) || ! is_readable( $file ) || md5_file( $file ) !== $hashes[ $item['slug'] ] ) {
				continue; // Not the untouched bundled graphic: it stays.
			}
		}

		remotive_seed_attach_image( $post_id, $item['image'], $item['title'] ?? '' );

		if ( (int) get_post_thumbnail_id( $post_id ) !== $thumb ) {
			++$changed;
		}
	}

	return $changed;
}

/**
 * Rename the /work/ page to /case-studies/ if it still exists at the old slug.
 *
 * Idempotent: does nothing if a page already exists at /case-studies/, and
 * does nothing if no page exists at /work/ — safe to run multiple times or
 * on a fresh install. Child pages (individual case studies) have their parent
 * ID unchanged, so their permalinks update automatically once the parent slug
 * changes.
 */
function remotive_migrate_work_slug() {
	if ( get_page_by_path( 'case-studies', OBJECT, 'page' ) ) {
		return; // Already at the right slug.
	}

	$work = get_page_by_path( 'work', OBJECT, 'page' );
	if ( ! $work ) {
		return;
	}

	wp_update_post( array(
		'ID'        => $work->ID,
		'post_name' => 'case-studies',
	) );

	flush_rewrite_rules( false );
}

/**
 * Restricted to an administrator loading the dashboard, so the pages are
 * created by someone who is entitled to create them.
 */
function remotive_auto_setup_on_admin() {
	if ( ! is_admin() || wp_doing_ajax() ) {
		return;
	}

	if ( ! current_user_can( 'edit_theme_options' ) || ! current_user_can( 'publish_pages' ) ) {
		return;
	}

	remotive_maybe_auto_setup();
}
add_action( 'admin_init', 'remotive_auto_setup_on_admin' );

/**
 * Option holding the ID of the navigation menu the header uses.
 */
const REMOTIVE_PRIMARY_NAV = 'remotive_primary_nav_id';

/**
 * Build the primary navigation menu from the pages, in menu_order.
 *
 * Block themes keep navigation in a `wp_navigation` post rather than in the
 * classic Appearance -> Menus screen. Creating one here, and pointing the
 * header at it, means the running order and the labels are editable in the
 * Site Editor's Navigation panel instead of being frozen in the template
 * file. Reordering the menu reorders the header.
 *
 * Skipped if a menu already exists, so an edited menu is never rebuilt.
 *
 * @return int Navigation post ID, or 0 on failure.
 */
function remotive_build_primary_navigation() {
	$existing = (int) get_option( REMOTIVE_PRIMARY_NAV );

	if ( $existing ) {
		$post = get_post( $existing );

		if ( $post && 'wp_navigation' === $post->post_type && 'trash' !== $post->post_status ) {
			return $existing;
		}
	}

	$items = array();

	foreach ( remotive_required_pages() as $slug => $page ) {
		if ( empty( $page['in_menu'] ) ) {
			continue;
		}

		$target = get_page_by_path( $slug, OBJECT, 'page' );

		if ( ! $target ) {
			continue;
		}

		// Linking by post ID rather than by URL keeps the menu item pointing at
		// the right page if the slug is ever changed in the editor.
		$items[] = sprintf(
			'<!-- wp:navigation-link {"label":"%1$s","type":"page","id":%2$d,"url":"%3$s","kind":"post-type"} /-->',
			esc_attr( $page['title'] ),
			(int) $target->ID,
			esc_url( get_permalink( $target->ID ) )
		);
	}

	if ( ! $items ) {
		return 0;
	}

	$nav_id = wp_insert_post(
		array(
			'post_title'   => __( 'Primary', 'remotive' ),
			'post_name'    => 'primary',
			'post_type'    => 'wp_navigation',
			'post_status'  => 'publish',
			'post_content' => implode( "\n", $items ),
		),
		true
	);

	if ( is_wp_error( $nav_id ) ) {
		return 0;
	}

	update_option( REMOTIVE_PRIMARY_NAV, (int) $nav_id );

	return (int) $nav_id;
}

/**
 * Point the header's navigation block at that menu.
 *
 * The block markup in parts/header.html carries a hard-coded list of links,
 * which stays as the fallback. When a menu exists this filter sets the block's
 * `ref`, so WordPress renders the menu instead and the site's running order
 * follows whatever the menu says.
 *
 * Only the header navigation is touched. It is matched on its class name, so
 * the footer's own navigation blocks keep their separate link lists.
 *
 * @param array $parsed_block A parsed block.
 * @return array
 */
function remotive_use_primary_navigation( $parsed_block ) {
	if ( empty( $parsed_block['blockName'] ) || 'core/navigation' !== $parsed_block['blockName'] ) {
		return $parsed_block;
	}

	$class = $parsed_block['attrs']['className'] ?? '';

	if ( false === strpos( $class, 'rm-nav__links' ) ) {
		return $parsed_block;
	}

	// Already pointed somewhere deliberately — leave it alone.
	if ( ! empty( $parsed_block['attrs']['ref'] ) ) {
		return $parsed_block;
	}

	$nav_id = (int) get_option( REMOTIVE_PRIMARY_NAV );

	if ( ! $nav_id ) {
		return $parsed_block;
	}

	$nav = get_post( $nav_id );

	// If the menu was deleted or trashed, fall through to the links written in
	// the template rather than rendering an empty header.
	if ( ! $nav || 'wp_navigation' !== $nav->post_type || 'publish' !== $nav->post_status ) {
		return $parsed_block;
	}

	$parsed_block['attrs']['ref'] = $nav_id;

	return $parsed_block;
}
// Superseded by inc/setup/classic-menus.php (v1.33.0): menus are managed in
// Appearance -> Menus and rendered by remotive_render_classic_menu(). The
// wp_navigation wiring below is left in place, unhooked, so a site that
// previously used the Site Editor menu can restore it by re-adding this
// filter.
// add_filter( 'render_block_data', 'remotive_use_primary_navigation' );

/**
 * Handle the setup button submission.
 */
function remotive_handle_site_setup() {
	if ( ! current_user_can( 'edit_theme_options' ) || ! current_user_can( 'publish_pages' ) ) {
		wp_die( esc_html__( 'You do not have permission to set up site pages.', 'remotive' ) );
	}

	check_admin_referer( 'remotive_site_setup' );

	$result = remotive_run_site_setup();

	// Pages exist now: publish any shipped content that is still missing,
	// then populate and assign the classic menus.
	$seeded = function_exists( 'remotive_seed_run' ) ? remotive_seed_run() : array();

	if ( $seeded ) {
		$result['created'] = array_merge( $result['created'] ?? array(), $seeded );
	}

	if ( function_exists( 'remotive_build_classic_menus' ) ) {
		remotive_build_classic_menus();
	}

	$redirect = add_query_arg(
		array(
			'page'            => 'remotive-theme-options',
			'remotive_setup'  => 'done',
			'created'         => count( $result['created'] ),
			'updated'         => count( $result['updated'] ),
		),
		admin_url( 'themes.php' )
	);

	wp_safe_redirect( $redirect );
	exit;
}
add_action( 'admin_post_remotive_site_setup', 'remotive_handle_site_setup' );

/**
 * Handle a per-item restore from the Shipped content card.
 */
function remotive_handle_restore_seed() {
	if ( ! current_user_can( 'edit_theme_options' ) || ! current_user_can( 'publish_pages' ) ) {
		wp_die( esc_html__( 'You do not have permission to restore shipped content.', 'remotive' ) );
	}

	$key = isset( $_POST['item'] ) ? sanitize_text_field( wp_unslash( $_POST['item'] ) ) : '';

	check_admin_referer( 'remotive_restore_seed_' . $key );

	$restored = function_exists( 'remotive_seed_restore' ) ? remotive_seed_restore( $key ) : false;

	$redirect = add_query_arg(
		array(
			'page'              => 'remotive-theme-options',
			'remotive_restored' => $restored ? rawurlencode( $restored ) : '0',
		),
		admin_url( 'themes.php' )
	);

	wp_safe_redirect( $redirect );
	exit;
}
add_action( 'admin_post_remotive_restore_seed', 'remotive_handle_restore_seed' );

/**
 * Render the setup card. Called from the options page, outside the settings
 * form, since this posts to admin-post.php rather than options.php.
 */
function remotive_render_site_setup_card() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$status  = remotive_site_setup_status();
	$missing = 0;

	foreach ( $status as $item ) {
		if ( ! $item['exists'] || ! $item['template_ok'] ) {
			$missing++;
		}
	}
	?>
	<?php $seed = function_exists( 'remotive_seed_status' ) ? remotive_seed_status() : array( 'total' => 0, 'live' => 0 ); ?>
	<div class="rm-admin__card rm-admin__card--setup">
		<h2><?php esc_html_e( 'Shipped content', 'remotive' ); ?></h2>
		<p class="rm-admin__card-desc">
			<?php
			printf(
				/* translators: 1: number live, 2: total. */
				esc_html__( '%1$d of %2$d shipped items are published: four Insights articles, six service pages and thirteen case studies. These are created automatically when the theme is activated, so the site is complete from the start. Editing or deleting any of them is safe; setup never rewrites or resurrects an item on its own.', 'remotive' ),
				(int) $seed['live'],
				(int) $seed['total']
			);
			?>
		</p>
		<p class="rm-admin__card-desc">
			<?php esc_html_e( 'Restore replaces an item with the version shipped in the current theme release: title, content, excerpt and SEO meta. Use it after a theme update that improved the shipped copy, or to bring back an item that was deleted. Edits you made to that item are lost; nothing else on the site is touched.', 'remotive' ); ?>
		</p>

		<?php if ( function_exists( 'remotive_seed_content' ) ) : ?>
			<table class="rm-setup-table widefat striped">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Item', 'remotive' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Type', 'remotive' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Status', 'remotive' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Action', 'remotive' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( remotive_seed_content() as $rm_item ) : ?>
						<?php
						$rm_key  = $rm_item['type'] . ':' . $rm_item['slug'];
						$rm_post = remotive_seed_find( $rm_item['slug'], $rm_item['type'], $rm_item['parent'] ?? '' );

						if ( $rm_post ) {
							$rm_status = 'publish' === $rm_post->post_status
								? __( 'Published', 'remotive' )
								: ucfirst( $rm_post->post_status );
						} else {
							$rm_status = __( 'Deleted', 'remotive' );
						}
						?>
						<tr>
							<td data-label="<?php esc_attr_e( 'Item', 'remotive' ); ?>">
								<strong><?php echo esc_html( $rm_item['title'] ); ?></strong>
								<?php if ( $rm_post ) : ?>
									<a href="<?php echo esc_url( get_edit_post_link( $rm_post->ID ) ); ?>"><?php esc_html_e( 'Edit', 'remotive' ); ?></a>
								<?php endif; ?>
							</td>
							<td data-label="<?php esc_attr_e( 'Type', 'remotive' ); ?>"><?php echo esc_html( 'post' === $rm_item['type'] ? __( 'Insights article', 'remotive' ) : ( ( $rm_item['parent'] ?? '' ) === 'case-studies' ? __( 'Case study', 'remotive' ) : __( 'Page', 'remotive' ) ) ); ?></td>
							<td data-label="<?php esc_attr_e( 'Status', 'remotive' ); ?>"><?php echo esc_html( $rm_status ); ?></td>
							<td data-label="<?php esc_attr_e( 'Action', 'remotive' ); ?>">
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm( '<?php echo esc_js( __( 'Replace this item with the shipped version? Edits to it are lost.', 'remotive' ) ); ?>' );">
									<input type="hidden" name="action" value="remotive_restore_seed" />
									<input type="hidden" name="item" value="<?php echo esc_attr( $rm_key ); ?>" />
									<?php wp_nonce_field( 'remotive_restore_seed_' . $rm_key ); ?>
									<button type="submit" class="button button-small">
										<?php echo esc_html( $rm_post ? __( 'Restore shipped version', 'remotive' ) : __( 'Recreate', 'remotive' ) ); ?>
									</button>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>

	<div class="rm-admin__card rm-admin__card--setup">
		<h2><?php esc_html_e( 'Menus', 'remotive' ); ?></h2>
		<p class="rm-admin__card-desc">
			<?php esc_html_e( 'Menus are managed the classic way, in Appearance → Menus. The theme registers three locations: Main menu (header), Footer: Sitemap column, and Footer: Services column. Edit a menu there and the site updates immediately; no Site Editor needed. If a location has no menu assigned, the header or footer falls back to its built-in list of links, so the site never renders an empty menu.', 'remotive' ); ?>
		</p>
		<p>
			<a class="button button-secondary" href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>">
				<?php esc_html_e( 'Edit menus', 'remotive' ); ?>
			</a>
		</p>
		<p class="rm-admin__card-desc">
			<?php esc_html_e( 'Note: new pages are not added to a menu automatically. The service pages and case studies sit under Services and Case Studies, so they are reachable through those pages and the footer; add them to the main menu only if you want them at the top level.', 'remotive' ); ?>
		</p>
	</div>

	<div class="rm-admin__card rm-admin__card--setup">
		<h2><?php esc_html_e( 'Site pages', 'remotive' ); ?></h2>
		<p class="rm-admin__card-desc">
			<?php esc_html_e( 'The theme ships the templates; WordPress still needs the Pages themselves. These are created automatically when the theme is activated, so the list below should already read Ready. Use the button to re-check, or to rebuild anything that was later deleted. It never overwrites a page that already exists.', 'remotive' ); ?>
		</p>

		<table class="rm-setup-table widefat striped">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Page', 'remotive' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Slug', 'remotive' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Status', 'remotive' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Still to do', 'remotive' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $status as $slug => $item ) : ?>
					<tr>
						<td>
							<strong><?php echo esc_html( $item['title'] ); ?></strong>
							<?php if ( $item['exists'] && $item['edit_link'] ) : ?>
								<a href="<?php echo esc_url( $item['edit_link'] ); ?>"><?php esc_html_e( 'Edit', 'remotive' ); ?></a>
							<?php endif; ?>
						</td>
						<td><code>/<?php echo esc_html( $slug ); ?>/</code></td>
						<td>
							<?php if ( ! $item['exists'] ) : ?>
								<span class="rm-setup-pill rm-setup-pill--missing"><?php esc_html_e( 'Missing', 'remotive' ); ?></span>
							<?php elseif ( ! $item['template_ok'] ) : ?>
								<span class="rm-setup-pill rm-setup-pill--warn"><?php esc_html_e( 'Template not set', 'remotive' ); ?></span>
							<?php else : ?>
								<span class="rm-setup-pill rm-setup-pill--ok"><?php esc_html_e( 'Ready', 'remotive' ); ?></span>
							<?php endif; ?>
						</td>
						<td class="rm-setup-note"><?php echo esc_html( $item['note'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="remotive_site_setup">
			<?php wp_nonce_field( 'remotive_site_setup' ); ?>
			<?php
			submit_button(
				$missing > 0
					/* translators: %d: number of pages needing work. */
					? sprintf( __( 'Set up %d page(s)', 'remotive' ), $missing )
					: __( 'Re-check pages', 'remotive' ),
				$missing > 0 ? 'primary' : 'secondary',
				'submit',
				false
			);
			?>
		</form>
	</div>
	<?php
}

/* ==========================================================================
 * Version sync — everything that has to follow the theme, with no manual step.
 *
 * The migration registry above handles one-off schema changes. This handles
 * the other half: things that live OUTSIDE the theme's files (saved options,
 * the team roster, drop-ins in wp-content/, page slugs) and therefore never
 * update just because new theme files were uploaded. It runs once per theme
 * VERSION — on activation and on the first request after any upload, however
 * the files got there (Appearance upload, FTP, deploy script) — rather than
 * on a WordPress hook that only some of those routes fire.
 * ======================================================================== */

const REMOTIVE_SYNCED_VERSION    = 'remotive_synced_version';
const REMOTIVE_DEFAULTS_SNAPSHOT = 'remotive_defaults_snapshot';
const REMOTIVE_OPTIONS_BACKUP    = 'remotive_theme_options_backup';
const REMOTIVE_SLUG_REDIRECTS    = 'remotive_slug_redirects';
const REMOTIVE_SEEDED_VERSION    = 'remotive_seeded_version';

/**
 * Save remotive_theme_options without running the form sanitizer.
 *
 * The sanitizer exists to clean what a person typed into the settings form,
 * and it fills every key it does not receive. A programmatic sync is not a
 * form post: it must be able to REMOVE a key so the shipped default shows
 * through again, which the sanitizer would immediately undo.
 *
 * @param array $value The full options array to store.
 */
/**
 * Move a site on the old default colour mode (dark) to the new one (light), once.
 *
 * Only the value 'dark' is changed: 'system' was a deliberate choice, and a site that never saved its
 * options reads the new default already. Written through remotive_update_options_raw() so the sanitiser
 * does not rewrite the other options on the way.
 *
 * @return bool
 */
function remotive_migrate_default_colour_mode() {
	global $wpdb;

	$opts = get_option( 'remotive_theme_options' );

	if ( ! is_array( $opts ) || ! isset( $opts['default_theme'] ) || 'dark' !== (string) $opts['default_theme'] ) {
		return true;
	}

	$new                  = $opts;
	$new['default_theme'] = 'light';

	if ( ! is_object( $wpdb ) || ! method_exists( $wpdb, 'update' ) ) {
		remotive_update_options_raw( $new ); // No database layer (the test bootstrap).

		return true;
	}

	// Compare and swap on the stored row: the write only lands if the options are still exactly what was
	// read, so a Theme Options save that lands in between is never overwritten by this older snapshot.
	// When the swap finds the row changed, that save chose a mode, and this migration leaves it alone.
	$wpdb->update(
		$wpdb->options,
		array( 'option_value' => maybe_serialize( $new ) ),
		array( 'option_name' => 'remotive_theme_options', 'option_value' => maybe_serialize( $opts ) )
	);
	wp_cache_delete( 'remotive_theme_options', 'options' );
	wp_cache_delete( 'alloptions', 'options' );

	return true;
}

/**
 * Give each seeded category its description, where it has none.
 *
 * The seeder created the three categories with no description, so their archives had no meta
 * description. A description an editor has written is kept.
 *
 * @return bool
 */
function remotive_migrate_category_descriptions() {
	foreach ( array( 'seo-ai-search', 'paid-media', 'agency-guides' ) as $slug ) {
		$term = get_term_by( 'slug', $slug, 'category' );

		if ( ! $term || is_wp_error( $term ) || '' !== trim( (string) $term->description ) ) {
			continue;
		}

		wp_update_term( $term->term_id, 'category', array( 'description' => remotive_seed_category_description( $slug ) ) );
	}

	return true;
}

function remotive_update_options_raw( $value ) {
	$priority = has_filter( 'sanitize_option_remotive_theme_options', 'remotive_sanitize_theme_options' );

	if ( false !== $priority ) {
		remove_filter( 'sanitize_option_remotive_theme_options', 'remotive_sanitize_theme_options', $priority );
	}

	update_option( 'remotive_theme_options', $value );

	if ( false !== $priority ) {
		add_filter( 'sanitize_option_remotive_theme_options', 'remotive_sanitize_theme_options', $priority );
	}
}

/**
 * Option keys whose shipped default was rewritten in the release that
 * introduced this sync (the Fix / Found / Scale story).
 *
 * The first time the sync runs there is no record of what defaults a site was
 * last given, so it cannot tell "still the old default" from "someone edited
 * it". For these keys only, that first run releases the saved value so the new
 * default shows — the previous values are kept in REMOTIVE_OPTIONS_BACKUP so
 * nothing is lost. Every later release uses the snapshot instead and never
 * touches a value that was edited.
 *
 * @return string[]
 */
function remotive_forced_default_keys() {
	return array(
		'hero_line_1', 'hero_sub', 'hero_cta_primary', 'hero_cta_second',
		'services_heading', 'services_sub', 'why_sub',
		'about_heading', 'about_sub',
		'cta_heading', 'cta_sub', 'cta_button',
		'stat_1_block', 'stat_2_block', 'stat_3_block',
	);
}

/**
 * Let new shipped defaults reach a site whose options were already saved.
 *
 * remotive_get_theme_option() returns a saved value in preference to the code
 * default, and the settings screen saves EVERY field on the first Save — so a
 * site that has ever pressed Save has frozen all of its copy at that release's
 * defaults, and no later release could change any of it.
 *
 * Rule: a saved value that still equals the default the theme shipped last
 * time was never customised, so it is released (removed) and the getter falls
 * back to the new default. A value that differs was written by a person and
 * is left alone. The snapshot of shipped defaults is refreshed each run.
 */
function remotive_sync_option_defaults() {
	$defaults = remotive_theme_option_defaults();
	$saved    = get_option( 'remotive_theme_options', array() );
	$saved    = is_array( $saved ) ? $saved : array();
	$snapshot = get_option( REMOTIVE_DEFAULTS_SNAPSHOT, null );
	$first    = ! is_array( $snapshot );
	$forced   = remotive_forced_default_keys();
	$version  = wp_get_theme( get_stylesheet() )->get( 'Version' );
	$released = array();

	foreach ( $defaults as $key => $shipped ) {
		// The roster is an array with its own merge below; everything else
		// here is a plain string setting.
		if ( ! is_scalar( $shipped ) || ! array_key_exists( $key, $saved ) ) {
			continue;
		}

		$current = $saved[ $key ];

		if ( $current === $shipped ) {
			continue; // Already showing the shipped value.
		}

		$was_stock = ! $first && array_key_exists( $key, $snapshot ) && $snapshot[ $key ] === $current;
		$is_forced = $first && in_array( $key, $forced, true );

		if ( $was_stock || $is_forced ) {
			$released[ $key ] = $current;
			unset( $saved[ $key ] );
		}
	}

	if ( $released ) {
		$backup = get_option( REMOTIVE_OPTIONS_BACKUP, array() );
		$backup = is_array( $backup ) ? $backup : array();

		$backup[ $version . ' @ ' . gmdate( 'Y-m-d H:i' ) . ' UTC' ] = $released;

		// Keep the last few releases only; this is an undo, not an archive.
		update_option( REMOTIVE_OPTIONS_BACKUP, array_slice( $backup, -5, null, true ), false );
		remotive_update_options_raw( $saved );
	}

	update_option( REMOTIVE_DEFAULTS_SNAPSHOT, array_filter( $defaults, 'is_scalar' ), false );
}

/**
 * Line the live case-study pages up with the slugs the theme links to.
 *
 * Every link in the templates, footer and seed points at the theme's own
 * slugs. A site whose case studies were created some other way (or cleaned up
 * by hand) can hold the same pages under different slugs, and then every one
 * of those links 404s. Match by title, under /case-studies/, and move the
 * page to the theme's slug. Pages that already sit at the right slug are only
 * topped up with the theme's page template and SEO fields when those are
 * empty — never overwritten.
 *
 * WordPress does not record an old-slug redirect for hierarchical types like
 * pages, so the previous path is remembered here and redirected on 404.
 */
function remotive_sync_case_study_slugs() {
	if ( ! function_exists( 'remotive_seed_content' ) ) {
		return;
	}

	$parent = get_page_by_path( 'case-studies', OBJECT, 'page' );

	if ( ! $parent ) {
		return;
	}

	$redirects = get_option( REMOTIVE_SLUG_REDIRECTS, array() );
	$redirects = is_array( $redirects ) ? $redirects : array();
	$moved     = false;

	foreach ( remotive_seed_content() as $item ) {
		if ( 'page' !== $item['type'] || 'case-studies' !== ( $item['parent'] ?? '' ) ) {
			continue;
		}

		$page = get_page_by_path( 'case-studies/' . $item['slug'], OBJECT, 'page' );

		if ( ! $page ) {
			$matches = get_posts(
				array(
					'post_type'        => 'page',
					'post_status'      => 'publish',
					'post_parent'      => $parent->ID,
					'title'            => $item['title'],
					'numberposts'      => 1,
					'suppress_filters' => true,
				)
			);

			if ( ! $matches ) {
				continue; // Nothing to align; the seed creates it on an admin load.
			}

			$page     = $matches[0];
			$old_slug = $page->post_name;

			wp_update_post(
				array(
					'ID'        => $page->ID,
					'post_name' => $item['slug'],
				)
			);

			if ( $old_slug !== $item['slug'] ) {
				$redirects[ 'case-studies/' . $old_slug ] = 'case-studies/' . $item['slug'];
				$moved = true;
			}
		}

		// Top up, never overwrite.
		$template = get_post_meta( $page->ID, '_wp_page_template', true );

		if ( ! empty( $item['template'] ) && ( '' === $template || 'default' === $template ) ) {
			update_post_meta( $page->ID, '_wp_page_template', $item['template'] );
		}

		foreach ( array( 'rm_title' => 'rank_math_title', 'rm_desc' => 'rank_math_description', 'rm_kw' => 'rank_math_focus_keyword' ) as $from => $meta_key ) {
			if ( ! empty( $item[ $from ] ) && '' === (string) get_post_meta( $page->ID, $meta_key, true ) ) {
				update_post_meta( $page->ID, $meta_key, $item[ $from ] );
			}
		}
	}

	if ( $moved ) {
		update_option( REMOTIVE_SLUG_REDIRECTS, $redirects, false );
	}
}

/**
 * 301 a remembered old path to its new one — only when it would 404.
 */
function remotive_redirect_moved_slugs() {
	if ( ! is_404() ) {
		return;
	}

	$redirects = get_option( REMOTIVE_SLUG_REDIRECTS, array() );

	if ( empty( $redirects ) || ! is_array( $redirects ) ) {
		return;
	}

	$path = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : '';
	$path = trim( (string) $path, '/' );

	if ( isset( $redirects[ $path ] ) ) {
		wp_safe_redirect( home_url( '/' . $redirects[ $path ] . '/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'remotive_redirect_moved_slugs', 1 );

/**
 * Files this theme no longer ships, relative to the theme folder.
 *
 * WordPress's "replace current theme" upload swaps the whole folder, but an
 * FTP or deploy-script upload only ADDS files, so anything deleted from the
 * theme lingers on the server indefinitely. Listing it here makes removal a
 * one-line change in the release that retires it: the version sync deletes
 * whatever is listed, wherever it is still sitting. Globs are allowed.
 *
 * Team portraits are personal likenesses, so a photo that has been retired or
 * replaced under a new name belongs here rather than being left behind.
 *
 * @return string[]
 */
function remotive_retired_files() {
	return array(
		// 1.88.0: the rollover portrait was removed; one image per person.
		'assets/team/*-alt.avif',
	);
}

/**
 * Delete every retired file that is still present.
 *
 * Confined to the theme folder: each match is resolved with realpath() and
 * skipped unless it is a regular file inside the theme directory, so a bad
 * pattern (or a symlink) can never reach outside it.
 */
function remotive_prune_retired_files() {
	$root = realpath( get_stylesheet_directory() );

	if ( false === $root ) {
		return;
	}

	foreach ( remotive_retired_files() as $pattern ) {
		if ( false !== strpos( $pattern, '..' ) || '/' === substr( $pattern, 0, 1 ) ) {
			continue; // Relative to the theme folder only.
		}

		foreach ( (array) glob( $root . '/' . $pattern ) as $file ) {
			$real = realpath( $file );

			if ( false === $real || 0 !== strpos( $real, $root . DIRECTORY_SEPARATOR ) || ! is_file( $real ) ) {
				continue;
			}

			// A read-only file system is not worth a fatal; the next version
			// bump tries again.
			@unlink( $real ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.unlink_unlink
		}
	}
}

/**
 * Replace the retired .com contact address with the .asia one.
 *
 * @return bool Whether the saved value was changed.
 */
function remotive_correct_contact_email() {
	$options = get_option( 'remotive_theme_options', array() );

	if ( is_array( $options ) && isset( $options['contact_email'] ) && 'hello@remotivemedia.com' === strtolower( trim( (string) $options['contact_email'] ) ) ) {
		$options['contact_email'] = 'hello@remotivemedia.asia';

		// Raw, for the reason given in remotive_retire_unsourced_stats().
		remotive_update_options_raw( $options );

		return true;
	}

	return false;
}

/**
 * Run every sync once per theme version.
 *
 * Claims the version FIRST, then runs each step isolated, so one failing step
 * can neither skip the others nor turn into an error on every page load.
 * Hooked to `init` (not just activation) because uploading new theme files
 * over the active theme fires no activation hook at all.
 */
function remotive_version_sync() {
	if ( wp_installing() ) {
		return;
	}

	$current = wp_get_theme( get_stylesheet() )->get( 'Version' );

	if ( ! $current || get_option( REMOTIVE_SYNCED_VERSION ) === $current ) {
		return;
	}

	update_option( REMOTIVE_SYNCED_VERSION, $current, false );

	$steps = array(
		'remotive_install_error_dropins',
		'remotive_sync_team_roster',
		'remotive_sync_team_order',
		'remotive_sync_team_names',
		'remotive_sync_option_defaults',
		'remotive_sync_case_study_slugs',
		'remotive_prune_retired_files',
		'remotive_i18n_install_tables',
	);

	foreach ( $steps as $step ) {
		if ( ! function_exists( $step ) ) {
			continue;
		}

		try {
			call_user_func( $step );
		} catch ( \Throwable $e ) {
			error_log( sprintf( '[remotive] version sync step %s failed: %s', $step, $e->getMessage() ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}
}
add_action( 'init', 'remotive_version_sync', 20 );

// Re-activating the same version should still repair a deleted drop-in.
add_action(
	'after_switch_theme',
	function () {
		delete_option( REMOTIVE_SYNCED_VERSION );
		remotive_version_sync();
	}
);

/**
 * Create any content the theme has started shipping since the site was set up.
 *
 * Seeding records every item it has ever handled, so this only adds NEW items
 * and never re-creates one an owner deleted. Admin-only for the same reason
 * remotive_auto_setup_on_admin() is: content is created by someone entitled
 * to create it.
 */
function remotive_seed_new_content_on_admin() {
	if ( ! is_admin() || wp_doing_ajax() || ! current_user_can( 'publish_pages' ) ) {
		return;
	}

	$current = wp_get_theme( get_stylesheet() )->get( 'Version' );

	if ( ! $current || get_option( REMOTIVE_SEEDED_VERSION ) === $current ) {
		return;
	}

	update_option( REMOTIVE_SEEDED_VERSION, $current, false );

	if ( function_exists( 'remotive_seed_run' ) ) {
		remotive_seed_run();
		remotive_sync_case_study_slugs(); // A just-seeded page may need its template/meta topped up.
	}
}
add_action( 'admin_init', 'remotive_seed_new_content_on_admin', 20 );
