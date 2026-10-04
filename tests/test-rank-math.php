<?php
/**
 * Rank Math compatibility: the theme's rules reach the plugin's own filters.
 * Run: php tests/test-rank-math.php
 */

require __DIR__ . '/bootstrap.php';
// Stands in for the language layer, which reads the URL's language directory.
function remotive_i18n_lang() {
	return $GLOBALS['remotive_i18n_lang'] ?? 'en';
}

require dirname( __DIR__ ) . '/inc/landing/landing-pages.php';
require dirname( __DIR__ ) . '/inc/forms/thank-you.php';
require dirname( __DIR__ ) . '/inc/core/rank-math.php';

// The filters are registered on the plugin's hook names (not core's, which it discards).
foreach ( array( 'rank_math/frontend/robots', 'rank_math/frontend/canonical', 'rank_math/sitemap/entry' ) as $hook ) {
	t_ok( ! empty( $GLOBALS['filters'][ $hook ] ), "$hook is hooked" );
}

// Landing language keeps its own canonical.
t_reset();
$GLOBALS['remotive_i18n_lang'] = 'ms';
$GLOBALS['T']['slug']      = 'seo-audit';
t_eq( remotive_rank_math_canonical( 'https://example.com/seo-audit/' ), 'https://example.com/ms/seo-audit/', 'Malay canonical' );
$GLOBALS['remotive_i18n_lang'] = 'en';
t_eq( remotive_rank_math_canonical( 'https://example.com/seo-audit/' ), 'https://example.com/seo-audit/', 'English canonical' );

// A page that is not a landing page keeps the plugin's canonical.
t_reset();
$GLOBALS['T']['template'] = false;
t_eq( remotive_rank_math_canonical( 'https://example.com/about/' ), 'https://example.com/about/', 'canonical untouched off the landing pages' );

// Confirmation page: noindex but follow; the landing confirmation is left to the landing rules.
t_reset();
$GLOBALS['T']['template'] = false;
$r = remotive_rank_math_thanks_robots( array( 'index' => 'index', 'follow' => 'follow' ) );
t_eq( $r, array( 'index' => 'noindex', 'follow' => 'follow' ), 'thank-you page noindex, follow' );
$GLOBALS['T']['template'] = true;
$r = remotive_rank_math_thanks_robots( array( 'index' => 'index', 'follow' => 'follow' ) );
t_eq( $r, array( 'index' => 'index', 'follow' => 'follow' ), 'landing page handled by the landing filter, not this one' );

// Sitemap entries for the hidden pages are dropped; others pass.
t_reset();
$GLOBALS['T']['landing_ids'] = array( 11, 12 );
$entry = array( 'loc' => 'https://example.com/x/' );
t_eq( remotive_rank_math_sitemap_entry( $entry, 'post', (object) array( 'ID' => 11 ) ), false, 'landing page dropped from sitemap' );
t_eq( remotive_rank_math_sitemap_entry( $entry, 'post', (object) array( 'ID' => 5 ) ), false, 'confirmation page dropped (stub page id 5)' );
t_eq( remotive_rank_math_sitemap_entry( $entry, 'post', (object) array( 'ID' => 99 ) ), $entry, 'ordinary page kept' );
t_eq( remotive_rank_math_sitemap_entry( $entry, 'term', null ), $entry, 'non-post entries kept' );

t_done( 'rank-math' );
