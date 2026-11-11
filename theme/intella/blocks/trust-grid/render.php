<?php
/**
 * Trust points: heading and a grid of short points with icons.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'trust-title' );
?>
<section<?php echo intella_section_attrs( $attributes, '', $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="container">
		<?php echo intella_section_head( $attributes, $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<ul class="trust-grid" role="list">
			<?php foreach ( intella_items( $attributes['items'] ) as $item ) : ?>
			<li class="trust-item">
				<span class="icon-tile"><?php echo intella_icon( ! empty( $item['icon'] ) ? $item['icon'] : 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				<div><h3 class="trust-item__title"><?php echo esc_html( $item['title'] ?? '' ); ?></h3><p class="trust-item__text"><?php echo esc_html( $item['text'] ?? '' ); ?></p></div>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
