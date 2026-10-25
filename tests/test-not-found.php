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

// ---- A service slug at the top level goes under /services/ ----
t_eq( remotive_not_found_target( '/seo/' ), array( 'service', 'seo' ), '/seo/ is the SEO service' );
t_eq( remotive_not_found_target( '/Paid-Media' ), array( 'service', 'paid-media' ), 'whatever the case' );
t_eq( remotive_not_found_target( '/seo-audit/' ), null, 'the landing page is its own address' );
t_eq( remotive_not_found_target( '/services/seo/' ), null, 'the real address is not touched' );

// ---- Capitalised addresses have one lowercase form ----
t_eq( remotive_lowercase_path( '/About/' ), '/about/', 'a capital is lowered' );
t_eq( remotive_lowercase_path( '/about/' ), '', 'already lowercase: nothing to do' );
t_eq( remotive_lowercase_path( '/Logo.PNG' ), '', 'a file keeps its case' );
t_eq( remotive_lowercase_path( '' ), '', 'an empty path is left alone' );
t_eq( remotive_lowercase_path( '/ms/Perkhidmatan/' ), '/ms/perkhidmatan/', 'inside a language directory too' );

t_done( 'not-found' );
