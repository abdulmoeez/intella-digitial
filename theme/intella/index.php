<?php
/**
 * Fallback template (also the blog index at /articles/ until a dedicated home.php exists).
 *
 * @package Intella
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="container">
	<h1><?php echo esc_html( is_home() ? single_post_title( '', false ) : get_the_archive_title() ); ?></h1>

	<?php if ( have_posts() ) : ?>
		<div class="entry-list">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content/entry-card' );
			endwhile;
			?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/content/none' ); ?>
	<?php endif; ?>
</div>
<?php
get_footer();
