<?php
/**
 * Process timeline (dark): steps on a line that fills as it moves on (journey.js), one card per step
 * with what the client gets and what we need, and a closing call to action.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'process-title' );
$prefix   = intella_uid( 'journey' );
$steps    = intella_items( $attributes['steps'] );
$check    = intella_icon( 'check' );
?>
<section class="section section--dark process" aria-labelledby="<?php echo esc_attr( $title_id ); ?>">
	<div class="container">
		<div class="section-head section-head--center">
			<h2 class="section-title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo intella_accent( $attributes['title'], 'accent--light' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			<p class="section-lead"><?php echo esc_html( $attributes['lead'] ); ?></p>
		</div>
		<div class="journey" data-journey>
			<div class="journey__rail" role="tablist" aria-label="<?php esc_attr_e( 'Our process, step by step', 'intella' ); ?>">
				<?php foreach ( $steps as $i => $step ) : ?>
				<button class="journey__step<?php echo 0 === $i ? ' is-reached' : ''; ?>" type="button" role="tab" id="<?php echo esc_attr( "{$prefix}-tab-{$i}" ); ?>" aria-controls="<?php echo esc_attr( "{$prefix}-panel-{$i}" ); ?>" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>"<?php echo 0 === $i ? '' : ' tabindex="-1"'; ?>>
					<span class="journey__node" aria-hidden="true"><?php echo esc_html( intella_num( $i + 1 ) ); ?></span>
					<span class="journey__when"><?php echo esc_html( $step['when'] ?? '' ); ?></span>
					<span class="journey__step-title"><?php echo esc_html( ! empty( $step['tab'] ) ? $step['tab'] : ( $step['title'] ?? '' ) ); ?></span>
				</button>
				<?php endforeach; ?>
			</div>
			<div class="journey__panels">
				<?php foreach ( $steps as $i => $step ) : ?>
				<article class="journey__card<?php echo 0 === $i ? ' is-current' : ''; ?>" role="tabpanel" id="<?php echo esc_attr( "{$prefix}-panel-{$i}" ); ?>" aria-labelledby="<?php echo esc_attr( "{$prefix}-tab-{$i}" ); ?>"<?php echo 0 === $i ? '' : ' aria-hidden="true" inert'; ?>>
					<div class="journey__main">
						<p class="journey__meta"><span class="journey__icon"><?php echo intella_icon( ! empty( $step['icon'] ) ? $step['icon'] : 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span></p>
						<h3 class="journey__title"><?php echo esc_html( $step['title'] ?? '' ); ?></h3>
						<p class="journey__text"><?php echo esc_html( $step['text'] ?? '' ); ?></p>
					</div>
					<div class="journey__lists">
						<div>
							<p class="journey__label"><?php echo esc_html( $attributes['getLabel'] ); ?></p>
							<ul class="journey__get" role="list"><?php foreach ( intella_lines( $step['get'] ?? array() ) as $line ) : ?><li><?php echo $check; // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $line ); ?></li><?php endforeach; ?></ul>
						</div>
						<div>
							<p class="journey__label"><?php echo esc_html( $attributes['needLabel'] ); ?></p>
							<ul class="journey__yours" role="list"><?php foreach ( intella_lines( $step['need'] ?? array() ) as $line ) : ?><li><?php echo esc_html( $line ); ?></li><?php endforeach; ?></ul>
						</div>
					</div>
				</article>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="journey__end">
				<p class="journey__end-text"><?php echo esc_html( $attributes['endText'] ); ?></p>
			<?php echo intella_button( array( 'text' => $attributes['buttonText'], 'url' => $attributes['buttonUrl'] ), 'button button--light button--lg' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
	</div>
</section>
