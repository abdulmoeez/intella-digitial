<?php
/**
 * The enquiry form (name, email, phone with country code, website, service, message), shared by every block
 * that shows it. Posts to the intella-core form handler; the visitor lands on the thank-you page.
 *
 * @package Intella
 *
 * @var array $args {
 *     @type string $form     Form name stored with the lead, e.g. 'contact'.
 *     @type string $id       HTML id of the form (also the prefix for field ids).
 *     @type string $class    Form classes.
 *     @type string $title    Heading. $sub: line under it.
 *     @type array  $services "What do you need help with?" options (empty = no select).
 *     @type string $button   Button text.
 *     @type array  $trust    Reassurance points under the button.
 *     @type string $note     Small print at the bottom.
 * }
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args,
	array(
		'form'     => 'contact',
		'id'       => 'contact-form',
		'class'    => 'contact-form form-compact',
		'title'    => '',
		'sub'      => '',
		'services' => array(),
		'button'   => __( 'Send message', 'intella' ),
		'trust'    => array(),
		'note'     => '',
	)
);
$p     = sanitize_html_class( $args['id'] );
$check = intella_icon( 'check' );
?>
<form class="<?php echo esc_attr( $args['class'] ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>" action="<?php echo esc_url( intella_form_action() ); ?>" method="post" data-validate>
	<?php echo intella_form_hidden_fields( $args['form'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the helper. ?>
	<?php if ( $args['title'] ) : ?>
	<div class="form-head">
		<div><p class="form-head__title"><?php echo esc_html( $args['title'] ); ?></p><p class="form-head__sub"><?php echo esc_html( $args['sub'] ); ?></p></div>
	</div>
	<?php endif; ?>
	<div class="form-row">
		<div class="field">
			<label class="visually-hidden" for="<?php echo esc_attr( $p ); ?>-name"><?php esc_html_e( 'Name', 'intella' ); ?></label>
			<input class="field__input" id="<?php echo esc_attr( $p ); ?>-name" name="name" type="text" placeholder="<?php esc_attr_e( 'Name', 'intella' ); ?>" required autocomplete="name" data-error-required="<?php esc_attr_e( 'Enter your name.', 'intella' ); ?>">
		</div>
		<div class="field">
			<label class="visually-hidden" for="<?php echo esc_attr( $p ); ?>-email"><?php esc_html_e( 'Email address', 'intella' ); ?></label>
			<input class="field__input" id="<?php echo esc_attr( $p ); ?>-email" name="email" type="email" placeholder="<?php esc_attr_e( 'Email address', 'intella' ); ?>" required autocomplete="email" data-error-required="<?php esc_attr_e( 'Enter your email address.', 'intella' ); ?>" data-error-format="<?php esc_attr_e( 'Enter a valid email address, like name@company.com.', 'intella' ); ?>">
		</div>
	</div>
	<div class="form-row">
		<div class="field">
			<label class="visually-hidden" for="<?php echo esc_attr( $p ); ?>-phone"><?php esc_html_e( 'Phone number (optional)', 'intella' ); ?></label>
			<div class="phone-field">
				<div class="phone-field__country">
					<span class="phone-field__code" aria-hidden="true" data-dial-label>US +1</span>
					<?php echo intella_icon( 'chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<select class="phone-field__select" id="<?php echo esc_attr( $p ); ?>-country" name="phone_country" aria-label="<?php esc_attr_e( 'Country calling code', 'intella' ); ?>" autocomplete="tel-country-code" data-dial-select>
						<?php echo file_get_contents( __DIR__ . '/phone-countries.html' ); // phpcs:ignore WordPress.Security.EscapeOutput, WordPress.WP.AlternativeFunctions -- static theme file. ?>
					</select>
				</div>
				<input class="field__input phone-field__input" id="<?php echo esc_attr( $p ); ?>-phone" name="phone" type="tel" inputmode="tel" placeholder="<?php esc_attr_e( 'Phone (optional)', 'intella' ); ?>" autocomplete="tel-national" data-validate-phone data-error-format="<?php esc_attr_e( 'Enter a valid phone number: 6 to 14 digits, without the country code.', 'intella' ); ?>">
			</div>
			<input type="hidden" name="phone_full" data-phone-full>
		</div>
		<div class="field">
			<label class="visually-hidden" for="<?php echo esc_attr( $p ); ?>-website"><?php esc_html_e( 'Website URL (optional)', 'intella' ); ?></label>
			<input class="field__input" id="<?php echo esc_attr( $p ); ?>-website" name="website" type="text" inputmode="url" placeholder="<?php esc_attr_e( 'Website (optional)', 'intella' ); ?>" autocomplete="url" data-validate-url data-error-format="<?php esc_attr_e( 'Enter a valid web address, like yourcompany.com.', 'intella' ); ?>">
		</div>
	</div>
	<?php if ( $args['services'] ) : ?>
	<div class="field">
		<label class="visually-hidden" for="<?php echo esc_attr( $p ); ?>-service"><?php esc_html_e( 'What do you need help with? (optional)', 'intella' ); ?></label>
		<select class="field__input field__select" id="<?php echo esc_attr( $p ); ?>-service" name="service">
			<option value=""><?php esc_html_e( 'Choose a service', 'intella' ); ?></option>
			<?php foreach ( $args['services'] as $service ) : ?>
			<option><?php echo esc_html( $service ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<?php endif; ?>
	<div class="field">
		<label class="visually-hidden" for="<?php echo esc_attr( $p ); ?>-message"><?php esc_html_e( 'Message', 'intella' ); ?></label>
		<textarea class="field__input" id="<?php echo esc_attr( $p ); ?>-message" name="message" placeholder="<?php esc_attr_e( 'Tell us about your business and your goals…', 'intella' ); ?>" rows="5" required minlength="10" data-error-required="<?php esc_attr_e( 'Tell us a little about what you need.', 'intella' ); ?>" data-error-short="<?php esc_attr_e( 'Add a little more detail (at least 10 characters).', 'intella' ); ?>"></textarea>
	</div>
	<div class="hp-field" aria-hidden="true">
		<label for="<?php echo esc_attr( $p ); ?>-company"><?php esc_html_e( 'Leave this empty', 'intella' ); ?></label>
		<input id="<?php echo esc_attr( $p ); ?>-company" name="company_hp" type="text" tabindex="-1" autocomplete="off">
	</div>
	<p class="form-status" role="alert" data-form-status<?php echo isset( $_GET['intella_form'] ) ? '' : ' hidden'; // phpcs:ignore WordPress.Security.NonceVerification ?>><?php echo isset( $_GET['intella_form'] ) ? esc_html__( 'Something in the form needs another look. Please check your email address and website, then try again.', 'intella' ) : ''; // phpcs:ignore WordPress.Security.NonceVerification ?></p>
	<button class="button button--primary button--lg button--block" type="submit" data-submit>
		<span data-submit-label><?php echo esc_html( $args['button'] ); ?></span> <?php echo intella_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</button>
	<?php if ( $args['trust'] ) : ?>
	<ul class="form-trust" role="list"><?php foreach ( $args['trust'] as $line ) : ?><li><?php echo $check; // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( $line ); ?></li><?php endforeach; ?></ul>
	<?php endif; ?>
	<?php if ( $args['note'] ) : ?>
	<p class="form-note"><span aria-hidden="true">*</span> <?php echo esc_html( $args['note'] ); ?></p>
	<?php endif; ?>
</form>
