<?php
/**
 * Related services: cards linking to services that go well with this one.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'related-title' );
$arrow    = intella_icon( 'arrow-right' );
?>
<section<?php echo intella_section_attrs( $attributes, '', $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="container">
		<div class="section-head section-head--split">
			<div><h2 class="section-title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo intella_accent( $attributes['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2></div>
			<?php echo intella_button( array( 'text' => $attributes['buttonText'], 'url' => $attributes['buttonUrl'] ), 'button button--outline', false ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
		<ul class="link-grid link-grid--<?php echo (int) $attributes['columns']; ?>" role="list">
			<?php foreach ( intella_items( $attributes['items'] ) as $item ) : ?>
			<li>
				<article class="link-card">
					<span class="icon-tile"><?php echo intella_icon( ! empty( $item['icon'] ) ? $item['icon'] : 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<h3 class="link-card__title"><a href="<?php echo esc_url( intella_url( $item['url'] ?? '' ) ); ?>"><?php echo esc_html( $item['title'] ?? '' ); ?></a></h3>
					<p class="link-card__text"><?php echo esc_html( $item['text'] ?? '' ); ?></p>
					<span class="link-card__more" aria-hidden="true"><?php esc_html_e( 'Learn more', 'intella' ); ?> <?php echo $arrow; // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				</article>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
