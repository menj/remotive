<?php
/**
 * Patterns that have no place in this theme, found by reading the source.
 * A regression guard, not a substitute for review. Run: php tests/check-security-patterns.php
 *
 *  - wp_redirect() (use wp_safe_redirect)
 *  - eval(), exec-family calls, base64_decode()
 *  - unserialize() without allowed_classes => false
 *  - a raw $wpdb query (this theme has none; any new one must be reviewed)
 *  - a PHP module without the direct-access guard
 *  - register_rest_route() without a permission_callback
 *  - JSON-LD printed without JSON_HEX_TAG, which would let </script> break out
 */

$root   = dirname( __DIR__ );
$failed = array();

$files = array_merge( array( $root . '/functions.php' ), glob( $root . '/inc/*/*.php' ) );

foreach ( $files as $file ) {
	$rel  = str_replace( $root . '/', '', $file );
	$code = file_get_contents( $file );

	// Strip comments so prose that mentions a function does not trip the check.
	$tokens = '';
	foreach ( token_get_all( $code ) as $t ) {
		if ( is_array( $t ) && in_array( $t[0], array( T_COMMENT, T_DOC_COMMENT ), true ) ) {
			continue;
		}
		$tokens .= is_array( $t ) ? $t[1] : $t;
	}

	foreach ( array(
		'/\bwp_redirect\s*\(/'                                  => 'wp_redirect() (use wp_safe_redirect)',
		'/\beval\s*\(/'                                         => 'eval()',
		'/\b(shell_exec|passthru|proc_open|popen|system|exec)\s*\(/' => 'a command execution function',
		'/\bbase64_decode\s*\(/'                                => 'base64_decode()',
		'/\$wpdb\s*->\s*(query|get_results|get_row|get_var|get_col)\s*\(/' => 'a raw $wpdb query',
	) as $pattern => $label ) {
		if ( null !== $label && preg_match( $pattern, $tokens ) ) {
			$failed[] = "$rel: $label";
		}
	}

	if ( preg_match_all( '/\bunserialize\s*\((.*?)\)\s*;/s', $tokens, $m ) ) {
		foreach ( $m[1] as $args ) {
			if ( false === strpos( $args, 'allowed_classes' ) ) {
				$failed[] = "$rel: unserialize() without allowed_classes";
			}
		}
	}

	if ( 'functions.php' !== $rel && ! preg_match( "/defined\(\s*'ABSPATH'\s*\)\s*\|\|\s*exit|!\s*defined\(\s*'ABSPATH'\s*\)\s*\)\s*\{\s*exit/", $code ) ) {
		$failed[] = "$rel: missing the ABSPATH direct-access guard";
	}

	if ( preg_match_all( '/register_rest_route\s*\((.*?)\n\t\);/s', $tokens, $m ) ) {
		foreach ( $m[1] as $call ) {
			if ( false === strpos( $call, 'permission_callback' ) ) {
				$failed[] = "$rel: register_rest_route() without permission_callback";
			}
		}
	}

	if ( preg_match_all( '/ld\+json.*?wp_json_encode\s*\(([^;]*?)\)\s*\./s', $tokens, $m ) ) {
		foreach ( $m[1] as $args ) {
			if ( false === strpos( $args, 'JSON_HEX_TAG' ) ) {
				$failed[] = "$rel: JSON-LD printed without JSON_HEX_TAG";
			}
		}
	}
}

if ( $failed ) {
	foreach ( array_unique( $failed ) as $f ) {
		fwrite( STDERR, "FAIL: $f\n" );
	}
	exit( 1 );
}

echo 'Security patterns: ' . count( $files ) . " files clean.\n";
