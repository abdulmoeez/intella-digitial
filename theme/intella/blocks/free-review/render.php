<?php
/**
 * Free review offer: dark panel with heading, intro and checklist, beside a short form.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'offer-title' );
$form_id  = intella_uid( 'review' );
$check    = intella_icon( 'check' );
?>
<section<?php echo intella_section_attrs( $attributes, '', $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="container">
		<div class="offer">
			<div>
				<h2 class="offer__title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo intella_accent( $attributes['title'], 'accent--light' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<?php if ( $attributes['lead'] ) : ?>
					<p class="offer__lead"><?php echo esc_html( $attributes['lead'] ); ?></p>
				<?php endif; ?>
				<?php if ( intella_lines( $attributes['checks'] ) ) : ?>
					<ul class="check-list" role="list">
						<?php foreach ( intella_lines( $attributes['checks'] ) as $line ) : ?>
							<li><?php echo $check; // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( $line ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
			<form class="form-card" action="<?php echo esc_url( intella_form_action() ); ?>" method="post" data-validate>
				<?php echo intella_form_hidden_fields( 'review' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the helper. ?>
				<div class="form-head">
					<div><p class="form-head__title"><?php echo esc_html( $attributes['formTitle'] ); ?></p><p class="form-head__sub"><?php echo esc_html( $attributes['formSub'] ); ?></p></div>
				</div>
				<div class="field">
					<label class="field__label" for="<?php echo esc_attr( $form_id ); ?>-website"><?php esc_html_e( 'Your website', 'intella' ); ?></label>
					<input class="field__input" id="<?php echo esc_attr( $form_id ); ?>-website" name="website" type="url" placeholder="https://" required autocomplete="url">
				</div>
				<div class="field">
					<label class="field__label" for="<?php echo esc_attr( $form_id ); ?>-email"><?php esc_html_e( 'Work email', 'intella' ); ?></label>
					<input class="field__input" id="<?php echo esc_attr( $form_id ); ?>-email" name="email" type="email" placeholder="you@company.com" required autocomplete="email">
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
	</div>
</section>
