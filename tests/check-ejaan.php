<?php
/**
 * Malay (ms) spelling and punctuation, checked against the rules of the Dewan
 * Bahasa dan Pustaka's Pedoman Umum Ejaan Bahasa Melayu that the site follows
 * (summarised in docs/ssot.md): the rules a script can check, run over every
 * Malay string in the dictionary and the landing pages. It cannot judge
 * wording; it stops the mechanical mistakes from coming back.
 *
 * Run: php tests/check-ejaan.php
 */

define( 'ABSPATH', '/x/' );

$root = dirname( __DIR__ );
$data = require $root . '/inc/i18n/ms.php';
$text = array();

foreach ( $data['strings'] as $en => $ms ) {
	$text[ 'dictionary: ' . mb_substr( $en, 0, 50 ) ] = $ms;
}

$titles = array();
foreach ( $data['seo'] as $path => $fields ) {
	$titles[ 'seo title: ' . ( $path ?: 'home' ) ] = $fields['title'];
	foreach ( $fields as $k => $v ) {
		$text[ "seo $k: " . ( $path ?: 'home' ) ] = $v;
	}
}

// Landing copy: every four-part array( English, Malay, Simplified, Traditional ).
$q = "'((?:[^'\\\\]|\\\\.)*)'";
foreach ( array( 'inc/landing/landing-pages.php', 'inc/forms/thank-you.php', 'inc/landing/landing-copy.php' ) as $file ) {
	$code = file_get_contents( $root . '/' . $file );
	if ( preg_match_all( "/(.{0,22})array\\(\\s*$q,\\s*$q,\\s*$q,\\s*$q\\s*\\)/s", $code, $m, PREG_SET_ORDER ) ) {
		foreach ( $m as $hit ) {
			$ms                                    = str_replace( "\\'", "'", $hit[3] );
			$text[ "landing: " . mb_substr( $ms, 0, 40 ) ] = $ms;
			if ( false !== strpos( $hit[1], "'title'" ) ) {
				$titles[ 'landing title: ' . mb_substr( $ms, 0, 40 ) ] = $ms;
			}
		}
	}
}

$allowed_pun = array( 'adapun', 'andaipun', 'ataupun', 'bagaimanapun', 'biarpun', 'kalaupun', 'kendatipun', 'mahupun', 'meskipun', 'sekalipun', 'sungguhpun', 'walaupun', 'lagipun', 'dihimpun', 'himpun', 'terhimpun', 'menghimpun' );

$rules = array(
	'a comma before an anak ayat that follows its main clause (kerana, supaya, agar, sebelum, selepas)' => '/(?<!ketiga)(?<=\w), (kerana|supaya|agar|sebelum|selepas)\b/u',
	'tetapi or melainkan without a comma before it'                                                      => '/(?<=[A-Za-z0-9%)]) (tetapi|melainkan)\b/u',
	'US dollars written AS$ or a bare $ (write US$)'                                                     => '/AS\$|(?<![A-Za-z])\$(?=\d)/u',
	'k after a number (write ribu)'                                                                      => '/\d(?:\.\d+)?k\b/u',
	'an em dash (the tanda pisah is the en dash, spaced)'                                                => '/—/u',
	'an en dash without a space on both sides'                                                           => '/(?<! )–|–(?! )/u',
	'a straight double quote (use curly quotes)'                                                         => '/"/u',
	'a serial comma missing before the final dan of a list of three short items'                         => '/(?:^|[:(;] |\. )(?:[\p{L}\p{N}\-+.\/&]+ ?){1,2}, (?:[\p{L}\p{N}\-+.\/&]+ ?){1,2} dan (?:[\p{L}\p{N}\-+.\/&]+ ?){1,2}(?=$|[.,;:)])/u',
	'Indonesian spelling or abbreviation'                                                                => '/\b(karena|bahwa|yg|dgn|utk|tdk|jgn|dlm|krn|sbb|resiko|prosentase|persen|aktifitas|kualitas|sistim|ekstrim|analisa|optimisasi|optimalisasi|standar|situs|tautan|layanan|pelayanan|informasi)\b/iu',
	'di, ke or dari joined to a word of place or direction'                                              => '/\b(dimana|disini|disana|disitu|diatas|dibawah|didalam|diluar|didepan|dibelakang|ditengah|disebelah|disamping|diantara|dikalangan|kemana|kesini|kesana|kedalam|kebawah|keatas|kedepan)\b/iu',
	'ke pada or dari pada written apart'                                                                 => '/\b(ke pada|dari pada)\b/iu',
	'lah, kah or tah written apart'                                                                      => '/\w (lah|kah|tah)\b/u',
	'nya, ku or mu written apart'                                                                        => '/\w (nya|ku|mu)\b/u',
	'ke before a number without a hyphen'                                                                => '/\bke ?\d/u',
	'a decade written 50an (write 50-an)'                                                                => '/\b\d0an\b/u',
	'a space before punctuation or two spaces in a row'                                                  => '/ [,;:?!]| {2,}|\s\.(?!\.)(?=\s|$)/u',
	'an English month or weekday'                                                                        => '/\b(January|February|March|May|June|July|August|October|December|Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday)\b/u',
	'a language or people written without a capital after bahasa/orang'                                  => '/\b(bahasa|orang|bangsa) (melayu|inggeris|cina|mandarin|jepun|india)\b/u',
	'an abbreviated title without its full stop (Dr., Prof., En., Pn., Tn.)'                              => '/\b(Dr|Prof|En|Pn|Tn|Sdr|Sdri|Hj|Hjh)\b(?!\.)/u',
);

$failed = array();

foreach ( $text as $where => $ms ) {
	foreach ( $rules as $label => $rx ) {
		if ( preg_match( $rx, $ms, $m ) ) {
			$failed[] = "$where: $label: «" . trim( $m[0] ) . '»';
		}
	}

	if ( preg_match_all( '/\b\w{3,}pun\b/u', $ms, $m ) ) {
		foreach ( $m[0] as $word ) {
			if ( ! in_array( mb_strtolower( $word ), $allowed_pun, true ) ) {
				$failed[] = "$where: 'pun' written together outside the list: «$word»";
			}
		}
	}
}

// A title is not a sentence: no full stop at the end (Pedoman, tanda titik, rule 11).
foreach ( $titles as $where => $title ) {
	if ( preg_match( '/[^.]\.$/u', trim( $title ) ) ) {
		$failed[] = "$where: a title ends with a full stop: «" . mb_substr( $title, -30 ) . '»';
	}
}

if ( $failed ) {
	foreach ( array_unique( $failed ) as $f ) {
		fwrite( STDERR, "FAIL: $f\n" );
	}
	exit( 1 );
}

echo 'Malay spelling: ' . count( $text ) . " strings follow the Pedoman Umum Ejaan checks.\n";
