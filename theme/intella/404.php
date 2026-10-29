<?php
/**
 * Page not found.
 *
 * @package Intella
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="container">
	<h1><?php esc_html_e( 'Page not found', 'intella' ); ?></h1>
	<p><?php esc_html_e( 'The page you were looking for has moved or no longer exists.', 'intella' ); ?></p>
	<p><a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Go to the homepage', 'intella' ); ?></a></p>
	<?php get_search_form(); ?>
</div>
<?php
get_footer();
