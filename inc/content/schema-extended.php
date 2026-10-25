<?php
/**
 * The rest of schema.org: every type and property the site can state truthfully, beyond the set Google
 * Search lists as a rich result feature.
 *
 * inc/content/schema-markup.php describes the organisation, the site, the page and the article, and
 * inc/content/schema-elements.php the page furniture and the two layouts' sections. This file fills
 * those nodes out and adds the types schema.org has for what the site shows:
 *
 * - Organization: alternate name, brand, slogan, the languages it works in, what it knows about, its
 *   audience, where it is (Place), the free audit it makes as an Offer, and its catalogue of services
 *   (OfferCatalog).
 * - WebSite: languages, description, copyright, free to read.
 * - WebPage: description, dates, the lead image (ImageObject), the breadcrumb, a ReadAction, the main
 *   entity of the page (the article, the service, the questions, the people, the organisation), and the
 *   page's copies in the other languages (workTranslation; the language layer adds translationOfWork
 *   on the copies).
 * - BlogPosting: the image as an ImageObject with its size, keywords, reading time, copyright, the
 *   author linked to the team roster when the names match.
 * - The blog index: Blog with its posts, and the RSS feed as a DataFeed. A category archive: an
 *   ItemList of its posts.
 * - A case study: an Article (genre "Case study") as the page's main entity.
 * - The services page: an OfferCatalog of the service pages. A service page: the free audit Offer,
 *   the audience, the contact channel (ServiceChannel) and the terms.
 * - The team page: an ItemList of the Person nodes.
 * - The header navigation: a SiteNavigationElement per link.
 * - The ad landing pages and the one-page layout: the free audit Offer.
 *
 * Nothing here states what the site does not show: no ratings, reviews, prices for services, opening
 * hours or events. Run `php tests/test-schema-extended.php` for the tests.
 *
 * @package Remotive
 */

defined( 'ABSPATH' ) || exit;

/**
 * The @id of the organisation node.
 *
 * @return array
 */
function remotive_schema_org_ref() {
	return array( '@id' => home_url( '/#organization' ) );
}

/**
 * The languages the site is published in, as BCP 47 codes: English and every language switched on.
 *
 * @return string[]
 */
function remotive_schema_languages() {
	$codes = array( 'en' );

	if ( function_exists( 'remotive_i18n_languages' ) && function_exists( 'remotive_i18n_language_enabled' ) ) {
		foreach ( remotive_i18n_languages() as $code => $lang ) {
			if ( 'en' !== $code && remotive_i18n_language_enabled( $code ) ) {
				$codes[] = $lang['hreflang'];
			}
		}
	}

	return $codes;
}

/**
 * The free audit the site offers on every landing page, as an Offer. One node, shared by every page
 * that mentions it, so the organisation's makesOffer and a Service's offers point at the same thing.
 *
 * @return array
 */
function remotive_schema_free_audit_offer() {
	$offer = array(
		'@type'          => 'Offer',
		'@id'            => home_url( '/#free-audit' ),
		'name'           => 'Free audit',
		'description'    => 'A free audit of your search, paid media or analytics set-up: what holds growth back, ranked by what to fix first.',
		'price'          => '0',
		'priceCurrency'  => 'SGD',
		'availability'   => 'https://schema.org/InStock',
		'eligibleRegion' => array(
			array( '@type' => 'Country', 'name' => 'Singapore' ),
			array( '@type' => 'Country', 'name' => 'Malaysia' ),
		),
		'offeredBy'      => remotive_schema_org_ref(),
		'url'            => home_url( '/seo-audit/' ),
	);

	return apply_filters( 'remotive_schema_free_audit_offer', $offer );
}

/**
 * The organisation's audience: the brands the home page addresses.
 *
 * @return array
 */
function remotive_schema_audience() {
	return array(
		'@type'            => 'BusinessAudience',
		'audienceType'     => 'B2B and B2C brands',
		'geographicArea'   => array( '@type' => 'Place', 'name' => 'Asia' ),
	);
}

/**
 * Fill the Organization node out with what schema.org has for a company.
 *
 * @param array $org The Organization node.
 * @return array
 */
function remotive_schema_extend_organization( $org ) {
	$name   = get_bloginfo( 'name' );
	$slogan = trim( (string) get_bloginfo( 'description' ) );

	$org['alternateName'] = array_values( array_unique( array_filter( array( 'Re:Motive Media', 'Remotive Media Asia', 'Remotive' ), function ( $alt ) use ( $name ) {
		return $alt !== $name;
	} ) ) );
	$org['brand']         = array(
		'@type' => 'Brand',
		'@id'   => home_url( '/#brand' ),
		'name'  => $name,
	);

	if ( isset( $org['logo']['@id'] ) ) {
		$org['brand']['logo'] = array( '@id' => $org['logo']['@id'] );
	}

	if ( '' !== $slogan ) {
		$org['slogan'] = $slogan;
	}

	$org['knowsLanguage'] = remotive_schema_languages();
	$org['knowsAbout']    = array( 'Search engine optimisation', 'Generative engine optimisation', 'Paid search', 'Paid social', 'Programmatic advertising', 'Influencer marketing', 'Marketing analytics', 'Conversion rate optimisation' );
	$org['audience']      = remotive_schema_audience();
	$org['makesOffer']    = array( '@id' => home_url( '/#free-audit' ) );

	if ( isset( $org['address'] ) ) {
		$org['location'] = array(
			'@type'   => 'Place',
			'@id'     => home_url( '/#office' ),
			'name'    => $name . ' office',
			'address' => $org['address'],
		);
	}

	$about = function_exists( 'get_page_by_path' ) ? get_page_by_path( 'about', OBJECT, 'page' ) : null;

	if ( $about && 'publish' === $about->post_status ) {
		$org['mainEntityOfPage'] = get_permalink( $about->ID );
	}

	return $org;
}
add_filter( 'remotive_schema_organization', 'remotive_schema_extend_organization' );

/**
 * The description a page carries: the excerpt when there is one, else the SEO description the
 * language layer or Rank Math would print, else nothing.
 *
 * @param WP_Post|null $post The post.
 * @return string
 */
function remotive_schema_page_description( $post ) {
	if ( $post instanceof WP_Post && '' !== trim( (string) $post->post_excerpt ) ) {
		return trim( wp_strip_all_tags( (string) $post->post_excerpt ) );
	}

	if ( $post instanceof WP_Post && function_exists( 'get_post_meta' ) ) {
		$meta = (string) get_post_meta( $post->ID, 'rank_math_description', true );

		if ( '' !== trim( $meta ) ) {
			return trim( $meta );
		}
	}

	return '';
}

/**
 * An ImageObject for an attachment, with its size when WordPress knows it.
 *
 * @param int    $attachment_id Attachment ID.
 * @param string $id            The node's @id.
 * @return array|null
 */
function remotive_schema_image_object( $attachment_id, $id ) {
	$src = wp_get_attachment_image_src( (int) $attachment_id, 'full' );

	if ( ! $src || empty( $src[0] ) ) {
		return null;
	}

	$node = array(
		'@type'      => 'ImageObject',
		'@id'        => $id,
		'url'        => $src[0],
		'contentUrl' => $src[0],
	);

	if ( ! empty( $src[1] ) && ! empty( $src[2] ) ) {
		$node['width']  = (int) $src[1];
		$node['height'] = (int) $src[2];
	}

	$alt = trim( (string) get_post_meta( (int) $attachment_id, '_wp_attachment_image_alt', true ) );

	if ( '' !== $alt ) {
		$node['caption'] = $alt;
	}

	return $node;
}

/**
 * The page's copies in the other languages, as WebPage stubs. The language layer turns these into
 * translationOfWork on the copies themselves.
 *
 * @return array[]
 */
function remotive_schema_translations() {
	if ( ! function_exists( 'remotive_i18n_key' ) || ! function_exists( 'remotive_i18n_available' ) || ! function_exists( 'remotive_i18n_url' ) ) {
		return array();
	}

	$key = remotive_i18n_key();

	if ( null === $key ) {
		return array();
	}

	$out = array();

	foreach ( remotive_i18n_languages() as $code => $lang ) {
		if ( 'en' === $code || ! remotive_i18n_available( $code, $key ) ) {
			continue;
		}

		$url   = remotive_i18n_url( $code, $key );
		$out[] = array(
			'@type'      => 'WebPage',
			'@id'        => $url . '#webpage',
			'url'        => $url,
			'inLanguage' => $lang['hreflang'],
		);
	}

	return $out;
}

/**
 * Whether the current page is one of the case studies: a page under the case-studies page.
 *
 * @return bool
 */
function remotive_schema_is_case_study() {
	if ( ! is_page() ) {
		return false;
	}

	$post = get_post();

	if ( ! $post instanceof WP_Post || ! $post->post_parent ) {
		return false;
	}

	return 'case-studies' === get_post_field( 'post_name', $post->post_parent );
}

/**
 * Fill the WebPage node out: description, dates, lead image, breadcrumb, ReadAction, main entity,
 * copies in other languages.
 *
 * @param array $page The WebPage node.
 * @return array
 */
function remotive_schema_extend_webpage( $page ) {
	$url  = remotive_schema_current_url();
	$post = is_singular() ? get_post() : null;

	$page['isAccessibleForFree'] = true;
	$page['potentialAction']     = array( '@type' => 'ReadAction', 'target' => $url );

	if ( ! is_front_page() ) {
		$page['breadcrumb'] = array( '@id' => $url . '#breadcrumb' );
	}

	if ( $post instanceof WP_Post ) {
		$description = remotive_schema_page_description( $post );

		if ( '' !== $description ) {
			$page['description'] = $description;
		}

		$page['datePublished'] = get_the_date( 'c', $post );
		$page['dateModified']  = get_the_modified_date( 'c', $post );

		$thumb = get_post_thumbnail_id( $post );

		if ( $thumb ) {
			$image = remotive_schema_image_object( $thumb, $url . '#primaryimage' );

			if ( $image ) {
				$page['primaryImageOfPage'] = $image;
			}
		}
	}

	$translations = remotive_schema_translations();

	if ( $translations ) {
		$page['workTranslation'] = $translations;
	}

	// The main entity: what the page is about, where another node describes it.
	if ( is_singular( 'post' ) ) {
		$page['mainEntity'] = array( '@id' => $url . '#article' );
	} elseif ( remotive_schema_is_case_study() ) {
		$page['@type']      = 'ItemPage';
		$page['mainEntity'] = array( '@id' => $url . '#case-study' );
	} elseif ( is_page_template( 'page-service' ) ) {
		$page['@type']      = 'ItemPage';
		$page['mainEntity'] = array( '@id' => $url . '#service' );
	} elseif ( is_page_template( 'page-services' ) ) {
		$page['mainEntity'] = array( '@id' => home_url( '/services/#catalog' ) );
	} elseif ( is_page_template( 'page-faq' ) ) {
		$page['mainEntity'] = array( '@id' => $url . '#faq' );
	} elseif ( is_page_template( 'page-team' ) ) {
		$page['mainEntity'] = array( '@id' => $url . '#people' );
	} elseif ( is_page_template( 'page-contact' ) || is_page_template( 'page-about' ) ) {
		$page['mainEntity'] = remotive_schema_org_ref();
	} elseif ( is_home() ) {
		$page['mainEntity'] = array( '@id' => $url . '#blog' );
	} elseif ( is_category() ) {
		$page['mainEntity'] = array( '@id' => $url . '#posts' );
	}

	return $page;
}
add_filter( 'remotive_schema_webpage', 'remotive_schema_extend_webpage' );

/**
 * A landing page's WebPage node: the copy's own address, headline, lead and language. The landing
 * pages sit outside the language layer's head rewrite, so the node is built in the language here.
 *
 * @param array $page The WebPage node.
 * @return array
 */
function remotive_schema_landing_webpage( $page ) {
	if ( ! function_exists( 'remotive_is_landing_page' ) || ! remotive_is_landing_page() || ! function_exists( 'remotive_lp_title_parts' ) ) {
		return $page;
	}

	$parts = remotive_lp_title_parts();

	if ( null === $parts ) {
		return $page;
	}

	$url  = remotive_schema_landing_url();
	$lang = remotive_lp_languages()[ remotive_lp_requested_lang() ][1];

	$page['@id']        = $url . '#webpage';
	$page['url']        = $url;
	$page['name']       = $parts[0];
	$page['inLanguage'] = $lang;
	$page['breadcrumb'] = array( '@id' => $url . '#breadcrumb' );

	if ( '' !== $parts[2] ) {
		$page['description'] = $parts[2];
	}

	if ( isset( $page['potentialAction']['target'] ) ) {
		$page['potentialAction']['target'] = $url;
	}

	unset( $page['workTranslation'] );

	return $page;
}
add_filter( 'remotive_schema_webpage', 'remotive_schema_landing_webpage', 20 );

/**
 * The address of the landing page copy being rendered.
 *
 * @return string
 */
function remotive_schema_landing_url() {
	$slug = (string) get_post_field( 'post_name', get_queried_object_id() );

	return remotive_lp_url( $slug, remotive_lp_requested_lang() );
}

/**
 * The landing page's breadcrumb and element nodes at the copy's address, named in its language.
 *
 * @param array $graph JSON-LD nodes.
 * @return array
 */
function remotive_schema_landing_graph( $graph ) {
	if ( ! function_exists( 'remotive_is_landing_page' ) || ! remotive_is_landing_page() || ! function_exists( 'remotive_lp_title_parts' ) ) {
		return $graph;
	}

	$parts = remotive_lp_title_parts();

	if ( null === $parts ) {
		return $graph;
	}

	$url     = remotive_schema_landing_url();
	$english = remotive_schema_current_url();
	$lang    = remotive_lp_languages()[ remotive_lp_requested_lang() ][1];
	$home    = array( 'Home', 'Utama', '首页', '首頁' );

	foreach ( $graph as $i => $node ) {
		$types = isset( $node['@type'] ) ? (array) $node['@type'] : array();

		if ( isset( $node['@id'] ) && is_string( $node['@id'] ) && 0 === strpos( $node['@id'], $english . '#' ) ) {
			$graph[ $i ]['@id'] = $url . substr( $node['@id'], strlen( $english ) );
		}

		if ( isset( $node['isPartOf']['@id'] ) && is_string( $node['isPartOf']['@id'] ) && 0 === strpos( $node['isPartOf']['@id'], $english . '#' ) ) {
			$graph[ $i ]['isPartOf']['@id'] = $url . substr( $node['isPartOf']['@id'], strlen( $english ) );
		}

		if ( in_array( 'Service', $types, true ) && isset( $node['url'] ) ) {
			$graph[ $i ]['url'] = $url;
		}

		if ( in_array( 'BreadcrumbList', $types, true ) && isset( $node['itemListElement'] ) ) {
			$i_lang                       = remotive_lp_lang_index();
			$graph[ $i ]['itemListElement'] = array(
				array( '@type' => 'ListItem', 'position' => 1, 'name' => $home[ $i_lang ], 'item' => remotive_lp_url( '', remotive_lp_requested_lang() ) ),
				array( '@type' => 'ListItem', 'position' => 2, 'name' => $parts[0], 'item' => $url ),
			);
		}
	}

	return $graph;
}
add_filter( 'remotive_schema_graph', 'remotive_schema_landing_graph', 30 );

/**
 * Reading time as an ISO 8601 duration, at 200 words a minute, never under a minute.
 *
 * @param int $words Word count.
 * @return string
 */
function remotive_schema_reading_time( $words ) {
	return 'PT' . max( 1, (int) ceil( (int) $words / 200 ) ) . 'M';
}

/**
 * The team member whose name matches an author's display name, as a Person reference.
 *
 * @param string $display_name The author's display name.
 * @return array|null
 */
function remotive_schema_team_person_ref( $display_name ) {
	if ( ! function_exists( 'remotive_team_members' ) ) {
		return null;
	}

	foreach ( remotive_team_members() as $member ) {
		if ( 0 === strcasecmp( trim( $member['name'] ), trim( (string) $display_name ) ) ) {
			return array( '@id' => home_url( '/#person-' . ( $member['slug'] ? $member['slug'] : sanitize_title( $member['name'] ) ) ) );
		}
	}

	return null;
}

/**
 * Fill the BlogPosting node out.
 *
 * @param array $node The BlogPosting node.
 * @return array
 */
function remotive_schema_extend_blog_posting( $node ) {
	$post = get_post();

	if ( ! $post instanceof WP_Post ) {
		return $node;
	}

	$url = get_permalink( $post );

	$node['isAccessibleForFree'] = true;
	$node['copyrightHolder']     = remotive_schema_org_ref();
	$node['copyrightYear']       = (int) get_the_date( 'Y', $post );
	$node['genre']               = 'Marketing';

	if ( isset( $node['wordCount'] ) ) {
		$node['timeRequired'] = remotive_schema_reading_time( $node['wordCount'] );
	}

	$description = remotive_schema_page_description( $post );

	if ( '' !== $description ) {
		$node['description'] = $description;
	}

	$tags = get_the_tags( $post->ID );

	if ( $tags && ! is_wp_error( $tags ) ) {
		$node['keywords'] = implode( ', ', wp_list_pluck( $tags, 'name' ) );
	}

	$thumb = get_post_thumbnail_id( $post );

	if ( $thumb ) {
		$image = remotive_schema_image_object( $thumb, $url . '#primaryimage' );

		if ( $image ) {
			$node['image']        = $image;
			$node['thumbnailUrl'] = $image['url'];
		}
	}

	if ( isset( $node['author']['name'] ) ) {
		$person = remotive_schema_team_person_ref( $node['author']['name'] );

		if ( $person ) {
			$node['author'] = $person;
		}
	}

	return $node;
}
add_filter( 'remotive_schema_blog_posting', 'remotive_schema_extend_blog_posting' );

/**
 * A short BlogPosting node for a post in a list, pointing at the post's own page.
 *
 * @param WP_Post $post The post.
 * @return array
 */
function remotive_schema_post_summary( $post ) {
	$url  = get_permalink( $post );
	$node = array(
		'@type'            => 'BlogPosting',
		'@id'              => $url . '#article',
		'headline'         => get_the_title( $post ),
		'url'              => $url,
		'datePublished'    => get_the_date( 'c', $post ),
		'dateModified'     => get_the_modified_date( 'c', $post ),
		'author'           => array( '@type' => 'Person', 'name' => get_the_author_meta( 'display_name', (int) $post->post_author ) ),
		'publisher'        => remotive_schema_org_ref(),
		'mainEntityOfPage' => $url,
	);
	$image = get_the_post_thumbnail_url( $post, 'full' );

	if ( $image ) {
		$node['image'] = $image;
	}

	return $node;
}

/**
 * The header navigation as SiteNavigationElement nodes, one per link, from the header part's own
 * link list (the fallback the block carries) or the classic menu assigned to the location.
 *
 * @param string $page_url Address of the page.
 * @return array[]
 */
function remotive_schema_navigation( $page_url ) {
	$links = array();
	$menu  = function_exists( 'remotive_header_menu_links' ) ? remotive_header_menu_links() : array();

	if ( ! $menu ) {
		$part = get_stylesheet_directory() . '/parts/header.html';

		if ( is_readable( $part ) && preg_match_all( '/wp:navigation-link \{"label":"([^"]+)","url":"([^"]+)"\}/', (string) file_get_contents( $part ), $m, PREG_SET_ORDER ) ) {
			foreach ( $m as $hit ) {
				$menu[] = array( 'label' => $hit[1], 'url' => $hit[2] );
			}
		}
	}

	$n = 0;

	foreach ( $menu as $item ) {
		++$n;
		$links[] = array(
			'@type'    => 'SiteNavigationElement',
			'@id'      => $page_url . '#nav-' . $n,
			'name'     => $item['label'],
			'url'      => 0 === strpos( $item['url'], '/' ) ? home_url( $item['url'] ) : $item['url'],
			'isPartOf' => array( '@id' => $page_url . '#header' ),
		);
	}

	return $links;
}

/**
 * The nodes the current page adds beyond the base graph.
 *
 * @param string $url Address of the page.
 * @return array[]
 */
function remotive_schema_extended_nodes( $url ) {
	$nodes = array( remotive_schema_free_audit_offer() );
	$org   = remotive_schema_org_ref();

	// The blog index: the Blog, its posts on this page, and the feed.
	if ( is_home() ) {
		$posts = array();

		foreach ( ( $GLOBALS['wp_query']->posts ?? array() ) as $post ) {
			if ( $post instanceof WP_Post && 'post' === $post->post_type ) {
				$posts[] = remotive_schema_post_summary( $post );
			}
		}

		$nodes[] = array(
			'@type'      => 'Blog',
			'@id'        => $url . '#blog',
			'name'       => remotive_schema_page_name(),
			'url'        => $url,
			'publisher'  => $org,
			'inLanguage' => get_bloginfo( 'language' ),
			'blogPost'   => $posts,
		);
		$nodes[] = array(
			'@type'          => 'DataFeed',
			'@id'            => $url . '#feed',
			'name'           => get_bloginfo( 'name' ) . ' RSS feed',
			'url'            => get_feed_link(),
			'encodingFormat' => 'application/rss+xml',
			'provider'       => $org,
		);
	}

	// A category archive: the posts as an ItemList.
	if ( is_category() ) {
		$items = array();
		$n     = 0;

		foreach ( ( $GLOBALS['wp_query']->posts ?? array() ) as $post ) {
			if ( $post instanceof WP_Post && 'post' === $post->post_type ) {
				++$n;
				$items[] = array( '@type' => 'ListItem', 'position' => $n, 'item' => remotive_schema_post_summary( $post ) );
			}
		}

		$nodes[] = array(
			'@type'           => 'ItemList',
			'@id'             => $url . '#posts',
			'name'            => remotive_schema_page_name(),
			'numberOfItems'   => count( $items ),
			'itemListElement' => $items,
		);
	}

	// A case study: an Article the page is about.
	if ( remotive_schema_is_case_study() ) {
		$post    = get_post();
		$article = array(
			'@type'            => 'Article',
			'@id'              => $url . '#case-study',
			'headline'         => get_the_title( $post ),
			'genre'            => 'Case study',
			'url'              => $url,
			'datePublished'    => get_the_date( 'c', $post ),
			'dateModified'     => get_the_modified_date( 'c', $post ),
			'author'           => $org,
			'publisher'        => $org,
			'inLanguage'       => get_bloginfo( 'language' ),
			'mainEntityOfPage' => array( '@id' => $url . '#webpage' ),
			'isAccessibleForFree' => true,
		);
		$description = remotive_schema_page_description( $post );

		if ( '' !== $description ) {
			$article['description'] = $description;
		}

		$thumb = get_post_thumbnail_id( $post );

		if ( $thumb ) {
			$article['image'] = array( '@id' => $url . '#primaryimage' );
		}

		$nodes[] = $article;
	}

	// The services page: the catalogue of service pages.
	if ( is_page_template( 'page-services' ) ) {
		$parent = get_post();
		$items  = array();
		$n      = 0;

		if ( $parent instanceof WP_Post ) {
			foreach ( get_pages( array( 'parent' => $parent->ID, 'sort_column' => 'menu_order,post_title' ) ) as $child ) {
				++$n;
				$items[] = array(
					'@type'       => 'Offer',
					'itemOffered' => array(
						'@type' => 'Service',
						'@id'   => get_permalink( $child ) . '#service',
						'name'  => get_the_title( $child ),
						'url'   => get_permalink( $child ),
					),
				);
			}
		}

		$nodes[] = array(
			'@type'           => 'OfferCatalog',
			'@id'             => home_url( '/services/#catalog' ),
			'name'            => remotive_schema_page_name(),
			'numberOfItems'   => count( $items ),
			'itemListElement' => $items,
		);
	}

	// The team page: the people as an ItemList of the Person nodes the organisation lists.
	if ( is_page_template( 'page-team' ) && function_exists( 'remotive_team_members' ) ) {
		$items = array();
		$n     = 0;

		foreach ( remotive_team_members() as $member ) {
			++$n;
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $n,
				'item'     => array( '@id' => home_url( '/#person-' . ( $member['slug'] ? $member['slug'] : sanitize_title( $member['name'] ) ) ) ),
			);
		}

		$nodes[] = array(
			'@type'           => 'ItemList',
			'@id'             => $url . '#people',
			'name'            => remotive_schema_page_name(),
			'numberOfItems'   => count( $items ),
			'itemListElement' => $items,
		);
	}

	// The header navigation, on the full layout (the one-page layout's bar and a kept legal page's bar
	// carry no navigation).
	$one_page = ( function_exists( 'remotive_agency_front' ) && remotive_agency_front() )
		|| ( function_exists( 'remotive_agency_legal_chrome' ) && remotive_agency_legal_chrome() )
		|| ( function_exists( 'remotive_is_landing_page' ) && remotive_is_landing_page() );

	if ( ! $one_page ) {
		$nodes = array_merge( $nodes, remotive_schema_navigation( $url ) );
	}

	return $nodes;
}

/**
 * Fill the WebSite node and the Service nodes out, and add the page's extra nodes.
 *
 * @param array $graph JSON-LD nodes.
 * @return array
 */
function remotive_schema_extend_graph( $graph ) {
	$url   = remotive_schema_current_url();
	$terms = get_page_by_path( 'terms', OBJECT, 'page' );
	$terms = $terms && 'publish' === $terms->post_status ? get_permalink( $terms->ID ) : '';

	foreach ( $graph as $i => $node ) {
		$types = isset( $node['@type'] ) ? (array) $node['@type'] : array();

		if ( in_array( 'WebSite', $types, true ) ) {
			$graph[ $i ]['inLanguage']          = remotive_schema_languages();
			$graph[ $i ]['about']               = remotive_schema_org_ref();
			$graph[ $i ]['copyrightHolder']     = remotive_schema_org_ref();
			$graph[ $i ]['isAccessibleForFree'] = true;
			$tagline                            = trim( (string) get_bloginfo( 'description' ) );

			if ( '' !== $tagline ) {
				$graph[ $i ]['description'] = $tagline;
			}
		}

		if ( in_array( 'Service', $types, true ) ) {
			$graph[ $i ]['serviceType'] = isset( $node['name'] ) ? $node['name'] : 'Marketing';
			$graph[ $i ]['audience']    = remotive_schema_audience();
			$graph[ $i ]['offers']      = array( '@id' => home_url( '/#free-audit' ) );
			$graph[ $i ]['brand']       = array( '@id' => home_url( '/#brand' ) );
			$graph[ $i ]['availableChannel'] = array(
				'@type'      => 'ServiceChannel',
				'serviceUrl' => home_url( '/contact/' ),
				'availableLanguage' => remotive_schema_languages(),
			);

			if ( '' !== $terms ) {
				$graph[ $i ]['termsOfService'] = $terms;
			}
		}
	}

	return array_merge( $graph, remotive_schema_extended_nodes( $url ) );
}
add_filter( 'remotive_schema_graph', 'remotive_schema_extend_graph', 20 );
