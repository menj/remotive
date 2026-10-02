<?php
/**
 * Remotive Media — About page contact form handler.
 *
 * Thin wrapper around remotive_handle_lead_form_submission() (see
 * inc/forms/lead-form-handler.php). Unlike the homepage CTA, this form's
 * destination isn't configurable in Theme Options — it's a simpler,
 * secondary contact point, always using the native handler. If that
 * changes, add an 'about_form_action' option following the exact same
 * pattern as 'cta_form_action' in inc/options/theme-options.php.
 */

defined( 'ABSPATH' ) || exit;

function remotive_handle_about_submission() {
	// Unlike the homepage CTA (always the homepage, so a hardcoded
	// home_url() anchor is correct), this form can be embedded on any
	// page using the page-about.html template — the admin names that
	// page's slug, not this code. wp_get_referer() finds wherever the
	// form was actually submitted from; wp_safe_redirect() (used inside
	// remotive_handle_lead_form_submission()) independently enforces
	// that the final redirect stays on this site regardless, so this
	// is safe even if the referer were ever something unexpected.
	$referer        = wp_get_referer();
	$redirect_base  = $referer ? remove_query_arg( 'remotive_about', $referer ) : home_url( '/' );
	$redirect_base  = strtok( $redirect_base, '#' ) . '#contact-form';

	remotive_handle_lead_form_submission( array(
		'form_key'       => 'about',
		'nonce_action'   => 'remotive_about_submit',
		'nonce_name'     => 'remotive_about_nonce',
		'honeypot_field' => 'remotive_about_website',
		'redirect_base'  => $redirect_base,
		/* translators: %s: the site name */
		'email_subject'  => sprintf( __( 'New contact form message via %s', 'remotive' ), get_bloginfo( 'name' ) ),
	) );
}
add_action( 'admin_post_remotive_about_submit', 'remotive_handle_about_submission' );
add_action( 'admin_post_nopriv_remotive_about_submit', 'remotive_handle_about_submission' );
