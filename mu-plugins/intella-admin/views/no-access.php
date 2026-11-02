<?php
/**
 * Shown to logged-in users who don't have panel access.
 *
 * @package Intella_Admin
 */

defined( 'ABSPATH' ) || exit;
?>
<h1><?php esc_html_e( "You don't have access to the admin panel", 'intella' ); ?></h1>
<p><?php esc_html_e( 'Ask a WordPress administrator at Intella Digital to add you on the Admins page.', 'intella' ); ?></p>
