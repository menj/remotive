<?php
/**
 * Block markup in templates/ and parts/: every "<!-- wp:x -->" comment has a matching closing comment,
 * every comment's attributes are valid JSON, and a heading's level in its comment matches its tag.
 * The editor rejects a block whose comment and markup disagree ("unexpected or invalid content"), and
 * nothing else in CI looks at block markup.
 *
 * Run: php tests/check-block-markup.php
 */

$root  = dirname( __DIR__ );
$files = array_merge( glob( $root . '/templates/*.html' ), glob( $root . '/parts/*.html' ) );
$fail  = 0;
$total = 0;

foreach ( $files as $file ) {
	++$total;
	$name  = str_replace( $root . '/', '', $file );
	$html  = (string) file_get_contents( $file );
	$bad   = array();
	$stack = array();

	preg_match_all( '/<!--\s*(\/?)wp:([a-z0-9\/-]+)(\s+(\{.*?\}))?\s*(\/?)-->/s', $html, $m, PREG_SET_ORDER | PREG_OFFSET_CAPTURE );

	foreach ( $m as $hit ) {
		$closing = '' !== $hit[1][0];
		$block   = $hit[2][0];
		$json    = isset( $hit[4] ) ? $hit[4][0] : '';
		$self    = isset( $hit[5] ) && '/' === $hit[5][0];

		if ( '' !== $json && null === json_decode( $json, true ) ) {
			$bad[] = "wp:$block has attributes that are not valid JSON";
		}

		if ( $closing ) {
			$open = array_pop( $stack );

			if ( null === $open || $open[0] !== $block ) {
				$bad[] = "closing wp:$block does not match the open block " . ( $open ? 'wp:' . $open[0] : '(none)' );
			}

			continue;
		}

		if ( $self ) {
			continue;
		}

		$stack[] = array( $block, $json, $hit[0][1] + strlen( $hit[0][0] ) );

		// A heading: the level in the comment must be the level of the tag that follows.
		if ( 'heading' === $block ) {
			if ( ! preg_match( '/^\s*<h([1-6])\b/', substr( $html, $hit[0][1] + strlen( $hit[0][0] ), 40 ), $tag ) ) {
				$bad[] = 'wp:heading is not followed by a heading tag';
				continue;
			}

			$attrs = '' !== $json ? json_decode( $json, true ) : array();
			$level = (int) ( is_array( $attrs ) && isset( $attrs['level'] ) ? $attrs['level'] : 2 );

			if ( $level !== (int) $tag[1] ) {
				$bad[] = "wp:heading says level $level but the tag is h" . $tag[1];
			}
		}
	}

	foreach ( $stack as $open ) {
		$bad[] = 'wp:' . $open[0] . ' is never closed';
	}

	if ( $bad ) {
		++$fail;
		echo "FAIL: $name: " . implode( '; ', array_unique( $bad ) ) . "\n";
	}
}

echo "Block markup: " . ( $total - $fail ) . " of $total files are balanced, with valid attributes and matching heading levels.\n";
exit( $fail ? 1 : 0 );
