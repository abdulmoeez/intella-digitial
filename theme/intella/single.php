<?php
/**
 * Single blog post.
 *
 * @package Intella
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'entry container' ); ?>>
		<header class="entry__header">
			<h1 class="entry__title"><?php the_title(); ?></h1>
			<p class="entry__meta">
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
				&middot; <?php the_author(); ?>
			</p>
		</header>
		<div class="entry__content">
			<?php the_content(); ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
