<?php
/**
 * Pages. The content comes from the editor (section blocks), never from this file.
 *
 * @package Intella
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'page-content' ); ?>>
		<?php the_content(); ?>
	</article>
	<?php
endwhile;

get_footer();
