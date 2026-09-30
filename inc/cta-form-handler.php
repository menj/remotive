<?php
/**
 * Remotive Media — homepage CTA form handler.
 *
 * Thin wrapper around remotive_handle_lead_form_submission() (see
 * inc/lead-form-handler.php for the shared nonce/rate-limit/honeypot/
 * email logic every native form on this theme goes through). The
 * homepage's email-capture form (templates/front-page.html) submits here
 * by default via WordPress's standard admin-post.php pattern — no
 * third-party form service required out of the box. The submission URL
 * is still configurable at Appearance -> Theme Options -> Call-to-
 * Action, so a Formspree-style endpoint or a CRM webhook can replace
 * this at any time without touching code; this file is just what runs
 * when that setting is left at its default.
 */

defined( 'ABSPATH' ) || exit;

function remotive_handle_cta_submission() {
	remotive_handle_lead_form_submission( array(
		'form_key'       => 'cta',
		'nonce_action'   => 'remotive_cta_submit',
		'nonce_name'     => 'remotive_cta_nonce',
		'honeypot_field' => 'remotive_cta_website',
		'redirect_base'  => home_url( '/#contact' ),
		/* translators: %s: the site name */
		'email_subject'  => sprintf( __( 'New audit request from %s', 'remotive' ), get_bloginfo( 'name' ) ),
	) );
}
add_action( 'admin_post_remotive_cta_submit', 'remotive_handle_cta_submission' );
add_action( 'admin_post_nopriv_remotive_cta_submit', 'remotive_handle_cta_submission' );
