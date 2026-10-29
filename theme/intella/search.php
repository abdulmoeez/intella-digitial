<?php
/**
 * Search results.
 *
 * @package Intella
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="container">
	<h1>
		<?php
		/* translators: %s: search term */
		printf( esc_html__( 'Search results for "%s"', 'intella' ), esc_html( get_search_query() ) );
		?>
	</h1>

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
