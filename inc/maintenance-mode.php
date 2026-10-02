<?php
/**
 * Remotive Media — Maintenance mode.
 *
 * Appearance -> Theme Options -> Site behaviour -> Maintenance mode. When on,
 * visitors who cannot edit content get the "back shortly" page (the same
 * markup as drop-ins/maintenance.php) with a 503 and Retry-After, so search
 * engines treat the outage as temporary and keep the indexed pages.
 * Users who can edit posts, the login screen, wp-admin, cron and the REST
 * API are left alone, so the site can still be reviewed and switched back on.
 */

defined( 'ABSPATH' ) || exit;

function remotive_maintenance_mode_serve() {
	if ( '1' !== (string) remotive_get_theme_option( 'maintenance_mode' ) ) {
		return;
	}

	if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
		return;
	}

	if ( is_admin() || wp_doing_cron() || wp_doing_ajax() || is_feed() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}

	$page = get_stylesheet_directory() . '/drop-ins/maintenance.php';

	if ( ! is_readable( $page ) ) {
		wp_die(
			esc_html__( 'Back shortly.', 'remotive' ),
			esc_html__( 'Back shortly', 'remotive' ),
			array( 'response' => 503 )
		);
	}

	nocache_headers();
	require $page;
	exit;
}
add_action( 'template_redirect', 'remotive_maintenance_mode_serve', 0 );
