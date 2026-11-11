<?php
/**
 * All services: one side tab per service line (tabs.js). Each panel has an intro, a carousel of example
 * screens (carousels.js; HTML illustrations until real screenshots exist) and links to its services.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'all-services-title' );
$tabs     = intella_items( $attributes['tabs'] );
$key      = static fn( array $tab, int $i ): string => ! empty( $tab['key'] ) ? sanitize_title( $tab['key'] ) : 'line-' . $i;
?>
<section<?php echo intella_section_attrs( $attributes, '', $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="container">
		<div class="section-head section-head--center">
			<h2 class="section-title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo intella_accent( $attributes['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
		</div>
		<div class="tabs tabs--side tabs--services" data-tabs>
			<div class="tabs__list" role="tablist" aria-label="<?php esc_attr_e( 'Service lines', 'intella' ); ?>" aria-orientation="vertical">
				<?php foreach ( $tabs as $i => $tab ) : ?>
				<button class="tabs__tab" type="button" role="tab" id="tab-<?php echo esc_attr( $key( $tab, $i ) ); ?>" aria-controls="<?php echo esc_attr( $key( $tab, $i ) ); ?>" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>">
					<span class="tabs__tab-text"><span class="tabs__tab-title"><?php echo esc_html( $tab['tab'] ?? '' ); ?></span><span class="tabs__tab-sub"><?php echo esc_html( $tab['sub'] ?? '' ); ?></span></span>
				</button>
				<?php endforeach; ?>
			</div>
			<div class="tabs__panels">
				<?php foreach ( $tabs as $i => $tab ) : ?>
					<?php $slides = intella_items( $tab['slides'] ?? array() ); ?>
				<div class="tabs__panel svc-panel" role="tabpanel" id="<?php echo esc_attr( $key( $tab, $i ) ); ?>" aria-labelledby="tab-<?php echo esc_attr( $key( $tab, $i ) ); ?>">
					<div class="svc-panel__head">
						<div class="svc-panel__sticky">
							<h3 class="svc-panel__title"><?php echo intella_accent( $tab['title'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h3>
						</div>
						<div>
							<p class="svc-panel__lead"><?php echo esc_html( $tab['lead'] ?? '' ); ?></p>
							<p class="svc-panel__text"><?php echo esc_html( $tab['text'] ?? '' ); ?></p>
						</div>
					</div>
					<?php if ( $slides ) : ?>
					<div class="svc-carousel" aria-roledescription="carousel" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: service line */ __( '%s: example screens', 'intella' ), $tab['tab'] ?? '' ) ); ?>" data-carousel>
						<div class="svc-carousel__viewport"><div class="svc-carousel__track" aria-live="polite"><?php foreach ( $slides as $j => $slide ) : ?><div class="svc-carousel__slide" role="group" aria-roledescription="slide" aria-label="<?php echo esc_attr( sprintf( /* translators: 1: number, 2: total */ __( '%1$d of %2$d', 'intella' ), $j + 1, count( $slides ) ) ); ?>" data-slide><div class="svc-panel__screen"><?php echo intella_visual( $slide['main'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput -- admin-only HTML field. ?></div><?php if ( ! empty( $slide['alt'] ) ) : ?><div class="svc-panel__screen svc-panel__screen--alt"><?php echo intella_visual( $slide['alt'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div><?php endif; ?></div><?php endforeach; ?></div></div>
						<?php if ( count( $slides ) > 1 ) : ?>
						<div class="svc-carousel__nav"><div class="svc-carousel__dots"><?php foreach ( $slides as $j => $slide ) : ?><button type="button" class="svc-carousel__dot" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: number */ __( 'Show slide %d', 'intella' ), $j + 1 ) ); ?>" data-dot="<?php echo (int) $j; ?>"></button><?php endforeach; ?></div></div>
						<?php endif; ?>
					</div>
					<?php endif; ?>
					<ul class="svc-links" role="list">
						<?php foreach ( intella_items( $tab['links'] ?? array() ) as $link ) : ?>
						<li>
							<a class="svc-link" href="<?php echo esc_url( intella_url( $link['url'] ?? '' ) ); ?>">
								<span class="svc-link__logo" title="<?php echo esc_attr( $link['tool'] ?? '' ); ?>"><?php echo intella_tool_logo( $link['tool'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
								<span class="svc-link__text"><span class="svc-link__title"><?php echo esc_html( $link['title'] ?? '' ); ?></span><span class="svc-link__desc"><?php echo esc_html( $link['description'] ?? '' ); ?></span></span>
								<span class="svc-link__arrow"><?php echo intella_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
							</a>
						</li>
						<?php endforeach; ?>
					</ul>
					<?php if ( ! empty( $tab['ctaText'] ) ) : ?>
					<div class="svc-panel__cta"><?php echo intella_button( array( 'text' => $tab['ctaText'], 'url' => $tab['ctaUrl'] ?? '' ), 'button button--primary svc-panel__more' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
					<?php endif; ?>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
