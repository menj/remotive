<?php
/**
 * Classic menu management.
 *
 * This is a block theme, and WordPress hides Appearance -> Menus whenever
 * wp_is_block_theme() is true. That is a reasonable default and a bad fit
 * here: the site is edited by people who want the classic Menus screen, not
 * the Site Editor. So this file does three things.
 *
 * 1. Registers four menu locations (header, and the footer's three
 *    columns: Services, Case Studies, Company).
 * 2. Puts the Appearance -> Menus screen back, and links it from Theme
 *    Options, so menus are managed the classic way.
 * 3. Renders those menus in place of the templates' core/navigation blocks,
 *    emitting the same class names the stylesheet already targets, so the
 *    header keeps its underline hover, its squeeze breakpoints and its
 *    mobile overlay behaviour without a single CSS change.
 *
 * The navigation blocks in the templates stay as the fallback: if a
 * location has no menu assigned, the block renders its own hard-coded links
 * exactly as before. Nothing breaks on a fresh install before menus exist.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the menu locations.
 */
function remotive_register_menus() {
	add_theme_support( 'menus' );

	register_nav_menus(
		array(
			'primary'         => __( 'Main menu (header)', 'remotive' ),
			'footer_services' => __( 'Footer — Services column', 'remotive' ),
			'footer_cases'    => __( 'Footer — Case Studies column', 'remotive' ),
			'footer_company'  => __( 'Footer — Company column', 'remotive' ),
		)
	);
}
add_action( 'after_setup_theme', 'remotive_register_menus' );

/**
 * Migrate a pre-1.65.7 'footer_sitemap' assignment to 'footer_company'.
 *
 * 1.65.7 retired the 'footer_sitemap' location name in favour of
 * 'footer_company' (Case Studies also gained its own real location,
 * 'footer_cases', rather than being permanently excluded). A site that
 * assigned a menu under the old name before this change would otherwise
 * lose that assignment silently the moment 'footer_sitemap' stopped being
 * registered — the theme_mod entry would still exist, but nothing reads
 * it any more. Runs once per site: after copying the value across (only
 * when 'footer_company' isn't already assigned, so a deliberate new
 * choice is never overwritten), it removes the orphaned old key so this
 * check is cheap on every subsequent load.
 */
function remotive_migrate_footer_sitemap_location() {
	$locations = get_theme_mod( 'nav_menu_locations', array() );

	if ( empty( $locations['footer_sitemap'] ) ) {
		return; // Nothing to migrate.
	}

	if ( empty( $locations['footer_company'] ) ) {
		$locations['footer_company'] = $locations['footer_sitemap'];
	}

	unset( $locations['footer_sitemap'] );
	set_theme_mod( 'nav_menu_locations', $locations );
}
add_action( 'admin_init', 'remotive_migrate_footer_sitemap_location' );

/**
 * Restore the Appearance -> Menus screen.
 *
 * Core historically registered nav-menus.php only for classic themes,
 * which is why this shim exists. Current WordPress adds the screen for
 * block themes too once menu locations are registered, so the shim now
 * checks whether the entry already exists instead of assuming — adding
 * unconditionally produced two "Menus" items in the Appearance menu.
 * Runs late so core's own entry is registered first.
 */
function remotive_restore_menus_screen() {
	if ( ! wp_is_block_theme() ) {
		return; // Classic theme: core already added it.
	}

	global $submenu;

	if ( ! empty( $submenu['themes.php'] ) ) {
		foreach ( $submenu['themes.php'] as $item ) {
			if ( isset( $item[2] ) && 'nav-menus.php' === $item[2] ) {
				return; // Core already added it.
			}
		}
	}

	add_theme_page(
		__( 'Menus', 'remotive' ),
		__( 'Menus', 'remotive' ),
		'edit_theme_options',
		'nav-menus.php'
	);
}
add_action( 'admin_menu', 'remotive_restore_menus_screen', 20 );

/**
 * Map a navigation block's className to a registered menu location.
 *
 * Each footer navigation carries its own distinguishing class, matched
 * directly rather than by render order. The footer used to tell its
 * columns apart with a static render-order counter, which broke the
 * moment a third column (Case Studies) was added between the other two:
 * the counter still only recognised two buckets, so two of the three nav
 * blocks collapsed onto the same location. Explicit class matching can't
 * drift out of sync with the template again, however many columns get
 * added or reordered. As of 1.65.7, all three footer columns — Services,
 * Case Studies, and Company — have their own real registered location;
 * Case Studies previously had none and always rendered its hard-coded
 * fallback, which meant an administrator could never actually edit it.
 *
 * @param string $class The block's className attribute.
 * @return string Menu location slug, or ''.
 */
function remotive_nav_location_for_class( $class ) {
	if ( false !== strpos( $class, 'rm-nav__links' ) ) {
		return 'primary';
	}

	if ( false !== strpos( $class, 'rm-footer__links--cases' ) ) {
		return 'footer_cases';
	}

	if ( false !== strpos( $class, 'rm-footer__links--company' ) ) {
		return 'footer_company';
	}

	if ( false !== strpos( $class, 'rm-footer__links' ) ) {
		return 'footer_services';
	}

	return '';
}

/**
 * Give menu items the class names the stylesheet expects.
 *
 * Every rule in remotive.css targets .wp-block-navigation-item and
 * .wp-block-navigation__container, so classic menu output has to carry those
 * names rather than the classic defaults. Adding them here is cheaper and
 * far less brittle than restyling the whole navigation for a second markup
 * shape.
 *
 * @param string[] $classes Item classes.
 * @return string[]
 */
function remotive_nav_item_classes( $classes ) {
	$classes[] = 'wp-block-navigation-item';
	$classes[] = 'wp-block-navigation-link';
	return $classes;
}

/**
 * Add the block link class to each anchor.
 *
 * @param array $atts Anchor attributes.
 * @return array
 */
function remotive_nav_link_atts( $atts ) {
	$atts['class'] = trim( ( $atts['class'] ?? '' ) . ' wp-block-navigation-item__content' );
	return $atts;
}

/**
 * Render an assigned classic menu in place of a navigation block.
 *
 * @param string $block_content Rendered block HTML.
 * @param array  $block         Parsed block.
 * @return string
 */
function remotive_render_classic_menu( $block_content, $block ) {
	if ( empty( $block['blockName'] ) || 'core/navigation' !== $block['blockName'] ) {
		return $block_content;
	}

	$class    = $block['attrs']['className'] ?? '';
	$location = remotive_nav_location_for_class( $class );

	// Not one of our navigations, or no menu assigned to that location yet:
	// leave the block's own output alone.
	if ( '' === $location || ! has_nav_menu( $location ) ) {
		return $block_content;
	}

	$is_header = ( 'primary' === $location );

	add_filter( 'nav_menu_css_class', 'remotive_nav_item_classes' );
	add_filter( 'nav_menu_link_attributes', 'remotive_nav_link_atts' );

	$menu = wp_nav_menu(
		array(
			'theme_location' => $location,
			'container'      => false,
			'menu_class'     => 'wp-block-navigation__container',
			'depth'          => 1,
			'echo'           => false,
			'fallback_cb'    => '__return_empty_string',
		)
	);

	remove_filter( 'nav_menu_css_class', 'remotive_nav_item_classes' );
	remove_filter( 'nav_menu_link_attributes', 'remotive_nav_link_atts' );

	if ( ! $menu ) {
		return $block_content;
	}

	$classes = 'wp-block-navigation ' . esc_attr( $class );

	if ( ! $is_header ) {
		// The footer lists are vertical; the header row is not.
		$classes .= ' is-vertical';
	}

	$html = '<nav class="' . $classes . ' is-layout-flex"'
		. ( $is_header ? ' aria-label="' . esc_attr__( 'Main', 'remotive' ) . '"' : '' )
		. '>' . $menu;

	// The header needs its mobile toggle: below 600px the stylesheet hides
	// the link list and shows this button, which the small-screen script
	// wires to the overlay. Footer menus never collapse, so they get none.
	if ( $is_header ) {
		$html .= '<button type="button" class="rm-nav__burger" aria-expanded="false" aria-controls="rm-nav-overlay" aria-label="'
			. esc_attr__( 'Open menu', 'remotive' ) . '"><span></span></button>';
	}

	$html .= '</nav>';

	return $html;
}
add_filter( 'render_block', 'remotive_render_classic_menu', 10, 2 );

/**
 * Build the four menus from the site's pages, once.
 *
 * Runs from the same setup routine that creates the pages, so a fresh
 * install arrives with menus already populated and assigned. Never touches a
 * location that already has a menu, so edits are safe.
 *
 * @return string[] Human-readable notes about what was created.
 */
function remotive_build_classic_menus() {
	$notes = array();

	$definitions = array(
		'primary'         => array(
			'name'  => __( 'Main menu', 'remotive' ),
			'items' => array( 'services', 'case-studies', 'about', 'team', 'blog', 'faq', 'contact' ),
		),
		'footer_company'  => array(
			'name'  => __( 'Footer — Company', 'remotive' ),
			'items' => array( 'about', 'team', 'blog', 'faq', 'contact', 'privacy', 'terms' ),
		),
		'footer_services' => array(
			'name'  => __( 'Footer — Services', 'remotive' ),
			'items' => array(
				'services/seo',
				'services/paid-media',
				'services/social',
				'services/content',
				'services/email',
				'services/analytics',
			),
		),
		'footer_cases'    => array(
			'name'  => __( 'Footer — Case Studies', 'remotive' ),
			// Mirrors the template fallback: a curated six with short
			// labels plus the "all" link, not all 13 with their full
			// page titles — a footer column is too narrow for titles
			// like "Premium Skincare: +168% GMV Across Three SEA
			// Markets" to read as navigation. An administrator can add
			// the rest from the Menus screen; that editability is the
			// point of this location existing at all.
			'items' => array(
				array(
					'path'  => 'case-studies/market-entry-trading-platform',
					'label' => __( 'Market entry, trading platform', 'remotive' ),
				),
				array(
					'path'  => 'case-studies/marketplace-launch-skincare',
					'label' => __( 'Marketplace launch, skincare', 'remotive' ),
				),
				array(
					'path'  => 'case-studies/programmatic-advertising-automotive',
					'label' => __( 'Programmatic, automotive', 'remotive' ),
				),
				array(
					'path'  => 'case-studies/technical-seo-industrial-automation',
					'label' => __( 'Technical SEO, automation', 'remotive' ),
				),
				array(
					'path'  => 'case-studies/seo-ai-visibility-healthcare',
					'label' => __( 'AI visibility, healthcare', 'remotive' ),
				),
				array(
					'path'  => 'case-studies/ecommerce-seo-footwear',
					'label' => __( 'Ecommerce SEO, footwear', 'remotive' ),
				),
				array(
					'path'  => 'case-studies',
					'label' => __( 'All 13 case studies →', 'remotive' ),
				),
			),
		),
	);

	$locations = get_theme_mod( 'nav_menu_locations', array() );

	foreach ( $definitions as $location => $definition ) {
		if ( ! empty( $locations[ $location ] ) && wp_get_nav_menu_object( $locations[ $location ] ) ) {
			continue; // Already assigned — leave it alone.
		}

		$menu = wp_get_nav_menu_object( $definition['name'] );
		$menu_id = $menu ? (int) $menu->term_id : 0;

		if ( ! $menu_id ) {
			$created = wp_create_nav_menu( $definition['name'] );

			if ( is_wp_error( $created ) ) {
				continue;
			}

			$menu_id = (int) $created;

			foreach ( $definition['items'] as $item ) {
				// An item is either a plain page path (label comes from
				// the page title) or an array with 'path' and 'label'
				// keys, for places like the footer's Case Studies
				// column where the full page title is too long to read
				// as navigation.
				$path  = is_array( $item ) ? $item['path'] : $item;
				$page  = get_page_by_path( $path, OBJECT, 'page' );

				if ( ! $page ) {
					continue;
				}

				$label = is_array( $item ) && ! empty( $item['label'] )
					? $item['label']
					: $page->post_title;

				wp_update_nav_menu_item(
					$menu_id,
					0,
					array(
						'menu-item-title'     => $label,
						'menu-item-object'    => 'page',
						'menu-item-object-id' => $page->ID,
						'menu-item-type'      => 'post_type',
						'menu-item-status'    => 'publish',
					)
				);
			}

			$notes[] = sprintf( __( '%s — menu created', 'remotive' ), $definition['name'] );
		}

		$locations[ $location ] = $menu_id;
	}

	set_theme_mod( 'nav_menu_locations', $locations );

	return $notes;
}
