<?php
/** Colour scheme: defaults, sanitising, contrast, generated CSS. Run: php tests/test-colours.php */
require __DIR__ . '/bootstrap.php';

// colours.php reads options through the theme's getter; use the real one's contract.
function remotive_get_theme_option( $k ) {
	$d = remotive_colour_option_defaults();
	return $GLOBALS['T']['options'][ $k ] ?? ( $d[ $k ] ?? '' );
}

require dirname( __DIR__ ) . '/inc/options/colours.php';

$defaults = remotive_colour_option_defaults();
t_eq( count( $defaults ), 25, 'twelve roles x two modes + the reset switch' );
t_eq( remotive_colours_css(), '', 'shipped colours emit no CSS' );

// Shipped colours raise no contrast warning.
$clean = array();
remotive_sanitize_colour_options( array(), $clean );
t_eq( count( $GLOBALS['T']['errors'] ), 0, 'defaults produce no warnings' );
t_eq( $clean['colour_dark_ink'], '#ffffff', 'a missing value falls back to the default' );

// Bad input falls back; short hex is normalised; injection is rejected.
t_reset();
$in    = array(
	'colour_dark_paper'         => 'not-a-colour',
	'colour_light_cyan'         => '#0AF',
	'colour_light_magenta_dark' => '#ff0000;background:url(x)',
	'colour_dark_cyan'          => '#112233',
);
$clean = array();
remotive_sanitize_colour_options( $in, $clean );
t_eq( $clean['colour_dark_paper'], '#1a1a2e', 'a non-colour keeps the default' );
t_eq( $clean['colour_light_cyan'], '#00aaff', '#0AF is stored as #00aaff' );
t_eq( $clean['colour_light_magenta_dark'], '#cd360b', 'a CSS injection attempt keeps the default' );
t_eq( $clean['colour_dark_cyan'], '#112233', 'a valid colour is kept' );

// A low-contrast pair is allowed but flagged.
t_reset();
$clean = array();
remotive_sanitize_colour_options( array( 'colour_dark_ink' => '#1a1a2e' ), $clean );
t_eq( $clean['colour_dark_ink'], '#1a1a2e', 'a low-contrast colour is still saved' );
t_ok( count( $GLOBALS['T']['errors'] ) > 0, 'and a warning is raised' );
t_eq( $GLOBALS['T']['errors'][0][1], 'warning', 'as a warning, not an error' );

// Reset restores every default and clears its own flag.
t_reset();
$clean = array();
remotive_sanitize_colour_options( array( 'colour_dark_ink' => '#123456', 'colours_reset' => '1' ), $clean );
t_eq( $clean['colour_dark_ink'], '#ffffff', 'reset restores the default' );
t_eq( $clean['colours_reset'], '0', 'and does not persist the switch' );

// Contrast maths against known values.
t_ok( abs( remotive_colour_contrast( '#000000', '#ffffff' ) - 21 ) < 0.01, 'black on white is 21:1' );
t_ok( abs( remotive_colour_contrast( '#cd360b', '#f7f4ec' ) - 4.63 ) < 0.02, 'magenta text on cream is 4.63:1' );
t_ok( abs( remotive_colour_contrast( '#ffffff', '#fff' ) - 1 ) < 0.001, 'a colour on itself is 1:1, #rgb accepted' );

// Every shipped pair clears its minimum in both modes.
foreach ( array( 'dark', 'light' ) as $mode ) {
	$values = array();
	foreach ( $defaults as $k => $v ) {
		$values[ $k ] = $v;
	}
	foreach ( remotive_colour_pairs( $mode ) as $pair ) {
		$r = remotive_colour_contrast( remotive_colour_value( $values, $mode, $pair[1] ), remotive_colour_value( $values, $mode, $pair[2] ) );
		t_ok( $r >= $pair[3], "$mode: {$pair[0]} is $r:1, needs {$pair[3]}" );
	}
}

// Generated CSS: only changes, in the right selector per mode.
t_reset();
$GLOBALS['T']['options'] = array( 'colour_dark_paper' => '#ff0000', 'colour_light_ink' => '#336699' );
$css                     = remotive_colours_css();
t_ok( false !== strpos( $css, 'html:not([data-theme="light"]){--wp--preset--color--paper:#ff0000;}' ), 'dark change uses the dark selector' );
t_ok( false !== strpos( $css, 'html[data-theme="light"]{--wp--preset--color--ink:#336699;}' ), 'light change uses the light selector' );
t_ok( false === strpos( $css, 'cyan' ), 'unchanged colours are not written' );

// The block editor gets the same stylesheet.
$settings = remotive_colours_editor_settings( array( 'styles' => array() ) );
t_eq( count( $settings['styles'] ), 1, 'the editor receives the changed colours' );
t_reset();
t_eq( remotive_colours_editor_settings( array( 'styles' => array() ) ), array( 'styles' => array() ), 'and nothing when nothing changed' );

t_done( 'colours' );
