<?php
/**
 * Remotive Media — configurable colour scheme.
 *
 * Appearance -> Theme Options -> Colours. Every colour the theme draws from
 * its palette can be set separately for dark mode and light mode. A value is
 * written out as a CSS variable only when it differs from the shipped one, so
 * an untouched install is byte-for-byte what it was before this existed.
 *
 * The slugs are roles, not literal shades: `ink` is the text colour and
 * `paper` the page background in whichever mode is showing, and the `-dark`
 * variants are the stronger accent used for text and buttons (they are only
 * darker than the plain colour in light mode). The labels below say what each
 * one is for.
 *
 * Contrast is checked live in the admin and again on save. A low-contrast
 * pair is allowed, because the owner decides, but it is flagged.
 */

defined( 'ABSPATH' ) || exit;

/**
 * The palette roles: slug => array( label, dark default, light default, help ).
 *
 * Dark defaults are the theme.json palette; light defaults are the overrides in
 * assets/css/remotive.css. Keep the three in step.
 *
 * @return array<string,array<int,string>>
 */
function remotive_colour_tokens() {
	return array(
		'ink'           => array( __( 'Text', 'remotive' ), '#ffffff', '#1e1e1e', __( 'Body text and headings.', 'remotive' ) ),
		'paper'         => array( __( 'Page background', 'remotive' ), '#1a1a2e', '#f7f4ec', __( 'The main page surface.', 'remotive' ) ),
		'paper-2'       => array( __( 'Deep background', 'remotive' ), '#0d1117', '#f0f8ff', __( 'Recessed bands and panels.', 'remotive' ) ),
		'card'          => array( __( 'Card surface', 'remotive' ), '#252542', '#ffffff', __( 'Cards, forms and raised panels.', 'remotive' ) ),
		'cyan'          => array( __( 'Cyan plate', 'remotive' ), '#00aeef', '#00a2ff', __( 'Cyan fills and decoration. Not for text in light mode.', 'remotive' ) ),
		'cyan-dark'     => array( __( 'Cyan, strong', 'remotive' ), '#00aeef', '#00308f', __( 'Cyan used for text, links and bands.', 'remotive' ) ),
		'magenta'       => array( __( 'Magenta plate', 'remotive' ), '#ec008c', '#ff449f', __( 'Magenta fills and decoration. Not for text in light mode.', 'remotive' ) ),
		'magenta-dark'  => array( __( 'Magenta, strong', 'remotive' ), '#ff0198', '#cd360b', __( 'Magenta used for text, buttons and bands.', 'remotive' ) ),
		'accent-3'      => array( __( 'Third plate', 'remotive' ), '#39b54a', '#f2f216', __( 'Green (dark) or yellow (light) fills and decoration.', 'remotive' ) ),
		'accent-3-dark' => array( __( 'Third plate, strong', 'remotive' ), '#39b54a', '#008110', __( 'Third colour used for text.', 'remotive' ) ),
		'contrast-bg'   => array( __( 'Contrast band background', 'remotive' ), '#1a1a2e', '#1a1a2e', __( 'Bands that stay dark in both modes.', 'remotive' ) ),
		'contrast-text' => array( __( 'Contrast band text', 'remotive' ), '#ffffff', '#ffffff', __( 'Text on those bands.', 'remotive' ) ),
	);
}

/** Option key for a token in a mode ('dark' or 'light'). */
function remotive_colour_key( $mode, $slug ) {
	return 'colour_' . $mode . '_' . str_replace( '-', '_', $slug );
}

/**
 * Defaults for every colour option.
 *
 * @return array<string,string>
 */
function remotive_colour_option_defaults() {
	$out = array( 'colours_reset' => '0' );

	foreach ( remotive_colour_tokens() as $slug => $t ) {
		$out[ remotive_colour_key( 'dark', $slug ) ]  = $t[1];
		$out[ remotive_colour_key( 'light', $slug ) ] = $t[2];
	}

	return $out;
}

/**
 * The pairs whose contrast matters: label, foreground token, background token,
 * minimum ratio. A foreground starting with '#' is a fixed colour the
 * stylesheet uses in that mode.
 *
 * @param string $mode 'dark' or 'light'.
 * @return array<int,array>
 */
function remotive_colour_pairs( $mode ) {
	// The label colour on magenta buttons and bands is fixed per mode in remotive.css.
	$on_magenta = 'dark' === $mode ? '#14141f' : '#ffffff';

	return array(
		array( __( 'Text on page background', 'remotive' ), 'ink', 'paper', 4.5 ),
		array( __( 'Text on cards', 'remotive' ), 'ink', 'card', 4.5 ),
		array( __( 'Text on deep background', 'remotive' ), 'ink', 'paper-2', 4.5 ),
		array( __( 'Magenta text on page', 'remotive' ), 'magenta-dark', 'paper', 4.5 ),
		array( __( 'Cyan text on page', 'remotive' ), 'cyan-dark', 'paper', 4.5 ),
		array( __( 'Third-colour text on page', 'remotive' ), 'accent-3-dark', 'paper', 4.5 ),
		array( __( 'Button label on magenta', 'remotive' ), $on_magenta, 'magenta-dark', 4.5 ),
		array( __( 'Text on contrast bands', 'remotive' ), 'contrast-text', 'contrast-bg', 4.5 ),
	);
}

/** WCAG relative luminance of a #rrggbb colour. */
function remotive_colour_luminance( $hex ) {
	$hex = ltrim( $hex, '#' );

	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}

	$lin = array();
	foreach ( array( 0, 2, 4 ) as $i ) {
		$c     = hexdec( substr( $hex, $i, 2 ) ) / 255;
		$lin[] = $c <= 0.03928 ? $c / 12.92 : pow( ( $c + 0.055 ) / 1.055, 2.4 );
	}

	return 0.2126 * $lin[0] + 0.7152 * $lin[1] + 0.0722 * $lin[2];
}

/** WCAG contrast ratio between two #rrggbb colours (1 to 21). */
function remotive_colour_contrast( $a, $b ) {
	$la = remotive_colour_luminance( $a );
	$lb = remotive_colour_luminance( $b );

	return ( max( $la, $lb ) + 0.05 ) / ( min( $la, $lb ) + 0.05 );
}

/**
 * The colour value of a token in a mode, from a settings array.
 *
 * @param array  $values Settings.
 * @param string $mode   'dark' or 'light'.
 * @param string $slug   Token slug, or a fixed #hex.
 */
function remotive_colour_value( $values, $mode, $slug ) {
	if ( '#' === substr( $slug, 0, 1 ) ) {
		return $slug;
	}

	$key = remotive_colour_key( $mode, $slug );

	return isset( $values[ $key ] ) ? $values[ $key ] : '#000000';
}

/**
 * Sanitise the colour fields into $clean. Anything that is not a hex colour
 * falls back to the shipped value; the reset switch restores every default.
 *
 * @param array $input Submitted settings.
 * @param array $clean Settings being built (by reference).
 */
function remotive_sanitize_colour_options( $input, &$clean ) {
	$defaults = remotive_colour_option_defaults();
	$reset    = isset( $input['colours_reset'] ) && '1' === (string) $input['colours_reset'];

	foreach ( $defaults as $key => $default ) {
		if ( 'colours_reset' === $key ) {
			continue;
		}

		$value = $reset || ! isset( $input[ $key ] ) ? null : sanitize_hex_color( (string) $input[ $key ] );

		// sanitize_hex_color() accepts #rgb; store one form.
		if ( $value && 4 === strlen( $value ) ) {
			$value = '#' . $value[1] . $value[1] . $value[2] . $value[2] . $value[3] . $value[3];
		}

		$clean[ $key ] = $value ? strtolower( $value ) : $default;
	}

	$clean['colours_reset'] = '0';

	// Allowed, but say so: a pair under the minimum is hard to read.
	foreach ( array( 'dark' => __( 'Dark mode', 'remotive' ), 'light' => __( 'Light mode', 'remotive' ) ) as $mode => $mode_label ) {
		foreach ( remotive_colour_pairs( $mode ) as $pair ) {
			$ratio = remotive_colour_contrast( remotive_colour_value( $clean, $mode, $pair[1] ), remotive_colour_value( $clean, $mode, $pair[2] ) );

			if ( $ratio < $pair[3] ) {
				add_settings_error(
					'remotive_theme_options',
					'colour_contrast_' . $mode . '_' . sanitize_key( $pair[0] ),
					sprintf(
						/* translators: 1: mode, 2: pair label, 3: ratio, 4: minimum ratio */
						__( '%1$s: "%2$s" has a contrast of %3$s:1, below the recommended %4$s:1. Saved, but it may be hard to read.', 'remotive' ),
						$mode_label,
						$pair[0],
						number_format_i18n( $ratio, 2 ),
						number_format_i18n( $pair[3], 1 )
					),
					'warning'
				);
			}
		}
	}
}

/**
 * The settings tab: one group per mode (twelve pickers and a live contrast
 * table each), and a reset switch.
 *
 * @return array
 */
function remotive_colours_tab() {
	$groups = array();

	foreach ( array(
		'dark'  => array( __( 'Dark mode', 'remotive' ), __( 'The default look for first-time visitors unless you chose otherwise under Site behaviour.', 'remotive' ) ),
		'light' => array( __( 'Light mode', 'remotive' ), __( 'Shown when a visitor switches to light, or their device asks for it.', 'remotive' ) ),
	) as $mode => $info ) {
		$fields = array();

		foreach ( remotive_colour_tokens() as $slug => $t ) {
			$fields[ remotive_colour_key( $mode, $slug ) ] = array(
				'label'  => $t[0],
				'type'   => 'color',
				'helper' => $t[3],
			);
		}

		$fields[ 'colour_contrast_' . $mode ] = array(
			'label' => __( 'Contrast check', 'remotive' ),
			'type'  => 'contrast',
			'mode'  => $mode,
		);

		$groups[] = array(
			'label'       => $info[0],
			'description' => $info[1],
			'fields'      => $fields,
		);
	}

	$groups[] = array(
		'label'       => __( 'Reset', 'remotive' ),
		'description' => '',
		'fields'      => array(
			'colours_reset' => array(
				'label'        => __( 'Restore the shipped colours', 'remotive' ),
				'type'         => 'toggle',
				'toggle_label' => __( 'Put every colour back to its default when I save', 'remotive' ),
			),
		),
	);

	return array(
		'label'       => __( 'Colours', 'remotive' ),
		'icon'        => 'dashicons-art',
		'description' => __( 'The site\'s colour scheme, set separately for dark and light mode. Only colours you change are written to the site, so leaving this alone changes nothing. Each mode shows a live contrast check; a low-contrast pair is allowed but flagged.', 'remotive' ),
		'groups'      => $groups,
	);
}

/**
 * Render the contrast table for one mode. The rows are filled in by
 * assets/js/admin-theme-options.js as the pickers change; the values here are
 * the saved ones, so the table is right before any script runs.
 *
 * @param string $mode 'dark' or 'light'.
 */
function remotive_render_contrast_table( $mode ) {
	$values = array();
	foreach ( remotive_colour_option_defaults() as $key => $unused ) {
		$values[ $key ] = remotive_get_theme_option( $key );
	}

	echo '<div class="rm-admin__contrast" data-mode="' . esc_attr( $mode ) . '"><table><tbody>';

	foreach ( remotive_colour_pairs( $mode ) as $pair ) {
		$fg    = remotive_colour_value( $values, $mode, $pair[1] );
		$bg    = remotive_colour_value( $values, $mode, $pair[2] );
		$ratio = remotive_colour_contrast( $fg, $bg );
		$ok    = $ratio >= $pair[3];

		printf(
			'<tr data-fg="%1$s" data-bg="%2$s" data-min="%3$s" data-good="%10$s" data-bad="%11$s" class="%4$s"><th scope="row">%5$s</th><td><span class="rm-admin__swatch" style="background:%6$s;color:%7$s">Aa</span></td><td class="rm-admin__ratio">%8$s:1</td><td class="rm-admin__verdict">%9$s</td></tr>',
			esc_attr( '#' === substr( $pair[1], 0, 1 ) ? $pair[1] : '#' . remotive_colour_key( $mode, $pair[1] ) ),
			esc_attr( '#' . remotive_colour_key( $mode, $pair[2] ) ),
			esc_attr( $pair[3] ),
			$ok ? 'is-ok' : 'is-low',
			esc_html( $pair[0] ),
			esc_attr( $bg ),
			esc_attr( $fg ),
			esc_html( number_format_i18n( $ratio, 2 ) ),
			esc_html( $ok ? __( 'Good', 'remotive' ) : __( 'Low', 'remotive' ) ),
			esc_attr__( 'Good', 'remotive' ),
			esc_attr__( 'Low', 'remotive' )
		);
	}

	echo '</tbody></table></div>';
}

/**
 * CSS that applies any changed colours: custom properties only, in the same
 * selectors the stylesheet uses for each mode, but one step more specific so
 * they win regardless of load order.
 *
 * @return string
 */
function remotive_colours_css() {
	$css = '';

	foreach ( array(
		'dark'  => 'html:not([data-theme="light"])',
		'light' => 'html[data-theme="light"]',
	) as $mode => $selector ) {
		$decl = '';

		foreach ( remotive_colour_tokens() as $slug => $t ) {
			$value   = remotive_get_theme_option( remotive_colour_key( $mode, $slug ) );
			$default = 'dark' === $mode ? $t[1] : $t[2];

			if ( is_string( $value ) && preg_match( '/^#[0-9a-f]{6}$/', $value ) && $value !== $default ) {
				$decl .= '--wp--preset--color--' . $slug . ':' . $value . ';';
			}
		}

		if ( '' !== $decl ) {
			$css .= $selector . '{' . $decl . '}';
		}
	}

	return $css;
}
