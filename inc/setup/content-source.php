<?php
/**
 * The shipped content, read from JSON.
 *
 * The articles, service pages and case studies the theme publishes on activation are stored as data,
 * one JSON file per item under content/, listed in order in content/index.json. They are never PHP,
 * HTML or text files: a JSON file is parsed and validated, never executed or included, so an edit to
 * one cannot run code, and a file in the wrong format is refused instead of being picked up. The
 * writing is block markup inside the JSON strings, exactly as it is stored in the database.
 *
 * File shape (see tests/check-content-json.php for the enforced rules):
 *
 *   {
 *     "type":     "post" | "page",
 *     "slug":     "lowercase-with-hyphens",
 *     "title":    "...",
 *     "parent":   "services"            (optional: the page this one sits under)
 *     "template": "page-service"        (optional: the block template)
 *     "excerpt":  "...",
 *     "category": ["Name", "slug"]      (optional, posts)
 *     "tags":     ["...", "..."]        (optional, posts)
 *     "image":    "file.jpg"            (optional: a file in assets/seed-images/)
 *     "rm_title": "...", "rm_desc": "...", "rm_kw": "...",   (Rank Math title, description, keyword)
 *     "content":  ["<!-- wp:paragraph -->", "<p>...</p>", ...]   (the body, one line per entry; a
 *                  plain string is accepted too. The lines are joined with a line feed.)
 *   }
 *
 * remotive_seed_content() is the one entry point; everything that used to read the old PHP data file
 * (the seeder, the migrations, the Shipped content card and the check scripts) calls it unchanged.
 *
 * @package Remotive
 */

defined( 'ABSPATH' ) || exit;

/** The largest content file read, in bytes. The longest shipped item is about 14 KB. */
const REMOTIVE_CONTENT_MAX_BYTES = 262144;

/**
 * The folder the content files live in.
 *
 * @return string
 */
function remotive_content_dir() {
	return dirname( __DIR__, 2 ) . '/content';
}

/**
 * The keys an item may carry, and whether each is required.
 *
 * @return array<string,bool>
 */
function remotive_content_keys() {
	return array(
		'type'     => true,
		'slug'     => true,
		'title'    => true,
		'excerpt'  => true,
		'rm_title' => true,
		'rm_desc'  => true,
		'rm_kw'    => true,
		'content'  => true,
		'parent'   => false,
		'template' => false,
		'category' => false,
		'tags'     => false,
		'image'    => false,
	);
}

/**
 * Whether markup carries something active: a script, a frame, an embedded object, a form, a style
 * block, an event handler attribute or a javascript: address. Shipped content is text, headings,
 * lists and links; none of these belong in it.
 *
 * @param string $html Markup.
 * @return bool
 */
function remotive_content_has_active_markup( $html ) {
	$html = (string) $html;

	// A browser reads java&#x73;cript: and java&Tab;script: as javascript:, so test the decoded text too,
	// with whitespace and control characters (which a scheme may be split by) removed.
	$decoded = preg_replace_callback(
		'/&#(x[0-9a-f]+|[0-9]+);?/i',
		static function ( $m ) {
			$code = 'x' === strtolower( $m[1][0] ) ? hexdec( substr( $m[1], 1 ) ) : (int) $m[1];

			return $code > 0 && $code < 0x110000 ? html_entity_decode( '&#' . $code . ';', ENT_QUOTES | ENT_HTML5, 'UTF-8' ) : '';
		},
		$html
	);
	$decoded = html_entity_decode( (string) $decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$squeezed = preg_replace( '/[\x00-\x20\x7f]+/', '', $decoded );

	return (bool) preg_match( '/<\s*(?:script|iframe|object|embed|form|style|link|meta|base)\b|<[^>]*\son[a-z]+\s*=|javascript\s*:|data\s*:\s*text\/html/i', $html )
		|| (bool) preg_match( '/<(?:script|iframe|object|embed|form|style|link|meta|base)\b|javascript:|vbscript:|data:text\/html/i', (string) $squeezed );
}

/**
 * The problems with one item, as readable sentences. An empty list means the item is valid.
 *
 * @param mixed  $item The decoded item.
 * @param string $rel  Its path under content/, for the messages and the file name rule.
 * @return string[]
 */
function remotive_content_problems( $item, $rel = '' ) {
	if ( ! is_array( $item ) || array() === $item || array_keys( $item ) === range( 0, count( $item ) - 1 ) ) {
		return array( 'the file is not a JSON object' );
	}

	$problems = array();
	$keys     = remotive_content_keys();

	foreach ( array_diff( array_keys( $item ), array_keys( $keys ) ) as $extra ) {
		$problems[] = "unknown key \"$extra\"";
	}

	foreach ( $keys as $key => $required ) {
		if ( $required && ! array_key_exists( $key, $item ) ) {
			$problems[] = "missing key \"$key\"";
		}
	}

	$text = array(
		'title'    => 200,
		'excerpt'  => 400,
		'rm_title' => 160,
		'rm_desc'  => 400,
		'rm_kw'    => 120,
	);

	foreach ( $text as $key => $max ) {
		if ( array_key_exists( $key, $item ) && ( ! is_string( $item[ $key ] ) || '' === trim( $item[ $key ] ) || strlen( $item[ $key ] ) > $max ) ) {
			$problems[] = "\"$key\" must be a non-empty string of at most $max bytes";
		}
	}

	$slug = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

	if ( array_key_exists( 'type', $item ) && ! in_array( $item['type'], array( 'post', 'page' ), true ) ) {
		$problems[] = '"type" must be "post" or "page"';
	}

	if ( array_key_exists( 'slug', $item ) && ( ! is_string( $item['slug'] ) || ! preg_match( $slug, $item['slug'] ) ) ) {
		$problems[] = '"slug" must be lowercase letters, digits and hyphens';
	}

	if ( array_key_exists( 'parent', $item ) && ( ! is_string( $item['parent'] ) || ! preg_match( '#^[a-z0-9]+(?:-[a-z0-9]+)*(?:/[a-z0-9]+(?:-[a-z0-9]+)*)*$#', $item['parent'] ) ) ) {
		$problems[] = '"parent" must be a page path of lowercase slugs';
	}

	if ( array_key_exists( 'template', $item ) && ( ! is_string( $item['template'] ) || ! preg_match( $slug, $item['template'] ) ) ) {
		$problems[] = '"template" must be a template name (lowercase letters, digits and hyphens)';
	}

	if ( array_key_exists( 'image', $item ) && ( ! is_string( $item['image'] ) || ! preg_match( '/^[a-z0-9][a-z0-9._-]*\.(?:jpe?g|png|webp)$/', $item['image'] ) ) ) {
		$problems[] = '"image" must be a plain image file name (jpg, png or webp)';
	}

	if ( array_key_exists( 'category', $item ) && ( ! is_array( $item['category'] ) || 2 !== count( $item['category'] ) || ! isset( $item['category'][0], $item['category'][1] ) || ! is_string( $item['category'][0] ) || '' === trim( $item['category'][0] ) || ! is_string( $item['category'][1] ) || ! preg_match( $slug, $item['category'][1] ) ) ) {
		$problems[] = '"category" must be [name, slug]';
	}

	if ( array_key_exists( 'tags', $item ) ) {
		$bad = ! is_array( $item['tags'] ) || ( array() !== $item['tags'] && array_keys( $item['tags'] ) !== range( 0, count( $item['tags'] ) - 1 ) );

		foreach ( (array) $item['tags'] as $tag ) {
			$bad = $bad || ! is_string( $tag ) || '' === trim( $tag ) || strlen( $tag ) > 80;
		}

		if ( $bad ) {
			$problems[] = '"tags" must be a list of short, non-empty strings';
		}
	}

	if ( array_key_exists( 'content', $item ) ) {
		if ( ! is_string( $item['content'] ) || '' === trim( $item['content'] ) ) {
			$problems[] = '"content" must be non-empty text once its lines are joined';
		} else {
			if ( remotive_content_has_active_markup( $item['content'] ) ) {
				$problems[] = '"content" carries active markup (script, frame, object, form, style, event handler or javascript: address)';
			}

			if ( function_exists( 'remotive_seed_blocks_balanced' ) && ! remotive_seed_blocks_balanced( $item['content'] ) ) {
				$problems[] = '"content" opens a block it does not close, or closes one that is not open';
			}
		}
	}

	if ( '' !== $rel && isset( $item['type'], $item['slug'] ) && is_string( $item['type'] ) && is_string( $item['slug'] ) ) {
		$expected = ( 'post' === $item['type'] ? 'posts' : 'pages' ) . '/' . $item['slug'] . '.json';

		if ( $rel !== $expected ) {
			$problems[] = "the file is named $rel but its type and slug make it $expected";
		}
	}

	return $problems;
}

/**
 * Read and decode one JSON file, or explain why not.
 *
 * Reads only a regular file, refuses a large one, a byte order mark and invalid UTF-8, and decodes
 * with a depth limit. Nothing is included or evaluated.
 *
 * @param string $path File path.
 * @return array|string The decoded value, or an error message.
 */
function remotive_content_read_json( $path ) {
	if ( ! is_file( $path ) || ! is_readable( $path ) ) {
		return 'the file is missing or unreadable';
	}

	$size = filesize( $path );

	if ( false === $size || $size > REMOTIVE_CONTENT_MAX_BYTES ) {
		return 'the file is larger than ' . REMOTIVE_CONTENT_MAX_BYTES . ' bytes';
	}

	$raw = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

	if ( false === $raw || '' === $raw ) {
		return 'the file is empty';
	}

	if ( 0 === strpos( $raw, "\xEF\xBB\xBF" ) ) {
		return 'the file starts with a byte order mark';
	}

	if ( ! preg_match( '//u', $raw ) ) {
		return 'the file is not valid UTF-8';
	}

	$data = json_decode( $raw, true, 16 );

	if ( JSON_ERROR_NONE !== json_last_error() ) {
		return 'invalid JSON: ' . json_last_error_msg();
	}

	return is_array( $data ) ? $data : 'the file is not a JSON object';
}

/**
 * The absolute path of an index entry, or '' when the entry is not a content file of the allowed
 * shape: posts/<slug>.json or pages/<slug>.json, nothing else, nothing that climbs out of content/.
 *
 * @param string $dir The content folder.
 * @param mixed  $rel The index entry.
 * @return string
 */
function remotive_content_resolve( $dir, $rel ) {
	if ( ! is_string( $rel ) || ! preg_match( '#^(?:posts|pages)/[a-z0-9]+(?:-[a-z0-9]+)*\.json$#', $rel ) ) {
		return '';
	}

	$root = realpath( $dir );
	$real = realpath( $dir . '/' . $rel );

	if ( false === $root || false === $real || 0 !== strpos( $real, $root . DIRECTORY_SEPARATOR ) ) {
		return '';
	}

	return $real;
}

/**
 * Join an item's body lines into the text stored in the database.
 *
 * @param array $item A decoded item.
 * @return array The item, its content now a string.
 */
function remotive_content_join_lines( array $item ) {
	if ( isset( $item['content'] ) && is_array( $item['content'] ) ) {
		if ( array() !== $item['content'] && array_keys( $item['content'] ) !== range( 0, count( $item['content'] ) - 1 ) ) {
			$item['content'] = null; // An object, not a list of lines; the validator reports it.

			return $item;
		}

		$lines = array_values( $item['content'] );

		foreach ( $lines as $line ) {
			if ( ! is_string( $line ) ) {
				$item['content'] = null; // The validator reports it.

				return $item;
			}
		}

		$item['content'] = implode( "\n", $lines );
	}

	return $item;
}

/**
 * How many entries content/index.json lists, or null when it cannot be read.
 *
 * remotive_seed_content() skips a file it refuses, so a shorter list than this one means something was
 * left out; code that removes things the theme no longer ships must not act on a list that is incomplete.
 *
 * @return int|null
 */
function remotive_content_listed_count() {
	$index = remotive_content_read_json( remotive_content_dir() . '/index.json' );

	return is_array( $index ) && isset( $index['items'] ) && is_array( $index['items'] ) ? count( $index['items'] ) : null;
}

/**
 * Every shipped item, in the order of content/index.json.
 *
 * An index that cannot be read yields no items and an item that fails validation is skipped; each is
 * reported in the error log, so a broken file never takes the site down and never reaches the
 * database. The result is read once per request.
 *
 * @param bool $reload Read the files again.
 * @return array[] Seed definitions.
 */
function remotive_seed_content( $reload = false ) {
	static $items = null;

	if ( null !== $items && ! $reload ) {
		return $items;
	}

	$items = array();
	$dir   = remotive_content_dir();
	$index = remotive_content_read_json( $dir . '/index.json' );

	if ( ! is_array( $index ) || ! isset( $index['items'] ) || ! is_array( $index['items'] ) ) {
		error_log( 'Remotive content: content/index.json ' . ( is_string( $index ) ? $index : 'has no "items" list' ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log

		return $items;
	}

	$seen = array();

	foreach ( $index['items'] as $rel ) {
		$path = remotive_content_resolve( $dir, $rel );

		if ( '' === $path || isset( $seen[ $rel ] ) ) {
			error_log( 'Remotive content: index entry ' . ( is_string( $rel ) ? $rel : '(not a string)' ) . ' is not an allowed, unique content file' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			continue;
		}

		$seen[ $rel ] = true;
		$item         = remotive_content_read_json( $path );

		if ( is_string( $item ) ) {
			error_log( "Remotive content: $rel: $item" ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			continue;
		}

		$item     = remotive_content_join_lines( $item );
		$problems = remotive_content_problems( $item, $rel );

		if ( $problems ) {
			error_log( "Remotive content: $rel is skipped: " . implode( '; ', $problems ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			continue;
		}

		$items[] = $item;
	}

	return $items;
}
