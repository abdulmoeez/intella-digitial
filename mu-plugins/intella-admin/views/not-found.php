<?php
/**
 * Unknown /admin/... URL or a board that doesn't exist.
 *
 * @package Intella_Admin
 */

defined( 'ABSPATH' ) || exit;
?>
<h1><?php esc_html_e( 'Page not found', 'intella' ); ?></h1>
<p><a href="<?php echo esc_url( intella_admin_url() ); ?>"><?php esc_html_e( 'Back to the dashboard', 'intella' ); ?></a></p>
