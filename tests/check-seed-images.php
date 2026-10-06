<?php
/**
 * Every seeded Insights article names an image, and the image and its AVIF
 * companion are in assets/seed-images/. Catches a duplicated empty 'image' key
 * (the last key wins), which published three articles with no picture.
 * Run: php tests/check-seed-images.php
 */

require __DIR__ . '/bootstrap.php';
require dirname( __DIR__ ) . '/inc/setup/content-seed-data.php';

$root     = dirname( __DIR__ );
$problems = array();
$count    = 0;

foreach ( remotive_seed_content() as $item ) {
	if ( 'post' !== ( $item['type'] ?? '' ) ) {
		continue;
	}

	++$count;
	$slug = $item['slug'] ?? '?';
	$file = $item['image'] ?? '';

	if ( '' === $file ) {
		$problems[] = "$slug: no image";
		continue;
	}

	foreach ( array( $file, preg_replace( '/\.(png|jpe?g)$/i', '.avif', $file ) ) as $f ) {
		if ( ! is_readable( $root . '/assets/seed-images/' . $f ) ) {
			$problems[] = "$slug: assets/seed-images/$f is missing";
		}
	}
}

if ( $problems ) {
	fwrite( STDERR, "Seed images:\n - " . implode( "\n - ", $problems ) . "\n" );
	exit( 1 );
}

echo "Seed images: $count articles, each with a photo and its AVIF.\n";
