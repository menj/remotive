<?php
/**
 * Every page in content/pages/ (the services and the case studies) is fully translated into Malay,
 * Simplified Chinese and Traditional Chinese. Each page's text is run through the language layer's own
 * translator, which reports every piece of text it has no entry for. A page whose English was reworded, or a new section, fails here until the three
 * dictionaries in inc/i18n/ have the new text. Not covered: pages built from templates/ (home, team,
 * contact and the rest), which WordPress renders, and the English-only Insights articles.
 * Run: php tests/check-i18n-coverage.php
 */

require __DIR__ . '/bootstrap.php';

class T_Wpdb {
	public $prefix = 'wp_';
	public function suppress_errors( $x = null ) { return false; }
	public function prepare( $q ) { return $q; }
	public function get_results( $q, $o = null ) { return array(); }
	public function get_charset_collate() { return ''; }
}
$GLOBALS['wpdb'] = new T_Wpdb();

function get_theme_file_path( $p ) { return dirname( __DIR__ ) . '/' . $p; }
function get_current_user_id() { return 1; }
function get_post() { return (object) array( 'post_modified_gmt' => '2026-10-01 10:00:00' ); }

foreach ( array( 'wp_parse_url' => 'return parse_url( $u, $c );', 'wp_strip_all_tags' => 'return strip_tags( $u );', 'untrailingslashit' => 'return rtrim( $u, "/\\\\" );', 'trailingslashit' => 'return rtrim( $u, "/\\\\" ) . "/";', 'home_url' => 'return "https://example.com" . $u;' ) as $name => $body ) {
	if ( ! function_exists( $name ) ) {
		eval( "function $name( \$u = '', \$c = -1 ) { $body }" ); // phpcs:ignore Squiz.PHP.Eval.Discouraged
	}
}

foreach ( array( 'is_front_page', 'is_home', 'is_category', 'is_tag', 'is_author', 'is_date', 'is_paged', 'is_singular' ) as $name ) {
	if ( ! function_exists( $name ) ) {
		eval( "function $name() { return false; }" ); // phpcs:ignore Squiz.PHP.Eval.Discouraged
	}
}

require dirname( __DIR__ ) . '/inc/i18n/i18n.php';
require dirname( __DIR__ ) . '/inc/i18n/i18n-admin.php';

$pages  = glob( dirname( __DIR__ ) . '/content/pages/*.json' );
$fail   = 0;
$checks = 0;

foreach ( $pages as $file ) {
	$item = json_decode( (string) file_get_contents( $file ), true );

	if ( ! is_array( $item ) || empty( $item['content'] ) ) {
		continue;
	}

	$text = is_array( $item['content'] ) ? implode( "\n", $item['content'] ) : (string) $item['content'];
	$html = '<h1>' . $item['title'] . '</h1>' . preg_replace( '/<!--.*?-->/s', '', $text );

	foreach ( array( 'ms', 'zh-hans', 'zh-hant' ) as $lang ) {
		$missing = array();
		remotive_i18n_translate_body( $html, $lang, $missing );
		++$checks;

		if ( $missing ) {
			++$fail;
			echo 'FAIL: ' . basename( $file, '.json' ) . " has " . count( $missing ) . " untranslated text(s) in $lang, for example: " . substr( (string) array_key_first( $missing ), 0, 90 ) . "...\n";
		}
	}
}

echo "i18n-coverage: " . ( $checks - $fail ) . " passed, $fail failed (" . count( $pages ) . " pages x 3 languages)\n";
exit( $fail ? 1 : 0 );
