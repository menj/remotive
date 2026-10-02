<?php
/**
 * Checks the landing page copy is complete: every text is an array of four
 * non-empty strings (English, Bahasa Melayu, Simplified and Traditional
 * Chinese), every service has every field, and the Chinese strings actually
 * contain Chinese. Run: php tests/check-landing-copy.php
 */

define( 'ABSPATH', __DIR__ . '/' );

require dirname( __DIR__ ) . '/inc/landing/landing-copy.php';

$problems = array();

$check_text = function ( $where, $t ) use ( &$problems ) {
	// Rendering reads indexes 0 to 3, so the keys must be exactly those.
	if ( ! is_array( $t ) || array( 0, 1, 2, 3 ) !== array_keys( $t ) ) {
		$problems[] = "$where: expected 4 strings, got " . ( is_array( $t ) ? count( $t ) : gettype( $t ) );
		return;
	}
	foreach ( $t as $i => $text ) {
		if ( ! is_string( $text ) || '' === trim( $text ) ) {
			$problems[] = "$where: string $i is empty";
		}
	}
	foreach ( array( 2, 3 ) as $i ) {
		if ( isset( $t[ $i ] ) && is_string( $t[ $i ] ) && ! preg_match( '/\p{Han}/u', $t[ $i ] ) ) {
			$problems[] = "$where: string $i contains no Chinese characters";
		}
	}
};

$fields = array( 'photo_alt', 'label', 'eyebrow', 'title', 'lead', 'form_title', 'cta' );

foreach ( remotive_landing_services() as $slug => $service ) {
	foreach ( $fields as $field ) {
		if ( ! isset( $service[ $field ] ) ) {
			$problems[] = "$slug: missing $field";
			continue;
		}
		// A label such as "SEO" is the same in every language.
		if ( 'label' === $field ) {
			continue;
		}
		$check_text( "$slug.$field", $service[ $field ] );
	}

	if ( empty( $service['points'] ) || 3 !== count( $service['points'] ) ) {
		$problems[] = "$slug: expected 3 points";
	} else {
		foreach ( $service['points'] as $n => $point ) {
			$check_text( "$slug.points[$n]", $point );
		}
	}

	$faq = remotive_lp_faq_items( $slug );
	if ( count( $faq ) < 3 ) {
		$problems[] = "$slug: expected at least 3 FAQ items";
	}
	foreach ( $faq as $n => $qa ) {
		$check_text( "$slug.faq[$n].question", $qa[0] );
		$check_text( "$slug.faq[$n].answer", $qa[1] );
	}
}

if ( $problems ) {
	fwrite( STDERR, "Landing copy problems:\n - " . implode( "\n - ", $problems ) . "\n" );
	exit( 1 );
}

echo 'Landing copy complete: ' . count( remotive_landing_services() ) . " services, four languages.\n";
