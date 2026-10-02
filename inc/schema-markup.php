<?php
/**
 * Structured data (schema.org JSON-LD) for the theme's existing content.
 *
 * Design principles, in the order they were required:
 *
 * 1. MODE-INDEPENDENT. The graph is emitted in <head> as JSON-LD and
 *    describes content, not presentation — the light/dark toggle flips
 *    CSS custom properties on <html> and never touches this output, so
 *    the markup is identical in both modes by construction.
 *
 * 2. PER-TYPE ACQUIESCENCE TO SEO PLUGINS. If an active SEO plugin
 *    takes control of a particular schema type (Organization, WebSite,
 *    WebPage, Article, BreadcrumbList, Person are the types the major
 *    plugins emit), the theme yields that type — and ONLY that type.
 *    Types no plugin manages (this theme's Service and FAQPage nodes,
 *    which no major plugin emits automatically) stay theme-native and
 *    take precedence, exactly as the requirement states. The map of
 *    who-manages-what lives in remotive_schema_plugin_managed_types()
 *    and is filterable, so a site owner can widen or narrow the yield
 *    without touching theme code.
 *
 * 3. ONE SOURCE OF TRUTH. Entity facts come from the same places the
 *    rendered site uses: remotive_get_theme_option() for email, address
 *    and socials (wp-admin editable), get_bloginfo()/home_url() for the
 *    site, the FAQ answers parsed from the landing-page templates
 *    themselves so the schema can never drift from the visible content.
 *    The legal name and registration facts mirror ssot.md.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Schema types each SEO plugin takes control of when active.
 *
 * Sources: each plugin's own documented default output. None of the
 * major plugins emits Service automatically, and FAQPage only appears
 * when the editor uses that plugin's FAQ block — which is detected
 * separately in remotive_schema_suppressed_types() below, per page,
 * rather than assumed site-wide here.
 *
 * @return array<string, string[]> plugin key => managed schema types.
 */
function remotive_schema_plugin_managed_types() {
	$map = array();

	if ( defined( 'WPSEO_VERSION' ) ) { // Yoast SEO.
		$map['yoast'] = array( 'Organization', 'WebSite', 'WebPage', 'Article', 'BlogPosting', 'BreadcrumbList', 'Person' );
	}
	if ( class_exists( 'RankMath' ) ) {
		// Rank Math's entire schema output lives behind its "rich-snippet"
		// module (verified against plugin source v1.0.277: every snippet —
		// WebSite, WebPage, Article, BreadcrumbList, Person, publisher —
		// is loaded by that module, and its REST/admin paths gate on
		// Helper::is_module_active( 'rich-snippet' )). With the module
		// switched off the plugin emits nothing, so the theme must keep
		// everything; yielding would leave the site with no markup at all.
		$rm_schema_active = true; // Conservative default if the helper is unavailable.
		if ( is_callable( array( '\\RankMath\\Helper', 'is_module_active' ) ) ) {
			$rm_schema_active = (bool) \RankMath\Helper::is_module_active( 'rich-snippet' );
		}
		if ( $rm_schema_active ) {
			$map['rank-math'] = array( 'Organization', 'WebSite', 'WebPage', 'Article', 'BlogPosting', 'BreadcrumbList', 'Person' );
		}
	}
	if ( defined( 'AIOSEO_VERSION' ) ) { // All in One SEO.
		$map['aioseo'] = array( 'Organization', 'WebSite', 'WebPage', 'Article', 'BlogPosting', 'BreadcrumbList', 'Person' );
	}
	if ( defined( 'SEOPRESS_VERSION' ) ) {
		$map['seopress'] = array( 'Organization', 'WebSite', 'Article', 'BlogPosting' );
	}
	if ( defined( 'THE_SEO_FRAMEWORK_VERSION' ) ) {
		$map['the-seo-framework'] = array( 'Organization', 'WebSite', 'WebPage', 'BreadcrumbList' );
	}
	if ( defined( 'SLIM_SEO_VER' ) ) {
		$map['slim-seo'] = array( 'Organization', 'WebSite', 'WebPage', 'Article', 'BlogPosting', 'Person' );
	}

	/**
	 * Adjust which schema types active plugins are treated as managing.
	 *
	 * @param array $map plugin key => array of schema type names.
	 */
	return apply_filters( 'remotive_schema_plugin_managed_types', $map );
}

/**
 * The union of types to suppress on the current request.
 *
 * @return string[]
 */
function remotive_schema_suppressed_types() {
	$suppressed = array();
	foreach ( remotive_schema_plugin_managed_types() as $types ) {
		$suppressed = array_merge( $suppressed, $types );
	}

	// A plugin FAQ block in the current page's content means that plugin
	// is emitting FAQPage for this page — yield ours on this page only.
	if ( is_singular() ) {
		$post = get_post();
		if ( $post instanceof WP_Post ) {
			foreach ( array( 'rank-math/faq-block', 'yoast/faq-block', 'aioseo/faq' ) as $faq_block ) {
				if ( has_block( $faq_block, $post ) ) {
					$suppressed[] = 'FAQPage';
					break;
				}
			}

			// Rank Math's Schema Generator stores user-attached schemas as
			// rank_math_schema_* post meta whose serialized value carries
			// the @type (verified against class-db.php in v1.0.277). If
			// the user attached, say, a Service schema to a page through
			// Rank Math, the plugin has taken control of that type on
			// that page — the theme yields it there, and only there.
			if ( class_exists( 'RankMath' ) ) {
				foreach ( (array) get_post_meta( $post->ID ) as $meta_key => $values ) {
					if ( 0 !== strpos( (string) $meta_key, 'rank_math_schema' ) ) {
						continue;
					}
					foreach ( (array) $values as $value ) {
						// get_post_meta() has already unserialised once. A second
						// unserialize of a custom field a contributor can edit is an
						// object-injection risk, so it is not done; a still-serialised
						// string is read without allowing any class to be built.
						if ( is_string( $value ) && is_serialized( $value ) ) {
							$value = @unserialize( $value, array( 'allowed_classes' => false ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
						}
						if ( is_array( $value ) && ! empty( $value['@type'] ) ) {
							foreach ( (array) $value['@type'] as $type ) {
								$suppressed[] = (string) $type;
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Final say over which schema types the theme withholds. Add a type
	 * to silence the theme's node for it; remove one to force the
	 * theme's node out even alongside a plugin.
	 *
	 * @param string[] $suppressed Schema type names.
	 */
	return array_unique( apply_filters( 'remotive_schema_suppressed_types', $suppressed ) );
}

/**
 * Social profile URLs that are actually set (the shipped default is '#').
 *
 * @return string[]
 */
function remotive_schema_same_as() {
	$urls = array();
	foreach ( array( 'social_instagram', 'social_linkedin', 'social_tiktok', 'social_facebook', 'social_x', 'social_youtube', 'social_threads' ) as $key ) {
		$url = trim( (string) remotive_get_theme_option( $key ) );
		if ( '' !== $url && '#' !== $url && false !== filter_var( $url, FILTER_VALIDATE_URL ) ) {
			$urls[] = esc_url_raw( $url );
		}
	}
	return $urls;
}

/**
 * Organization node built from theme options + ssot.md facts.
 *
 * @return array
 */
function remotive_schema_organization() {
	$address = array(
		'@type'          => 'PostalAddress',
		'streetAddress'  => trim( remotive_get_theme_option( 'address_line_1' ) . ', ' . remotive_get_theme_option( 'address_line_2' ), ', ' ),
		'addressCountry' => 'SG',
	);
	// The shipped third line is "Singapore 138589"; split it when it
	// still matches that shape, otherwise pass it through whole.
	$line3 = trim( (string) remotive_get_theme_option( 'address_line_3' ) );
	if ( preg_match( '/^(.*?)\s+(\d{4,6})$/', $line3, $m ) ) {
		$address['addressLocality'] = $m[1];
		$address['postalCode']      = $m[2];
	} elseif ( '' !== $line3 ) {
		$address['addressLocality'] = $line3;
	}

	// Dual-typed: ProfessionalService is the LocalBusiness subtype for
	// agencies with a real registered office (which this one has, per
	// ACRA / ssot.md) — it carries the Local-business feature without
	// fabricating opening hours the business doesn't publish.
	$org = array(
		'@type'       => array( 'Organization', 'ProfessionalService' ),
		'@id'         => home_url( '/#organization' ),
		'name'        => get_bloginfo( 'name' ),
		'legalName'   => 'Remotive Media Asia Pte. Ltd.',
		'description' => 'Full-funnel digital marketing for brands across Asia: performance media, paid search, SEO, social, creative, and analytics. Registered in Singapore, working across APAC.',
		'url'         => home_url( '/' ),
		'email'       => sanitize_email( remotive_get_theme_option( 'contact_email' ) ),
		'foundingDate' => '2024-01-31',
		'identifier'  => array(
			'@type'    => 'PropertyValue',
			'name'     => 'UEN',
			'value'    => '202404376G',
		),
		'contactPoint' => array(
			'@type'       => 'ContactPoint',
			'contactType' => 'sales',
			'email'       => sanitize_email( remotive_get_theme_option( 'contact_email' ) ),
			'url'         => home_url( '/contact/' ),
			'areaServed'  => array( 'SG', 'MY' ),
		),
		'address'     => $address,
		'areaServed'  => array(
			array( '@type' => 'Country', 'name' => 'Singapore' ),
			array( '@type' => 'Country', 'name' => 'Malaysia' ),
		),
	);

	$logo_id = get_theme_mod( 'custom_logo' );
	$logo    = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';
	if ( $logo ) {
		$org['logo'] = array(
			'@type'           => 'ImageObject',
			'@id'             => home_url( '/#logo' ),
			'url'             => $logo,
			'creator'         => array( '@id' => home_url( '/#organization' ) ),
			'creditText'      => get_bloginfo( 'name' ),
			'copyrightNotice' => get_bloginfo( 'name' ),
		);
		/**
		 * License / acquire-license URLs for the logo image metadata,
		 * when the site publishes them. Return array with 'license'
		 * and/or 'acquireLicensePage' keys.
		 */
		$license = apply_filters( 'remotive_schema_logo_license', array() );
		foreach ( array( 'license', 'acquireLicensePage' ) as $key ) {
			if ( ! empty( $license[ $key ] ) ) {
				$org['logo'][ $key ] = esc_url_raw( $license[ $key ] );
			}
		}
	}

	$same_as = remotive_schema_same_as();
	if ( $same_as ) {
		$org['sameAs'] = $same_as;
	}

	// The team, from the same roster the grids render
	// (remotive_team_members() in inc/theme-options.php), so an edit in
	// Theme Options updates the display and the structured data together.
	$employees = array();

	foreach ( remotive_team_members() as $member ) {
		$person = array(
			'@type' => 'Person',
			'name'  => $member['name'],
			// Anchored so other nodes (and other sites) can reference the
			// person stably; slug preferred, name-derived fallback.
			'@id'   => home_url( '/#person-' . ( $member['slug'] ? $member['slug'] : sanitize_title( $member['name'] ) ) ),
			// Explicit even though employee implies it: consumers that
			// lift the Person out of the array keep the affiliation.
			'worksFor' => array( '@id' => home_url( '/#organization' ) ),
		);

		if ( '' !== $member['role'] ) {
			$person['jobTitle'] = $member['role'];
		}

		if ( '' !== trim( $member['bio'] ) ) {
			$person['description'] = $member['bio'];
		}

		// The portrait ships with the theme; only claim it when the file
		// is actually there, so a member without photography emits no
		// broken image URL.
		if ( $member['slug'] && is_readable( get_stylesheet_directory() . '/assets/team/' . $member['slug'] . '.avif' ) ) {
			$person['image'] = get_stylesheet_directory_uri() . '/assets/team/' . $member['slug'] . '.avif';
		}

		$employees[] = $person;
	}

	if ( $employees ) {
		$org['employee'] = $employees;
	}

	/**
	 * @param array $org The Organization node.
	 */
	return apply_filters( 'remotive_schema_organization', $org );
}

/**
 * Services the site offers, per surface. The three core blocks mirror
 * the Services page; the landing pages add their market-specific
 * service (the consolidation strategy documented in ssot.md — one page
 * per market, not one per keyword).
 *
 * @return array[]
 */
function remotive_schema_services() {
	$org_ref  = array( '@id' => home_url( '/#organization' ) );
	$services = array();

	$core = array(
		'demand-creation'   => array( 'Demand Creation', 'Performance media, B2B LinkedIn, social and influencer, and creative and design — building new demand.' ),
		'demand-capture'    => array( 'Demand Capture', 'Paid search, SEO and GEO, and category strategy — capturing existing intent.' ),
		'conversion-data'   => array( 'Conversion & Data', 'Analytics infrastructure, CRM integration, and lifecycle and engagement — converting and proving value.' ),
	);

	if ( is_front_page() || is_page_template( 'page-services' ) ) {
		foreach ( $core as $slug => $svc ) {
			$services[] = array(
				'@type'       => 'Service',
				'@id'         => home_url( '/services/#' . $slug ),
				'name'        => $svc[0],
				'description' => $svc[1],
				'provider'    => $org_ref,
				'areaServed'  => array( 'Singapore', 'Malaysia' ),
			);
		}
	}

	// Any page on the reusable service-detail template (v1.29.0) is a
	// Service: named by its own title, described by its excerpt, owned
	// by the organization. New service pages get schema with no code
	// changes.
	if ( is_page_template( 'page-service' ) && is_singular() ) {
		$post = get_post();
		if ( $post instanceof WP_Post ) {
			$node = array(
				'@type'      => 'Service',
				'@id'        => get_permalink( $post ) . '#service',
				'name'       => get_the_title( $post ),
				'url'        => get_permalink( $post ),
				'provider'   => $org_ref,
				'areaServed' => array( 'Singapore', 'Malaysia' ),
			);
			$excerpt = get_the_excerpt( $post );
			if ( $excerpt ) {
				$node['description'] = $excerpt;
			}
			$services[] = $node;
		}
	}

	/**
	 * @param array $services Service nodes for the current request.
	 */
	return apply_filters( 'remotive_schema_services', $services );
}

/**
 * FAQ entries parsed from a landing-page template's own details blocks,
 * so the schema always states exactly what the page renders. Answers
 * are flattened to plain text.
 *
 * @param string $template Template file name inside templates/.
 * @return array[] question/answer pairs.
 */
function remotive_schema_faq_from_template( $template ) {
	static $cache = array();
	if ( isset( $cache[ $template ] ) ) {
		return $cache[ $template ];
	}

	$path  = get_stylesheet_directory() . '/templates/' . $template;
	$pairs = array();
	if ( is_readable( $path ) ) {
		$html = (string) file_get_contents( $path );
		if ( preg_match_all( '#<summary>(.*?)</summary>(.*?)</details>#s', $html, $m, PREG_SET_ORDER ) ) {
			foreach ( $m as $hit ) {
				$q = trim( wp_strip_all_tags( $hit[1] ) );
				$a = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $hit[2] ) ) );
				if ( '' !== $q && '' !== $a ) {
					$pairs[] = array( $q, $a );
				}
			}
		}
	}

	$cache[ $template ] = $pairs;
	return $pairs;
}

/**
 * FAQPage node for the current request, when it has template FAQs.
 *
 * @return array|null
 */
function remotive_schema_faq_page() {
	$template = '';

	if ( is_page_template( 'page-faq' ) ) {
		$template = 'page-faq.html';
	}
	if ( '' === $template ) {
		return null;
	}

	$entities = array();
	foreach ( remotive_schema_faq_from_template( $template ) as $pair ) {
		$entities[] = array(
			'@type'          => 'Question',
			'name'           => $pair[0],
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $pair[1],
			),
		);
	}
	if ( ! $entities ) {
		return null;
	}

	return array(
		'@type'      => 'FAQPage',
		'@id'        => remotive_schema_current_url() . '#faq',
		'mainEntity' => $entities,
	);
}

/**
 * Canonical-ish URL for the current request.
 *
 * @return string
 */
function remotive_schema_current_url() {
	if ( is_front_page() ) {
		return home_url( '/' );
	}
	if ( is_singular() ) {
		$permalink = get_permalink();
		if ( $permalink ) {
			return $permalink;
		}
	}
	if ( is_home() ) {
		$page = get_option( 'page_for_posts' );
		if ( $page ) {
			return get_permalink( $page );
		}
	}
	return home_url( add_query_arg( array(), $GLOBALS['wp']->request ?? '' ) );
}

/**
 * WebPage node (typed per template) for the current request.
 *
 * @return array
 */
function remotive_schema_webpage() {
	$type = 'WebPage';
	if ( is_page_template( 'page-about' ) ) {
		// The page about the organization itself. It was typed
		// ProfilePage while it also carried the team roster; now that
		// the roster has its own page, AboutPage is the accurate type,
		// and Google reserves ProfilePage for a single person's or
		// organization's profile rather than a company story.
		$type = 'AboutPage';
	} elseif ( is_page_template( 'page-team' ) ) {
		// A listing of the people, so a collection rather than a
		// profile of any one of them.
		$type = 'CollectionPage';
	} elseif ( is_page_template( 'page-contact' ) ) {
		$type = 'ContactPage';
	} elseif ( function_exists( 'is_author' ) && is_author() ) {
		$type = 'ProfilePage';
	} elseif ( is_home() || is_archive() ) {
		$type = 'CollectionPage';
	} elseif ( is_search() ) {
		$type = 'SearchResultsPage';
	}

	$page = array(
		'@type'    => $type,
		'@id'      => remotive_schema_current_url() . '#webpage',
		'url'      => remotive_schema_current_url(),
		'name'     => wp_get_document_title(),
		'isPartOf' => array( '@id' => home_url( '/#website' ) ),
		'about'    => array( '@id' => home_url( '/#organization' ) ),
		'inLanguage' => get_bloginfo( 'language' ),
	);

	if ( 'ProfilePage' === $type ) {
		if ( function_exists( 'is_author' ) && is_author() ) {
			$author = get_queried_object();
			if ( $author instanceof WP_User ) {
				$page['mainEntity'] = array(
					'@type' => 'Person',
					'name'  => $author->display_name,
					'url'   => get_author_posts_url( $author->ID ),
				);
			}
		} else {
			$page['mainEntity'] = array( '@id' => home_url( '/#organization' ) );
		}
	}

	return apply_filters( 'remotive_schema_webpage', $page );
}

/**
 * BlogPosting node for single posts.
 *
 * @return array|null
 */
function remotive_schema_blog_posting() {
	if ( ! is_singular( 'post' ) ) {
		return null;
	}
	$post = get_post();
	if ( ! $post instanceof WP_Post ) {
		return null;
	}

	$node = array(
		'@type'            => 'BlogPosting',
		'@id'              => get_permalink( $post ) . '#article',
		'headline'         => get_the_title( $post ),
		'url'              => get_permalink( $post ),
		'datePublished'    => get_the_date( 'c', $post ),
		'dateModified'     => get_the_modified_date( 'c', $post ),
		'inLanguage'       => get_bloginfo( 'language' ),
		'wordCount'        => str_word_count( wp_strip_all_tags( (string) $post->post_content ) ),
		'author'           => array(
			'@type' => 'Person',
			'name'  => get_the_author_meta( 'display_name', (int) $post->post_author ),
			'url'   => get_author_posts_url( (int) $post->post_author ),
		),
		'publisher'        => array( '@id' => home_url( '/#organization' ) ),
		'mainEntityOfPage' => array( '@id' => get_permalink( $post ) . '#webpage' ),
		// Speakable: the title and body selectors from single.html, so
		// assistants read the actual article surface.
		'speakable'        => array(
			'@type'       => 'SpeakableSpecification',
			'cssSelector' => array( '.rm-post__title', '.rm-post__content' ),
		),
	);

	$categories = get_the_category( $post->ID );
	if ( $categories && ! is_wp_error( $categories ) ) {
		$node['articleSection'] = wp_list_pluck( $categories, 'name' );
	}

	$image = get_the_post_thumbnail_url( $post, 'full' );
	if ( $image ) {
		$node['image'] = $image;
	}

	return apply_filters( 'remotive_schema_blog_posting', $node );
}


/**
 * BreadcrumbList for the current request: Home, an optional Insights
 * level for posts, then the current page. The site's hierarchy is one
 * level deep, so the trail is short by construction.
 *
 * @return array|null
 */
function remotive_schema_breadcrumbs() {
	if ( is_front_page() ) {
		return null;
	}

	$items = array(
		array( 'name' => 'Home', 'item' => home_url( '/' ) ),
	);

	if ( is_singular( 'post' ) ) {
		$posts_page = (int) get_option( 'page_for_posts' );
		if ( $posts_page ) {
			$items[] = array( 'name' => get_the_title( $posts_page ), 'item' => get_permalink( $posts_page ) );
		}
		$items[] = array( 'name' => get_the_title(), 'item' => get_permalink() );
	} else {
		$items[] = array( 'name' => wp_get_document_title(), 'item' => remotive_schema_current_url() );
	}

	$list = array();
	foreach ( $items as $i => $crumb ) {
		$list[] = array(
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'name'     => $crumb['name'],
			'item'     => $crumb['item'],
		);
	}

	return array(
		'@type'           => 'BreadcrumbList',
		'@id'             => remotive_schema_current_url() . '#breadcrumb',
		'itemListElement' => $list,
	);
}

/**
 * VideoObject for a self-hosted core/video block in the current post —
 * content-gated: emitted only when the post actually contains one and
 * the required properties (name, thumbnail, upload date) can be stated
 * truthfully from the attachment and post data. External embeds are
 * skipped because their thumbnail and upload date can't be asserted
 * from here.
 *
 * @return array|null
 */
function remotive_schema_video() {
	if ( ! is_singular( 'post' ) ) {
		return null;
	}
	$post = get_post();
	if ( ! $post instanceof WP_Post || ! has_block( 'core/video', $post ) ) {
		return null;
	}

	$thumbnail = get_the_post_thumbnail_url( $post, 'full' );
	if ( ! $thumbnail ) {
		return null; // Required property — without it the node would fail validation.
	}

	foreach ( parse_blocks( (string) $post->post_content ) as $block ) {
		if ( 'core/video' !== ( $block['blockName'] ?? '' ) || empty( $block['attrs']['id'] ) ) {
			continue;
		}
		$attachment = get_post( (int) $block['attrs']['id'] );
		$src        = wp_get_attachment_url( (int) $block['attrs']['id'] );
		if ( ! $attachment || ! $src ) {
			continue;
		}
		return array(
			'@type'        => 'VideoObject',
			'@id'          => get_permalink( $post ) . '#video',
			'name'         => get_the_title( $attachment ) ?: get_the_title( $post ),
			'description'  => get_the_excerpt( $post ),
			'contentUrl'   => $src,
			'thumbnailUrl' => $thumbnail,
			'uploadDate'   => get_the_date( 'c', $attachment ),
		);
	}
	return null;
}

/**
 * Assemble the graph for the current request, minus suppressed types.
 *
 * @return array
 */
function remotive_schema_graph() {
	$suppressed = remotive_schema_suppressed_types();
	$graph      = array();

	$candidates = array(
		remotive_schema_organization(),
		array(
			'@type'     => 'WebSite',
			'@id'       => home_url( '/#website' ),
			'url'       => home_url( '/' ),
			'name'      => get_bloginfo( 'name' ),
			'publisher' => array( '@id' => home_url( '/#organization' ) ),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => array(
					'@type'       => 'EntryPoint',
					'urlTemplate' => home_url( '/?s={search_term_string}' ),
				),
				'query-input' => 'required name=search_term_string',
			),
		),
		remotive_schema_webpage(),
		remotive_schema_breadcrumbs(),
		remotive_schema_blog_posting(),
		remotive_schema_video(),
		remotive_schema_faq_page(),
	);
	$candidates = array_merge( $candidates, remotive_schema_services() );

	// Typed pages are WebPage for suppression purposes: a plugin that
	// controls WebPage controls the page node, whatever subtype the
	// theme chose (this closes a v1.26.0 gap where AboutPage and
	// ContactPage slipped past the exact-string match). Article covers
	// BlogPosting the same way; a dual-typed node yields if ANY of its
	// types is managed.
	$families = array(
		'AboutPage'         => 'WebPage',
		'ContactPage'       => 'WebPage',
		'CollectionPage'    => 'WebPage',
		'SearchResultsPage' => 'WebPage',
		'ProfilePage'       => 'WebPage',
		'BlogPosting'       => 'Article',
		'ProfessionalService' => 'Organization',
	);
	foreach ( $candidates as $node ) {
		if ( empty( $node ) || ! isset( $node['@type'] ) ) {
			continue;
		}
		$types = (array) $node['@type'];
		foreach ( $types as $type ) {
			if ( isset( $families[ $type ] ) ) {
				$types[] = $families[ $type ];
			}
		}
		if ( array_intersect( array_unique( $types ), $suppressed ) ) {
			continue;
		}
		$graph[] = $node;
	}

	/**
	 * The assembled graph, after per-type plugin suppression.
	 *
	 * @param array $graph JSON-LD nodes.
	 */
	return apply_filters( 'remotive_schema_graph', $graph );
}

/**
 * Print the JSON-LD block in <head>.
 */
function remotive_output_schema_markup() {
	if ( is_admin() || is_404() || is_embed() || is_feed() ) {
		return;
	}

	$graph = remotive_schema_graph();
	if ( empty( $graph ) ) {
		return;
	}

	$data = array(
		'@context' => 'https://schema.org',
		'@graph'   => array_values( $graph ),
	);

	echo "\n" . '<script type="application/ld+json" id="remotive-schema">'
		. wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
		. '</script>' . "\n";
}
add_action( 'wp_head', 'remotive_output_schema_markup', 6 );
