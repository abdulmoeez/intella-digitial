<?php
/**
 * Services by service line: one tab per line (tabs.js), each with an intro, its services and a button.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'services-title' );
$prefix   = intella_uid( 'wwd' );
$tabs     = intella_items( $attributes['tabs'] );
$arrow    = intella_icon( 'arrow-right' );
?>
<section<?php echo intella_section_attrs( $attributes, 'services', $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="container">
		<div class="section-head section-head--split">
			<div>
				<h2 class="section-title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo intella_accent( $attributes['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			</div>
			<div>
				<p class="section-lead"><?php echo esc_html( $attributes['lead'] ); ?></p>
				<?php echo intella_button( array( 'text' => $attributes['buttonText'], 'url' => $attributes['buttonUrl'] ), 'button button--outline' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		</div>
		<div class="wwd" data-tabs>
			<div class="wwd__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Service lines', 'intella' ); ?>"><?php foreach ( $tabs as $i => $tab ) : ?><button class="wwd__tab" type="button" role="tab" id="<?php echo esc_attr( "{$prefix}-t-{$i}" ); ?>" aria-controls="<?php echo esc_attr( "{$prefix}-{$i}" ); ?>" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>"<?php echo 0 === $i ? '' : ' tabindex="-1"'; ?>><?php echo esc_html( $tab['tab'] ?? '' ); ?></button><?php endforeach; ?></div>
			<div class="wwd__panels">
			<?php foreach ( $tabs as $i => $tab ) : ?>
			<div class="wwd__panel" role="tabpanel" id="<?php echo esc_attr( "{$prefix}-{$i}" ); ?>" aria-labelledby="<?php echo esc_attr( "{$prefix}-t-{$i}" ); ?>"<?php echo 0 === $i ? '' : ' hidden'; ?>>
				<div class="wwd__intro">
					<h3 class="wwd__title"><?php echo intella_accent( $tab['title'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h3>
					<p class="wwd__lead"><?php echo esc_html( $tab['lead'] ?? '' ); ?></p>
					<p class="wwd__text"><?php echo esc_html( $tab['text'] ?? '' ); ?></p>
				</div>
				<ul class="wwd__links" role="list">
					<?php foreach ( intella_items( $tab['links'] ?? array() ) as $link ) : ?>
						<li><a class="wwd__link" href="<?php echo esc_url( intella_url( $link['url'] ?? '' ) ); ?>"><span class="svc-link__logo" title="<?php echo esc_attr( $link['tool'] ?? '' ); ?>"><?php echo intella_tool_logo( $link['tool'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><span><b><?php echo esc_html( $link['title'] ?? '' ); ?></b><small><?php echo esc_html( $link['description'] ?? '' ); ?></small></span><?php echo $arrow; // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
					<?php endforeach; ?>
				</ul>
				<?php if ( ! empty( $tab['ctaText'] ) ) : ?>
					<div class="wwd__cta"><?php echo intella_button( array( 'text' => $tab['ctaText'], 'url' => $tab['ctaUrl'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
				<?php endif; ?>
			</div>
			<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
