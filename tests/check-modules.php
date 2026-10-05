<?php
/**
 * Every PHP module under inc/ is loaded, and every load points at a real file.
 * A moved or misspelt require would otherwise only fail on a live site.
 * Run: php tests/check-modules.php
 */

$root    = dirname( __DIR__ );
$loaded  = array();
$missing = array();

$sources = array_merge( array( $root . '/functions.php' ), glob( $root . '/inc/*/*.php' ) );

foreach ( $sources as $source ) {
	$code = file_get_contents( $source );
	$dir  = dirname( $source );

	// require get_stylesheet_directory() . '/inc/x/y.php';  and  require_once __DIR__ . '/y.php';
	preg_match_all( "#require(?:_once)?\s+get_stylesheet_directory\(\)\s*\.\s*'(/inc/[^']+)'#", $code, $a );
	preg_match_all( "#require(?:_once)?\s+__DIR__\s*\.\s*'/([^'/]+\.php)'#", $code, $b );

	foreach ( $a[1] as $path ) {
		$full = $root . $path;
		is_file( $full ) ? $loaded[ realpath( $full ) ] = true : $missing[] = "$path (from " . basename( $source ) . ')';
	}
	foreach ( $b[1] as $file ) {
		$full = $dir . '/' . $file;
		is_file( $full ) ? $loaded[ realpath( $full ) ] = true : $missing[] = "$file (from " . basename( $source ) . ')';
	}
}

// Translation dictionaries are loaded by name from inc/i18n/<language>.php, not by a require.
$dictionaries = array( 'ms', 'zh-hans', 'zh-hant' );

foreach ( $dictionaries as $code ) {
	if ( ! is_file( $root . '/inc/i18n/' . $code . '.php' ) ) {
		$missing[] = "inc/i18n/$code.php (dictionary)";
	}
}

$orphans = array();
foreach ( glob( $root . '/inc/*/*.php' ) as $file ) {
	if ( 'i18n' === basename( dirname( $file ) ) && in_array( basename( $file, '.php' ), $dictionaries, true ) ) {
		continue;
	}

	if ( empty( $loaded[ realpath( $file ) ] ) ) {
		$orphans[] = str_replace( $root . '/', '', $file );
	}
}

$stray = glob( $root . '/inc/*.php' );

foreach ( $missing as $m ) {
	fwrite( STDERR, "FAIL: require points at a missing file: $m\n" );
}
foreach ( $orphans as $o ) {
	fwrite( STDERR, "FAIL: never loaded: $o\n" );
}
foreach ( $stray as $s ) {
	fwrite( STDERR, 'FAIL: module outside a folder: ' . str_replace( $root . '/', '', $s ) . "\n" );
}

if ( $missing || $orphans || $stray ) {
	exit( 1 );
}

echo 'Modules: ' . count( $loaded ) . " loaded, none missing, none orphaned.\n";
