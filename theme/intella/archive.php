<?php
/**
 * Archives (tags, categories, authors, case-study archive until it has its own template).
 *
 * @package Intella
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="container">
	<header class="archive-header">
		<h1><?php the_archive_title(); ?></h1>
		<?php the_archive_description( '<div class="archive-header__description">', '</div>' ); ?>
	</header>

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
