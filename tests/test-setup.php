<?php
/**
 * Setup migrations: the contact address correction.
 * Run: php tests/test-setup.php
 */

require __DIR__ . '/bootstrap.php';
require dirname( __DIR__ ) . '/inc/setup/site-setup.php';

// The retired .com address is replaced, whatever its case or spacing.
foreach ( array( 'hello@remotivemedia.com', ' Hello@RemotiveMedia.com ' ) as $old ) {
	t_reset();
	$GLOBALS['T']['options']['remotive_theme_options'] = array( 'contact_email' => $old, 'legal_name' => 'Keep me' );
	t_ok( remotive_correct_contact_email(), "'$old' replaced" );
	$saved = get_option( 'remotive_theme_options' );
	t_eq( $saved['contact_email'], 'hello@remotivemedia.asia', "'$old' now .asia" );
	t_eq( $saved['legal_name'], 'Keep me', 'other saved options untouched' );
}

// An address an administrator chose on purpose is left alone.
t_reset();
$GLOBALS['T']['options']['remotive_theme_options'] = array( 'contact_email' => 'sales@example.com' );
t_ok( ! remotive_correct_contact_email(), 'custom address not changed' );
t_eq( get_option( 'remotive_theme_options' )['contact_email'], 'sales@example.com', 'custom address kept' );

// Nothing saved yet: nothing to do, and the shipped default is .asia.
t_reset();
t_ok( ! remotive_correct_contact_email(), 'no saved options, no change' );

// The migration is registered at the schema version that ships it.
t_ok( version_compare( REMOTIVE_SETUP_SCHEMA, '1.103.1', '>=' ), 'schema version covers the migration' );


// ---- Seed link helpers (subdirectory installs, and stored text compared with a seed hash) ----
require_once dirname( __DIR__ ) . '/inc/setup/content-seed.php';

$seed = '<p><a href="/services/seo/">SEO</a> <a href="/blog/seo-vs-sem/">vs</a> <a href="https://example.org/x">ext</a> <a href="//cdn.example.org/y">proto</a></p>';

t_ok( remotive_seed_content_to_site( $seed, '' ) === $seed, 'seed links: a site at the domain root is left as seeded' );

$sub = remotive_seed_content_to_site( $seed, '/blog-site' );
t_ok( false !== strpos( $sub, 'href="/blog-site/services/seo/"' ), 'seed links: a subdirectory site gets its base path' );
t_ok( false !== strpos( $sub, 'href="https://example.org/x"' ) && false !== strpos( $sub, 'href="//cdn.example.org/y"' ), 'seed links: absolute and protocol-relative links are untouched' );

// What a site stores after the resolver ran, put back to the seeded form.
$perma  = array( 'seo-vs-sem' => 'https://example.com/2026/09/seo-vs-sem/' );
$stored = str_replace( 'href="/blog/seo-vs-sem/"', 'href="https://example.com/2026/09/seo-vs-sem/"', $seed );
t_ok( remotive_seed_content_from_site( $stored, '', $perma ) === $seed, 'seed links: a resolved permalink is turned back into /blog/slug/ for the hash check' );

$stored_sub = remotive_seed_content_to_site( $stored, '/blog-site' );
t_ok( remotive_seed_content_from_site( $stored_sub, '/blog-site', $perma ) === $seed, 'seed links: base path and permalink are both undone' );
t_ok( md5( remotive_seed_content_from_site( $stored_sub, '/blog-site', $perma ) ) === md5( $seed ), 'seed links: the round trip hashes the same as the seed' );


// ---- The seeded pages are refreshed like the articles ----
require_once dirname( __DIR__ ) . '/inc/setup/content-seed-data.php';
$page_hashes = remotive_old_page_copy_hashes();
$page_slugs  = array();

foreach ( remotive_seed_content() as $item ) {
	if ( 'page' === $item['type'] ) {
		$page_slugs[] = $item['slug'];
		t_ok( isset( $page_hashes[ $item['slug'] ] ), 'page ' . $item['slug'] . ' has earlier-release hashes' );
	}
}

t_eq( count( $page_hashes ), count( $page_slugs ), 'there is one hash entry per seeded page' );
foreach ( $page_hashes as $slug => $fields ) {
	foreach ( $fields as $name => $list ) {
		t_ok( $list && ! array_diff( array_map( function ( $h ) { return preg_match( '/^[a-f0-9]{32}$/', $h ) ? 1 : 0; }, $list ), array( 1 ) ), "$slug / $name: every hash is an md5" );
	}
}
t_ok( '1.119.0' === REMOTIVE_SETUP_SCHEMA, 'the migration schema is 1.119.0' );

t_done( 'setup' );
