<?php
/**
 * Language layer HTML translator: how it splits a page into tags and text.
 * Run: php tests/test-i18n-tokenizer.php
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


$ms   = 'Read enough. Let us talk.';
$want = remotive_i18n_data( 'ms' )['strings'][ $ms ] ?? null;
t_ok( null !== $want, 'the Malay dictionary has the sentence the tests translate' );

function t_translate( $html ) {
	$missing = array();
	return remotive_i18n_translate_body( $html, 'ms', $missing );
}

// Plain text is translated.
t_eq( t_translate( '<p>' . $ms . '</p>' ), '<p>' . esc_html( $want ) . '</p>', 'a paragraph is translated' );

// An inline script that ends with "<letter" before its closing tag no longer swallows the rest of the page.
foreach ( array(
	'for(i=0;i<n;i++){x()}',
	'if(a<b){c()}',
	'document.write("<script src=a></scr"+"ipt>")',
	'var s="<p>";',
) as $js ) {
	$out = t_translate( '<script>' . $js . '</script><p>' . $ms . '</p>' );
	t_ok( false !== strpos( $out, esc_html( $want ) ), 'the text after the inline script ' . $js . ' is translated' );
	t_ok( false !== strpos( $out, '<script>' . $js . '</script>' ), 'the script ' . $js . ' is copied through unchanged' );
}

// Same for a style block and for attributes that hold ">".
$out = t_translate( '<style>a>b{color:red}</style><p>' . $ms . '</p>' );
t_ok( false !== strpos( $out, esc_html( $want ) ), 'the text after a style block is translated' );
$out = t_translate( '<script type="text/x-template" data-x="a>b">x</script><p>' . $ms . '</p>' );
t_ok( false !== strpos( $out, esc_html( $want ) ), 'a ">" inside a script attribute does not end the tag early' );

// A void element marked translate="no" must not hide the rest of the page.
foreach ( array( '<img translate="no" src="a.png" alt="x">', '<input translate="no" name="n">', '<br translate="no">' ) as $void ) {
	$out = t_translate( $void . '<p>' . $ms . '</p>' );
	t_ok( false !== strpos( $out, esc_html( $want ) ), 'the text after ' . $void . ' is translated' );
}

// A non-void element marked translate="no" still hides its own contents, and only those.
$out = t_translate( '<nav translate="no"><a href="/">' . $ms . '</a></nav><p>' . $ms . '</p>' );
t_eq( substr_count( $out, $ms ), 1, 'translate="no" keeps its own text in English' );
t_ok( false !== strpos( $out, '<p>' . esc_html( $want ) . '</p>' ), 'the text after the translate="no" block is translated' );

// The visible attributes of a textarea are translated even though its text is not.
$out = t_translate( '<textarea placeholder="' . $ms . '">' . $ms . '</textarea>' );
t_ok( false !== strpos( $out, 'placeholder="' . esc_attr( $want ) . '"' ), 'a textarea placeholder is translated' );
t_ok( false !== strpos( $out, '>' . $ms . '</textarea>' ), 'the text typed in a textarea is left alone' );

// An unterminated script still behaves as before and does not break the page.
t_ok( is_string( t_translate( '<script>var a=1;<p>' . $ms . '</p>' ) ), 'an unterminated script does not fatal' );

t_done( 'i18n-tokenizer' );
