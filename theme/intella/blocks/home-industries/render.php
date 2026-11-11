<?php
/**
 * Industries and proof: industry photos, headline numbers that count up (count-up.js), outcomes that shuffle
 * four at a time (results.js) and trust points.
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
		<div class="section-head section-head--center">
			<h2 class="section-title<?php echo $attributes['bigTitle'] ? ' industries__title' : ''; ?>" id="<?php echo esc_attr( $title_id ); ?>"><?php echo intella_accent( $attributes['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			<p class="section-lead"><?php echo esc_html( $attributes['lead'] ); ?></p>
		</div>
		<ul class="sectors" role="list">
			<?php foreach ( intella_items( $attributes['sectors'] ) as $i => $sector ) : ?>
			<li class="sector" style="--i:<?php echo (int) $i; ?>"><span class="sector__media"><?php echo intella_image( $sector['image'] ?? 0, 'medium_large', array( 'class' => 'sector__img', 'alt' => '', 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><span class="sector__name"><?php echo esc_html( $sector['name'] ?? '' ); ?></span></li>
			<?php endforeach; ?>
		</ul>
		<?php if ( intella_items( $attributes['stats'] ) ) : ?>
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
		<?php endif; ?>
		<?php if ( intella_items( $attributes['results'] ) || intella_items( $attributes['trust'] ) ) : ?>
		<div class="proof-grid">
			<div class="proof-grid__col">
				<h3 class="proof-grid__title"><?php echo esc_html( $attributes['resultsTitle'] ); ?></h3>
				<ul class="results" role="list" data-results aria-live="polite">
					<?php foreach ( intella_items( $attributes['results'] ) as $i => $result ) : ?>
					<li class="result-card"<?php echo $i < 4 ? '' : ' hidden'; ?>>
						<span class="result-card__icon"><?php echo intella_icon( ! empty( $result['icon'] ) ? $result['icon'] : 'trend' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						<p class="result-card__value"><?php echo esc_html( $result['value'] ?? '' ); ?></p>
						<p class="result-card__label"><?php echo esc_html( $result['label'] ?? '' ); ?></p>
						<p class="result-card__who"><?php echo esc_html( $result['who'] ?? '' ); ?></p>
					</li>
					<?php endforeach; ?>
				</ul>
				<?php if ( $attributes['linkText'] ) : ?>
					<a class="text-link" href="<?php echo esc_url( intella_url( $attributes['linkUrl'] ) ); ?>"><?php echo esc_html( $attributes['linkText'] ); ?> <?php echo intella_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
				<?php endif; ?>
			</div>
			<div class="proof-grid__col">
				<h3 class="proof-grid__title"><?php echo esc_html( $attributes['trustTitle'] ); ?></h3>
				<ul class="trust-list" role="list">
					<?php foreach ( intella_items( $attributes['trust'] ) as $point ) : ?>
					<li class="trust-item">
						<span class="icon-tile"><?php echo intella_icon( ! empty( $point['icon'] ) ? $point['icon'] : 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						<div><p class="trust-item__title"><?php echo esc_html( $point['title'] ?? '' ); ?></p><p class="trust-item__text"><?php echo esc_html( $point['text'] ?? '' ); ?></p></div>
					</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
		<?php endif; ?>
	</div>
</section>
