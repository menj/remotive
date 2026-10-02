<?php
/**
 * Checks the version numbers agree: style.css (the source), readme.txt's
 * "Stable tag" and "Latest version", and the newest entry in
 * docs/changelog.md. Run: php tests/check-versions.php
 */

$root = dirname( __DIR__ );

preg_match( '/^Version:\s*([0-9]+\.[0-9]+\.[0-9]+)/m', file_get_contents( $root . '/style.css' ), $style );
preg_match( '/^Stable tag:\s*([0-9]+\.[0-9]+\.[0-9]+)/m', file_get_contents( $root . '/readme.txt' ), $stable );
preg_match( '/Latest version:\s*([0-9]+\.[0-9]+\.[0-9]+)/', file_get_contents( $root . '/readme.txt' ), $latest );
preg_match( '/^## \[([0-9]+\.[0-9]+\.[0-9]+)\]/m', file_get_contents( $root . '/docs/changelog.md' ), $log );

$found = array(
	'style.css Version'            => $style[1] ?? null,
	'readme.txt Stable tag'        => $stable[1] ?? null,
	'readme.txt Latest version'    => $latest[1] ?? null,
	'docs/changelog.md newest'     => $log[1] ?? null,
);

$expected = $found['style.css Version'];
$bad      = array();

foreach ( $found as $label => $value ) {
	if ( null === $value ) {
		$bad[] = "$label: not found";
	} elseif ( $value !== $expected ) {
		$bad[] = "$label is $value, style.css says $expected";
	}
}

if ( $bad ) {
	fwrite( STDERR, "Version mismatch:\n - " . implode( "\n - ", $bad ) . "\n" );
	exit( 1 );
}

echo "Versions agree: $expected\n";
