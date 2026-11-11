<?php
/**
 * Why Intella: heading, button and a team photo carousel (carousels.js), beside four short reasons.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'why-title' );
$photos   = array_values( array_filter( intella_items( $attributes['photos'] ), static fn( $p ) => ! empty( $p['image'] ) ) );
$count    = count( $photos );
?>
<section<?php echo intella_section_attrs( $attributes, '', $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="container why">
		<div>
			<h2 class="section-title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo intella_accent( $attributes['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			<p class="section-lead"><?php echo esc_html( $attributes['lead'] ); ?></p>
			<?php if ( $attributes['buttonText'] ) : ?>
			<div class="why__actions">
				<?php echo intella_button( array( 'text' => $attributes['buttonText'], 'url' => $attributes['buttonUrl'] ), 'button button--primary button--lg' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
			<?php endif; ?>
			<?php if ( $photos ) : ?>
			<figure class="why__team team-carousel" aria-roledescription="carousel" aria-label="<?php esc_attr_e( 'Our team at work', 'intella' ); ?>" data-carousel>
				<div class="team-carousel__viewport">
					<div class="team-carousel__track" aria-live="polite" data-track>
					<?php foreach ( $photos as $i => $photo ) : ?>
					<div class="team-carousel__slide" role="group" aria-roledescription="slide" aria-label="<?php echo esc_attr( sprintf( /* translators: 1: number, 2: total */ __( '%1$d of %2$d', 'intella' ), $i + 1, $count ) ); ?>" data-slide>
						<?php echo intella_image( $photo['image'], 'full', array( 'alt' => $photo['alt'] ?? '', 'loading' => 'lazy', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</div>
					<?php endforeach; ?>
					</div>
				</div>
				<?php if ( $count > 1 ) : ?>
				<div class="team-carousel__dots"><?php for ( $i = 0; $i < $count; $i++ ) : ?><button type="button" class="svc-carousel__dot" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: number */ __( 'Show photo %d', 'intella' ), $i + 1 ) ); ?>" data-dot="<?php echo (int) $i; ?>"></button><?php endfor; ?></div>
				<?php endif; ?>
			</figure>
			<?php endif; ?>
		</div>
		<ul class="bento" role="list">
			<?php foreach ( intella_items( $attributes['cards'] ) as $card ) : ?>
				<?php $dark = ! empty( $card['dark'] ); ?>
			<li class="bento__card<?php echo $dark ? ' bento__card--dark' : ''; ?>">
				<span class="icon-tile<?php echo $dark ? ' icon-tile--dark' : ''; ?>"><?php echo intella_icon( ! empty( $card['icon'] ) ? $card['icon'] : 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				<h3 class="bento__title"><?php echo esc_html( $card['title'] ?? '' ); ?></h3>
				<p><?php echo esc_html( $card['text'] ?? '' ); ?></p>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
