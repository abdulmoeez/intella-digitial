<?php
/**
 * Enquiry forms: every lead form on the site posts here (admin-post.php?action=intella_form).
 *
 * The handler checks the hidden spam traps, validates the fields again on the server, saves the enquiry as a
 * private "Lead" (Leads in the admin menu, administrators only), emails it, then sends the visitor to the
 * thank-you page. Campaign details (utm_*, gclid, landing page) are kept with the lead.
 *
 * Forms are printed by the theme with intella_form_hidden_fields() inside the <form>.
 *
 * @package Intella_Core
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'intella_register_leads' );
add_action( 'admin_post_nopriv_intella_form', 'intella_form_handle' );
add_action( 'admin_post_intella_form', 'intella_form_handle' );
add_action( 'add_meta_boxes_intella_lead', 'intella_lead_box' );

/** Fields handled internally, never stored as answers. */
const INTELLA_FORM_INTERNAL = array( 'action', 'form', '_ts', '_sig', '_success', '_back', 'company_hp' );

/** Campaign fields kept with every lead. */
const INTELLA_FORM_CAMPAIGN = array( 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'landing_page', 'referrer' );

/**
 * The private Lead post type. Only administrators can see leads (they hold personal data).
 */
function intella_register_leads(): void {
	register_post_type(
		'intella_lead',
		array(
			'labels'          => array(
				'name'          => __( 'Leads', 'intella' ),
				'singular_name' => __( 'Lead', 'intella' ),
				'edit_item'     => __( 'Lead', 'intella' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_rest'    => false,
			'menu_icon'       => 'dashicons-email-alt',
			'menu_position'   => 26,
			'supports'        => array( 'title' ),
			'capability_type' => 'post',
			'capabilities'    => array(
				'create_posts'       => 'do_not_allow',
				'edit_posts'         => 'manage_options',
				'edit_others_posts'  => 'manage_options',
				'delete_posts'       => 'manage_options',
				'delete_others_posts' => 'manage_options',
				'read_private_posts' => 'manage_options',
				'publish_posts'      => 'manage_options',
			),
			'map_meta_cap'    => true,
		)
	);
}

/**
 * Hidden fields every form needs: the action, form name, spam-trap timestamp, where to go next and campaign fields.
 *
 * @param string $form    Form name, e.g. 'contact', 'review', 'proposal'.
 * @param string $success Thank-you page path.
 */
function intella_form_hidden_fields( string $form, string $success = '/thank-you/' ): string {
	$ts   = (string) time();
	$html = sprintf( '<input type="hidden" name="action" value="intella_form"><input type="hidden" name="form" value="%s">', esc_attr( $form ) );
	$html .= sprintf( '<input type="hidden" name="_ts" value="%s"><input type="hidden" name="_sig" value="%s">', esc_attr( $ts ), esc_attr( wp_hash( 'intella_form|' . $ts ) ) );
	$html .= sprintf( '<input type="hidden" name="_success" value="%s">', esc_attr( $success ) );
	foreach ( array( 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'referrer' ) as $field ) {
		$html .= sprintf( '<input type="hidden" name="%1$s" data-campaign-field="%1$s">', esc_attr( $field ) );
	}
	$html .= '<input type="hidden" name="landing_page" data-campaign-field="landing_page">';
	return $html;
}

/**
 * The form's action URL.
 */
function intella_form_action(): string {
	return admin_url( 'admin-post.php' );
}

/**
 * Handle a submitted form.
 */
function intella_form_handle(): void {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- public forms on cached pages; protected by the honeypot and signed timestamp below.
	$post    = wp_unslash( $_POST );
	$back    = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$success = wp_validate_redirect( home_url( sanitize_text_field( $post['_success'] ?? '/thank-you/' ) ), home_url( '/thank-you/' ) );
	// phpcs:enable

	// Spam traps: the hidden "company_hp" field must be empty, and the form must have been on screen for 3+ seconds.
	$ts  = (int) ( $post['_ts'] ?? 0 );
	$sig = (string) ( $post['_sig'] ?? '' );
	if ( ! empty( $post['company_hp'] ) || ! hash_equals( wp_hash( 'intella_form|' . $ts ), $sig ) || time() - $ts < 3 ) {
		wp_safe_redirect( $success ); // Pretend it worked, so bots learn nothing.
		exit;
	}

	$form    = sanitize_key( $post['form'] ?? 'enquiry' );
	$answers = array();
	$count   = 0;
	foreach ( $post as $key => $value ) {
		$key = sanitize_key( (string) $key );
		if ( in_array( $key, INTELLA_FORM_INTERNAL, true ) || in_array( $key, INTELLA_FORM_CAMPAIGN, true ) || ++$count > 40 ) {
			continue;
		}
		$value = is_array( $value ) ? implode( ', ', array_map( 'sanitize_text_field', $value ) ) : (string) $value;
		$value = 'message' === $key ? sanitize_textarea_field( $value ) : sanitize_text_field( $value );
		if ( '' !== $value ) {
			$answers[ $key ] = mb_substr( $value, 0, 5000 );
		}
	}

	// The same checks the browser makes, repeated on the server.
	$email = sanitize_email( $answers['email'] ?? '' );
	$valid = is_email( $email );
	foreach ( array( 'website', 'site_url', 'store_url' ) as $url_field ) {
		if ( ! empty( $answers[ $url_field ] ) && ! wp_http_validate_url( preg_match( '#^https?://#i', $answers[ $url_field ] ) ? $answers[ $url_field ] : 'https://' . $answers[ $url_field ] ) ) {
			$valid = false;
		}
	}
	if ( ! $valid ) {
		wp_safe_redirect( add_query_arg( 'intella_form', 'error', $back ) );
		exit;
	}

	$campaign = array();
	foreach ( INTELLA_FORM_CAMPAIGN as $key ) {
		if ( ! empty( $post[ $key ] ) ) {
			$campaign[ $key ] = mb_substr( sanitize_text_field( (string) $post[ $key ] ), 0, 500 );
		}
	}
	$campaign['submitted_from'] = esc_url_raw( $back );

	$name    = $answers['name'] ?? $email;
	$lead_id = wp_insert_post(
		array(
			'post_type'   => 'intella_lead',
			'post_status' => 'private',
			/* translators: 1: person's name or email, 2: form name */
			'post_title'  => sprintf( __( '%1$s (%2$s form)', 'intella' ), $name, $form ),
		)
	);
	if ( $lead_id ) {
		update_post_meta( $lead_id, '_intella_form', $form );
		update_post_meta( $lead_id, '_intella_answers', $answers );
		update_post_meta( $lead_id, '_intella_campaign', $campaign );
	}

	intella_form_email( $form, $answers, $campaign, $email, (int) $lead_id );

	wp_safe_redirect( $success );
	exit;
}

/**
 * Email the enquiry to the team.
 *
 * @param string $form     Form name.
 * @param array  $answers  Visitor's answers.
 * @param array  $campaign Campaign details.
 * @param string $email    Visitor's email (used as Reply-To).
 * @param int    $lead_id  Saved lead.
 */
function intella_form_email( string $form, array $answers, array $campaign, string $email, int $lead_id ): void {
	$settings = get_option( 'intella_site', array() );
	$to       = ! empty( $settings['form_to'] ) ? $settings['form_to'] : get_option( 'admin_email' );
	$lines    = array();
	foreach ( $answers as $key => $value ) {
		$lines[] = ucwords( str_replace( '_', ' ', $key ) ) . ': ' . $value;
	}
	if ( $campaign ) {
		$lines[] = '';
		foreach ( $campaign as $key => $value ) {
			$lines[] = $key . ': ' . $value;
		}
	}
	if ( $lead_id ) {
		$lines[] = '';
		$lines[] = admin_url( 'post.php?post=' . $lead_id . '&action=edit' );
	}
	wp_mail(
		$to,
		/* translators: 1: form name, 2: person's name or email */
		sprintf( __( 'New %1$s enquiry from %2$s', 'intella' ), $form, $answers['name'] ?? $email ),
		implode( "\n", $lines ),
		array( 'Reply-To: ' . $email )
	);
}

/**
 * Show a lead's answers on its admin screen.
 */
function intella_lead_box(): void {
	add_meta_box(
		'intella-lead',
		__( 'Enquiry', 'intella' ),
		static function ( WP_Post $post ): void {
			$rows = array_merge(
				array( 'form' => get_post_meta( $post->ID, '_intella_form', true ) ),
				(array) get_post_meta( $post->ID, '_intella_answers', true ),
				(array) get_post_meta( $post->ID, '_intella_campaign', true )
			);
			echo '<table class="widefat striped"><tbody>';
			foreach ( $rows as $key => $value ) {
				printf( '<tr><th style="width:30%%">%s</th><td>%s</td></tr>', esc_html( ucwords( str_replace( '_', ' ', (string) $key ) ) ), nl2br( esc_html( (string) $value ) ) );
			}
			echo '</tbody></table>';
		},
		'intella_lead',
		'normal',
		'high'
	);
}
