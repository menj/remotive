<?php
/**
 * Remotive Media — form confirmation page support.
 *
 * The thank-you page every native form redirects to on success. Three jobs:
 * keep it out of search results, vary its opening line by which form was
 * submitted, and expose a conversion event for analytics.
 *
 * Why a page rather than the previous inline confirmation: a query
 * parameter on the page the visitor was already on is not a conversion
 * destination. GA4, Google Ads and Meta all key on a URL, and
 * assets/js/lead-form-status.js strips the parameter with
 * history.replaceState() the moment it reads it — so whether a tag ever
 * sees it depends on script order. A real page removes that race, and
 * gives the visitor a genuine answer to "what happens now" instead of one
 * line of text.
 *
 * @package Remotive
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the current request is the thank-you page.
 *
 * @return bool
 */
function remotive_is_thanks_page() {
	return is_page( 'thank-you' );
}

/**
 * Keep the confirmation page out of search results.
 *
 * It is thin by design and only meaningful immediately after a submission;
 * indexed, it would compete with real pages and could be landed on cold,
 * where "thank you" makes no sense. noindex rather than blocking in
 * robots.txt: the page must stay crawlable so ad platforms can verify the
 * conversion URL resolves.
 *
 * follow is kept so the links out of it still pass value.
 *
 * @param array $robots
 * @return array
 */
function remotive_thanks_noindex( $robots ) {
	if ( remotive_is_thanks_page() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}

	return $robots;
}
add_filter( 'wp_robots', 'remotive_thanks_noindex' );

/**
 * Which form the visitor came from.
 *
 * Read-only use of a GET value that only ever selects between known keys,
 * so there is no nonce to check: nothing is written and an arbitrary value
 * falls through to the default sentence.
 *
 * @return string One of 'cta', 'about', 'contact', or '' when unknown.
 */
function remotive_thanks_source() {
	$from = isset( $_GET['from'] ) ? sanitize_key( wp_unslash( $_GET['from'] ) ) : '';

	return in_array( $from, array( 'cta', 'about', 'contact' ), true ) ? $from : '';
}

/**
 * Opening line, varied by which form was submitted.
 *
 * The homepage CTA collects an email address only, so promising a reply to
 * "what you sent" would be wrong there — it says what will actually arrive
 * instead. Contact and About both collect a message.
 *
 * @param array $tokens
 * @return array
 */
function remotive_thanks_tokens( $tokens ) {
	switch ( remotive_thanks_source() ) {
		case 'cta':
			$lead = __( 'Your audit request is in. We will look at what you have and reply within three business days with what we would fix first.', 'remotive' );
			break;

		case 'about':
		case 'contact':
			$lead = __( 'We have your message. A person reads it and replies within three business days, Singapore hours.', 'remotive' );
			break;

		default:
			$lead = __( 'We have your message and will reply within three business days, Singapore hours.', 'remotive' );
	}

	$tokens['__REMOTIVE_THANKS_LEAD__'] = esc_html( $lead );

	return $tokens;
}
add_filter( 'remotive_theme_option_tokens', 'remotive_thanks_tokens' );

/**
 * Push a conversion event for tag managers.
 *
 * Emitted only on the thank-you page and only when the visitor arrived from
 * a known form, so a direct visit or a refresh with no source does not
 * report a conversion.
 *
 * dataLayer is created if absent, which is the documented GTM pattern and
 * costs nothing when no container is installed — the array is simply never
 * read. No vendor script is loaded and no identifier is sent: the payload
 * is the event name and which form it came from. Analytics is the site
 * owner's choice, so this exposes the signal rather than picking a vendor.
 *
 * A matching CustomEvent is dispatched for anything listening without a
 * tag manager.
 */
function remotive_thanks_conversion_event() {
	if ( ! remotive_is_thanks_page() ) {
		return;
	}

	$source = remotive_thanks_source();

	if ( '' === $source ) {
		return;
	}

	printf(
		'<script>window.dataLayer=window.dataLayer||[];' .
		'window.dataLayer.push({"event":"remotive_lead","form":%1$s});' .
		'document.addEventListener("DOMContentLoaded",function(){' .
		'document.dispatchEvent(new CustomEvent("remotive:lead",{detail:{form:%1$s}}));' .
		'});</script>',
		wp_json_encode( $source )
	);
}
add_action( 'wp_footer', 'remotive_thanks_conversion_event', 5 );
