<?php
/**
 * Page not found. The content is the private page "Page not found" (slug page-not-found), so its text and
 * links are edited like any other page. If that page is missing, a plain message is shown.
 *
 * @package Intella
 */

defined( 'ABSPATH' ) || exit;

$intella_404 = get_page_by_path( 'page-not-found', OBJECT, 'page' );

get_header();

if ( $intella_404 && has_blocks( $intella_404->post_content ) ) {
	echo do_blocks( $intella_404->post_content ); // phpcs:ignore WordPress.Security.EscapeOutput -- block output.
} else {
	?>
	<div class="container">
		<h1><?php esc_html_e( 'Page not found', 'intella' ); ?></h1>
		<p><?php esc_html_e( 'The page you were looking for has moved or no longer exists.', 'intella' ); ?></p>
		<p><a class="button button--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Go to the homepage', 'intella' ); ?></a></p>
	</div>
	<?php
}

get_footer();
