<?php
/**
 * Get in touch: heading and rotating testimonials (quote-sliders.js) beside the proposal form.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'cta-title' );
$p        = intella_uid( 'proposal' );
$people   = function_exists( 'intella_shared_items' ) ? intella_shared_items( 'testimonial', (array) $attributes['people'], 2 ) : array();
$check    = intella_icon( 'check' );
?>
<section<?php echo intella_section_attrs( $attributes, '', $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="container">
		<div class="cta-panel">
			<div class="cta-panel__intro">
				<h2 class="cta-panel__title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo intella_accent( $attributes['title'], 'accent--light' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<p class="cta-panel__lead"><?php echo esc_html( $attributes['lead'] ); ?></p>
				<?php if ( $people ) : ?>
				<div class="quote-slider" data-quote-slider aria-roledescription="carousel" aria-label="<?php esc_attr_e( 'What our clients say', 'intella' ); ?>">
					<div class="quote-slider__slides" aria-live="polite">
						<?php foreach ( $people as $i => $person ) : ?>
						<figure class="quote-slide<?php echo 0 === $i ? ' is-active' : ''; ?>" data-quote-slide>
							<blockquote><p><?php echo esc_html( '“' . trim( $person['text'], '“”"' ) . '”' ); ?></p></blockquote>
							<figcaption class="person">
								<?php echo intella_avatar( $person ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								<div><p class="person__name"><?php echo esc_html( $person['name'] ); ?></p><p class="person__role"><?php echo esc_html( $person['role'] ); ?></p></div>
							</figcaption>
						</figure>
						<?php endforeach; ?>
					</div>
					<?php if ( count( $people ) > 1 ) : ?>
					<div class="quote-slider__dots" role="group" aria-label="<?php esc_attr_e( 'Choose a testimonial', 'intella' ); ?>">
						<?php foreach ( $people as $i => $person ) : ?>
						<button class="quote-slider__dot<?php echo 0 === $i ? ' is-active' : ''; ?>" type="button" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: number */ __( 'Testimonial %d', 'intella' ), $i + 1 ) ); ?>" aria-pressed="<?php echo 0 === $i ? 'true' : 'false'; ?>" data-quote-dot="<?php echo (int) $i; ?>"></button>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</div>
			<form class="cta-form" id="<?php echo esc_attr( $p ); ?>" method="post" data-validate action="<?php echo esc_url( intella_form_action() ); ?>">
				<?php echo intella_form_hidden_fields( 'proposal' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<p class="cta-form__title"><?php echo esc_html( $attributes['formTitle'] ); ?></p>
				<div class="field">
					<label class="visually-hidden" for="<?php echo esc_attr( $p ); ?>-name"><?php esc_html_e( 'Name', 'intella' ); ?></label>
					<input class="field__input" id="<?php echo esc_attr( $p ); ?>-name" name="name" type="text" placeholder="<?php esc_attr_e( 'Your name *', 'intella' ); ?>" required autocomplete="name" data-error-required="<?php esc_attr_e( 'Enter your name.', 'intella' ); ?>">
				</div>
				<div class="field">
					<label class="visually-hidden" for="<?php echo esc_attr( $p ); ?>-email"><?php esc_html_e( 'Email address', 'intella' ); ?></label>
					<input class="field__input" id="<?php echo esc_attr( $p ); ?>-email" name="email" type="email" placeholder="<?php esc_attr_e( 'Your email address *', 'intella' ); ?>" required autocomplete="email" data-error-required="<?php esc_attr_e( 'Enter your email address.', 'intella' ); ?>" data-error-format="<?php esc_attr_e( 'Enter a valid email address, like name@company.com.', 'intella' ); ?>">
				</div>
				<div class="field">
					<label class="visually-hidden" for="<?php echo esc_attr( $p ); ?>-phone"><?php esc_html_e( 'Phone number (optional)', 'intella' ); ?></label>
					<input class="field__input" id="<?php echo esc_attr( $p ); ?>-phone" name="phone" type="tel" inputmode="tel" placeholder="<?php esc_attr_e( 'Phone number', 'intella' ); ?>" autocomplete="tel" data-validate-phone data-error-format="<?php esc_attr_e( 'Enter a valid phone number: 6 to 14 digits.', 'intella' ); ?>">
				</div>
				<div class="field">
					<label class="visually-hidden" for="<?php echo esc_attr( $p ); ?>-website"><?php esc_html_e( 'Website URL (optional)', 'intella' ); ?></label>
					<input class="field__input" id="<?php echo esc_attr( $p ); ?>-website" name="website" type="text" inputmode="url" placeholder="<?php esc_attr_e( 'Website URL', 'intella' ); ?>" autocomplete="url" data-validate-url data-error-format="<?php esc_attr_e( 'Enter a valid web address, like yourcompany.com.', 'intella' ); ?>">
				</div>
				<div class="field">
					<label class="visually-hidden" for="<?php echo esc_attr( $p ); ?>-message"><?php esc_html_e( 'Message', 'intella' ); ?></label>
					<textarea class="field__input" id="<?php echo esc_attr( $p ); ?>-message" name="message" rows="4" placeholder="<?php echo esc_attr( $attributes['messageHint'] ); ?>" required minlength="10" data-error-required="<?php esc_attr_e( 'Tell us a little about what you need.', 'intella' ); ?>" data-error-short="<?php esc_attr_e( 'Add a little more detail (at least 10 characters).', 'intella' ); ?>"></textarea>
				</div>
				<input type="hidden" name="service" value="<?php echo esc_attr( $attributes['service'] ); ?>">
				<div class="hp-field" aria-hidden="true">
					<label for="<?php echo esc_attr( $p ); ?>-company"><?php esc_html_e( 'Leave this empty', 'intella' ); ?></label>
					<input id="<?php echo esc_attr( $p ); ?>-company" name="company_hp" type="text" tabindex="-1" autocomplete="off">
				</div>
				<p class="form-status" role="alert" data-form-status hidden></p>
				<button class="button button--lg button--block cta-form__submit" type="submit" data-submit><span data-submit-label><?php echo esc_html( $attributes['buttonText'] ); ?></span> <?php echo intella_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
				<?php if ( intella_lines( $attributes['trust'] ) ) : ?>
				<ul class="form-trust" role="list"><?php foreach ( intella_lines( $attributes['trust'] ) as $line ) : ?><li><?php echo $check; // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( $line ); ?></li><?php endforeach; ?></ul>
				<?php endif; ?>
			</form>
		</div>
	</div>
</section>
