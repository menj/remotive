<?php
/**
 * Lead storage.
 *
 * Enquiries used to exist only as email. If wp_mail() failed, and it does
 * fail for ordinary reasons like SPF misconfiguration or host throttling,
 * the enquiry was gone with no record anywhere. Every submission is now
 * written to the database first and emailed second, so a mail problem costs
 * a notification rather than a lead.
 *
 * One store holds every enquiry regardless of which form produced it: the
 * theme's own forms and, when the plugin is active, Contact Form 7. That is
 * the point of merging them. An enquiry is an enquiry, and having half of
 * them in a plugin table and half in email is how leads get missed.
 *
 * Leads are an ordinary custom post type, so they survive a theme switch,
 * export through WordPress's own tools, and need no separate table.
 *
 * @package Remotive
 */

defined( 'ABSPATH' ) || exit;

const REMOTIVE_LEAD_POST_TYPE = 'remotive_lead';

/**
 * Register the lead post type.
 *
 * Private, not publicly queryable: an enquiry must never be reachable at a
 * URL, appear in search, or turn up in a sitemap.
 *
 * Dedicated capabilities, not the default 'post' capability_type. With
 * capability_type left as 'post', every capability except create_posts
 * mapped straight to the site's ordinary post capabilities — which every
 * Editor, and every Author for their own posts, already holds. show_in_menu
 * being false only hides the list table from the admin sidebar; it does not
 * block the URL. An Editor typing edit.php?post_type=remotive_lead directly
 * could see, export, and delete every enquiry, including names, emails,
 * messages, and IP addresses. Dedicated capabilities close that: WordPress's
 * own list-table permission check (current_user_can('edit_remotive_leads'))
 * now fails for anyone who doesn't hold it, and remotive_grant_lead_caps()
 * below grants it to administrators only.
 */
function remotive_register_lead_post_type() {
	register_post_type(
		REMOTIVE_LEAD_POST_TYPE,
		array(
			'labels'              => array(
				'name'          => __( 'Enquiries', 'remotive' ),
				'singular_name' => __( 'Enquiry', 'remotive' ),
				'menu_name'     => __( 'Enquiries', 'remotive' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => false, // Surfaced under Theme Options instead.
			'show_in_rest'        => false,
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
			'capability_type'     => array( 'remotive_lead', 'remotive_leads' ),
			'capabilities'        => array(
				'edit_post'              => 'edit_remotive_lead',
				'read_post'              => 'read_remotive_lead',
				'delete_post'            => 'delete_remotive_lead',
				'edit_posts'             => 'edit_remotive_leads',
				'edit_others_posts'      => 'edit_others_remotive_leads',
				'publish_posts'          => 'publish_remotive_leads',
				'read_private_posts'     => 'read_private_remotive_leads',
				'delete_posts'           => 'delete_remotive_leads',
				'delete_private_posts'   => 'delete_private_remotive_leads',
				'delete_published_posts' => 'delete_published_remotive_leads',
				'delete_others_posts'    => 'delete_others_remotive_leads',
				'edit_private_posts'     => 'edit_private_remotive_leads',
				'edit_published_posts'   => 'edit_published_remotive_leads',
				'create_posts'           => 'do_not_allow',
			),
			'map_meta_cap'        => true,
			'supports'            => array( 'title' ),
		)
	);
}
add_action( 'init', 'remotive_register_lead_post_type' );

/**
 * Grant the dedicated enquiry capabilities to administrators only.
 *
 * Runs on theme activation and defensively on every admin page load (the
 * check itself is one in_array() against a role object already loaded by
 * WordPress, so the repeated call costs nothing). The defensive re-check
 * matters for the same reason the retention cron gets one below: a site
 * upgraded while the theme stays active never fires after_switch_theme,
 * so activation-only granting would leave an upgraded site's administrator
 * role without these capabilities and lock everyone out of their own
 * enquiries until a reactivation nobody would think to do.
 *
 * Idempotent: add_cap() on a role that already has the capability is a
 * harmless no-op, so this is safe to run on every request.
 */
function remotive_grant_lead_caps() {
	$role = get_role( 'administrator' );
	if ( ! $role ) {
		return;
	}
	foreach ( remotive_lead_capabilities() as $cap ) {
		if ( ! $role->has_cap( $cap ) ) {
			$role->add_cap( $cap );
		}
	}
}
add_action( 'after_switch_theme', 'remotive_grant_lead_caps' );
add_action( 'admin_init', 'remotive_grant_lead_caps' );

/**
 * Remove the dedicated enquiry capabilities when the theme is switched away.
 *
 * Mirrors remotive_unschedule_lead_purge()'s cleanup pattern: a capability
 * this theme invented has no meaning to whatever theme replaces it.
 */
function remotive_revoke_lead_caps() {
	$role = get_role( 'administrator' );
	if ( ! $role ) {
		return;
	}
	foreach ( remotive_lead_capabilities() as $cap ) {
		$role->remove_cap( $cap );
	}
}
add_action( 'switch_theme', 'remotive_revoke_lead_caps' );

/**
 * The full set of capabilities the lead post type's 'capabilities' array
 * introduces, shared between the grant and revoke routines so they can
 * never drift out of sync with each other.
 *
 * @return string[]
 */
function remotive_lead_capabilities() {
	return array(
		'edit_remotive_lead',
		'read_remotive_lead',
		'delete_remotive_lead',
		'edit_remotive_leads',
		'edit_others_remotive_leads',
		'publish_remotive_leads',
		'read_private_remotive_leads',
		'delete_remotive_leads',
		'delete_private_remotive_leads',
		'delete_published_remotive_leads',
		'delete_others_remotive_leads',
		'edit_private_remotive_leads',
		'edit_published_remotive_leads',
	);
}

/**
 * A spam status for enquiries.
 *
 * Marking spam with post meta kept it in the same folder as everything
 * else and relied on every query remembering to exclude it. A status is
 * how WordPress already separates spam comments: it keeps spam out of the
 * default view by construction, gives the admin list its own Spam link
 * with a count, and means a missed exclusion cannot leak spam into the
 * enquiries list.
 */
function remotive_register_lead_spam_status() {
	register_post_status(
		'remotive_spam',
		array(
			'label'                     => _x( 'Spam', 'enquiry status', 'remotive' ),
			'public'                    => false,
			'internal'                  => false,
			'protected'                 => true,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => false,
			'show_in_admin_status_list' => true,
			/* translators: %s: number of spam enquiries */
			'label_count'               => _n_noop( 'Spam <span class="count">(%s)</span>', 'Spam <span class="count">(%s)</span>', 'remotive' ),
		)
	);
}
add_action( 'init', 'remotive_register_lead_spam_status' );

/**
 * Store one enquiry.
 *
 * @param array $lead {
 *     @type string $name    Sender's name, may be empty.
 *     @type string $email   Sender's email address.
 *     @type string $message Message body, may be empty.
 *     @type string $source  Which form it came from, for the admin list.
 * }
 * @return int|WP_Error Post ID, or an error.
 */
function remotive_store_lead( $lead ) {
	$email   = sanitize_email( $lead['email'] ?? '' );
	$name    = sanitize_text_field( $lead['name'] ?? '' );
	$message = sanitize_textarea_field( $lead['message'] ?? '' );
	$source  = sanitize_text_field( $lead['source'] ?? 'form' );

	if ( ! is_email( $email ) ) {
		return new WP_Error( 'remotive_lead_email', __( 'A lead needs a valid email address.', 'remotive' ) );
	}

	$title = $name !== '' ? $name . ' — ' . $email : $email;

	// Spam goes straight to the spam folder. It is still stored, because a
	// filter that deletes is a filter that loses clients, but it never
	// enters the enquiries list.
	$post_id = wp_insert_post(
		array(
			'post_type'    => REMOTIVE_LEAD_POST_TYPE,
			'post_status'  => ! empty( $lead['is_spam'] ) ? 'remotive_spam' : 'publish',
			'post_title'   => $title,
			'post_content' => $message,
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		return $post_id;
	}

	update_post_meta( $post_id, '_remotive_lead_email', $email );
	update_post_meta( $post_id, '_remotive_lead_name', $name );
	update_post_meta( $post_id, '_remotive_lead_source', $source );

	// Kept so a later spam or false-positive report can carry the same
	// context Akismet saw at submission time. Both are already in the
	// server logs the privacy policy describes.
	if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
		update_post_meta( $post_id, '_remotive_lead_ip', sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) );
	}
	if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
		update_post_meta( $post_id, '_remotive_lead_agent', sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) );
	}


	/**
	 * Fires after an enquiry is stored, whichever form produced it.
	 *
	 * @param int   $post_id Stored lead.
	 * @param array $lead    The submitted values.
	 */
	do_action( 'remotive_lead_stored', $post_id, $lead );

	return $post_id;
}

/**
 * Record whether the notification email went out.
 *
 * Kept separate from storage so the lead is already safe by the time the
 * mail is attempted. An enquiry showing "email failed" in the admin list is
 * a prompt to reply manually, not a lost enquiry.
 *
 * @param int  $post_id Stored lead.
 * @param bool $sent    Whether wp_mail() reported success.
 */
function remotive_record_lead_mail_status( $post_id, $sent ) {
	if ( $post_id && ! is_wp_error( $post_id ) ) {
		update_post_meta( $post_id, '_remotive_lead_mailed', $sent ? '1' : '0' );
	}
}

/**
 * Capture Contact Form 7 submissions into the same store.
 *
 * Runs only when the plugin is active. Field names vary between forms, so
 * the usual CF7 conventions are tried in order and the first match wins;
 * anything unrecognised is kept in the message body rather than dropped, so
 * a custom field never disappears silently.
 *
 * @param WPCF7_ContactForm $contact_form The submitted form.
 */
function remotive_capture_cf7_submission( $contact_form ) {
	if ( ! class_exists( 'WPCF7_Submission' ) ) {
		return;
	}

	$submission = WPCF7_Submission::get_instance();

	if ( ! $submission ) {
		return;
	}

	$posted = $submission->get_posted_data();

	if ( ! is_array( $posted ) ) {
		return;
	}

	$email   = '';
	$name    = '';
	$message = '';
	$extra   = array();

	foreach ( $posted as $key => $value ) {
		if ( is_array( $value ) ) {
			$value = implode( ', ', array_map( 'strval', $value ) );
		}

		$value = (string) $value;
		$lower = strtolower( (string) $key );

		if ( '' === $email && ( 'your-email' === $lower || false !== strpos( $lower, 'email' ) ) ) {
			$email = $value;
		} elseif ( '' === $name && ( 'your-name' === $lower || false !== strpos( $lower, 'name' ) ) ) {
			$name = $value;
		} elseif ( '' === $message && ( 'your-message' === $lower || false !== strpos( $lower, 'message' ) ) ) {
			$message = $value;
		} elseif ( '' !== trim( $value ) && '_' !== substr( $lower, 0, 1 ) ) {
			$extra[] = $key . ': ' . $value;
		}
	}

	if ( $extra ) {
		$message = trim( $message . "\n\n" . implode( "\n", $extra ) );
	}

	// CF7 runs its own Akismet check via its built-in integration before
	// firing wpcf7_mail_sent, so a submission reaching this hook has
	// already passed CF7's filter. We run ours independently so the
	// is_spam flag is set correctly in the stored record — without it,
	// every CF7 submission would land as 'publish' regardless of what
	// Akismet thinks, and the manual spam/ham actions on the leads card
	// would never have cause to run on them.
	$is_spam = function_exists( 'remotive_akismet_is_spam' )
		? remotive_akismet_is_spam(
			array(
				'name'    => $name,
				'email'   => $email,
				'message' => $message,
			)
		)
		: false;

	$stored = remotive_store_lead(
		array(
			'name'    => $name,
			'email'   => $email,
			'message' => $message,
			'source'  => 'Contact Form 7: ' . $contact_form->title(),
			'is_spam' => $is_spam,
		)
	);

	// CF7 sends its own mail, and reaching this hook means it succeeded.
	remotive_record_lead_mail_status( $stored, true );
}
add_action( 'wpcf7_mail_sent', 'remotive_capture_cf7_submission' );

/**
 * Delete enquiries older than the retention period.
 *
 * A privacy policy has to state how long enquiries are kept, and a stated
 * period that nothing enforces is worse than none. Zero disables deletion.
 */
function remotive_purge_expired_leads() {
	$months = (int) remotive_get_theme_option( 'lead_retention_months' );

	if ( $months < 1 ) {
		return;
	}

	$old = get_posts(
		array(
			'post_type'      => REMOTIVE_LEAD_POST_TYPE,
			// Spam is not 'any', so it has to be named or the spam folder
			// would grow without limit while real enquiries expired.
			'post_status'    => array( 'publish', 'remotive_spam' ),
			'posts_per_page' => 100,
			'fields'         => 'ids',
			'date_query'     => array(
				array( 'before' => $months . ' months ago' ),
			),
		)
	);

	foreach ( $old as $id ) {
		wp_delete_post( $id, true );
	}
}
add_action( 'remotive_purge_leads', 'remotive_purge_expired_leads' );

/**
 * Schedule the purge once.
 *
 * Also hooked to admin_init, not just after_switch_theme: a site that
 * upgrades this theme while it stays active never fires the activation
 * hook, so activation-only scheduling would leave an upgraded site with
 * no purge event at all — expired enquiries, including spam ones holding
 * IP addresses and messages, would simply accumulate forever. The
 * wp_next_scheduled() guard above already makes repeat calls a no-op, so
 * checking on every admin page load costs one lightweight cron-table
 * lookup and nothing else.
 */
function remotive_schedule_lead_purge() {
	if ( ! wp_next_scheduled( 'remotive_purge_leads' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'remotive_purge_leads' );
	}
}
add_action( 'after_switch_theme', 'remotive_schedule_lead_purge' );
add_action( 'admin_init', 'remotive_schedule_lead_purge' );

/**
 * Clear the schedule when the theme is switched away.
 */
function remotive_unschedule_lead_purge() {
	$timestamp = wp_next_scheduled( 'remotive_purge_leads' );

	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, 'remotive_purge_leads' );
	}
}
add_action( 'switch_theme', 'remotive_unschedule_lead_purge' );

/**
 * Every stored enquiry, newest first.
 *
 * @param int $limit Maximum to return.
 * @return WP_Post[]
 */
function remotive_get_leads( $limit = 200, $spam = false ) {
	return get_posts(
		array(
			'post_type'      => REMOTIVE_LEAD_POST_TYPE,
			'post_status'    => $spam ? 'remotive_spam' : 'publish',
			'posts_per_page' => (int) $limit,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);
}

/**
 * How many stored enquiries Akismet flagged as spam.
 *
 * @return int
 */
function remotive_count_spam_leads() {
	$counts = wp_count_posts( REMOTIVE_LEAD_POST_TYPE );

	return isset( $counts->remotive_spam ) ? (int) $counts->remotive_spam : 0;
}

/**
 * Neutralise a value that could be interpreted as a spreadsheet formula
 * when a CSV export is opened in Excel, Google Sheets, or similar.
 *
 * A cell beginning with =, +, -, or @ (after leading whitespace) can
 * execute as a formula on open, which is a real risk for name and message
 * fields nobody has validated beyond "is this a plausible enquiry".
 * Prefixing with an apostrophe is the standard mitigation: spreadsheet
 * applications treat a leading apostrophe as "force this cell to text"
 * and strip it from the displayed value, so an ordinary name or message
 * is unaffected and a formula-shaped one opens as inert text instead of
 * executing.
 *
 * @param string $value Raw value bound for a CSV cell.
 * @return string Value safe to hand to fputcsv().
 */
function remotive_csv_safe( $value ) {
	$value = (string) $value;
	if ( preg_match( '/^[\s]*[=+\-@]/', $value ) ) {
		return "'" . $value;
	}
	return $value;
}

/**
 * Send the stored enquiries as a CSV download.
 */
function remotive_export_leads_csv() {
	if ( ! current_user_can( 'edit_remotive_leads' ) ) {
		wp_die( esc_html__( 'You are not allowed to export enquiries.', 'remotive' ) );
	}

	check_admin_referer( 'remotive_export_leads' );

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=remotive-enquiries-' . gmdate( 'Y-m-d' ) . '.csv' );

	$out = fopen( 'php://output', 'w' );
	fputcsv( $out, array( 'Date', 'Name', 'Email', 'Message', 'Source', 'Email sent', 'Spam' ) );

	$rows = array_merge( remotive_get_leads( 5000 ), remotive_get_leads( 5000, true ) );

	foreach ( $rows as $lead ) {
		fputcsv(
			$out,
			array(
				get_the_date( 'Y-m-d H:i', $lead ),
				remotive_csv_safe( get_post_meta( $lead->ID, '_remotive_lead_name', true ) ),
				remotive_csv_safe( get_post_meta( $lead->ID, '_remotive_lead_email', true ) ),
				remotive_csv_safe( $lead->post_content ),
				remotive_csv_safe( get_post_meta( $lead->ID, '_remotive_lead_source', true ) ),
				'0' === get_post_meta( $lead->ID, '_remotive_lead_mailed', true ) ? 'no' : 'yes',
				'remotive_spam' === $lead->post_status ? 'yes' : 'no',
			)
		);
	}

	fclose( $out );
	exit;
}
add_action( 'admin_post_remotive_export_leads', 'remotive_export_leads_csv' );

/**
 * The enquiries card on the Theme Options screen.
 *
 * A short list rather than a full inbox: enough to see that submissions are
 * arriving and to spot one whose notification failed, with the CSV export
 * for anything more. WordPress's own list screen is available for the rest.
 */
function remotive_render_leads_card() {
	if ( ! current_user_can( 'edit_remotive_leads' ) ) {
		return;
	}

	$leads     = remotive_get_leads( 10 );
	$total     = (int) wp_count_posts( REMOTIVE_LEAD_POST_TYPE )->publish;
	$retention = (int) remotive_get_theme_option( 'lead_retention_months' );
	?>
	<div class="rm-admin__card rm-admin__card--setup">
		<h2><?php esc_html_e( 'Enquiries', 'remotive' ); ?></h2>
		<p class="rm-admin__card-desc">
			<?php
			printf(
				/* translators: %d: number of stored enquiries */
				esc_html( _n( '%d enquiry stored.', '%d enquiries stored.', $total, 'remotive' ) ),
				(int) $total
			);
			echo ' ';
			if ( $retention > 0 ) {
				printf(
					/* translators: %d: retention period in months */
					esc_html__( 'Deleted automatically after %d months.', 'remotive' ),
					(int) $retention
				);
			} else {
				esc_html_e( 'Kept indefinitely; set a retention period on the Display tab.', 'remotive' );
			}
			echo ' ';
			esc_html_e( 'Every enquiry is stored before the notification email is attempted, so a mail failure never loses one. Submissions from Contact Form 7 are captured here too when that plugin is active.', 'remotive' );
			?>
		</p>
		<p class="rm-admin__card-desc">
			<?php
			$spam_count = function_exists( 'remotive_count_spam_leads' ) ? remotive_count_spam_leads() : 0;

			if ( function_exists( 'remotive_akismet_ready' ) && remotive_akismet_ready() ) {
				printf(
					/* translators: %d: number of enquiries marked as spam */
					esc_html__( 'Spam filtering is on through Akismet. %d in the spam folder, kept rather than deleted.', 'remotive' ),
					(int) $spam_count
				);
			} elseif ( class_exists( 'Akismet' ) ) {
				esc_html_e( 'Akismet is installed but has no API key, so contact form submissions are not being checked for spam. Add a key on the Akismet settings screen.', 'remotive' );
			} else {
				esc_html_e( 'Spam filtering is off. Install and connect the Akismet plugin and contact form submissions will be checked against the same service as comment spam. The forms work either way.', 'remotive' );
			}
			?>
		</p>

		<?php if ( ! $leads ) : ?>
			<p class="rm-admin__card-desc"><em><?php esc_html_e( 'Nothing yet. Submissions will appear here.', 'remotive' ); ?></em></p>
		<?php else : ?>
			<table class="rm-setup-table widefat">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Received', 'remotive' ); ?></th>
						<th scope="col"><?php esc_html_e( 'From', 'remotive' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Message', 'remotive' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Source', 'remotive' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $leads as $lead ) : ?>
					<?php
					$lead_email = get_post_meta( $lead->ID, '_remotive_lead_email', true );
					$lead_name  = get_post_meta( $lead->ID, '_remotive_lead_name', true );
					$mailed     = get_post_meta( $lead->ID, '_remotive_lead_mailed', true );
					?>
					<tr>
						<td data-label="<?php esc_attr_e( 'Received', 'remotive' ); ?>">
							<?php echo esc_html( get_the_date( 'j M Y, H:i', $lead ) ); ?>
							<?php if ( '0' === $mailed ) : ?>
								<br><strong><?php esc_html_e( 'Email failed', 'remotive' ); ?></strong>
							<?php endif; ?>
						</td>
						<td data-label="<?php esc_attr_e( 'From', 'remotive' ); ?>">
							<?php if ( $lead_name ) : ?>
								<strong><?php echo esc_html( $lead_name ); ?></strong><br>
							<?php endif; ?>
							<a href="<?php echo esc_url( 'mailto:' . $lead_email ); ?>"><?php echo esc_html( $lead_email ); ?></a>
						</td>
						<td data-label="<?php esc_attr_e( 'Message', 'remotive' ); ?>">
							<?php echo esc_html( wp_trim_words( $lead->post_content, 18 ) ); ?>
						</td>
						<td data-label="<?php esc_attr_e( 'Source', 'remotive' ); ?>">
							<?php echo esc_html( get_post_meta( $lead->ID, '_remotive_lead_source', true ) ); ?>
							<?php if ( function_exists( 'remotive_akismet_ready' ) && remotive_akismet_ready() ) : ?>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:.35rem">
									<input type="hidden" name="action" value="remotive_lead_spam">
									<input type="hidden" name="lead_id" value="<?php echo esc_attr( $lead->ID ); ?>">
									<input type="hidden" name="verdict" value="spam">
									<?php wp_nonce_field( 'remotive_lead_spam_' . $lead->ID ); ?>
									<button type="submit" class="button-link"><?php esc_html_e( 'Mark as spam', 'remotive' ); ?></button>
								</form>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<?php
			$spam_leads = function_exists( 'remotive_get_leads' ) ? remotive_get_leads( 10, true ) : array();
			if ( $spam_leads ) :
				?>
				<h3><?php esc_html_e( 'Spam folder', 'remotive' ); ?></h3>
				<p class="rm-admin__card-desc">
					<?php esc_html_e( 'Check these occasionally. Not spam moves the enquiry back into the list above and tells Akismet it got that one wrong, which improves the filter. The full folder is on the enquiries screen, alongside the other views.', 'remotive' ); ?>
				</p>
				<table class="rm-setup-table widefat">
					<tbody>
					<?php foreach ( $spam_leads as $spam_lead ) : ?>
						<tr>
							<td data-label="<?php esc_attr_e( 'Received', 'remotive' ); ?>"><?php echo esc_html( get_the_date( 'j M Y', $spam_lead ) ); ?></td>
							<td data-label="<?php esc_attr_e( 'From', 'remotive' ); ?>"><?php echo esc_html( get_post_meta( $spam_lead->ID, '_remotive_lead_email', true ) ); ?></td>
							<td data-label="<?php esc_attr_e( 'Message', 'remotive' ); ?>"><?php echo esc_html( wp_trim_words( $spam_lead->post_content, 12 ) ); ?></td>
							<td data-label="<?php esc_attr_e( 'Action', 'remotive' ); ?>">
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
									<input type="hidden" name="action" value="remotive_lead_spam">
									<input type="hidden" name="lead_id" value="<?php echo esc_attr( $spam_lead->ID ); ?>">
									<input type="hidden" name="verdict" value="ham">
									<?php wp_nonce_field( 'remotive_lead_spam_' . $spam_lead->ID ); ?>
									<button type="submit" class="button-link"><?php esc_html_e( 'Not spam', 'remotive' ); ?></button>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

			<p>
				<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=remotive_export_leads' ), 'remotive_export_leads' ) ); ?>">
					<?php esc_html_e( 'Export all as CSV', 'remotive' ); ?>
				</a>
				<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . REMOTIVE_LEAD_POST_TYPE ) ); ?>">
					<?php esc_html_e( 'View all enquiries', 'remotive' ); ?>
				</a>
			</p>
		<?php endif; ?>
	</div>
	<?php
}
