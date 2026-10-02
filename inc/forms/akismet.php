<?php
/**
 * Akismet spam filtering for the contact forms.
 *
 * The forms have a honeypot, which stops naive bots and nothing else. This
 * runs a submission past Akismet the same way a comment is checked, so
 * contact spam is filtered by the same service and the same reputation data
 * as comment spam.
 *
 * The plugin is optional. Akismet holds the API key and this module has no
 * way to store one, so with the plugin inactive, or active but unconnected,
 * every check returns "not spam" and submissions carry on exactly as before.
 * The forms must never stop working because a spam filter is missing.
 *
 * Suspected spam is stored rather than discarded, marked as spam and hidden
 * from the enquiries list. No filter is perfect, and a silently deleted
 * enquiry is a lost client; a filed one can be recovered in a click.
 *
 * @package Remotive
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether Akismet is present and holds a key.
 *
 * @return bool
 */
function remotive_akismet_ready() {
	if ( ! class_exists( 'Akismet' ) || ! is_callable( array( 'Akismet', 'comment_check' ) ) ) {
		return false;
	}

	$key = is_callable( array( 'Akismet', 'get_api_key' ) ) ? Akismet::get_api_key() : '';

	return ! empty( $key );
}

/**
 * Ask Akismet whether a submission is spam.
 *
 * The payload uses comment field names because that is the vocabulary of
 * the comment-check endpoint; comment_type marks it as a contact form so
 * Akismet scores it appropriately rather than as blog comment.
 *
 * @param array $lead {
 *     @type string $name    Sender's name.
 *     @type string $email   Sender's email address.
 *     @type string $message Message body.
 * }
 * @return bool True only when Akismet positively identifies spam.
 */
function remotive_akismet_is_spam( $lead ) {
	if ( ! remotive_akismet_ready() ) {
		return false;
	}

	$payload = array(
		'comment_type'         => 'contact-form',
		'comment_author'       => $lead['name'] ?? '',
		'comment_author_email' => $lead['email'] ?? '',
		'comment_content'      => $lead['message'] ?? '',
		'permalink'            => wp_get_referer() ? wp_get_referer() : home_url( '/' ),
		'user_ip'              => function_exists( 'remotive_client_ip' ) ? remotive_client_ip() : '',
		'user_agent'           => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
		'referrer'             => isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '',
	);

	/**
	 * Adjust what is sent to Akismet for a contact form submission.
	 *
	 * @param array $payload The comment-check payload.
	 * @param array $lead    The submitted values.
	 */
	$payload = apply_filters( 'remotive_akismet_payload', $payload, $lead );

	$result = Akismet::comment_check( $payload );

	// comment_check returns false when it cannot reach the service or has
	// no key. An outage must not block a genuine enquiry, so anything that
	// is not a positive spam verdict counts as clean.
	if ( ! is_object( $result ) || empty( $result->is_spam ) ) {
		return false;
	}

	// A "discard" tip means Akismet is certain, usually a known bot. Even
	// then the submission is stored rather than dropped, because being
	// certain is not the same as being right.
	return true;
}

/**
 * Report a stored enquiry to Akismet as a missed spam or a false positive.
 *
 * Akismet's own submit_spam_comment() reads from the comments table, so it
 * cannot be used for a lead. This posts to the same endpoints directly,
 * which is what teaches the filter and improves it for everyone.
 *
 * @param int    $post_id Stored lead.
 * @param string $verdict Either 'spam' or 'ham'.
 * @return bool Whether the report was sent.
 */
function remotive_akismet_report_lead( $post_id, $verdict ) {
	if ( ! remotive_akismet_ready() || ! is_callable( array( 'Akismet', 'http_post' ) ) ) {
		return false;
	}

	$post = get_post( $post_id );

	if ( ! $post ) {
		return false;
	}

	$path = ( 'spam' === $verdict ) ? 'submit-spam' : 'submit-ham';

	$request = array(
		'blog'                 => get_option( 'home' ),
		'comment_type'         => 'contact-form',
		'comment_author'       => (string) get_post_meta( $post_id, '_remotive_lead_name', true ),
		'comment_author_email' => (string) get_post_meta( $post_id, '_remotive_lead_email', true ),
		'comment_content'      => $post->post_content,
		'user_ip'              => (string) get_post_meta( $post_id, '_remotive_lead_ip', true ),
		'user_agent'           => (string) get_post_meta( $post_id, '_remotive_lead_agent', true ),
	);

	Akismet::http_post( build_query( $request ), $path );

	return true;
}

/**
 * Handle the Mark as spam and Not spam actions on the enquiries card.
 */
function remotive_handle_lead_spam_action() {
	if ( ! current_user_can( 'edit_remotive_leads' ) ) {
		wp_die( esc_html__( 'You are not allowed to do that.', 'remotive' ) );
	}

	$post_id = isset( $_POST['lead_id'] ) ? absint( wp_unslash( $_POST['lead_id'] ) ) : 0;
	$verdict = isset( $_POST['verdict'] ) && 'ham' === $_POST['verdict'] ? 'ham' : 'spam';

	check_admin_referer( 'remotive_lead_spam_' . $post_id );

	$post = get_post( $post_id );

	if ( $post && REMOTIVE_LEAD_POST_TYPE === $post->post_type ) {
		// Moving between folders, the same as marking a comment. Restoring
		// from spam publishes it back into the enquiries list.
		wp_update_post(
			array(
				'ID'          => $post_id,
				'post_status' => 'spam' === $verdict ? 'remotive_spam' : 'publish',
			)
		);

		remotive_akismet_report_lead( $post_id, $verdict );
	}

	wp_safe_redirect( add_query_arg( 'remotive_lead', $verdict, admin_url( 'themes.php?page=remotive-theme-options' ) ) );
	exit;
}
add_action( 'admin_post_remotive_lead_spam', 'remotive_handle_lead_spam_action' );
