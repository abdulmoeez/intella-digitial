<?php
/**
 * Selected work: a case study finder (tabs of filter links), work cards and case study cards.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'work-title' );
$prefix   = intella_uid( 'finder' );
$finder   = intella_items( $attributes['finder'] );
?>
<section<?php echo intella_section_attrs( $attributes, '', $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="container">
		<div class="section-head section-head--split">
			<div>
				<h2 class="section-title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo intella_accent( $attributes['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			</div>
			<?php echo intella_button( array( 'text' => $attributes['buttonText'], 'url' => $attributes['buttonUrl'] ), 'button button--accent button--sm' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
		<?php if ( $finder ) : ?>
		<div class="finder" data-tabs>
			<div class="finder__top">
				<p class="finder__label"><?php echo esc_html( $attributes['finderLabel'] ); ?></p>
				<div class="finder__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Browse case studies by', 'intella' ); ?>"><?php foreach ( $finder as $i => $tab ) : ?><button class="finder__tab" type="button" role="tab" id="<?php echo esc_attr( "{$prefix}-tab-{$i}" ); ?>" aria-controls="<?php echo esc_attr( "{$prefix}-{$i}" ); ?>" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>"<?php echo 0 === $i ? '' : ' tabindex="-1"'; ?>><?php echo esc_html( $tab['tab'] ?? '' ); ?></button><?php endforeach; ?></div>
				<?php if ( $attributes['allText'] ) : ?>
					<a class="finder__all" href="<?php echo esc_url( intella_url( $attributes['allUrl'] ) ); ?>"><?php echo esc_html( $attributes['allText'] ); ?> <?php echo intella_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
				<?php endif; ?>
			</div>
			<?php foreach ( $finder as $i => $tab ) : ?>
				<ul class="finder__chips" role="tabpanel" id="<?php echo esc_attr( "{$prefix}-{$i}" ); ?>" aria-labelledby="<?php echo esc_attr( "{$prefix}-tab-{$i}" ); ?>"<?php echo 0 === $i ? '' : ' hidden'; ?>><?php foreach ( intella_items( $tab['chips'] ?? array() ) as $chip ) : ?><li><a class="finder__chip" href="<?php echo esc_url( intella_url( $chip['url'] ?? '' ) ); ?>"><?php echo esc_html( $chip['label'] ?? '' ); ?><?php if ( '' !== (string) ( $chip['count'] ?? '' ) ) : ?><span class="finder__count"><?php echo esc_html( $chip['count'] ); ?></span><?php endif; ?></a></li><?php endforeach; ?></ul>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
		<div class="work-grid">
			<?php foreach ( intella_items( $attributes['work'] ) as $card ) : ?>
			<article class="work-card">
				<div class="work-card__media"><?php echo intella_image( $card['image'] ?? 0, 'large', array( 'alt' => $card['alt'] ?? '', 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
				<div class="work-card__body">
					<span class="card-tag"><?php echo esc_html( $card['tag'] ?? '' ); ?></span>
					<h3 class="work-card__title"><?php echo esc_html( $card['title'] ?? '' ); ?></h3>
					<p class="work-card__text"><?php echo esc_html( $card['text'] ?? '' ); ?></p>
				</div>
			</article>
			<?php endforeach; ?>
		</div>
		<div class="case-grid">
			<?php foreach ( intella_items( $attributes['cases'] ) as $case ) : ?>
			<article class="case-card">
				<span class="card-tag"><?php echo esc_html( $case['tag'] ?? '' ); ?></span>
				<h3 class="case-card__title"><?php echo esc_html( $case['title'] ?? '' ); ?></h3>
				<p class="case-card__text"><?php echo esc_html( $case['text'] ?? '' ); ?></p>
				<a class="text-link" href="<?php echo esc_url( intella_url( $case['url'] ?? '' ) ); ?>"><?php echo esc_html( $case['linkText'] ?? '' ); ?> <?php echo intella_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
