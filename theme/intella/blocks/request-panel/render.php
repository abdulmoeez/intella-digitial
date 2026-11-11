<?php
/**
 * Request panel: "what happens next" steps and contact details beside a short request form
 * (name, work email, website, message). Enquiries arrive under Leads.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'cta-title' );
$p        = intella_uid( 'proposal' );
$phone    = intella_setting( 'phone' );
$email    = intella_setting( 'email' );
$check    = intella_icon( 'check' );
?>
<section<?php echo intella_section_attrs( $attributes, '', $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="container">
		<div class="cta-panel acta">
			<div class="cta-panel__intro">
				<h2 class="cta-panel__title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo intella_accent( $attributes['title'], 'accent--light' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<p class="cta-panel__lead"><?php echo esc_html( $attributes['lead'] ); ?></p>
				<p class="acta__sub"><?php esc_html_e( 'What happens next', 'intella' ); ?></p>
				<ol class="acta__steps" role="list"><?php foreach ( intella_items( $attributes['steps'] ) as $i => $step ) : ?><li><span class="acta__n"><?php echo (int) $i + 1; ?></span><div><b><?php echo esc_html( $step['title'] ?? '' ); ?></b><p><?php echo esc_html( $step['text'] ?? '' ); ?></p></div></li><?php endforeach; ?></ol>
				<p class="acta__contact"><?php esc_html_e( 'Prefer to talk?', 'intella' ); ?> <a href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo esc_html( $email ); ?></a> · <a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></p>
			</div>
			<form class="cta-form acta__form" id="<?php echo esc_attr( $p ); ?>" method="post" data-validate action="<?php echo esc_url( intella_form_action() ); ?>">
				<?php echo intella_form_hidden_fields( 'request' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<p class="cta-form__title"><?php echo esc_html( $attributes['formTitle'] ); ?></p>
				<div class="acta__row">
					<div class="field">
						<label class="acta__label" for="<?php echo esc_attr( $p ); ?>-name"><?php esc_html_e( 'Name', 'intella' ); ?></label>
						<input class="field__input" id="<?php echo esc_attr( $p ); ?>-name" name="name" type="text" placeholder="<?php esc_attr_e( 'Your name', 'intella' ); ?>" required autocomplete="name" data-error-required="<?php esc_attr_e( 'Enter your name.', 'intella' ); ?>">
					</div>
					<div class="field">
						<label class="acta__label" for="<?php echo esc_attr( $p ); ?>-email"><?php esc_html_e( 'Work email', 'intella' ); ?></label>
						<input class="field__input" id="<?php echo esc_attr( $p ); ?>-email" name="email" type="email" placeholder="you@company.com" required autocomplete="email" data-error-required="<?php esc_attr_e( 'Enter your email address.', 'intella' ); ?>" data-error-format="<?php esc_attr_e( 'Enter a valid email address, like name@company.com.', 'intella' ); ?>">
					</div>
				</div>
				<div class="acta__row acta__row--one">
					<div class="field">
						<label class="acta__label" for="<?php echo esc_attr( $p ); ?>-website"><?php esc_html_e( 'Website', 'intella' ); ?></label>
						<input class="field__input" id="<?php echo esc_attr( $p ); ?>-website" name="website" type="text" inputmode="url" placeholder="yourcompany.com" required autocomplete="url" data-validate-url data-error-required="<?php esc_attr_e( 'Enter your website address.', 'intella' ); ?>" data-error-format="<?php esc_attr_e( 'Enter a valid web address, like yourcompany.com.', 'intella' ); ?>">
					</div>
				</div>
				<div class="field">
					<label class="acta__label" for="<?php echo esc_attr( $p ); ?>-message"><?php esc_html_e( 'Anything else?', 'intella' ); ?> <span class="field__optional"><?php esc_html_e( '(optional)', 'intella' ); ?></span></label>
					<textarea class="field__input" id="<?php echo esc_attr( $p ); ?>-message" name="message" rows="3" placeholder="<?php echo esc_attr( $attributes['messageHint'] ); ?>"></textarea>
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
