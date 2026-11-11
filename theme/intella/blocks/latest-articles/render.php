<?php
/**
 * Latest articles: the newest blog posts as cards, pulled in automatically.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$posts = get_posts(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => max( 1, (int) $attributes['count'] ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);
if ( ! $posts ) {
	return;
}
$title_id = intella_uid( 'articles-title' );
?>
<section<?php echo intella_section_attrs( $attributes, '', $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="container">
		<div class="section-head section-head--split">
			<div>
				<h2 class="section-title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo intella_accent( $attributes['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			</div>
			<?php echo intella_button( array( 'text' => $attributes['buttonText'], 'url' => $attributes['buttonUrl'] ), 'button button--outline', false ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
		<div class="article-grid">
			<?php foreach ( $posts as $article ) : ?>
				<?php $category = get_the_category( $article->ID ); ?>
			<article class="article-card">
				<div class="article-card__media"><?php echo get_the_post_thumbnail( $article, 'medium_large', array( 'alt' => '', 'loading' => 'lazy' ) ); ?></div>
				<div class="article-card__body">
					<?php if ( $category ) : ?>
						<span class="card-tag"><?php echo esc_html( $category[0]->name ); ?></span>
					<?php endif; ?>
					<h3 class="article-card__title"><a href="<?php echo esc_url( get_permalink( $article ) ); ?>"><?php echo esc_html( get_the_title( $article ) ); ?></a></h3>
					<a class="text-link" href="<?php echo esc_url( get_permalink( $article ) ); ?>" aria-hidden="true" tabindex="-1"><?php esc_html_e( 'Read article', 'intella' ); ?> <?php echo intella_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
				</div>
			</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
