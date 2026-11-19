<?php
/**
 * Outgoing email through Mailgun's SMTP server (as the version 1 site did), so form enquiries actually arrive.
 *
 * Settings live in wp-config.php on each server, never in git:
 *   define( 'INTELLA_SMTP_USER', 'contact@mg.example.com' );   // Mailgun → Sending → Domain settings → SMTP credentials
 *   define( 'INTELLA_SMTP_PASS', '…' );
 * Optional:
 *   define( 'INTELLA_SMTP_HOST', 'smtp.mailgun.org' );          // smtp.eu.mailgun.org for an EU Mailgun domain
 *   define( 'INTELLA_SMTP_PORT', 587 );                         // 587 = STARTTLS, 465 = SSL
 *   define( 'INTELLA_MAIL_FROM', 'contact@example.com' );       // Default: the site email in Settings → Intella site
 *   define( 'INTELLA_MAIL_FROM_NAME', 'Intella Digital' );      // Default: the site title
 *
 * Without INTELLA_SMTP_USER and INTELLA_SMTP_PASS, WordPress sends mail its default way (fine for local sites).
 * Failed sends are written to the PHP error log, so a broken password doesn't fail silently.
 *
 * @package Intella_Core
 */

defined( 'ABSPATH' ) || exit;

add_action( 'phpmailer_init', 'intella_mail_smtp' );
add_filter( 'wp_mail_from', 'intella_mail_from' );
add_filter( 'wp_mail_from_name', 'intella_mail_from_name' );
add_action( 'wp_mail_failed', 'intella_mail_failed' );

/**
 * Whether SMTP is set up on this server.
 */
function intella_mail_smtp_ready(): bool {
	return defined( 'INTELLA_SMTP_USER' ) && defined( 'INTELLA_SMTP_PASS' ) && INTELLA_SMTP_USER && INTELLA_SMTP_PASS;
}

/**
 * Send through Mailgun's SMTP server.
 *
 * @param PHPMailer\PHPMailer\PHPMailer $mailer WordPress's mailer.
 */
function intella_mail_smtp( $mailer ): void {
	if ( ! intella_mail_smtp_ready() ) {
		return;
	}
	$port = defined( 'INTELLA_SMTP_PORT' ) ? (int) INTELLA_SMTP_PORT : 587;

	$mailer->isSMTP();
	$mailer->Host       = defined( 'INTELLA_SMTP_HOST' ) ? INTELLA_SMTP_HOST : 'smtp.mailgun.org'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName -- PHPMailer property.
	$mailer->Port       = $port; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
	$mailer->SMTPSecure = 465 === $port ? 'ssl' : 'tls'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
	$mailer->SMTPAuth   = true; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
	$mailer->Username   = INTELLA_SMTP_USER; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
	$mailer->Password   = INTELLA_SMTP_PASS; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
	$mailer->Timeout    = 15; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
	$mailer->Sender     = $mailer->From; // phpcs:ignore WordPress.NamingConventions.ValidVariableName -- Return-Path matches From, so bounces come back to us.
}

/**
 * The From address: the wp-config constant, else the site email setting, else the admin email.
 *
 * @param string $from WordPress's default (wordpress@domain).
 */
function intella_mail_from( string $from ): string {
	if ( defined( 'INTELLA_MAIL_FROM' ) && is_email( INTELLA_MAIL_FROM ) ) {
		return INTELLA_MAIL_FROM;
	}
	$settings = get_option( 'intella_site', array() );
	if ( ! empty( $settings['email'] ) && is_email( $settings['email'] ) ) {
		return $settings['email'];
	}
	return intella_mail_smtp_ready() ? (string) get_option( 'admin_email' ) : $from;
}

/**
 * The From name: the wp-config constant, else the site title.
 *
 * @param string $name WordPress's default ("WordPress").
 */
function intella_mail_from_name( string $name ): string {
	if ( defined( 'INTELLA_MAIL_FROM_NAME' ) && INTELLA_MAIL_FROM_NAME ) {
		return INTELLA_MAIL_FROM_NAME;
	}
	return get_bloginfo( 'name' ) ? wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) : $name;
}

/**
 * Log failed sends (the lead itself is still saved under Leads).
 *
 * @param WP_Error $error What went wrong.
 */
function intella_mail_failed( WP_Error $error ): void {
	error_log( 'Intella mail failed: ' . $error->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions -- deliberate.
}
