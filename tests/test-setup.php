<?php
/**
 * Setup migrations: the contact address correction.
 * Run: php tests/test-setup.php
 */

require __DIR__ . '/bootstrap.php';

// remotive_update_options_raw() lifts the settings sanitiser off for the save; no sanitiser is attached here.
if ( ! function_exists( 'has_filter' ) ) { function has_filter() { return false; } }
if ( ! function_exists( 'remove_filter' ) ) { function remove_filter() {} }

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
t_ok( '1.120.6' === REMOTIVE_SETUP_SCHEMA, 'the migration schema is 1.120.6' );
t_ok( isset( remotive_migration_registry()['1.120.6'] ), 'the seed repair migration is registered' );

// ---- Block markup that opens what it closes ----
t_ok( remotive_seed_blocks_balanced( "<!-- wp:paragraph -->\n<p>a</p>\n<!-- /wp:paragraph -->" ), 'a closed paragraph balances' );
t_ok( ! remotive_seed_blocks_balanced( "<!-- wp:paragraph -->\n<p>a</p>\n<!-- wp:heading -->\n<h2>b</h2>\n<!-- /wp:heading -->" ), 'an unclosed paragraph does not' );
t_ok( remotive_seed_blocks_balanced( '<!-- wp:post-title {"level":1} /-->' ), 'a self-closing block counts as both' );
t_ok( ! remotive_seed_blocks_balanced( "<!-- wp:paragraph --><p>a</p><!-- /wp:heading -->" ), 'a closing marker of another block does not balance an open one (a CodeAnt point)' );
t_ok( ! remotive_seed_blocks_balanced( "<!-- /wp:paragraph --><!-- wp:paragraph --><p>a</p>" ), 'nor do the markers in the wrong order' );
t_ok( ! remotive_seed_blocks_balanced( "<!-- wp:list --><!-- wp:list-item --><li>a</li><!-- /wp:list --><!-- /wp:list-item -->" ), 'nor closings that cross' );
t_ok( remotive_seed_blocks_balanced( "<!-- wp:list --><!-- wp:list-item --><li>a</li><!-- /wp:list-item --><!-- /wp:list -->" ), 'properly nested blocks balance' );
t_ok( remotive_seed_blocks_balanced( '' ), 'empty text balances' );
foreach ( remotive_seed_content() as $item ) {
	t_ok( remotive_seed_blocks_balanced( $item['content'] ?? '' ), 'shipped markup balances: ' . $item['slug'] );
}
// The repaired copies carry the hash of the text they replace, so an unedited broken copy is found.
$old_hashes = remotive_old_page_copy_hashes();
foreach ( array( 'social', 'analytics', 'email', 'content', 'seo', 'cookieless-audience-sports' ) as $slug ) {
	t_ok( isset( $old_hashes[ $slug ]['content'] ) && count( $old_hashes[ $slug ]['content'] ) >= 2, "$slug lists the broken copy's hash" );
}
t_ok( isset( remotive_migration_registry()['1.120.4'] ), 'the category description migration is registered' );
foreach ( array( 'seo-ai-search', 'paid-media', 'agency-guides' ) as $slug ) {
	$d = remotive_seed_category_description( $slug );
	t_ok( strlen( $d ) >= 70 && strlen( $d ) <= 160, "$slug has a description of meta length (" . strlen( $d ) . ')' );
}
t_eq( remotive_seed_category_description( 'uncategorized' ), '', 'an unknown category gets none' );

// ---- 1.120.3: the old default colour mode (dark) moves to light, once; other choices are kept ----
t_reset();
$GLOBALS['T']['options']['remotive_theme_options'] = array( 'default_theme' => 'dark', 'motion_effects' => '0' );
t_ok( remotive_migrate_default_colour_mode(), 'the migration reports done' );
t_eq( $GLOBALS['T']['options']['remotive_theme_options'], array( 'default_theme' => 'light', 'motion_effects' => '0' ), 'dark becomes light and nothing else changes' );
$GLOBALS['T']['options']['remotive_theme_options'] = array( 'default_theme' => 'system' );
remotive_migrate_default_colour_mode();
t_eq( $GLOBALS['T']['options']['remotive_theme_options']['default_theme'], 'system', 'a deliberate "system" choice is kept' );
unset( $GLOBALS['T']['options']['remotive_theme_options'] );
t_ok( remotive_migrate_default_colour_mode(), 'a site that never saved its options needs nothing' );
t_ok( isset( remotive_migration_registry()['1.120.3'] ), 'the migration is registered' );


// ---- A seeded page that declares a parent is looked for under that parent only ----
// The bootstrap's get_page_by_path() answers every path with page 5.
$args = remotive_seed_find_args( 'seo', 'page', 'services' );
t_eq( $args['post_parent'], 5, 'the lookup is limited to the parent page' );
t_eq( $args['name'], 'seo', 'and to the slug' );
t_ok( ! isset( remotive_seed_find_args( 'about', 'page' )['post_parent'] ), 'a top-level item has no parent condition' );

// ---- A renamed or binned parent: nothing is matched (an unrelated nested page with the slug is not the
// seeded one) and nothing is created (a child without its parent would be a top-level duplicate) ----
$GLOBALS['T']['no_page'] = true;
t_eq( remotive_seed_find_args( 'seo', 'page', 'services' ), null, 'with the parent missing no slug match is trusted' );
t_eq( remotive_seed_find( 'seo', 'page', 'services' ), null, 'and the lookup finds nothing' );
$src = file_get_contents( dirname( __DIR__ ) . '/inc/setup/content-seed.php' );
t_ok( 2 === preg_match_all( '/if \( ! \$parent \) \{\s*\n\s*(continue|return false)/', $src ), 'the seeder and the restore both stop at a missing parent' );
$GLOBALS['T']['no_page'] = false;

// ---- Overlapping admin loads: the second one waits while the first holds the lock ----
t_reset();
$GLOBALS['T']['options'][ REMOTIVE_SETUP_FLAG ] = '1.0.0';
t_ok( remotive_setup_lock_acquire(), 'the first request takes the lock' );
t_ok( ! remotive_setup_lock_acquire(), 'the second does not' );
remotive_maybe_auto_setup();
t_eq( $GLOBALS['T']['options'][ REMOTIVE_SETUP_FLAG ], '1.0.0', 'a locked setup runs nothing' );
remotive_setup_lock_release();
t_ok( remotive_setup_lock_acquire(), 'released, it can be taken again' );
// A request that lost the lock (its token is no longer the one stored) must not clear the new owner's.
$mine = $GLOBALS['remotive_setup_lock_token'];
set_transient( REMOTIVE_SETUP_LOCK, 'someone-else', 600 );
remotive_setup_lock_release();
t_eq( get_transient( REMOTIVE_SETUP_LOCK ), 'someone-else', 'a release only clears the lock the request itself holds' );
delete_transient( REMOTIVE_SETUP_LOCK );
$src = file_get_contents( dirname( __DIR__ ) . '/inc/setup/site-setup.php' );
t_ok( false !== strpos( $src, "'option_name' => REMOTIVE_SETUP_LOCK, 'option_value' => \$held" ), 'with a database the lock is taken by one compare-and-swap on the row' );
t_ok( function_exists( 'remotive_run_pending_migrations' ), 'the migrations run through their own function under the lock' );

// ---- The fatal page is printed once: the drop-in steps aside when the theme's handler is active ----
$dropin = file_get_contents( dirname( __DIR__ ) . '/drop-ins/php-error.php' );
t_ok( false !== strpos( $dropin, "defined( 'REMOTIVE_ERROR_HANDLER' )" ), 'the drop-in checks for the theme handler' );
t_ok( false !== strpos( file_get_contents( dirname( __DIR__ ) . '/inc/core/error-handler.php' ), "define( 'REMOTIVE_ERROR_HANDLER', true )" ), 'the theme handler declares itself' );
t_ok( false === strpos( file_get_contents( dirname( __DIR__ ) . '/inc/core/error-handler.php' ), 'wp_fatal_error_handler_enabled' ), 'the filter a theme cannot use is gone' );

// ---- The development folders are removed from an installed theme, never from a checkout ----
$tmp = sys_get_temp_dir() . '/remotive-dev-' . getmypid();
foreach ( array( '/tests', '/tests/sub', '/.github/workflows', '/inc' ) as $d ) {
	mkdir( $tmp . $d, 0777, true );
}
file_put_contents( $tmp . '/tests/a.php', 'a' );
file_put_contents( $tmp . '/tests/sub/b.php', 'b' );
file_put_contents( $tmp . '/.github/workflows/ci.yml', 'c' );
file_put_contents( $tmp . '/inc/keep.php', 'k' );
t_ok( remotive_remove_dev_files( $tmp ) >= 6, 'the test and workflow folders go' );
t_ok( ! is_dir( $tmp . '/tests' ) && ! is_dir( $tmp . '/.github' ), 'both folders are gone' );
t_ok( is_file( $tmp . '/inc/keep.php' ), 'the theme itself is untouched' );
mkdir( $tmp . '/tests' );
mkdir( $tmp . '/.git' );
t_eq( remotive_remove_dev_files( $tmp ), 0, 'a git checkout keeps its tests' );
t_ok( is_dir( $tmp . '/tests' ), 'and the folder is still there' );
rmdir( $tmp . '/.git' );
rmdir( $tmp . '/tests' );
unlink( $tmp . '/inc/keep.php' );
rmdir( $tmp . '/inc' );
rmdir( $tmp );
t_eq( remotive_remove_dev_files( '/nonexistent-' . getmypid() ), 0, 'a missing folder is nothing to do' );
t_eq( remotive_dev_folders(), array( 'tests', '.github' ), 'only the two development folders are ever named' );
$src = file_get_contents( dirname( __DIR__ ) . '/inc/setup/site-setup.php' );
t_ok( false !== strpos( $src, "'remotive_remove_dev_files',\n" ), 'the removal runs with the version sync, on activation and after an upload' );

t_done( 'setup' );
