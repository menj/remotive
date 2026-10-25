<?php
/**
 * The schema.org layer beyond Google's list: the pure parts.
 *
 * Run: php tests/test-schema-extended.php
 */

require __DIR__ . '/bootstrap.php';
require dirname( __DIR__ ) . '/inc/content/schema-markup.php';
require dirname( __DIR__ ) . '/inc/content/schema-extended.php';

// ---- Durations and reading time ----
t_eq( remotive_schema_duration( 0 ), 'PT0S', 'zero seconds' );
t_eq( remotive_schema_duration( 59 ), 'PT59S', 'under a minute' );
t_eq( remotive_schema_duration( 125 ), 'PT2M5S', 'minutes and seconds' );
t_eq( remotive_schema_duration( 3600 ), 'PT1H', 'a whole hour' );
t_eq( remotive_schema_duration( 3725 ), 'PT1H2M5S', 'hours, minutes, seconds' );
t_eq( remotive_schema_reading_time( 0 ), 'PT1M', 'reading time is never under a minute' );
t_eq( remotive_schema_reading_time( 630 ), 'PT4M', '630 words at 200 a minute, rounded up' );
t_eq( remotive_schema_reading_time( 1200 ), 'PT6M', '1200 words' );

// ---- Text for JSON: tags out, entities decoded ----
t_eq( remotive_schema_text( 'SEO &amp; AI Search' ), 'SEO & AI Search', 'an ampersand entity is decoded' );
t_eq( remotive_schema_text( '<em>Brand&#8217;s</em> growth' ), 'Brand’s growth', 'tags out, a numeric entity decoded' );
t_eq( remotive_schema_text( "  spaced  " ), 'spaced', 'trimmed' );

// ---- The free audit offer: a real offer, a price of zero, anchored on the home page ----
$offer = remotive_schema_free_audit_offer();
t_eq( $offer['@type'], 'Offer', 'it is an Offer' );
t_eq( $offer['@id'], 'https://example.com/#free-audit', 'anchored on the home page so every page shares it' );
t_eq( $offer['price'], '0', 'free' );
t_eq( $offer['priceCurrency'], 'SGD', 'in the home currency' );
t_eq( $offer['offeredBy']['@id'], 'https://example.com/#organization', 'offered by the organisation' );
t_ok( 2 === count( $offer['eligibleRegion'] ), 'for the two markets' );

// ---- The audience ----
$audience = remotive_schema_audience();
t_eq( $audience['@type'], 'BusinessAudience', 'a business audience' );
t_eq( $audience['geographicArea']['name'], 'Asia', 'in Asia' );

// ---- The languages: English plus whatever the language layer has on; here only English ----
t_eq( remotive_schema_languages(), array( 'en' ), 'English alone without the language layer' );

// ---- The organisation gains the rest of schema.org ----
$org = remotive_schema_extend_organization( array(
	'name'    => 'Re:Motive Media',
	'address' => array( '@type' => 'PostalAddress', 'addressCountry' => 'SG' ),
	'logo'    => array( '@type' => 'ImageObject', '@id' => 'https://example.com/#logo' ),
) );
t_eq( $org['brand']['@type'], 'Brand', 'a Brand' );
t_eq( $org['brand']['logo']['@id'], 'https://example.com/#logo', 'with the logo' );
t_ok( ! in_array( 'Re:Motive Media', $org['alternateName'], true ) && in_array( 'Remotive', $org['alternateName'], true ), 'the alternate names leave the site name out' );
t_eq( $org['location']['@type'], 'Place', 'the office is a Place' );
t_eq( $org['location']['address']['addressCountry'], 'SG', 'at the address' );
t_eq( $org['makesOffer']['@id'], 'https://example.com/#free-audit', 'it makes the free audit offer' );
t_ok( in_array( 'Search engine optimisation', $org['knowsAbout'], true ), 'it knows about SEO' );
t_eq( $org['audience']['@type'], 'BusinessAudience', 'and has an audience' );

// ---- The source for every file, so a type is never claimed by name only ----
$src = file_get_contents( dirname( __DIR__ ) . '/inc/content/schema-extended.php' );
foreach ( array( 'Brand', 'BusinessAudience', 'Place', 'Offer', 'OfferCatalog', 'ServiceChannel', 'Blog', 'DataFeed', 'ItemList', 'SiteNavigationElement', 'ImageObject', 'ReadAction', 'Article', 'ItemPage' ) as $type ) {
	t_ok( false !== strpos( $src, "'" . $type . "'" ), "the layer emits $type" );
}
t_ok( false === strpos( $src, 'aggregateRating' ) && false === strpos( $src, "'Review'" ) && false === strpos( $src, 'openingHours' ), 'and never a rating, a review or opening hours the site does not show' );

// ---- The theme owns the graph: a plugin's automatic nodes are removed, an editor's own kept ----
t_ok( remotive_schema_theme_owns_graph(), 'on by default' );
$rm = remotive_schema_strip_rank_math( array(
	'publisher'      => array( '@type' => 'Person' ),
	'WebSite'        => array( '@type' => 'WebSite' ),
	'BreadcrumbList' => array( '@type' => 'BreadcrumbList' ),
	'richSnippet'    => array( '@type' => 'Article' ),
	'schema-123'     => array( '@type' => 'Event', 'name' => 'Launch' ),
) );
t_eq( array_keys( $rm ), array( 'schema-123' ), 'only the editor-attached schema survives' );
t_eq( remotive_schema_strip_yoast( array( 'x' ) ), false, 'Yoast prints nothing' );
t_eq( remotive_schema_plugin_managed_types(), array(), 'no type is yielded to a plugin' );

t_done( 'schema-extended' );
