<?php
/**
 * Remotive Media — Contact page form handler.
 *
 * Thin wrapper around remotive_handle_lead_form_submission() (see
 * inc/lead-form-handler.php) — the third form using that shared logic,
 * alongside the homepage CTA and the About page. Own nonce action and
 * honeypot field name so its rate-limit/nonce state never collides with
 * the other two, even if a visitor has multiple tabs open across pages.
 */

defined( 'ABSPATH' ) || exit;

function remotive_handle_contact_submission() {
	// Same reasoning as the About form: this can be embedded on any page
	// using the page-contact.html template, so redirect back to wherever
	// it was actually submitted from rather than a hardcoded slug.
	$referer       = wp_get_referer();
	$redirect_base = $referer ? remove_query_arg( 'remotive_contact', $referer ) : home_url( '/' );
	$redirect_base = strtok( $redirect_base, '#' ) . '#contact-form';

	remotive_handle_lead_form_submission( array(
		'form_key'       => 'contact',
		'nonce_action'   => 'remotive_contact_submit',
		'nonce_name'     => 'remotive_contact_nonce',
		'honeypot_field' => 'remotive_contact_website',
		'redirect_base'  => $redirect_base,
		/* translators: %s: the site name */
		'email_subject'  => sprintf( __( 'New contact form message via %s', 'remotive' ), get_bloginfo( 'name' ) ),
	) );
}
add_action( 'admin_post_remotive_contact_submit', 'remotive_handle_contact_submission' );
add_action( 'admin_post_nopriv_remotive_contact_submit', 'remotive_handle_contact_submission' );
