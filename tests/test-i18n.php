<?php
/**
 * Language layer: which pages are live in which language, URLs, dictionaries.
 * Run: php tests/test-i18n.php
 */

require __DIR__ . '/bootstrap.php';

// A database with no saved translations, so only the shipped dictionaries count.
class T_Wpdb {
	public $prefix = 'wp_';
	public function suppress_errors( $x = null ) { return false; }
	public function prepare( $q ) { return $q; }
	public function get_results( $q, $o = null ) { return array(); }
	public function get_charset_collate() { return ''; }
}
$GLOBALS['wpdb'] = new T_Wpdb();

function get_theme_file_path( $p ) { return dirname( __DIR__ ) . '/' . $p; }
function wp_parse_url( $u, $c = -1 ) { return parse_url( $u, $c ); }
function wp_strip_all_tags( $s ) { return strip_tags( $s ); }
function get_current_user_id() { return 1; }
function get_post() { return (object) array( 'post_modified_gmt' => '2026-10-01 10:00:00' ); }
function is_front_page() { return $GLOBALS['t_front'] ?? false; }
function is_home() { return $GLOBALS['t_insights'] ?? false; }
function is_category() { return false; }
function is_tag() { return false; }
function is_author() { return false; }
function is_date() { return false; }
function is_paged() { return false; }
function is_singular() { return false; }

require dirname( __DIR__ ) . '/inc/i18n/i18n.php';
require dirname( __DIR__ ) . '/inc/i18n/i18n-admin.php';

// Prefixes and tags: Chinese is told apart by script, not by country.
$langs = remotive_i18n_languages();
t_eq( array_column( $langs, 'prefix' ), array( '', 'ms', 'zh-hans', 'zh-hant' ), 'language prefixes' );
t_eq( array_column( $langs, 'hreflang' ), array( 'en', 'ms-MY', 'zh-Hans', 'zh-Hant' ), 'hreflang tags' );
t_eq( array_column( $langs, 'native' ), array( 'English', 'Bahasa Melayu', '简体中文', '繁體中文' ), 'switcher names are written in the language' );

// Every page's search listing has both a title and a description key, even when only one is set.
foreach ( array( 'ms', 'zh-hans', 'zh-hant' ) as $code ) {
	foreach ( remotive_i18n_data( $code )['seo'] as $path => $fields ) {
		if ( ! array_key_exists( 'title', $fields ) || ! array_key_exists( 'description', $fields ) ) {
			t_ok( false, "seo entry '$path' in $code has both title and description" );
		}
	}
}
t_ok( true, 'every seo entry has both title and description' );

// URLs.
t_eq( remotive_i18n_url( 'ms', '' ), 'https://example.com/ms/', 'Malay home' );
t_eq( remotive_i18n_url( 'zh-hans', 'team' ), 'https://example.com/zh-hans/team/', 'Simplified team page' );
t_eq( remotive_i18n_url( 'en', 'team' ), 'https://example.com/team/', 'English keeps its address' );

// Every translated page is live in every language by default.
$live = remotive_i18n_default_live_pages();
foreach ( array( '', 'services', 'services/seo', 'case-studies', 'about', 'team', 'contact', 'faq', 'privacy', 'terms' ) as $page ) {
	t_ok( in_array( $page, $live, true ), "'$page' is a default live page" );
}
foreach ( array( 'ms', 'zh-hans', 'zh-hant' ) as $code ) {
	foreach ( array( '', 'services', 'about', 'contact' ) as $page ) {
		t_ok( remotive_i18n_available( $code, $page ), "'$page' is live in $code" );
	}
	t_ok( remotive_i18n_available( $code, 'case-studies/ecommerce-seo-footwear' ), "an unlisted case study is live in $code too" );
	foreach ( array( '2026/09/seo-vs-sem', '2026/09/seo-cost-singapore', 'blog', 'blog/page/2' ) as $page ) {
		t_ok( ! remotive_i18n_available( $code, $page ), "'$page' (Insights) is English only: not available in $code" );
	}
	t_ok( ! remotive_i18n_available( $code, 'no-such-page' ), "a page with no translation is not live in $code" );
	t_ok( ! remotive_i18n_available( $code, null ), "no page, not live in $code" );
}
t_ok( remotive_i18n_available( 'en', 'anything' ), 'English is always live' );

// Insights is English only: never a default-live page, whatever the dictionaries hold.
t_ok( remotive_i18n_is_english_only( 'blog' ) && remotive_i18n_is_english_only( '2026/09/seo-vs-sem' ) && ! remotive_i18n_is_english_only( 'about' ) && ! remotive_i18n_is_english_only( '' ), 'english-only matches the posts page and dated articles, not other pages' );
$bad = array_filter( remotive_i18n_default_live_pages(), 'remotive_i18n_is_english_only' );
t_eq( array_values( $bad ), array(), 'no Insights page is in the default live list' );

// The header switcher: four languages, the Chinese ones written in characters.
$GLOBALS['remotive_i18n_lang'] = 'en';
$GLOBALS['t_front']            = true;
$nav = remotive_i18n_render_switcher( true );
t_ok( false !== strpos( $nav, 'rm-lang--nav' ), 'header switcher has its own class' );
foreach ( array( '>EN<', '>BM<', '>简体<', '>繁體<' ) as $label ) {
	t_ok( false !== strpos( $nav, $label ), "header switcher shows $label" );
}
foreach ( array( 'https://example.com/ms/', 'https://example.com/zh-hans/', 'https://example.com/zh-hant/' ) as $href ) {
	t_ok( false !== strpos( $nav, 'href="' . $href . '"' ), "header switcher links to $href" );
}
t_ok( false !== strpos( remotive_i18n_render_switcher(), '>Bahasa Melayu<' ), 'the footer switcher keeps the full names' );
$GLOBALS['t_front'] = false;

// A page with no translation of its own (an article, an archive) still offers each language's home page.
$fallback = remotive_i18n_render_switcher( true );
t_ok( false !== strpos( $fallback, 'href="https://example.com/ms/"' ), 'pages without a translation link to the Malay home page' );
t_ok( false !== strpos( $fallback, 'href="https://example.com/zh-hant/"' ), 'and to the Traditional Chinese home page' );

// Theme Options > Languages: a language that is off disappears everywhere.
t_reset();
t_ok( remotive_i18n_language_enabled( 'ms' ), 'every language is on when nothing is saved' );
$GLOBALS['T']['options']['remotive_theme_options'] = array( 'lang_ms' => '0', 'lang_zh_hans' => '1' );
t_ok( ! remotive_i18n_language_enabled( 'ms' ), 'Malay switched off' );
t_ok( remotive_i18n_language_enabled( 'zh-hans' ), 'Simplified still on' );
t_ok( remotive_i18n_language_enabled( 'zh-hant' ), 'a language never saved stays on' );
t_ok( remotive_i18n_language_enabled( 'en' ), 'English is always on' );
t_ok( ! remotive_i18n_available( 'ms', '' ), 'no page is available in a language that is off' );
t_ok( remotive_i18n_available( 'zh-hans', '' ), 'pages stay available in a language that is on' );

$GLOBALS['t_front'] = true;
$nav = remotive_i18n_render_switcher( true );
t_ok( false === strpos( $nav, '>BM<' ), 'switcher drops the language that is off' );
t_ok( false !== strpos( $nav, '>简体<' ) && false !== strpos( $nav, '>繁體<' ), 'switcher keeps the others' );

// The sitemap: English and every live language on each entry, none for a language that is off.
$entries = remotive_i18n_sitemap_entries();
t_ok( isset( $entries[''] ) && isset( $entries['services'] ), 'live pages are in the sitemap' );
t_eq( array_keys( $entries[''] ), array( 'en', 'zh-hans', 'zh-hant' ), 'home entry: English plus the languages that are on' );
t_ok( ! isset( $entries['2026/09/seo-vs-sem'] ) && ! isset( $entries['blog'] ), 'Insights (the posts page and the articles) is not in the language sitemap' );
$xml = remotive_i18n_sitemap_xml();
t_ok( false !== strpos( $xml, '<loc>https://example.com/</loc>' ), 'English URL is listed with its alternates' );
t_ok( false !== strpos( $xml, 'hreflang="x-default"' ), 'x-default present' );
t_ok( false === strpos( $xml, '/ms/' ), 'no Malay URL while Malay is off' );
t_eq( substr_count( $xml, 'hreflang="zh-Hans" href="https://example.com/zh-hans/"' ), 3, 'each home entry carries the full alternate set (reciprocal)' );

// The Rank Math index entry carries a location and a last-modified time.
$idx = remotive_i18n_rankmath_index( '<sitemap><loc>x</loc></sitemap>' );
t_ok( false !== strpos( $idx, '<loc>https://example.com/sitemap-languages.xml</loc><lastmod>2026-10-01' ), 'Rank Math index entry has loc and lastmod' );

// Switcher placements.
$GLOBALS['T']['options']['remotive_theme_options'] = array( 'lang_nav' => '0', 'lang_footer' => '1' );
t_eq( remotive_i18n_render_switcher( true ), '', 'header switcher can be switched off' );
t_ok( '' !== remotive_i18n_render_switcher(), 'footer switcher stays when only the header is off' );
$GLOBALS['t_front'] = false;

// Dates are written the way the language writes them (Pedoman Umum Ejaan: "31 Ogos 1957").
t_eq( remotive_i18n_localise_date( 'October 2, 2026', 'ms' ), '2 Oktober 2026', 'Malay date' );
t_eq( remotive_i18n_localise_date( 'August 31, 1957', 'ms' ), '31 Ogos 1957', 'Malay August is Ogos' );
t_eq( remotive_i18n_localise_date( 'March 9, 2027', 'ms' ), '9 Mac 2027', 'Malay March is Mac' );
t_eq( remotive_i18n_localise_date( 'October 2, 2026', 'zh-hans' ), '2026年10月2日', 'Chinese date' );
t_eq( remotive_i18n_localise_date( 'October 2, 2026', 'zh-hant' ), '2026年10月2日', 'Traditional Chinese date' );
t_eq( remotive_i18n_localise_date( 'Services', 'ms' ), null, 'ordinary text is not a date' );
t_eq( remotive_i18n_localise_date( 'October 2, 2026', 'en' ), null, 'English is left alone' );

t_done( 'i18n' );
