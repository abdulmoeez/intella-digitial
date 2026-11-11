<?php
/**
 * Fixed-scope offer: a priced package card beside a picture (people, a sample report…).
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'h-offer' );
$check    = intella_icon( 'check' );
?>
<section<?php echo intella_section_attrs( $attributes, '', $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="container">
		<?php echo intella_section_head( $attributes, $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<div class="fixed">
			<div class="fixed__card">
				<p class="fixed__name"><?php echo esc_html( $attributes['name'] ); ?></p>
				<p class="fixed__price"><?php echo esc_html( $attributes['price'] ); ?></p>
				<ul class="fixed__list" role="list"><?php foreach ( intella_lines( $attributes['items'] ) as $item ) : ?><li><?php echo $check; // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( $item ); ?></li><?php endforeach; ?></ul>
				<?php if ( $attributes['delivery'] ) : ?><p class="fixed__delivery"><?php echo intella_icon( 'calendar' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( $attributes['delivery'] ); ?></p><?php endif; ?>
				<?php echo intella_button( array( 'text' => $attributes['buttonText'], 'url' => $attributes['buttonUrl'] ), 'button button--primary button--lg button--block' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
			<?php if ( $attributes['visual'] ) : ?>
			<div class="fixed__visual"><?php echo intella_visual( $attributes['visual'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- admin-only HTML field. ?></div>
			<?php endif; ?>
		</div>
	</div>
</section>
