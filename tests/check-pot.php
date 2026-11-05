<?php
/**
 * languages/remotive.pot lists every string the theme translates, and nothing else.
 * It is built by tools/make-pot.py, so this runs the same tool in check mode: a string added, changed
 * or removed in the PHP without rebuilding the file fails here.
 * Run: php tests/check-pot.php   (rebuild with: python3 tools/make-pot.py)
 */

$python = trim( (string) shell_exec( 'command -v python3 2>/dev/null' ) );

if ( '' === $python ) {
	echo "SKIP: python3 is not installed, so the translation template cannot be checked here.\n";
	exit( 0 );
}

$root = dirname( __DIR__ );
$out  = array();
$code = 0;
exec( escapeshellarg( $python ) . ' -I ' . escapeshellarg( $root . '/tools/make-pot.py' ) . ' --check 2>&1', $out, $code );

echo implode( "\n", $out ) . "\n";
exit( 0 === $code ? 0 : 1 );
