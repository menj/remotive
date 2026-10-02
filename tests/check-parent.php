<?php
/**
 * The child theme keeps Twenty Twenty-Five's colour slugs on its own colours.
 *
 * The parent's styles and patterns use base, contrast, accent-4 and accent-5.
 * If theme.json stops declaring them, the parent's light values come back on
 * the dark page; if the stylesheet stops aliasing them, they stop following
 * the light/dark switch. Run: php tests/check-parent.php
 */

$root    = dirname( __DIR__ );
$theme   = json_decode( file_get_contents( $root . '/theme.json' ), true );
$css     = file_get_contents( $root . '/assets/css/remotive.css' );
$style   = file_get_contents( $root . '/style.css' );
$slugs   = array_column( $theme['settings']['color']['palette'], 'slug' );
$failed  = 0;

if ( ! preg_match( '/^Template:\s*twentytwentyfive\s*$/m', $style ) ) {
	fwrite( STDERR, "FAIL: style.css does not name twentytwentyfive as its parent\n" );
	$failed++;
}

foreach ( array( 'base', 'contrast', 'accent-4', 'accent-5' ) as $slug ) {
	if ( ! in_array( $slug, $slugs, true ) ) {
		fwrite( STDERR, "FAIL: theme.json palette has no '$slug'\n" );
		$failed++;
	}
	if ( ! preg_match( '/--wp--preset--color--' . preg_quote( $slug, '/' ) . ':\s*var\(/', $css ) ) {
		fwrite( STDERR, "FAIL: remotive.css does not alias --wp--preset--color--$slug to a theme token\n" );
		$failed++;
	}
}

// A function the parent defines must never be redefined here.
foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ) ) as $file ) {
	if ( '.php' !== substr( $file, -4 ) || false !== strpos( $file, '/.git/' ) || false !== strpos( $file, '/tests/' ) ) {
		continue;
	}
	if ( preg_match( '/function\s+twentytwentyfive_/', file_get_contents( $file ) ) ) {
		fwrite( STDERR, 'FAIL: redefines a parent function in ' . str_replace( $root . '/', '', $file ) . "\n" );
		$failed++;
	}
}

if ( $failed ) {
	exit( 1 );
}

echo "Parent compatibility: slugs declared and aliased, parent functions untouched.\n";
