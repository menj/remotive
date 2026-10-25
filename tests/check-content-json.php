<?php
/**
 * The shipped content is JSON, and only JSON.
 *
 * - content/ holds index.json, posts/ and pages/, and nothing but .json files: no .html, .txt, .php,
 *   .md or anything else that could be mistaken for, or executed as, content.
 * - index.json lists every file, once, and every file is listed (no orphan, no missing file).
 * - Every file parses, passes the schema (remotive_content_problems()) and is named for its type and slug.
 * - The loader returns every listed item, so nothing is skipped without anyone noticing.
 * - Every item's image exists in assets/seed-images/.
 * - The only .html and .txt files in the theme are the ones that are not content: block templates,
 *   the static server error pages, the WordPress readme and documentation. A new one anywhere else fails.
 *
 * Run: php tests/check-content-json.php
 */

define( 'ABSPATH', '/' );

$root = dirname( __DIR__ );
require $root . '/inc/setup/content-source.php';
require $root . '/inc/setup/content-seed.php';

$fail = 0;
$n    = 0;

function cj_ok( $cond, $msg ) {
	global $fail, $n;
	++$n;

	if ( ! $cond ) {
		++$fail;
		echo "FAIL: $msg\n";
	}
}

$dir = $root . '/content';

// ---- 1. Only JSON inside content/ ----
cj_ok( is_dir( $dir ), 'content/ exists' );
$files = array();

if ( is_dir( $dir ) ) {
	$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) );

	foreach ( $it as $f ) {
		if ( $f->isLink() ) {
			cj_ok( false, 'content/ holds a link: ' . $f->getPathname() );
			continue;
		}

		$rel = ltrim( substr( $f->getPathname(), strlen( $dir ) ), '/' );
		cj_ok( 'json' === $f->getExtension(), "content/$rel is not a .json file" );
		cj_ok( 'index.json' === $rel || (bool) preg_match( '#^(?:posts|pages)/[a-z0-9]+(?:-[a-z0-9]+)*\.json$#', $rel ), "content/$rel is not index.json, posts/<slug>.json or pages/<slug>.json" );
		$files[ $rel ] = true;
	}
}

// ---- 2. The index and the files agree ----
$index = remotive_content_read_json( $dir . '/index.json' );
cj_ok( is_array( $index ) && isset( $index['schema'], $index['items'] ) && 1 === $index['schema'] && is_array( $index['items'] ), 'index.json is valid, schema 1, with an items list' );
$listed = is_array( $index ) && isset( $index['items'] ) && is_array( $index['items'] ) ? $index['items'] : array();
cj_ok( count( $listed ) === count( array_unique( $listed ) ), 'index.json lists no file twice' );

foreach ( $listed as $rel ) {
	cj_ok( isset( $files[ $rel ] ), "index.json lists $rel, which does not exist" );
}

foreach ( array_keys( $files ) as $rel ) {
	cj_ok( 'index.json' === $rel || in_array( $rel, $listed, true ), "content/$rel exists but index.json does not list it" );
}

// ---- 3. Each file ----
foreach ( $listed as $rel ) {
	$path = remotive_content_resolve( $dir, $rel );
	cj_ok( '' !== $path, "$rel is an allowed content path" );

	if ( '' === $path ) {
		continue;
	}

	$item = remotive_content_read_json( $path );
	cj_ok( is_array( $item ), "$rel reads as JSON" . ( is_string( $item ) ? ": $item" : '' ) );

	if ( ! is_array( $item ) ) {
		continue;
	}

	$problems = remotive_content_problems( remotive_content_join_lines( $item ), $rel );
	cj_ok( ! $problems, "$rel is valid" . ( $problems ? ': ' . implode( '; ', $problems ) : '' ) );

	if ( ! empty( $item['image'] ) ) {
		cj_ok( is_file( $root . '/assets/seed-images/' . $item['image'] ), "$rel: assets/seed-images/{$item['image']} exists" );
	}
}

// ---- 4. The loader returns them all, in order ----
$loaded = remotive_seed_content( true );
cj_ok( count( $loaded ) === count( $listed ), 'the loader returns every listed item (' . count( $loaded ) . ' of ' . count( $listed ) . ')' );
$slugs = array();

foreach ( $loaded as $item ) {
	$slugs[] = ( 'post' === $item['type'] ? 'posts' : 'pages' ) . '/' . $item['slug'] . '.json';
}

cj_ok( $slugs === $listed, 'the loader keeps the order of index.json' );

// ---- 5. The refusals work: a file the loader must not accept ----
$bad = array(
	'a script in the body'    => array( 'content' => "<!-- wp:html -->\n<script>alert(1)</script>\n<!-- /wp:html -->" ),
	'an event handler'        => array( 'content' => "<!-- wp:paragraph -->\n<p onclick=\"x()\">a</p>\n<!-- /wp:paragraph -->" ),
	'a javascript: address'   => array( 'content' => "<!-- wp:paragraph -->\n<p><a href=\"javascript:x()\">a</a></p>\n<!-- /wp:paragraph -->" ),
	'an entity-encoded scheme' => array( 'content' => "<!-- wp:paragraph -->\n<p><a href=\"java&#x73;cript:x()\">a</a></p>\n<!-- /wp:paragraph -->" ),
	'a split scheme'          => array( 'content' => "<!-- wp:paragraph -->\n<p><a href=\"jav&#x09;ascript:x()\">a</a></p>\n<!-- /wp:paragraph -->" ),
	'a null type'             => array( 'type' => null ),
	'a null slug'             => array( 'slug' => null ),
	'null tags'               => array( 'tags' => null ),
	'an unclosed block'       => array( 'content' => "<!-- wp:paragraph -->\n<p>a</p>" ),
	'an unknown key'          => array( 'run' => 'x' ),
	'a path in the slug'      => array( 'slug' => '../x' ),
	'a path in the image'     => array( 'image' => '../../x.jpg' ),
	'a bad type'              => array( 'type' => 'attachment' ),
	'a PHP file as the image' => array( 'image' => 'x.php' ),
);
$sample = remotive_content_join_lines( $loaded ? $loaded[0] : array() );

foreach ( $bad as $what => $change ) {
	cj_ok( (bool) remotive_content_problems( array_merge( $sample, $change ), '' ), "the validator refuses $what" );
}

cj_ok( (bool) remotive_content_problems( remotive_content_join_lines( array_merge( $sample, array( 'content' => array( 'a' => 'x' ) ) ) ), '' ), 'the validator refuses a content object in place of a list of lines' );
cj_ok( ! array_filter( remotive_content_problems( array_merge( $sample, array( 'tags' => array() ) ), '' ), static function ( $p ) { return false !== strpos( $p, 'tags' ); } ), 'the validator accepts an empty tags list' );

foreach ( array( '../index.json', 'posts/../index.json', 'posts/x.html', 'posts/x.txt', 'posts/x.php', 'posts/Sub/x.json', '/etc/passwd', 'pages/a/b.json', array() ) as $entry ) {
	cj_ok( '' === remotive_content_resolve( $dir, $entry ), 'the index refuses the entry ' . json_encode( $entry ) );
}

// ---- 6. No .html or .txt file stands in for content anywhere else ----
$allowed = array(
	'#^templates/[a-z0-9-]+\.html$#',          // block templates: WordPress only reads templates as .html
	'#^parts/[a-z0-9-]+\.html$#',              // block template parts
	'#^error-pages/(?:500|503)\.html$#',       // static pages the web server shows when WordPress cannot
	'#^error-pages/htaccess-errors\.txt$#',    // server configuration to paste into .htaccess
	'#^docs/htaccess-cache\.txt$#',            // server configuration to paste into .htaccess
	'#^readme\.txt$#',                         // the WordPress.org readme
	'#^languages/README\.txt$#',               // a note about the translation files
);
$it = new RecursiveIteratorIterator( new RecursiveCallbackFilterIterator(
	new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ),
	function ( $current ) {
		return ! in_array( $current->getFilename(), array( '.git', 'node_modules' ), true );
	}
) );

foreach ( $it as $f ) {
	if ( ! in_array( strtolower( $f->getExtension() ), array( 'html', 'htm', 'txt' ), true ) ) {
		continue;
	}

	$rel = ltrim( substr( $f->getPathname(), strlen( $root ) ), '/' );
	$ok  = false;

	foreach ( $allowed as $pattern ) {
		$ok = $ok || (bool) preg_match( $pattern, $rel );
	}

	cj_ok( $ok, "$rel is an .html or .txt file that is not on the list of non-content files; content is stored as JSON in content/" );
}

// ---- 7. The old PHP data file is gone, and nothing names it ----
cj_ok( ! is_file( $root . '/inc/setup/content-seed-data.php' ), 'inc/setup/content-seed-data.php is gone' );

echo 'content-json: ' . ( $n - $fail ) . " passed, $fail failed\n";
exit( $fail ? 1 : 0 );
