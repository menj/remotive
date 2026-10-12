<?php
/**
 * Rescue of date style article addresses and icon requests.
 * Run: php tests/test-not-found.php
 */

require __DIR__ . '/bootstrap.php';
require dirname( __DIR__ ) . '/inc/core/not-found.php';

foreach ( array(
	'/2026/09/seo-vs-sem/'                => array( 'post', 'seo-vs-sem' ),
	'/2026/9/how-to-choose-an-seo-agency' => array( 'post', 'how-to-choose-an-seo-agency' ),
	'/2025/12/Facebook-Advertising/'       => array( 'post', 'facebook-advertising' ),
	'/favicon.ico'                         => array( 'icon', '' ),
	'/favicon.png'                         => array( 'icon', '' ),
	'/apple-touch-icon.png'                => array( 'icon', '' ),
	'/apple-touch-icon-precomposed.png'    => array( 'icon', '' ),
) as $path => $want ) {
	t_eq( remotive_not_found_target( $path ), $want, "$path is rescued" );
}

foreach ( array( '/2026/13/x/', '/2026/09/', '/2026/09/a/b/', '/blog/is-your-website-seo-friendly/', '/wp-admin/', '/1899/01/old/', '/2026/09/../etc/' ) as $path ) {
	t_eq( remotive_not_found_target( $path ), null, "$path keeps its 404" );
}

t_done( 'not-found' );
