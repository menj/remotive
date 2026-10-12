<?php
/**
 * Structured data for the page furniture and for the two layouts' sections.
 *
 * inc/content/schema-markup.php describes the organisation, the page and the
 * article. This file adds what sits inside the page, as schema.org JSON-LD:
 *
 * - Every page: the header (WPHeader), the footer (WPFooter) and, where the
 *   template has one, the sidebar (WPSideBar), each a WebPageElement with a
 *   CSS selector that points at the element.
 * - The agency home page: one WebPageElement per section, the six services as
 *   Service nodes in an ItemList, the four steps as a HowTo, the checklist and
 *   the team as ItemLists (the people are the Person nodes the organisation
 *   already lists).
 * - The ad landing pages: a Service node for the offer, in the language of the
 *   URL, and the header and footer.
 *
 * The questions keep their own FAQPage blocks. The nodes use types no SEO plugin
 * manages, so they are added after the plugin check and appear with or without
 * Rank Math. Run `php tests/test-schema-elements.php` for the tests.
 *
 * @package Remotive
 */

defined( 'ABSPATH' ) || exit;

/**
 * One WebPageElement (or a typed subclass) that belongs to the current page.
 *
 * @param string $type     WebPageElement, WPHeader, WPFooter or WPSideBar.
 * @param string $key      Anchor for the @id.
 * @param string $name     What the element is.
 * @param string $selector CSS selector that finds it on the page.
 * @param string $page_url Address of the page it sits on.
 * @return array
 */
function remotive_schema_element( $type, $key, $name, $selector, $page_url ) {
	return array(
		'@type'       => $type,
		'@id'         => $page_url . '#' . $key,
		'name'        => $name,
		'cssSelector' => $selector,
		'isPartOf'    => array( '@id' => $page_url . '#webpage' ),
	);
}

/**
 * Whether the current request has the theme's sidebar in its template.
 *
 * @return bool
 */
function remotive_schema_has_sidebar() {
	return is_home() || is_archive() || is_search() || is_singular( 'post' ) || is_page_template( 'page-contact' );
}

/**
 * The header, footer and sidebar of the full layout.
 *
 * @param string $page_url Address of the page.
 * @return array[]
 */
function remotive_schema_site_elements( $page_url ) {
	$nodes = array(
		remotive_schema_element( 'WPHeader', 'header', 'Site header', '.rm-nav', $page_url ),
	);

	if ( remotive_schema_has_sidebar() ) {
		$nodes[] = remotive_schema_element( 'WPSideBar', 'sidebar', 'Sidebar', '.rm-sidebar', $page_url );
	}

	$nodes[] = remotive_schema_element( 'WPFooter', 'footer', 'Site footer', '.rm-footer', $page_url );

	return $nodes;
}

/**
 * The agency page's services, as Service nodes (replacing the three generic
 * ones the full layout lists on its home page).
 *
 * @return array[]
 */
function remotive_schema_agency_services() {
	$org   = array( '@id' => home_url( '/#organization' ) );
	$nodes = array();
	$list  = array();
	$n     = 0;

	for ( $i = 1; $i <= 6; $i++ ) {
		$title = trim( (string) remotive_get_theme_option( 'agency_svc_' . $i . '_title' ) );

		if ( '' === $title ) {
			continue;
		}

		++$n;
		$node = array(
			'@type'      => 'Service',
			'@id'        => home_url( '/#service-' . $i ),
			'name'       => $title,
			'provider'   => $org,
			'areaServed' => array( 'Singapore', 'Malaysia' ),
		);
		$desc = trim( (string) remotive_get_theme_option( 'agency_svc_' . $i . '_desc' ) );
		$url  = function_exists( 'remotive_agency_service_url' ) ? remotive_agency_service_url( $i ) : '';

		if ( '' !== $desc ) {
			$node['description'] = $desc;
		}

		if ( '' !== $url ) {
			$node['url'] = $url;
		}

		$nodes[] = $node;
		$list[]  = array( '@type' => 'ListItem', 'position' => $n, 'item' => array( '@id' => $node['@id'] ) );
	}

	if ( $list ) {
		$nodes[] = array(
			'@type'           => 'ItemList',
			'@id'             => home_url( '/#services' ),
			'name'            => (string) remotive_get_theme_option( 'agency_services_heading' ),
			'numberOfItems'   => count( $list ),
			'itemListElement' => $list,
		);
	}

	return $nodes;
}

/**
 * Everything the agency home page adds to the graph.
 *
 * @param string $url Address of the page.
 * @return array[]
 */
function remotive_schema_agency_nodes( $url ) {
	$nodes = array(
		remotive_schema_element( 'WPHeader', 'header', 'Page header', '.rm-lp__bar', $url ),
		remotive_schema_element( 'WebPageElement', 'hero', 'Introduction and contact form', '.rm-lp__hero', $url ),
	);

	$on = function ( $key ) {
		return '1' === (string) remotive_get_theme_option( $key );
	};

	if ( $on( 'agency_show_services' ) ) {
		$services = remotive_schema_agency_services();

		if ( $services ) {
			$nodes = array_merge( $nodes, $services );
			$nodes[] = remotive_schema_element( 'WebPageElement', 'section-services', (string) remotive_get_theme_option( 'agency_services_heading' ), '#ag-services', $url );
		}
	}

	$steps = $on( 'agency_show_process' ) ? remotive_agency_process_items() : array();

	if ( $steps ) {
		$how = array();

		foreach ( $steps as $i => $step ) {
			$node = array( '@type' => 'HowToStep', 'position' => $i + 1, 'name' => $step[0] );

			if ( '' !== $step[1] ) {
				$node['text'] = $step[1];
			}

			$how[] = $node;
		}

		$nodes[] = array(
			'@type' => 'HowTo',
			'@id'   => $url . '#process',
			'name'  => (string) remotive_get_theme_option( 'agency_process_heading' ),
			'step'  => $how,
		);
		$nodes[] = remotive_schema_element( 'WebPageElement', 'section-process', (string) remotive_get_theme_option( 'agency_process_heading' ), '#ag-process', $url );
	}

	$signs = $on( 'agency_show_signs' ) ? remotive_agency_sign_items() : array();

	if ( $signs ) {
		$items = array();

		foreach ( $signs as $i => $text ) {
			$items[] = array( '@type' => 'ListItem', 'position' => $i + 1, 'name' => $text );
		}

		$nodes[] = array(
			'@type'           => 'ItemList',
			'@id'             => $url . '#signs',
			'name'            => (string) remotive_get_theme_option( 'agency_signs_heading' ),
			'numberOfItems'   => count( $items ),
			'itemListElement' => $items,
		);
		$nodes[] = remotive_schema_element( 'WebPageElement', 'section-signs', (string) remotive_get_theme_option( 'agency_signs_heading' ), '#ag-signs', $url );
	}

	if ( $on( 'agency_show_team' ) && function_exists( 'remotive_team_members' ) && remotive_team_members() ) {
		$people = array();

		foreach ( remotive_team_members() as $i => $member ) {
			$people[] = array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'item'     => array( '@id' => home_url( '/#person-' . ( $member['slug'] ? $member['slug'] : sanitize_title( $member['name'] ) ) ) ),
			);
		}

		$nodes[] = array(
			'@type'           => 'ItemList',
			'@id'             => $url . '#team-list',
			'name'            => (string) remotive_get_theme_option( 'agency_team_heading' ),
			'numberOfItems'   => count( $people ),
			'itemListElement' => $people,
		);
		$nodes[] = remotive_schema_element( 'WebPageElement', 'section-team', (string) remotive_get_theme_option( 'agency_team_heading' ), '#ag-team', $url );
	}

	if ( $on( 'agency_show_faq' ) && remotive_agency_faq_items() ) {
		$nodes[] = remotive_schema_element( 'WebPageElement', 'section-questions', (string) remotive_get_theme_option( 'agency_faq_heading' ), '#ag-questions', $url );
	}

	$nodes[] = remotive_schema_element( 'WebPageElement', 'section-contact', (string) remotive_get_theme_option( 'agency_final_heading' ), '.rm-lp__final', $url );
	$nodes[] = remotive_schema_element( 'WPFooter', 'footer', 'Page footer', '.rm-ag__foot', $url );

	return $nodes;
}

/**
 * The Service an ad landing page offers.
 *
 * @param string $url Address of the page.
 * @return array[]
 */
function remotive_schema_landing_nodes( $url ) {
	$slug     = (string) get_post_field( 'post_name', get_queried_object_id() );
	$services = function_exists( 'remotive_landing_services' ) ? remotive_landing_services() : array();
	$nodes    = array(
		remotive_schema_element( 'WPHeader', 'header', 'Page header', '.rm-lp__bar', $url ),
	);

	if ( isset( $services[ $slug ] ) ) {
		$i    = remotive_lp_lang_index();
		$svc  = $services[ $slug ];
		$node = array(
			'@type'      => 'Service',
			'@id'        => $url . '#service',
			'name'       => $svc['label'][ $i ],
			'url'        => $url,
			'provider'   => array( '@id' => home_url( '/#organization' ) ),
			'areaServed' => array( 'Singapore', 'Malaysia' ),
			'inLanguage' => remotive_lp_languages()[ remotive_lp_requested_lang() ][1],
		);

		if ( ! empty( $svc['lead'][ $i ] ) ) {
			$node['description'] = $svc['lead'][ $i ];
		}

		$nodes[] = $node;
		$nodes[] = remotive_schema_element( 'WebPageElement', 'offer', $svc['title'][ $i ], '.rm-lp__hero', $url );
	}

	$nodes[] = remotive_schema_element( 'WPFooter', 'footer', 'Page footer', '.rm-lp__foot', $url );

	return $nodes;
}

/**
 * Add the element nodes to the page's graph.
 *
 * @param array $graph JSON-LD nodes.
 * @return array
 */
function remotive_schema_add_elements( $graph ) {
	$url = remotive_schema_current_url();

	if ( function_exists( 'remotive_agency_front' ) && remotive_agency_front() ) {
		$extra = remotive_schema_agency_nodes( $url );
	} elseif ( function_exists( 'remotive_is_landing_page' ) && remotive_is_landing_page() ) {
		$extra = remotive_schema_landing_nodes( $url );
	} else {
		$extra = remotive_schema_site_elements( $url );
	}

	return array_merge( $graph, $extra );
}
add_filter( 'remotive_schema_graph', 'remotive_schema_add_elements' );

/**
 * On the agency home page the services are the agency's six, not the three
 * generic groups the full layout lists.
 *
 * @param array $services Service nodes from the full layout.
 * @return array
 */
function remotive_schema_agency_replace_services( $services ) {
	return ( function_exists( 'remotive_agency_front' ) && remotive_agency_front() ) ? array() : $services;
}
add_filter( 'remotive_schema_services', 'remotive_schema_agency_replace_services' );
