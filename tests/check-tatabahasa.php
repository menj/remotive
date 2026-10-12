<?php
/**
 * Malay (ms-MY) grammar, checked against the rules of Tatabahasa Dewan Edisi Ketiga (DBP, 2008)
 * that a script can check: the high-frequency error catalogue and the list of Indonesian forms to
 * remove. Run over every Malay string in the dictionary and the landing pages. It cannot judge
 * whether a sentence reads well; it stops known mistakes from coming back. Spelling and punctuation
 * are checked by tests/check-ejaan.php.
 *
 * Run: php tests/check-tatabahasa.php
 */

define( 'ABSPATH', '/x/' );

$root = dirname( __DIR__ );
$data = require $root . '/inc/i18n/ms.php';
$text = array();

foreach ( $data['strings'] as $en => $ms ) {
	$text[ 'dictionary: ' . mb_substr( $en, 0, 50 ) ] = $ms;
}

foreach ( $data['seo'] as $path => $fields ) {
	foreach ( $fields as $k => $v ) {
		$text[ "seo $k: " . ( $path ?: 'home' ) ] = $v;
	}
}

$q = "'((?:[^'\\\\]|\\\\.)*)'";
foreach ( array( 'inc/landing/landing-pages.php', 'inc/landing/agency-page.php', 'inc/forms/thank-you.php', 'inc/landing/landing-copy.php' ) as $file ) {
	$code = file_get_contents( $root . '/' . $file );
	if ( preg_match_all( "/array\\(\\s*$q,\\s*$q,\\s*$q,\\s*$q\\s*\\)/s", $code, $m, PREG_SET_ORDER ) ) {
		foreach ( $m as $hit ) {
			$ms                                                 = str_replace( "\\'", "'", $hit[2] );
			$text[ 'landing: ' . mb_substr( $ms, 0, 40 ) ] = $ms;
		}
	}
}

/** Rule name => regex that must not match (case-insensitive). */
$rules = array(
	'bentuk lewah memper-...-i (Tatabahasa Dewan, imbuhan: memperolehi, memperbaiki and the rest)' => '/\b(?:memperolehi|memperbaiki\w*|memperkuasai|memperdalami|memperakui|mempersetujui|memperhalusi)\b/iu',
	'memperbesar tidak menerima -kan' => '/\bmemperbesarkan\b/iu',
	'demi untuk ialah lewah (§4.10)' => '/\bdemi untuk\b/iu',
	'bilangan tak tentu tidak menerima kata nama ganda (§4.7)' => '/\b(?:semua|segala|banyak|beberapa|sedikit|para)\s+\p{L}+-\p{L}+\b/iu',
	'adalah tidak mendahului frasa nama (§4.6): guna ialah' => '/\badalah\s+(?:seorang|sebuah|suatu|satu|sebuah|pengarang|pengasas|ketua)\b/iu',
	'setengah (separuh) dan sesetengah (sebahagian) tidak sama (§4.8)' => '/\bsetengah\s+(?:orang|agensi|pelanggan|syarikat|jenama|pasaran|kes)\b/iu',
	'kata sendi nama: perbandingan menggunakan daripada, bukan dari (§4.1)' => '/\b(?:lebih|kurang|berbanding|berbeza|sama)\s+(?:\p{L}+\s+){0,3}dari\b(?!\s+(?:segi|sudut|sisi|semasa|satu|bulan|hari|tahun|pada|mana|sini|situ|sana|awal|luar|jauh|lapangan))/iu',
	'kata sendi nama: sasaran manusia menggunakan kepada, bukan pada (§4.2)' => '/\b(?:berikan|hadiahkan|serahkan|hantar|berkata|bercakap|beritahu|jual)\s+(?:\p{L}+\s+){0,2}pada\s+(?:anda|kami|mereka|pelanggan|klien|beliau|dia|pengguna)\b/iu',
	'peN-...-an daripada meN-: perlaksanaan ialah pelaksanaan (§4.3)' => '/\b(?:perlaksanaan|pelumbaan|pensegmenan|pengcacheaan|pengtadbiran|perlaporan|perpasaran|perkhidmatankan)\b/iu',
	'bentuk Indonesia (§3): metrum, ekamatra' => '/\b(?:metrum|ekamatra|pengrataan|pendedikasian|menugaskan rekaannya|bertekstur)\b/iu',
	'Ulasan untuk review ialah Laporan (§3): laporan industri, laporan pasaran' => '/\bulasan\s+(?:industri|pasaran)\b/iu',
	'Indonesia: karena, bisa, kantor, situs, layanan, biaya, unduh' => '/\b(?:karena|bisa|kantor|situs|layanan|pelayanan|biaya|unduh|mengunduh|unggah|kinerja|solusi|terkait|kegiatan)\b/iu',
);

$fail  = 0;
$total = 0;

foreach ( $text as $label => $value ) {
	++$total;
	$bad = array();

	foreach ( $rules as $name => $re ) {
		if ( preg_match( $re, $value, $m ) ) {
			$bad[] = $name . ' [' . trim( $m[0] ) . ']';
		}
	}

	if ( $bad ) {
		++$fail;
		echo "FAIL: $label: " . implode( '; ', $bad ) . "\n";
	}
}

echo 'Malay grammar: ' . ( $total - $fail ) . " strings pass the Tatabahasa Dewan checks, $fail fail.\n";
exit( $fail ? 1 : 0 );
