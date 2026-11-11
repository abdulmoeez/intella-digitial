<?php
/**
 * Industries as tags under a heading.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'industries-title' );
?>
<section<?php echo intella_section_attrs( $attributes, 'industries', $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="container">
		<?php echo intella_section_head( $attributes, $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<ul class="industries__list" role="list">
			<?php foreach ( intella_items( $attributes['items'] ) as $item ) : ?>
			<li class="industry<?php echo ! empty( $item['filled'] ) ? ' industry--filled' : ''; ?>"><?php echo esc_html( $item['name'] ?? '' ); ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
