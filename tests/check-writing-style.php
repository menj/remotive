<?php
/**
 * The English writing style (anti-AI writing style guide, v2) on the theme's own prose.
 *
 * Enforces the guide's hard rules on every scope listed in $scopes: no contractions,
 * no em dashes, none of the banned vocabulary or dead phrases, no emoji, no
 * negation-then-correction sentences, and sentence-case headings where a heading
 * list is given. A scope joins the list once its text has been brought into line,
 * so the list only grows. `php tests/check-writing-style.php --debt` also reports
 * the scopes that are not enforced yet.
 *
 * Run: php tests/check-writing-style.php
 */

define( 'ABSPATH', '/' );
function __( $s ) { return $s; }

$root = dirname( __DIR__ );

/** Words and phrases from the guide's banned lists (section 3A to 3E). */
$banned = array(
	'delve', 'realm', 'harness', 'unlock', 'tapestry', 'paradigm', 'cutting-edge', 'revolutionize', 'intricate', 'intricacies',
	'showcasing', 'crucial', 'pivotal', 'surpass', 'meticulously', 'vibrant', 'vivid', 'unparalleled', 'underscore', 'leverage', 'synergy',
	'innovative', 'game-changer', 'testament', 'commendable', 'meticulous', 'groundbreaking', 'foster', 'showcase', 'enhance', 'holistic', 'garner',
	'accentuate', 'pioneering', 'trailblazing', 'unleash', 'versatile', 'transformative', 'redefine', 'seamless', 'seamlessly', 'optimize', 'scalable',
	'robust', 'breakthrough', 'empower', 'streamline', 'frictionless', 'elevate', 'adaptive', 'effortless', 'data-driven', 'insightful', 'proactive',
	'mission-critical', 'visionary', 'disruptive', 'reimagine', 'unprecedented', 'intuitive', 'leading-edge', 'synergize', 'democratize', 'accelerate',
	'state-of-the-art', 'immersive', 'plug-and-play', 'turnkey', 'future-proof', 'supercharge', 'enduring', 'interplay', 'captivate', 'genuinely',
	'bolstered', 'nestled', 'multifaceted', 'tailored', 'ever-evolving', 'utilize', 'delightful', 'journey', 'embark', 'straightforward', 'furthermore',
	'additionally', 'moreover',
);

$phrases = array(
	"in today's", 'important to note', 'worth noting', 'in order to', "let's dive", 'at the end of the day', 'moving forward', 'in other words',
	'it goes without saying', 'deep dive', 'digital age', 'the power of', 'new era', 'that said', 'that being said', 'with that in mind', 'on top of that',
	'in summary', 'in conclusion', 'let that sink in', 'this changes everything', 'serves as', 'stands as', 'game changer',
);

/** Negation then correction (section 3F). */
$parallel = array(
	'/\bnot only\b/i', '/\b(?:it|this|that) (?:is|was) not (?:just |only )?(?:about )?[^.;]{2,60}[,;.] (?:it|this|that) (?:is|was)\b/i',
	'/\bnot just\b/i', '/\bless [a-z]+(?: [a-z]+)?, more\b/i', '/\bforget [a-z ]{2,30}\./i', '/\bis dead\b/i',
	'/\bno [a-z]+, no [a-z]+, (?:just|only)\b/i', '/\b(?:isn|aren|wasn|doesn|don)[\x{2019}\']t (?:just |only )?(?:about )?[^.;]{2,60}[,;.] (?:it|this|that|they|we)\b/iu',
);

$scopes = array();

// The agency home page and its footer, in the language layer's source language.
require_once $root . '/inc/options/agency-layout.php';
$agency = array();

foreach ( remotive_agency_option_defaults() as $key => $value ) {
	if ( is_string( $value ) && '' !== $value && ! in_array( $key, array( 'agency_mode', 'agency_hide_pages', 'agency_show_services', 'agency_link_services', 'agency_show_faq', 'agency_show_team', 'agency_team_size' ), true ) ) {
		$agency[ $key ] = $value;
	}
}

$agency['footer: Follow us'] = 'Follow us';
$agency['footer: description'] = 'Full-funnel digital marketing for brands who would rather be seen than shout. Registered in Singapore, working across APAC.';
$scopes['site description (footer, sidebar)'] = array(
	'parts/footer.html'  => (string) preg_replace( '/<!--.*?-->/s', '', (string) file_get_contents( $root . '/parts/footer.html' ) ),
	'parts/sidebar.html' => (string) preg_replace( '/<!--.*?-->/s', '', (string) file_get_contents( $root . '/parts/sidebar.html' ) ),
);
$scopes['agency page (defaults)'] = $agency;

// The seven articles: English only, so no translation keys depend on their wording.
require_once $root . '/inc/setup/content-seed-data.php';
$articles = array();

foreach ( remotive_seed_content() as $item ) {
	if ( 'post' === $item['type'] ) {
		$articles[ $item['slug'] ] = preg_replace( '/<!--.*?-->/s', '', $item['content'] );
		$articles[ $item['slug'] . ' (excerpt and search listing)' ] = $item['excerpt'] . "\n" . $item['rm_title'] . "\n" . $item['rm_desc'] . "\n" . $item['title'];
	}
}

$scopes['articles (7 posts)'] = $articles;
$pending = array();

// The documentation prose: the changelog, the readmes and the docs folder (the legal texts are page content, so they are out of scope).
$docs = array();

foreach ( array_merge( array( 'readme.md', 'readme.txt' ), array_map( function ( $f ) { return 'docs/' . basename( $f ); }, glob( $root . '/docs/*.md' ) ) ) as $rel ) {
	if ( 0 === strpos( basename( $rel ), 'legal-' ) ) {
		continue;
	}

	$text = (string) file_get_contents( $root . '/' . $rel );
	$text = preg_replace( '/```.*?```/s', '', $text );

	// Older release notes are a historical record, so only the current series is held to the guide.
	if ( in_array( $rel, array( 'docs/changelog.md', 'docs/upgrading.md' ), true ) && preg_match( '/^## \[(?!1\.11[67]\.)\d/m', $text, $mm, PREG_OFFSET_CAPTURE ) ) {
		$text = substr( $text, 0, $mm[0][1] );
	} elseif ( 'readme.txt' === $rel && false !== ( $pos = strpos( $text, '== Changelog ==' ) ) ) {
		$text = substr( $text, 0, $pos ) . substr( $text, $pos, 4000 );
	}

	$docs[ $rel ] = $text;
}

$scopes['documentation'] = $docs;

$fail  = 0;
$debt  = in_array( '--debt', $argv ?? array(), true );
$total = 0;

function style_hits( $text, $banned, $phrases, $parallel ) {
	$hits = array();
	$t    = html_entity_decode( strip_tags( (string) $text ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$t    = preg_replace( '/`[^`]*`/', '', $t );

	if ( preg_match_all( "/\b(?:[A-Za-z]+n[\x{2019}']t|[A-Za-z]+[\x{2019}'](?:re|ve|ll|d|m)|(?:it|that|there|here|what|who|let|he|she)[\x{2019}']s)\b/iu", $t, $m ) ) {
		foreach ( $m[0] as $x ) {
			$hits[] = "contraction: $x";
		}
	}

	if ( false !== strpos( $t, "\u{2014}" ) ) {
		$hits[] = 'em dash';
	}

	if ( preg_match( '/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u', $t ) ) {
		$hits[] = 'emoji';
	}

	// Words inside quotation marks are someone else's wording (a client's keyword, say), so they are exempt.
	$unquoted = preg_replace( '/["\x{201C}][^"\x{201D}]*["\x{201D}]/u', '', $t );

	// Technical nouns the guide catches only in their figurative sense (a test harness, a customer journey, the underscore character).
	$unquoted = preg_replace( '/\b(?:test |audit |render |browser |CSS |layout-CSS |rendered |hoverless |standalone |hand-built |same |mechanical )?harness\b|\b(?:customer[- ]|email )journeys?\b|\bunderscore\b/i', '', $unquoted );

	foreach ( $banned as $b ) {
		if ( preg_match( '/\b' . preg_quote( $b, '/' ) . '\b/i', $unquoted ) ) {
			$hits[] = "banned word: $b";
		}
	}

	foreach ( $phrases as $p ) {
		if ( false !== stripos( $unquoted, $p ) ) {
			$hits[] = "dead phrase: $p";
		}
	}

	foreach ( $parallel as $re ) {
		if ( preg_match( $re, $t, $m ) ) {
			$hits[] = 'negation then correction: ' . trim( $m[0] );
		}
	}

	return $hits;
}

foreach ( $scopes as $scope => $items ) {
	foreach ( $items as $label => $text ) {
		++$total;
		$hits = style_hits( $text, $banned, $phrases, $parallel );

		if ( $hits ) {
			++$fail;
			echo "FAIL: $scope / $label: " . implode( '; ', array_unique( $hits ) ) . "\n";
		}
	}
}

if ( $debt ) {
	foreach ( $pending as $scope => $items ) {
		foreach ( $items as $label => $text ) {
			$hits = style_hits( $text, $banned, $phrases, $parallel );

			if ( $hits ) {
				echo "DEBT: $scope / $label: " . implode( '; ', array_unique( $hits ) ) . "\n";
			}
		}
	}
}

echo 'writing-style: ' . ( $total - $fail ) . " passed, $fail failed\n";
exit( $fail ? 1 : 0 );
