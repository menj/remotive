<?php
/**
 * The seeded articles pass Rank Math's own content tests.
 * Thresholds are read from Rank Math 1.0.279's analyzer: description 120 to 160
 * characters, keyword density at least 0.76%, content length at least 600
 * words, the keyword in the title, description, URL, first 10% of the text
 * and at least one subheading, plus an internal and an external link.
 * Run: php tests/check-article-seo.php
 */
require __DIR__ . '/bootstrap.php';
require dirname( __DIR__ ) . '/inc/setup/content-seed-data.php';

$fail = 0;
$n    = 0;
function a_ok( $cond, $msg ) {
	global $fail, $n;
	++$n;
	if ( ! $cond ) {
		++$fail;
		echo "FAIL: $msg\n";
	}
}

foreach ( remotive_seed_content() as $i ) {
	if ( 'post' !== $i['type'] ) {
		continue;
	}
	$s     = $i['slug'];
	$kw    = strtolower( $i['rm_kw'] );
	$c     = $i['content'];
	$plain = strtolower( html_entity_decode( trim( preg_replace( '/\s+/', ' ', strip_tags( $c ) ) ) ) );
	$plain = str_replace( array( '-', '–' ), ' ', $plain );
	$words = str_word_count( $plain );

	a_ok( strlen( $i['rm_desc'] ) >= 120 && strlen( $i['rm_desc'] ) <= 160, "$s: description is 120 to 160 characters (" . strlen( $i['rm_desc'] ) . ')' );
	a_ok( false !== stripos( $i['rm_desc'], $kw ), "$s: keyword in description" );
	a_ok( false !== stripos( str_replace( '-', ' ', $i['rm_title'] ), $kw ), "$s: keyword in the SEO title" );
	a_ok( 0 === stripos( str_replace( '-', ' ', $i['rm_title'] ), $kw ), "$s: SEO title starts with the keyword" );
	a_ok( false !== strpos( $s, str_replace( ' ', '-', $kw ) ), "$s: keyword in the URL" );
	a_ok( false !== strpos( substr( $plain, 0, (int) ( strlen( $plain ) * 0.1 ) ), $kw ), "$s: keyword in the first 10% of the text" );
	a_ok( $words >= 600, "$s: at least 600 words ($words)" );
	a_ok( substr_count( $plain, $kw ) / $words * 100 >= 0.76, "$s: keyword density at least 0.76%" );

	preg_match_all( '#<h2[^>]*>(.*?)</h2>#is', $c, $h );
	$in_head = 0;
	foreach ( $h[1] as $x ) {
		$in_head += false !== stripos( str_replace( '-', ' ', $x ), $kw ) ? 1 : 0;
	}
	a_ok( $in_head >= 1, "$s: keyword in a subheading" );

	preg_match_all( '#<a [^>]*href="([^"]+)"#i', $c, $l );
	$ext = count( array_filter( $l[1], function ( $u ) { return 0 === strpos( $u, 'http' ); } ) );
	a_ok( $ext >= 1 && count( $l[1] ) - $ext >= 1, "$s: an internal and an external link" );

	// Every top-level paragraph and heading is a block, so the editor opens real blocks, not one Classic block.
	$stripped = preg_replace( '#<!-- wp:(paragraph|heading) -->\s*<(p|h2)>.*?</\2>\s*<!-- /wp:\1 -->#s', '', $c );
	a_ok( '' === trim( $stripped ), "$s: all content is block markup" );
}

// Migration table covers every article.
require dirname( __DIR__ ) . '/inc/setup/site-setup.php';
$hashes = function_exists( 'remotive_old_article_seo_hashes' ) ? remotive_old_article_seo_hashes() : array();
foreach ( remotive_seed_content() as $i ) {
	if ( 'post' === $i['type'] ) {
		a_ok( isset( $hashes[ $i['slug'] ]['content'], $hashes[ $i['slug'] ]['kw'] ), $i['slug'] . ': migration has old hashes' );
	}
}

// Reading time: 220 words a minute, never below one.
foreach ( array( 'get_post' => function ( $p = null ) { return $p; }, 'strip_shortcodes' => function ( $t ) { return $t; }, 'wp_strip_all_tags' => function ( $t ) { return strip_tags( $t ); } ) as $name => $fn ) {
	if ( ! function_exists( $name ) ) {
		eval( "function $name(\$a = null) { return \$a; }" ); // phpcs:ignore
	}
}
foreach ( array( 'add_filter', 'is_singular', '_n', 'esc_html' ) as $name ) {
	if ( ! function_exists( $name ) ) {
		eval( "function $name() { return func_get_args()[0] ?? true; }" ); // phpcs:ignore
	}
}
if ( ! function_exists( 'remotive_reading_minutes' ) ) {
	require dirname( __DIR__ ) . '/inc/content/article.php';
}
a_ok( 1 === remotive_reading_minutes( (object) array( 'post_content' => 'short' ) ), 'reading time: a short post is one minute' );
a_ok( 5 === remotive_reading_minutes( (object) array( 'post_content' => str_repeat( 'word ', 1000 ) ) ), 'reading time: 1,000 words is five minutes' );

echo "article-seo: " . ( $n - $fail ) . " passed, $fail failed\n";
exit( $fail ? 1 : 0 );
