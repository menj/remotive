<?php
/**
 * Structured data for the page furniture and the sections of both layouts.
 * Run: php tests/test-schema-elements.php
 */

require __DIR__ . '/bootstrap.php';

function remotive_i18n_lang() { return 'en'; }
function sanitize_textarea_field( $s ) { return trim( strip_tags( (string) $s ) ); }
function is_front_page() { return $GLOBALS['T']['front'] ?? true; }
function is_paged() { return false; }
function wp_parse_url( $u, $c = -1 ) { return parse_url( $u, $c ); }
function get_post_status( $p ) { return 'publish'; }
function date_i18n( $f ) { return gmdate( $f ); }
function get_bloginfo( $k = '' ) { return 'Re:Motive Media'; }
function wp_parse_args( $a, $d = array() ) { return array_merge( $d, (array) $a ); }
function wp_kses( $s ) { return $s; }
function _n( $a, $b, $n ) { return 1 === $n ? $a : $b; }
function is_home() { return false; }
function is_archive() { return false; }
function is_search() { return false; }
function is_singular() { return false; }
function sanitize_title( $s ) { return strtolower( preg_replace( '/[^a-z0-9]+/i', '-', $s ) ); }
function remotive_schema_current_url() { return 'https://example.com/'; }

require dirname( __DIR__ ) . '/inc/options/colours.php';
require dirname( __DIR__ ) . '/inc/options/agency-layout.php';
require dirname( __DIR__ ) . '/inc/options/theme-options.php';
require dirname( __DIR__ ) . '/inc/landing/landing-copy.php';
require dirname( __DIR__ ) . '/inc/landing/landing-pages.php';
require dirname( __DIR__ ) . '/inc/landing/agency-page.php';
require dirname( __DIR__ ) . '/inc/content/schema-elements.php';

function t_nodes( array $nodes, $type ) {
	return array_values( array_filter( $nodes, function ( $n ) use ( $type ) { return in_array( $type, (array) $n['@type'], true ); } ) );
}

function t_selectors( array $nodes ) {
	$out = array();
	foreach ( $nodes as $n ) {
		if ( isset( $n['cssSelector'] ) ) {
			$out[] = $n['cssSelector'];
		}
	}
	return $out;
}

// ---- The full layout: header, sidebar and footer ----
$GLOBALS['T']['template'] = false;
$site = remotive_schema_site_elements( 'https://example.com/blog/' );
t_eq( array_column( $site, '@type' ), array( 'WPHeader', 'WPFooter' ), 'a page without a sidebar gets a header and a footer' );
t_eq( t_selectors( $site ), array( '.rm-nav', '.rm-footer' ), 'the selectors match the template parts' );
t_eq( $site[0]['isPartOf'], array( '@id' => 'https://example.com/blog/#webpage' ), 'each element belongs to the page node' );
t_eq( $site[0]['@id'], 'https://example.com/blog/#header', 'the element has a stable @id' );

$GLOBALS['T']['template'] = true;
$with = remotive_schema_site_elements( 'https://example.com/contact/' );
t_eq( array_column( $with, '@type' ), array( 'WPHeader', 'WPSideBar', 'WPFooter' ), 'a page with a sidebar lists it too' );

// ---- The agency page ----
t_reset();
$GLOBALS['T']['options']['remotive_theme_options'] = array( 'agency_mode' => '1' );
$nodes = remotive_schema_agency_nodes( 'https://example.com/' );

t_eq( count( t_nodes( $nodes, 'Service' ) ), 6, 'the six services are Service nodes' );
$list = t_nodes( $nodes, 'ItemList' );
t_eq( $list[0]['numberOfItems'], 6, 'the services sit in an ItemList of six' );
t_eq( $list[0]['itemListElement'][0]['item']['@id'], 'https://example.com/#service-1', 'each list entry points at its Service' );
foreach ( t_nodes( $nodes, 'Service' ) as $svc ) {
	t_eq( $svc['provider'], array( '@id' => 'https://example.com/#organization' ), $svc['name'] . ': provider is the organisation' );
}

$how = t_nodes( $nodes, 'HowTo' );
t_eq( count( $how[0]['step'] ), 4, 'the process is a HowTo with four steps' );
t_eq( $how[0]['step'][0], array( '@type' => 'HowToStep', 'position' => 1, 'name' => 'Audit', 'text' => 'We review your site, tracking, accounts and competitors, and list what holds growth back.' ), 'a step carries its position, name and text' );

$signs = array_values( array_filter( t_nodes( $nodes, 'ItemList' ), function ( $n ) { return false !== strpos( $n['@id'], '#signs' ); } ) );
t_eq( $signs[0]['numberOfItems'], 6, 'the checklist is an ItemList of six' );
t_eq( $signs[0]['itemListElement'][0]['name'], 'Traffic is flat or falling, and nobody can say why.', 'a checklist entry carries its text' );

$sel = t_selectors( $nodes );
foreach ( array( '.rm-lp__bar', '.rm-lp__hero', '#services', '#process', '#signs', '#team', '#questions', '.rm-lp__final', '.rm-ag__foot' ) as $s ) {
	t_ok( in_array( $s, $sel, true ), "the section $s is marked as an element" );
}

// Every selector points at something the page prints.
$html = remotive_agency_render();
foreach ( array( '.rm-lp__bar' => 'class="rm-lp__bar"', '.rm-lp__hero' => 'class="rm-lp__hero"', '#services' => 'id="services"', '#process' => 'id="process"', '#signs' => 'id="signs"', '#team' => 'id="team"', '#questions' => 'id="questions"', '.rm-lp__final' => 'rm-lp__final', '.rm-ag__foot' => 'class="rm-ag__foot"' ) as $selector => $needle ) {
	t_ok( false !== strpos( $html, $needle ), "the page prints the element that $selector finds" );
}

// A hidden section leaves no schema behind.
$GLOBALS['T']['options']['remotive_theme_options'] = array( 'agency_mode' => '1', 'agency_show_process' => '0', 'agency_show_signs' => '0', 'agency_show_services' => '0' );
$nodes = remotive_schema_agency_nodes( 'https://example.com/' );
t_eq( count( t_nodes( $nodes, 'HowTo' ) ), 0, 'with the steps hidden there is no HowTo' );
t_eq( count( t_nodes( $nodes, 'Service' ) ), 0, 'with the services hidden there are no Service nodes' );
t_ok( ! in_array( '#signs', t_selectors( $nodes ), true ), 'with the checklist hidden its element is gone' );

// A blank step is skipped, as it is on the page.
$GLOBALS['T']['options']['remotive_theme_options'] = array( 'agency_mode' => '1', 'agency_proc_2_title' => '' );
t_eq( count( t_nodes( remotive_schema_agency_nodes( 'https://example.com/' ), 'HowTo' )[0]['step'] ), 3, 'a step with no title is left out of the HowTo' );

// The three generic services of the full layout do not appear on the agency page.
$GLOBALS['T']['options']['remotive_theme_options'] = array( 'agency_mode' => '1' );
t_eq( remotive_schema_agency_replace_services( array( 'x' ) ), array(), 'the full layout\'s service nodes yield on the agency page' );
$GLOBALS['T']['options']['remotive_theme_options'] = array( 'agency_mode' => '0' );
t_eq( remotive_schema_agency_replace_services( array( 'x' ) ), array( 'x' ), 'with the switch off they are left as they were' );

// Everything encodes to valid JSON.
t_ok( null !== json_decode( json_encode( array_merge( $site, $nodes ) ) ), 'the nodes encode to JSON' );

t_done( 'schema-elements' );
