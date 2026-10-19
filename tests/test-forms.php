<?php
/**
 * Form handling and error handling fixes found in a review of the codebase.
 * Run: php tests/test-forms.php
 */

require __DIR__ . '/bootstrap.php';

$GLOBALS['slashed'] = array();
$GLOBALS['kses']    = array();
$GLOBALS['has_kses'] = true;

function has_filter( $hook, $cb = false ) { return 'content_save_pre' === $hook && 'wp_filter_post_kses' === $cb && $GLOBALS['has_kses'] ? 10 : false; }
function kses_remove_filters() { $GLOBALS['kses'][] = 'off'; }
function kses_init_filters() { $GLOBALS['kses'][] = 'on'; }
function wp_slash( $v ) { return is_array( $v ) ? array_map( 'wp_slash', $v ) : addslashes( (string) $v ); }
function wp_insert_post( $args, $err = false ) { $GLOBALS['inserted'] = $args; $GLOBALS['kses'][] = 'insert'; return 7; }

require dirname( __DIR__ ) . '/inc/forms/leads.php';
require dirname( __DIR__ ) . '/inc/forms/lead-form-handler.php';

// ---- An enquiry is stored slashed, with kses paused for the insert only ----
$id = remotive_insert_lead_post( array( 'post_title' => 'A', 'post_content' => 'C:\\temp "quoted" R&D' ) );
t_eq( $id, 7, 'the insert returns the post id' );
t_eq( $GLOBALS['inserted']['post_content'], 'C:\\\\temp \\"quoted\\" R&D', 'the text reaches wp_insert_post slashed, so WordPress restores it exactly' );
t_eq( $GLOBALS['kses'], array( 'off', 'insert', 'on' ), 'kses is paused for the insert and restored straight after' );

$GLOBALS['kses']     = array();
$GLOBALS['has_kses'] = false;
remotive_insert_lead_post( array( 'post_content' => 'x' ) );
t_eq( $GLOBALS['kses'], array( 'insert' ), 'for a user with unfiltered_html the kses filters are not touched' );

// ---- The nonce check answers false instead of dying ----
function wp_verify_nonce( $nonce, $action ) { return ( 'good' === $nonce && 'act' === $action ) ? 1 : false; }
$_POST = array( 'n' => 'good' );
t_ok( remotive_form_nonce_valid( 'act', 'n' ), 'a valid nonce passes' );
t_ok( ! remotive_form_nonce_valid( 'other', 'n' ), 'a nonce for another action fails' );
$_POST = array( 'n' => 'expired' );
t_ok( ! remotive_form_nonce_valid( 'act', 'n' ), 'an expired nonce fails without ending the request' );
$_POST = array();
t_ok( ! remotive_form_nonce_valid( 'act', 'n' ), 'a missing nonce fails' );
$_POST = array( 'n' => array( 'good' ) );
t_ok( ! remotive_form_nonce_valid( 'act', 'n' ), 'a nonce posted as a list fails' );
$code = (string) file_get_contents( dirname( __DIR__ ) . '/inc/forms/lead-form-handler.php' );
t_ok( false === strpos( substr( $code, strpos( $code, 'function remotive_handle_lead_form_submission' ) ), 'check_admin_referer( $args' ), 'the lead handler no longer ends on the expired-link page' );

// ---- The honeypot ----
foreach ( array( 'x', '0', ' 0 ', 'http://spam.example', array( 'a' ), array() ) as $v ) {
	t_ok( remotive_honeypot_tripped( $v ), 'honeypot tripped by ' . json_encode( $v ) );
}
foreach ( array( null, '', '   ' ) as $v ) {
	t_ok( ! remotive_honeypot_tripped( $v ), 'honeypot not tripped by ' . json_encode( $v ) );
}

// ---- The rate limit comes after the email check, and the honeypot comes first ----
$code = (string) file_get_contents( dirname( __DIR__ ) . '/inc/forms/lead-form-handler.php' );
$h = strpos( $code, 'remotive_honeypot_tripped(', strpos( $code, 'function remotive_handle_lead_form_submission' ) );
$e = strpos( $code, '! is_email( $email )', $h );
$r = strpos( $code, 'remotive_form_rate_limit_exceeded( $args', $h );
t_ok( $h && $e && $r && $h < $e && $e < $r, 'the order is honeypot, then email check, then rate limit' );

// ---- The migrations save the options raw, so the sanitiser cannot switch things off ----
$setup = (string) file_get_contents( dirname( __DIR__ ) . '/inc/setup/site-setup.php' );
$raw   = strpos( $setup, 'function remotive_update_options_raw' );
$end   = strpos( $setup, "\n}\n", $raw );
$rest  = substr( $setup, 0, $raw ) . substr( $setup, $end );
t_eq( substr_count( $rest, "update_option( 'remotive_theme_options'" ), 0, 'only remotive_update_options_raw() calls update_option() on the theme options' );

// ---- Uncaught exceptions are logged and answered with a 500 off the page ----
define( 'DOING_AJAX', true );
$log = tempnam( sys_get_temp_dir(), 'rm' );
ini_set( 'error_log', $log );
require dirname( __DIR__ ) . '/inc/core/error-handler.php';
remotive_handle_exception( new RuntimeException( 'boom' ) );
t_ok( false !== strpos( (string) file_get_contents( $log ), 'Uncaught RuntimeException: boom' ), 'an uncaught exception on a non-page request is written to the log' );
t_ok( false === remotive_collect_php_notice( E_USER_ERROR, 'stop', __FILE__, 1 ), 'E_USER_ERROR is handed back to PHP, which stops the script' );
unlink( $log );

t_done( 'forms' );
