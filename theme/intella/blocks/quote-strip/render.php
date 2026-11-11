<?php
/**
 * Testimonials two at a time (dark + light). With more than two they rotate in pairs (quote-pairs.js).
 * Optional case study cards underneath. Quotes come from Testimonials in the admin.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'intella_shared_items' ) ) {
	return;
}
$title_id = intella_uid( 'clients-title' );
$people   = intella_shared_items( 'testimonial', (array) $attributes['people'], 2 );
$rotate   = count( $people ) > 2;
$pairs    = (int) ceil( count( $people ) / 2 );
?>
<section<?php echo intella_section_attrs( $attributes, '', $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="container">
		<?php if ( $attributes['buttonText'] ) : ?>
		<div class="section-head section-head--split">
			<div>
				<h2 class="section-title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo intella_accent( $attributes['title'], $attributes['plainAccent'] ? 'accent--plain' : '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			</div>
			<?php echo intella_button( array( 'text' => $attributes['buttonText'], 'url' => $attributes['buttonUrl'] ), 'button button--outline', false ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
		<?php else : ?>
		<div class="section-head section-head--center">
			<h2 class="section-title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo intella_accent( $attributes['title'], $attributes['plainAccent'] ? 'accent--plain' : '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
		</div>
		<?php endif; ?>
		<div class="quote-strip"<?php echo $rotate ? ' data-quote-pairs' : ''; ?>>
			<div class="quote-row quote-row--two"<?php echo $rotate ? ' aria-live="polite"' : ''; ?>>
				<?php foreach ( $people as $i => $person ) : ?>
				<figure class="quote-card<?php echo 0 === $i % 2 ? ' quote-card--dark' : ''; ?>"<?php echo $rotate ? ' data-quote-pair-card' : ''; ?><?php echo $i > 1 ? ' hidden' : ''; ?>>
					<blockquote><p><?php echo esc_html( '“' . trim( $person['text'], '“”"' ) . '”' ); ?></p></blockquote>
					<figcaption class="person">
						<?php echo intella_avatar( $person ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<div><p class="person__name"><?php echo esc_html( $person['name'] ); ?></p><p class="person__role"><?php echo esc_html( $person['role'] ); ?></p></div>
					</figcaption>
				</figure>
				<?php endforeach; ?>
			</div>
			<?php if ( $rotate ) : ?>
			<div class="quote-pairs__nav"><?php for ( $i = 0; $i < $pairs; $i++ ) : ?><button class="quote-pairs__dot" type="button" aria-label="<?php echo esc_attr( sprintf( /* translators: 1, 2: numbers */ __( 'Show testimonials %1$d and %2$d', 'intella' ), $i * 2 + 1, $i * 2 + 2 ) ); ?>"<?php echo 0 === $i ? ' aria-current="true"' : ''; ?> data-quote-pair-dot></button><?php endfor; ?></div>
			<?php endif; ?>
		</div>
		<?php if ( intella_items( $attributes['cases'] ) ) : ?>
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
		<?php endif; ?>
	</div>
</section>
