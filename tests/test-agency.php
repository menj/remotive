<?php
/**
 * The single-page agency layout: defaults, sanitising, redirect rules and the page.
 * Run: php tests/test-agency.php
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

require dirname( __DIR__ ) . '/inc/options/colours.php';
require dirname( __DIR__ ) . '/inc/options/agency-layout.php';
require dirname( __DIR__ ) . '/inc/options/theme-options.php';
require dirname( __DIR__ ) . '/inc/landing/landing-copy.php';
require dirname( __DIR__ ) . '/inc/landing/landing-pages.php';
require dirname( __DIR__ ) . '/inc/landing/agency-page.php';

function t_options( array $o ) {
	t_reset();
	$GLOBALS['T']['options']['remotive_theme_options'] = $o;
}

// ---- Off by default ----
t_reset();
$d = remotive_agency_option_defaults();
t_eq( $d['agency_mode'], '0', 'the agency layout is off by default' );
t_eq( $d['agency_hide_pages'], '1', 'hiding the other pages is on by default (once the layout is on)' );
t_ok( ! remotive_agency_mode(), 'mode reads as off on a fresh site' );
t_ok( ! remotive_agency_front(), 'the home page is not the agency page while the switch is off' );
t_eq( $d['agency_line_1'], 'Performance marketing, media & SEO', 'headline line 1 is the brief\'s' );
t_eq( $d['agency_line_2'], 'built to scale Asian brands, globally.', 'headline line 2 is the brief\'s' );
t_ok( 0 === strpos( $d['agency_lead'], 'We are a senior-led independent media team' ) && false !== strpos( $d['agency_lead'], 'across Asia and beyond.' ), 'the introduction is the brief\'s paragraph' );
t_ok( isset( remotive_theme_option_defaults()['agency_mode'] ), 'the agency options are part of the theme defaults' );
t_ok( isset( remotive_theme_options_tabs()['agency'] ), 'there is an Agency layout tab' );
t_eq( array_keys( remotive_theme_options_tabs() )[1], 'agency', 'the tab sits straight after Homepage' );

// ---- The words the brief asked to remove are not in the defaults ----
$all = strtolower( json_encode( $d ) );
foreach ( array( 'show me a growth plan', 'view our capabilities', '30 minutes', 'reply in one business day', 'ticker' ) as $banned ) {
	t_ok( false === strpos( $all, $banned ), "no \"$banned\" in the agency copy" );
}

// ---- Sanitising ----
$clean = array();
remotive_sanitize_agency_options( array(), $clean );
t_eq( $clean['agency_mode'], '0', 'an unticked switch saves as off' );
t_eq( $clean['agency_hide_pages'], '0', 'an unticked "other pages" box saves as off' );
t_eq( $clean['agency_line_1'], $d['agency_line_1'], 'a missing headline falls back to the default' );

$clean = array();
remotive_sanitize_agency_options( array( 'agency_mode' => '1', 'agency_line_1' => '  ', 'agency_eyebrow' => '', 'agency_team_size' => 'huge', 'agency_svc_2_title' => '', 'agency_lead' => '<b>Hello</b>' ), $clean );
t_eq( $clean['agency_mode'], '1', 'a ticked switch saves as on' );
t_eq( $clean['agency_line_1'], $d['agency_line_1'], 'a blank headline is replaced by the default, never left empty' );
t_eq( $clean['agency_eyebrow'], '', 'the eyebrow may be blank' );
t_eq( $clean['agency_team_size'], 'sm', 'an unknown portrait size falls back to small' );
t_eq( $clean['agency_svc_2_title'], '', 'a service with no title is kept blank (it is hidden)' );
t_eq( $clean['agency_lead'], 'Hello', 'markup is stripped from the introduction' );

// ---- Which requests are sent to the home page ----
$base = array( 'mode' => true, 'hide' => true, 'front' => false, 'content' => true, 'landing' => false, 'legal' => false, 'preview' => false );
t_ok( remotive_agency_should_redirect( $base ), 'an ordinary page goes to the home page' );
t_ok( ! remotive_agency_should_redirect( array_merge( $base, array( 'mode' => false ) ) ), 'nothing is redirected with the switch off' );
t_ok( ! remotive_agency_should_redirect( array_merge( $base, array( 'hide' => false ) ) ), 'nothing is redirected if the other pages are left live' );
t_ok( ! remotive_agency_should_redirect( array_merge( $base, array( 'front' => true ) ) ), 'the home page stays' );
t_ok( ! remotive_agency_should_redirect( array_merge( $base, array( 'landing' => true ) ) ), 'the ad landing pages stay' );
t_ok( ! remotive_agency_should_redirect( array_merge( $base, array( 'legal' => true ) ) ), 'the legal pages stay' );
t_ok( ! remotive_agency_should_redirect( array_merge( $base, array( 'preview' => true ) ) ), 'an editor\'s preview is not redirected' );
t_ok( ! remotive_agency_should_redirect( array_merge( $base, array( 'content' => false ) ) ), 'a request that is not content (a feed, a sitemap) is left alone' );

// ---- The agency page as a landing "service" ----
t_options( array() );
t_ok( null !== remotive_lp_service( 'agency' ), 'the shared form knows the agency page' );
t_ok( null !== remotive_lp_service( 'seo-audit' ), 'and still knows the ad landing pages' );
t_ok( null === remotive_lp_service( 'nonsense' ), 'an unknown service is rejected' );

// ---- The page ----
t_options( array( 'agency_mode' => '1' ) );
t_ok( remotive_agency_front(), 'the home page is the agency page when the switch is on' );
$html = remotive_agency_render();
t_eq( substr_count( $html, '<form ' ), 2, 'two contact forms: top and bottom' );
t_ok( false !== strpos( $html, 'id="rm-lp-top-name"' ) && false !== strpos( $html, 'id="rm-lp-bottom-name"' ), 'the two forms have distinct field ids' );
t_ok( false !== strpos( $html, 'name="service" value="agency"' ), 'the forms post as the agency service' );
t_eq( substr_count( $html, '<h1>' ), 1, 'one h1' );
t_ok( false !== strpos( $html, 'Performance marketing, media &amp; SEO<br> built to scale Asian brands, globally.' ), 'the headline is on the page' );
t_eq( substr_count( $html, 'class="rm-ag__card"' ), 6, 'six service cards' );
t_ok( false === strpos( $html, 'rm-ticker' ), 'no ticker' );
t_ok( false === strpos( $html, 'rm-hero__actions' ) && false === stripos( $html, 'growth plan' ), 'no hero buttons' );
t_ok( false === strpos( $html, 'wp-block-navigation' ) && substr_count( $html, '<nav' ) <= 1, 'no menu (the only nav is the language links)' );
t_ok( false !== strpos( $html, 'We reply within three business days. No mailing list.' ), 'the line under the form is the agency\'s own' );
t_ok( false === strpos( $html, 'Free. No commitment' ), 'not the ad pages\' line' );

// A form comes before the services, so it is above the first scroll.
t_ok( strpos( $html, 'id="rm-lp-start"' ) < strpos( $html, 'rm-ag__services' ), 'the first form comes before the services' );

// Switching parts off.
t_options( array( 'agency_mode' => '1', 'agency_show_services' => '0', 'agency_show_team' => '0' ) );
$html = remotive_agency_render();
t_ok( false === strpos( $html, 'rm-ag__services' ) && false === strpos( $html, 'rm-ag__team' ), 'the services and team sections can be switched off' );
t_eq( substr_count( $html, '<form ' ), 2, 'both forms remain' );

// A service with no title is hidden; the rest renumber.
t_options( array( 'agency_mode' => '1', 'agency_svc_2_title' => '' ) );
$html = remotive_agency_render();
t_eq( substr_count( $html, 'class="rm-ag__card"' ), 5, 'a blank title hides that card' );
t_ok( false !== strpos( $html, '>05<' ) && false === strpos( $html, '>06<' ), 'the numbers run on without a gap' );

// Escaping.
t_options( array( 'agency_mode' => '1', 'agency_line_1' => '<script>alert(1)</script>' ) );
t_ok( false === strpos( remotive_agency_render(), '<script>alert' ), 'copy is escaped' );

// Team.
t_options( array( 'agency_mode' => '1', 'agency_team_size' => 'xs', 'team' => array( array( 'name' => 'Ada Lovelace', 'role' => 'Engineer', 'slug' => 'ada', 'bio' => '', 'home' => '1' ) ) ) );
$html = remotive_agency_render();
t_ok( false !== strpos( $html, 'data-person="ada"' ) && false !== strpos( $html, '>AL<' ), 'a team member has a portrait slot with initials' );
t_ok( false !== strpos( $html, '--ag-photo:48px' ), 'the portrait size follows the setting' );

// ---- Landing page links on the service cards ----
t_options( array( 'agency_mode' => '1' ) );
$html = remotive_agency_render();
t_eq( substr_count( $html, 'class="rm-ag__go"' ), 3, 'three service cards link to a landing page (SEO, Google Ads, paid social)' );
t_ok( false !== strpos( $html, 'Get my free SEO audit' ), 'the SEO card uses the landing page\'s own button text' );
t_eq( array_keys( remotive_agency_service_pages() ), array( 1, 2, 3 ), 'only the first three services have a landing page' );
t_options( array( 'agency_mode' => '1', 'agency_link_services' => '0' ) );
t_ok( false === strpos( remotive_agency_render(), 'rm-ag__go' ), 'the links can be switched off' );

// ---- Questions ----
t_options( array( 'agency_mode' => '1' ) );
$html = remotive_agency_render();
t_eq( substr_count( $html, '<details class="rm-lp__q">' ), 6, 'six questions' );
t_ok( strpos( $html, 'rm-ag__team' ) < strpos( $html, 'rm-ag__faq' ) && strpos( $html, 'rm-ag__faq' ) < strpos( $html, 'rm-lp__final' ), 'the questions sit between the team and the closing form' );
t_options( array( 'agency_mode' => '1', 'agency_faq_2_q' => '' ) );
t_eq( substr_count( remotive_agency_render(), '<details class="rm-lp__q">' ), 5, 'a blank question is hidden' );
t_options( array( 'agency_mode' => '1', 'agency_show_faq' => '0' ) );
t_ok( false === strpos( remotive_agency_render(), 'rm-ag__faq' ), 'the questions section can be switched off' );
ob_start();
t_options( array( 'agency_mode' => '1' ) );
remotive_agency_faq_schema();
$schema = ob_get_clean();
t_ok( false !== strpos( $schema, '"@type":"FAQPage"' ) && 6 === substr_count( $schema, '"@type":"Question"' ), 'FAQ structured data matches the six questions' );

// ---- The entry guard lets the agency home page through, and nothing else ----
$srv = function ( $referer ) { return array( 'HTTP_REFERER' => $referer, 'HTTP_SEC_FETCH_SITE' => 'same-origin' ); };
t_options( array( 'agency_mode' => '1' ) );
t_ok( ! remotive_lp_is_internal_navigation( $srv( 'https://example.com/' ), 'example.com', '' ), 'a click from the agency home page reaches a landing page' );
t_ok( ! remotive_lp_is_internal_navigation( $srv( 'https://example.com/ms/' ), 'example.com', '' ), 'and from its Malay version' );
t_ok( remotive_lp_is_internal_navigation( $srv( 'https://example.com/about/' ), 'example.com', '' ), 'a click from any other page is still turned back' );
t_options( array() );
t_ok( remotive_lp_is_internal_navigation( $srv( 'https://example.com/' ), 'example.com', '' ), 'with the switch off, the home page is turned back too, as before' );

// ---- Section ids must not match the ids that remotive.css styles for the full version's home page ----
t_options( array( 'agency_mode' => '1' ) );
$html = remotive_agency_render();
foreach ( array( 'services', 'work', 'problem', 'why', 'about', 'contact' ) as $taken ) {
	t_ok( false === strpos( $html, 'id="' . $taken . '"' ), "the agency page has no id=\"$taken\" (remotive.css widens and centres it)" );
}

// ---- How we work and Signs ----
t_options( array( 'agency_mode' => '1' ) );
$html = remotive_agency_render();
t_ok( false !== strpos( $html, 'id="ag-process"' ) && 4 === substr_count( $html, 'class="rm-ag__step"' ), 'the page shows four numbered steps' );
t_ok( false !== strpos( $html, 'id="ag-signs"' ) && 6 === substr_count( $html, '<li>Traffic' ) + substr_count( $html, '<li>Ad spend' ) + substr_count( $html, '<li>Lead volume' ) + substr_count( $html, '<li>Reports show' ) + substr_count( $html, '<li>Competitors' ) + substr_count( $html, '<li>Search, paid' ), 'the page shows the six signs' );
t_ok( strpos( $html, 'id="ag-services"' ) < strpos( $html, 'id="ag-process"' ) && strpos( $html, 'id="ag-process"' ) < strpos( $html, 'id="ag-signs"' ) && strpos( $html, 'id="ag-signs"' ) < strpos( $html, 'id="ag-team"' ), 'the order is services, steps, signs, team' );
t_options( array( 'agency_mode' => '1', 'agency_show_process' => '0', 'agency_show_signs' => '0' ) );
$html = remotive_agency_render();
t_ok( false === strpos( $html, 'id="ag-process"' ) && false === strpos( $html, 'id="ag-signs"' ), 'the two sections switch off' );
t_options( array( 'agency_mode' => '1', 'agency_proc_2_title' => '', 'agency_sign_1' => '' ) );
$html = remotive_agency_render();
t_eq( substr_count( $html, 'class="rm-ag__step"' ), 3, 'a step with no title is hidden' );
t_ok( false === strpos( $html, 'Traffic is flat' ), 'a blank sign is hidden' );
$clean = array();
remotive_sanitize_agency_options( array( 'agency_proc_1_title' => '<b>Look</b>', 'agency_sign_2' => '', 'agency_signs_heading' => '' ), $clean );
t_eq( $clean['agency_proc_1_title'], 'Look', 'markup is stripped from a step title' );
t_eq( $clean['agency_sign_2'], '', 'a sign may be left blank' );
t_eq( $clean['agency_signs_heading'], 'Signs it is time to talk', 'a blank section heading falls back to the default' );
t_eq( $clean['agency_show_process'], '0', 'an unticked section switch saves as off' );


// ---- A blank form submission URL falls back to the built-in handler ----
if ( ! function_exists( 'sanitize_email' ) ) { function sanitize_email( $e ) { return trim( (string) $e ); } }
if ( ! function_exists( 'is_email' ) ) { function is_email( $e ) { return false !== strpos( (string) $e, '@' ); } }
$d = remotive_theme_option_defaults();
foreach ( array( '', '#', '   ' ) as $blank ) {
	$clean = remotive_sanitize_theme_options( array( 'cta_form_action' => $blank ) );
	t_eq( $clean['cta_form_action'], $d['cta_form_action'], 'a form action of ' . json_encode( $blank ) . ' falls back to the default, so enquiries are not lost' );
}
$clean = remotive_sanitize_theme_options( array( 'cta_form_action' => 'https://forms.example.com/x' ) );
t_eq( $clean['cta_form_action'], 'https://forms.example.com/x', 'a real endpoint is kept' );


// ---- Which requests count as content the redirect may send home ----
$none = array( 'singular' => false, 'home' => false, 'queried_id' => 0, 'archive' => false, 'search' => false, 'attachment' => false, 'sitemap' => false );
t_ok( remotive_agency_is_content_request( array_merge( $none, array( 'singular' => true ) ) ), 'a page or post is content' );
t_ok( remotive_agency_is_content_request( array_merge( $none, array( 'home' => true, 'queried_id' => 12 ) ) ), 'the posts page (it has a queried page) is content' );
t_ok( ! remotive_agency_is_content_request( array_merge( $none, array( 'home' => true, 'queried_id' => 0 ) ) ), 'a latest-posts front with no page behind it is not content, so "/" cannot redirect to itself' );
t_ok( ! remotive_agency_is_content_request( array_merge( $none, array( 'home' => true, 'queried_id' => 12, 'sitemap' => true ) ) ), 'a core sitemap request is left to core' );
t_ok( remotive_agency_is_content_request( array_merge( $none, array( 'archive' => true ) ) ) && remotive_agency_is_content_request( array_merge( $none, array( 'search' => true ) ) ) && remotive_agency_is_content_request( array_merge( $none, array( 'attachment' => true ) ) ), 'archives, search and attachments are content' );
t_ok( ! remotive_agency_is_content_request( $none ), 'nothing set is not content' );

t_done( 'agency' );
