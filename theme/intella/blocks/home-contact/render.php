<?php
/**
 * Contact: ways to reach us and rotating "how we work" cards (human-cards.js), beside the proposal form.
 * Enquiries arrive under Leads in the admin.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'contact-title' );
$form_id  = intella_uid( 'contact' );
$cards    = intella_items( $attributes['cards'] );
$check    = intella_icon( 'check' );
?>
<section<?php echo intella_section_attrs( $attributes, '', $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="container contact">
		<div>
			<h2 class="section-title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo intella_accent( $attributes['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			<p class="section-lead"><?php echo esc_html( $attributes['lead'] ); ?></p>
			<ul class="contact__ways" role="list">
				<?php foreach ( intella_items( $attributes['ways'] ) as $way ) : ?>
				<li class="contact-way">
					<span class="icon-tile"><?php echo intella_icon( ! empty( $way['icon'] ) ? $way['icon'] : 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<div><p class="contact-way__label"><?php echo esc_html( $way['label'] ?? '' ); ?></p><a class="contact-way__value" href="<?php echo esc_url( intella_url( $way['url'] ?? '' ) ); ?>"><?php echo esc_html( $way['value'] ?? '' ); ?></a></div>
				</li>
				<?php endforeach; ?>
			</ul>
			<?php if ( $cards ) : ?>
			<div class="contact-human" data-human aria-roledescription="carousel" aria-label="<?php esc_attr_e( 'How we work with you', 'intella' ); ?>">
				<?php foreach ( $cards as $i => $card ) : ?>
				<figure class="contact-human__card contact-human__card--<?php echo esc_attr( sanitize_html_class( $card['style'] ?? 'reply' ) ); ?><?php echo 0 === $i ? ' is-on' : ''; ?>" data-human-card<?php echo 0 === $i ? '' : ' aria-hidden="true"'; ?>>
					<div class="contact-human__chat">
						<?php foreach ( intella_lines( $card['bubbles'] ?? array() ) as $bubble ) : ?>
						<p class="contact-human__bubble"><?php echo esc_html( $bubble ); ?></p>
						<?php endforeach; ?>
						<p class="contact-human__typing" aria-hidden="true"><span></span><span></span><span></span></p>
					</div>
					<?php echo intella_image( $card['image'] ?? 0, 'full', array( 'class' => 'contact-human__person', 'alt' => '', 'loading' => 'lazy', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<figcaption class="contact-human__note"><span class="contact-human__dot" aria-hidden="true"></span><?php echo esc_html( $card['note'] ?? '' ); ?></figcaption>
				</figure>
				<?php endforeach; ?>
				<div class="contact-human__pips"><?php foreach ( $cards as $i => $card ) : ?><button class="contact-human__pip<?php echo 0 === $i ? ' is-on' : ''; ?>" type="button" data-human-dot="<?php echo (int) $i; ?>"><span class="visually-hidden"><?php echo esc_html( sprintf( /* translators: %d: number */ __( 'Show card %d', 'intella' ), $i + 1 ) ); ?></span></button><?php endforeach; ?></div>
			</div>
			<?php endif; ?>
		</div>
		<form class="contact-form" action="<?php echo esc_url( intella_form_action() ); ?>" method="post" data-validate>
			<?php echo intella_form_hidden_fields( 'contact' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the helper. ?>
			<div class="form-head">
				<div><p class="form-head__title"><?php echo esc_html( $attributes['formTitle'] ); ?></p><p class="form-head__sub"><?php echo esc_html( $attributes['formSub'] ); ?></p></div>
			</div>
			<div class="contact-form__row">
				<div class="field">
					<label class="field__label" for="<?php echo esc_attr( $form_id ); ?>-name"><?php esc_html_e( 'Name', 'intella' ); ?></label>
					<input class="field__input" id="<?php echo esc_attr( $form_id ); ?>-name" name="name" type="text" placeholder="<?php esc_attr_e( 'Your name', 'intella' ); ?>" required autocomplete="name">
				</div>
				<div class="field">
					<label class="field__label" for="<?php echo esc_attr( $form_id ); ?>-email"><?php esc_html_e( 'Email', 'intella' ); ?></label>
					<input class="field__input" id="<?php echo esc_attr( $form_id ); ?>-email" name="email" type="email" placeholder="you@company.com" required autocomplete="email">
				</div>
			</div>
			<div class="contact-form__row">
				<div class="field">
					<label class="field__label" for="<?php echo esc_attr( $form_id ); ?>-phone"><?php esc_html_e( 'Phone', 'intella' ); ?></label>
					<input class="field__input" id="<?php echo esc_attr( $form_id ); ?>-phone" name="phone" type="tel" placeholder="+1 555 000 0000" autocomplete="tel">
				</div>
				<div class="field">
					<label class="field__label" for="<?php echo esc_attr( $form_id ); ?>-website"><?php esc_html_e( 'Website URL', 'intella' ); ?></label>
					<input class="field__input" id="<?php echo esc_attr( $form_id ); ?>-website" name="website" type="url" placeholder="https://" autocomplete="url">
				</div>
			</div>
			<?php if ( intella_lines( $attributes['services'] ) ) : ?>
			<div class="field">
				<label class="field__label" for="<?php echo esc_attr( $form_id ); ?>-service"><?php esc_html_e( 'What do you need help with?', 'intella' ); ?> <span class="field__optional"><?php esc_html_e( '(optional)', 'intella' ); ?></span></label>
				<select class="field__input field__select" id="<?php echo esc_attr( $form_id ); ?>-service" name="service">
					<option value=""><?php esc_html_e( 'Choose a service', 'intella' ); ?></option>
					<?php foreach ( intella_lines( $attributes['services'] ) as $service ) : ?>
					<option><?php echo esc_html( $service ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<?php endif; ?>
			<div class="field">
				<label class="field__label" for="<?php echo esc_attr( $form_id ); ?>-message"><?php esc_html_e( 'Message', 'intella' ); ?></label>
				<textarea class="field__input" id="<?php echo esc_attr( $form_id ); ?>-message" name="message" placeholder="<?php esc_attr_e( 'Tell us about your goals…', 'intella' ); ?>" rows="4"></textarea>
			</div>
			<div class="hp-field" aria-hidden="true">
				<label for="<?php echo esc_attr( $form_id ); ?>-company"><?php esc_html_e( 'Leave this empty', 'intella' ); ?></label>
				<input id="<?php echo esc_attr( $form_id ); ?>-company" name="company_hp" type="text" tabindex="-1" autocomplete="off">
			</div>
			<button class="button button--primary button--lg button--block" type="submit" data-submit><span data-submit-label><?php echo esc_html( $attributes['buttonText'] ); ?></span> <?php echo intella_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
			<?php if ( intella_lines( $attributes['trust'] ) ) : ?>
				<ul class="form-trust" role="list"><?php foreach ( intella_lines( $attributes['trust'] ) as $line ) : ?><li><?php echo $check; // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( $line ); ?></li><?php endforeach; ?></ul>
			<?php endif; ?>
		</form>
	</div>
</section>
