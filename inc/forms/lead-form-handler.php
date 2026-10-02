<?php
/**
 * Remotive Media — shared native lead-form handling.
 *
 * Every native form this theme ships (the homepage CTA, the About page's
 * contact form) goes through this one function rather than each having
 * its own near-duplicate copy of the same nonce/honeypot/rate-limit/
 * escaping logic. One security-reviewed code path is easier to keep
 * correct than several drifting copies — see inc/forms/cta-form-handler.php
 * and inc/forms/about-form-handler.php for the two thin wrappers that call
 * this with their own action/nonce names.
 *
 * Threat model / residual risk, documented rather than silently assumed
 * away: these endpoints are intentionally public (admin_post_nopriv_*) —
 * anonymous visitors must be able to submit them, so no capability check
 * applies (by design). The nonce stops CSRF, the honeypot filters
 * unsophisticated bots, but neither stops a scripted client that loads
 * the real page first (for a valid nonce) and submits repeatedly. The
 * rate limit raises the cost of that but doesn't eliminate it. A
 * CAPTCHA or WAF-level rate limit would close that gap further —
 * deliberately not added, since picking a vendor is a product decision.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lightweight per-IP, per-form-type throttle using a transient — no
 * database schema change, no external service. REMOTE_ADDR can be
 * spoofed or shared behind a proxy/CDN (an inherent limitation of
 * IP-based throttling generally, not something introduced here), so
 * this is a real but imperfect mitigation, not a guarantee.
 *
 * $max is the number of submissions allowed per ten minutes. Forms that
 * receive paid mobile traffic raise it, because carrier-grade NAT puts many
 * unrelated visitors behind one address.
 */
function remotive_form_rate_limit_exceeded( $form_key, $max = 3 ) {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	if ( empty( $ip ) ) {
		return false; // Can't identify a submitter to throttle; fail open rather than block everyone.
	}

	$key   = 'remotive_rl_' . $form_key . '_' . md5( $ip );
	$count = (int) get_transient( $key );

	if ( $count >= $max ) {
		return true;
	}

	set_transient( $key, $count + 1, 10 * MINUTE_IN_SECONDS );
	return false;
}

/**
 * Handles one lead-form submission end to end: nonce, rate limit,
 * honeypot, email validation + CRLF guard, wp_mail(), redirect with a
 * success/error status. Exits (redirects) in every path — never returns.
 *
 * @param array $args {
 *     @type string $form_key         Short slug used in rate-limit/redirect keys, e.g. 'cta', 'about'.
 *     @type string $nonce_action     Must match the wp_nonce_field() action used in the form's token.
 *     @type string $nonce_name       Must match the wp_nonce_field() field name used in the form's token.
 *     @type string $honeypot_field   $_POST key of the honeypot input.
 *     @type string $redirect_base    Where to send the visitor back to (with #fragment if relevant).
 *     @type string $email_subject    Already-translated subject line for the notification email.
 *     @type string[] $extra_lines    Optional, already-sanitised "Label: value" lines appended to the stored and emailed message (service, campaign source).
 *     @type array  $thanks_args      Optional query args added to the thank-you redirect (already sanitised).
 *     @type string $thanks_url       Optional confirmation URL to use instead of the shared thank-you page.
 *     @type int    $rate_limit       Default 3. Submissions allowed per IP per ten minutes.
 * }
 */
function remotive_handle_lead_form_submission( $args ) {
	check_admin_referer( $args['nonce_action'], $args['nonce_name'] );

	$redirect_base = $args['redirect_base'];
	$status_key    = 'remotive_' . $args['form_key'];

	// Honeypot: a real visitor never sees or reaches this field. Any
	// value here means a bot filled every input it could find. Redirect
	// exactly as if the submission succeeded — no error, no signal that
	// a trap was hit.
	if ( ! empty( $_POST[ $args['honeypot_field'] ] ) ) {
		wp_safe_redirect( add_query_arg( $status_key, 'success', $redirect_base ) );
		exit;
	}

	// After the honeypot, so bot traffic that fills the trap does not use up the
	// quota of real visitors who share its address.
	if ( remotive_form_rate_limit_exceeded( $args['form_key'], isset( $args['rate_limit'] ) ? (int) $args['rate_limit'] : 3 ) ) {
		wp_safe_redirect( add_query_arg( $status_key, 'error', $redirect_base ) );
		exit;
	}

	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

	// sanitize_email() and is_email() below should already make CRLF
	// injection into the Reply-To header impossible (neither permits raw
	// control characters in a valid address) — this check doesn't rely
	// on that being true, it makes the guarantee explicit and
	// independent of how those functions are implemented internally,
	// now or in a future WordPress version.
	if ( $email !== '' && preg_match( '/[\r\n]/', $email ) ) {
		$email = '';
	}

	if ( empty( $email ) || ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( $status_key, 'error', $redirect_base ) );
		exit;
	}

	// Name and message are optional — the homepage CTA form only ever
	// sends email, the About page's contact form sends both. Both are
	// sanitized the same way regardless of which form they came from.
	$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

	if ( ! empty( $args['extra_lines'] ) ) {
		$message = trim( $message . "\n\n" . implode( "\n", $args['extra_lines'] ) );
	}

	$to        = remotive_get_theme_option( 'contact_email' );
	$body_lines = array();
	if ( $name !== '' ) {
		/* translators: %s: submitted name */
		$body_lines[] = sprintf( __( 'Name: %s', 'remotive' ), $name );
	}
	/* translators: %s: submitted email address */
	$body_lines[] = sprintf( __( 'Email: %s', 'remotive' ), $email );
	if ( $message !== '' ) {
		/* translators: %s: submitted message */
		$body_lines[] = sprintf( __( "Message:\n%s", 'remotive' ), $message );
	}
	/* translators: %s: submission date and time */
	$body_lines[] = sprintf( __( 'Submitted: %s', 'remotive' ), current_time( 'mysql' ) );

	$body = implode( "\n\n", $body_lines );

	// Ask Akismet before anything else. A positive verdict still stores the
	// submission, marked as spam, because no filter is perfect and a
	// silently discarded enquiry is a lost client; it is simply kept out of
	// the enquiries list and never emailed.
	$is_spam = function_exists( 'remotive_akismet_is_spam' )
		? remotive_akismet_is_spam(
			array(
				'name'    => $name,
				'email'   => $email,
				'message' => $message,
			)
		)
		: false;

	// Store first, mail second. wp_mail() fails for ordinary reasons, and
	// when it did the enquiry used to disappear entirely; this way a mail
	// failure costs the notification, not the lead.
	$stored = function_exists( 'remotive_store_lead' )
		? remotive_store_lead(
			array(
				'name'    => $name,
				'email'   => $email,
				'message' => $message,
				'source'  => $args['form_key'] . ' form',
				'is_spam' => $is_spam,
			)
		)
		: null;

	// Spam is filed, not forwarded. The sender still sees the ordinary
	// confirmation, because telling a spammer their submission was caught
	// only tells them what to change.
	$sent = $is_spam
		? true
		: wp_mail( $to, $args['email_subject'], $body, array( 'Reply-To: ' . $email ) );

	if ( ! $is_spam && function_exists( 'remotive_record_lead_mail_status' ) ) {
		remotive_record_lead_mail_status( $stored, (bool) $sent );
	}

	// The visitor is told the message was received once it is safely
	// stored, because from their side it has been: someone will read it.
	if ( $stored && ! is_wp_error( $stored ) ) {
		$sent = true;
	}

	// Success goes to a real page; errors stay on the form.
	//
	// A query parameter on the page the visitor was already on is not a
	// conversion destination: analytics and ad platforms fire on a URL, and
	// assets/js/lead-form-status.js strips the parameter with
	// history.replaceState() as soon as it has read it, so whether a tag
	// sees it at all is a race with script order. A distinct page removes
	// the ambiguity, and gives the visitor something better than one line
	// of text on the band they just submitted from.
	//
	// The form key travels as ?from=, so a single page can vary its own
	// copy and report which form converted without a page each.
	//
	// Falls back to the previous inline behaviour when the page is missing,
	// unpublished or renamed — a deleted page must not swallow a lead that
	// has already been stored and emailed.
	if ( $sent ) {
		$thanks     = get_page_by_path( 'thank-you', OBJECT, 'page' );
		$thanks_url = ! empty( $args['thanks_url'] ) ? $args['thanks_url'] : ( $thanks && 'publish' === $thanks->post_status ? get_permalink( $thanks->ID ) : '' );

		if ( $thanks_url ) {
			wp_safe_redirect(
				add_query_arg(
					array_merge( array( 'from' => $args['form_key'] ), isset( $args['thanks_args'] ) ? $args['thanks_args'] : array() ),
					$thanks_url
				)
			);
			exit;
		}
	}

	wp_safe_redirect( add_query_arg( $status_key, $sent ? 'success' : 'error', $redirect_base ) );
	exit;
}

/**
 * Fresh nonces for the lead forms, for pages served from a cache.
 *
 * A nonce lives 12 to 24 hours, and a page cached for longer carries expired
 * ones: the visitor would be told the link has expired and the lead would be
 * lost. The nonce check stays exactly as it is; instead, assets/js/remotive.js
 * asks this endpoint on page load and swaps fresh values into the form's
 * hidden fields. The server-rendered nonce remains as the no-script fallback.
 *
 * The response carries nothing secret: for a logged-out visitor a nonce is the
 * same for everyone, and it only authorises the form it belongs to. The
 * response is marked uncacheable so a cache cannot freeze it.
 */
function remotive_ajax_form_nonces() {
	nocache_headers();

	wp_send_json(
		array(
			'remotive_cta_nonce'     => wp_create_nonce( 'remotive_cta_submit' ),
			'remotive_about_nonce'   => wp_create_nonce( 'remotive_about_submit' ),
			'remotive_contact_nonce' => wp_create_nonce( 'remotive_contact_submit' ),
			'remotive_lp_nonce'      => wp_create_nonce( 'remotive_lp_submit' ),
		)
	);
}
add_action( 'wp_ajax_remotive_form_nonces', 'remotive_ajax_form_nonces' );
add_action( 'wp_ajax_nopriv_remotive_form_nonces', 'remotive_ajax_form_nonces' );
