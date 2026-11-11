<?php
/**
 * Numbers band: headline numbers that count up (count-up.js).
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$classes = 'section section--' . sanitize_html_class( $attributes['background'] ) . ' svc-numbers';
?>
<section class="<?php echo esc_attr( $classes ); ?>"<?php echo $attributes['anchor'] ? ' id="' . esc_attr( sanitize_title( $attributes['anchor'] ) ) . '"' : ''; ?> aria-label="<?php echo esc_attr( $attributes['label'] ); ?>">
	<div class="container">
		<div class="proof">
			<ul class="proof__stats" role="list">
				<?php foreach ( intella_items( $attributes['stats'] ) as $stat ) : ?>
				<li class="proof-stat">
					<p class="proof-stat__value"><span data-count="<?php echo esc_attr( $stat['number'] ?? '' ); ?>"><?php echo esc_html( $stat['number'] ?? '' ); ?></span><?php echo esc_html( $stat['suffix'] ?? '' ); ?></p>
					<p class="proof-stat__label"><?php echo esc_html( $stat['label'] ?? '' ); ?></p>
					<p class="proof-stat__note"><?php echo esc_html( $stat['note'] ?? '' ); ?></p>
				</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</section>
