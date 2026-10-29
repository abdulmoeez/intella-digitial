<?php
/**
 * Shown when a listing has no results.
 *
 * @package Intella
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="no-results">
	<p><?php esc_html_e( 'Nothing found here yet.', 'intella' ); ?></p>
	<?php get_search_form(); ?>
</section>
