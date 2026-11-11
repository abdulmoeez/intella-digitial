<?php
/**
 * Hero with short form: breadcrumb, H1, intro, a website + work email form and ticked notes, beside a slider of
 * "problem → how we fix it" slides (carousels.js) or a single picture.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'page-title' );
$p        = intella_uid( 'hero-form' );
$crumbs   = intella_items( $attributes['crumbs'] );
$slides   = intella_items( $attributes['slides'] );
$look     = trim( preg_replace( '/[^a-z0-9_ -]/i', '', (string) $attributes['look'] ) );
$visual   = sanitize_html_class( strtok( $look ?: 'cmhero', ' ' ) ) . '__visual';
$total    = count( $slides );
$check    = intella_icon( 'check' );
?>
<section class="<?php echo esc_attr( trim( 'page-hero ' . $look ) ); ?>" aria-labelledby="<?php echo esc_attr( $title_id ); ?>">
	<div class="container page-hero__inner">
		<div>
			<?php if ( $crumbs ) : ?>
			<nav class="breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'intella' ); ?>"><ol><?php foreach ( $crumbs as $i => $crumb ) : ?><?php if ( count( $crumbs ) - 1 === $i ) : ?><li aria-current="page"><?php echo esc_html( $crumb['label'] ?? '' ); ?></li><?php else : ?><li><a href="<?php echo esc_url( intella_url( $crumb['url'] ?? '/' ) ); ?>"><?php echo esc_html( $crumb['label'] ?? '' ); ?></a></li><?php endif; ?><?php endforeach; ?></ol></nav>
			<?php endif; ?>
			<h1 class="page-hero__title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo intella_accent( $attributes['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>
			<p class="page-hero__lead"><?php echo esc_html( $attributes['lead'] ); ?></p>
			<form class="wpform" id="<?php echo esc_attr( $p ); ?>" method="post" data-validate aria-label="<?php echo esc_attr( $attributes['formLabel'] ?: $attributes['buttonText'] ); ?>" action="<?php echo esc_url( intella_form_action() ); ?>">
				<?php echo intella_form_hidden_fields( 'review' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<div class="field">
					<label class="visually-hidden" for="<?php echo esc_attr( $p ); ?>-site"><?php esc_html_e( 'Your website', 'intella' ); ?></label>
					<input class="field__input" id="<?php echo esc_attr( $p ); ?>-site" name="site_url" type="text" inputmode="url" placeholder="<?php esc_attr_e( 'Your website', 'intella' ); ?>" required autocomplete="url" data-validate-url data-error-required="<?php esc_attr_e( 'Enter your website address.', 'intella' ); ?>" data-error-format="<?php esc_attr_e( 'Enter a valid web address, like yourcompany.com.', 'intella' ); ?>">
				</div>
				<div class="field">
					<label class="visually-hidden" for="<?php echo esc_attr( $p ); ?>-email"><?php esc_html_e( 'Work email', 'intella' ); ?></label>
					<input class="field__input" id="<?php echo esc_attr( $p ); ?>-email" name="email" type="email" placeholder="<?php esc_attr_e( 'Work email', 'intella' ); ?>" required autocomplete="email" data-error-required="<?php esc_attr_e( 'Enter your email address.', 'intella' ); ?>" data-error-format="<?php esc_attr_e( 'Enter a valid email address, like name@company.com.', 'intella' ); ?>">
				</div>
				<input type="hidden" name="service" value="<?php echo esc_attr( $attributes['service'] ); ?>">
				<div class="hp-field" aria-hidden="true"><label for="<?php echo esc_attr( $p ); ?>-company"><?php esc_html_e( 'Leave this empty', 'intella' ); ?></label><input id="<?php echo esc_attr( $p ); ?>-company" name="company_hp" type="text" tabindex="-1" autocomplete="off"></div>
				<button class="button button--primary button--lg" type="submit" data-submit><span data-submit-label><?php echo esc_html( $attributes['buttonText'] ); ?></span> <?php echo intella_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
				<p class="form-status" role="alert" data-form-status hidden></p>
			</form>
			<?php if ( intella_lines( $attributes['notes'] ) ) : ?>
			<ul class="note-list wphero__notes" role="list"><?php foreach ( intella_lines( $attributes['notes'] ) as $note ) : ?><li><?php echo $check; // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( $note ); ?></li><?php endforeach; ?></ul>
			<?php endif; ?>
		</div>
		<div class="page-hero__visual <?php echo esc_attr( $visual ); ?>"><?php if ( ! $slides ) : ?><?php echo intella_visual( $attributes['visual'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- admin-only HTML field. ?><?php else : ?><?php if ( $attributes['tools'] ) : ?><div class="pmhv"><?php echo intella_visual( $attributes['tools'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php endif; ?><figure class="cmhc" aria-roledescription="carousel" aria-label="<?php echo esc_attr( $attributes['sliderLabel'] ); ?>" data-carousel>
			<div class="cmhc__view"><div class="cmhc__track" aria-live="polite" data-track>
				<?php foreach ( $slides as $i => $slide ) : ?>
				<?php
				$look_class = ! empty( $slide['look'] ) ? ' cmhc__slide--' . sanitize_html_class( $slide['look'] ) : '';
				$photo      = ! empty( $slide['photo'] ) ? wp_get_attachment_image_src( (int) $slide['photo'], 'full' ) : false;
				?>
				<div class="cmhc__slide cmhc__slide--n<?php echo esc_attr( $i + 1 ); ?><?php echo esc_attr( $look_class ); ?>" role="group" aria-roledescription="slide" aria-label="<?php echo esc_attr( sprintf( /* translators: 1: number, 2: total, 3: title */ __( '%1$d of %2$d: %3$s', 'intella' ), $i + 1, $total, $slide['title'] ?? '' ) ); ?>" data-slide>
					<div class="cmhc__stage">
						<?php if ( ! empty( $slide['kicker'] ) ) : ?><p class="cmhc__lab"><?php echo esc_html( $slide['kicker'] ); ?></p><?php endif; ?>
						<p class="cmhc__title"><?php echo esc_html( $slide['title'] ?? '' ); ?></p>
						<div class="cmhc__ill"><?php echo intella_visual( $slide['card'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php if ( $photo ) : ?><img class="cmhc__ph" src="<?php echo esc_url( $photo[0] ); ?>" alt="" width="380" height="440" loading="<?php echo 0 === $i ? 'eager' : 'lazy'; ?>" decoding="async"><?php endif; ?></div>
						<p class="cmhc__fix"><span><?php echo esc_html( $attributes['fixLabel'] ); ?></span><?php echo esc_html( $slide['fix'] ?? '' ); ?></p>
					</div>
				</div>
				<?php endforeach; ?>
			</div></div>
			<div class="cmhc__nav">
				<button class="cmhc__arr" type="button" data-prev aria-label="<?php esc_attr_e( 'Previous slide', 'intella' ); ?>">←</button>
				<div class="cmhc__dots"><?php for ( $i = 0; $i < $total; $i++ ) : ?><button class="cmhc__dot" type="button" data-dot="<?php echo esc_attr( $i ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: slide number */ __( 'Show slide %d', 'intella' ), $i + 1 ) ); ?>"></button><?php endfor; ?></div>
				<button class="cmhc__arr" type="button" data-next aria-label="<?php esc_attr_e( 'Next slide', 'intella' ); ?>">→</button>
			</div>
		</figure><?php if ( $attributes['tools'] ) : ?></div><?php endif; ?><?php endif; ?></div>
	</div>
</section>
