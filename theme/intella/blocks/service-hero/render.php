<?php
/**
 * Service hero: breadcrumb, H1, intro and ticked notes beside a short proposal form.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'page-title' );
$p        = intella_uid( 'proposal' );
$crumbs   = intella_items( $attributes['crumbs'] );
$check    = intella_icon( 'check' );
?>
<section class="page-hero" aria-labelledby="<?php echo esc_attr( $title_id ); ?>">
	<div class="container page-hero__inner">
		<div>
			<?php if ( $crumbs ) : ?>
			<nav class="breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'intella' ); ?>"><ol><?php foreach ( $crumbs as $i => $crumb ) : ?><?php if ( count( $crumbs ) - 1 === $i ) : ?><li aria-current="page"><?php echo esc_html( $crumb['label'] ?? '' ); ?></li><?php else : ?><li><a href="<?php echo esc_url( intella_url( $crumb['url'] ?? '/' ) ); ?>"><?php echo esc_html( $crumb['label'] ?? '' ); ?></a></li><?php endif; ?><?php endforeach; ?></ol></nav>
			<?php endif; ?>
			<h1 class="page-hero__title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo intella_accent( $attributes['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>
			<p class="page-hero__lead"><?php echo esc_html( $attributes['lead'] ); ?></p>
			<?php if ( intella_lines( $attributes['notes'] ) ) : ?>
			<ul class="note-list" role="list">
				<?php foreach ( intella_lines( $attributes['notes'] ) as $note ) : ?>
				<li><?php echo $check; // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( $note ); ?></li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>
		</div>
		<form class="form-card form-compact" id="<?php echo esc_attr( $p ); ?>" method="post" data-validate action="<?php echo esc_url( intella_form_action() ); ?>">
			<?php echo intella_form_hidden_fields( 'proposal' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="form-head">
				<div><h2 class="form-head__title"><?php echo esc_html( $attributes['formTitle'] ); ?></h2><p class="form-head__sub"><?php echo esc_html( $attributes['formSub'] ); ?></p></div>
			</div>
			<div class="contact-form__row">
				<div class="field">
					<label class="visually-hidden" for="<?php echo esc_attr( $p ); ?>-name"><?php esc_html_e( 'Name', 'intella' ); ?></label>
					<input class="field__input" id="<?php echo esc_attr( $p ); ?>-name" name="name" type="text" placeholder="<?php esc_attr_e( 'Name', 'intella' ); ?>" required autocomplete="name">
				</div>
				<div class="field">
					<label class="visually-hidden" for="<?php echo esc_attr( $p ); ?>-email"><?php esc_html_e( 'Work email', 'intella' ); ?></label>
					<input class="field__input" id="<?php echo esc_attr( $p ); ?>-email" name="email" type="email" placeholder="<?php esc_attr_e( 'Email address', 'intella' ); ?>" required autocomplete="email">
				</div>
			</div>
			<div class="contact-form__row">
				<div class="field">
					<label class="visually-hidden" for="<?php echo esc_attr( $p ); ?>-phone"><?php esc_html_e( 'Phone', 'intella' ); ?></label>
					<input class="field__input" id="<?php echo esc_attr( $p ); ?>-phone" name="phone" type="tel" placeholder="<?php esc_attr_e( 'Phone (optional)', 'intella' ); ?>" autocomplete="tel">
				</div>
				<div class="field">
					<label class="visually-hidden" for="<?php echo esc_attr( $p ); ?>-website"><?php esc_html_e( 'Website URL', 'intella' ); ?></label>
					<input class="field__input" id="<?php echo esc_attr( $p ); ?>-website" name="website" type="url" placeholder="<?php esc_attr_e( 'Website (optional)', 'intella' ); ?>" autocomplete="url">
				</div>
			</div>
			<div class="field">
				<label class="visually-hidden" for="<?php echo esc_attr( $p ); ?>-message"><?php esc_html_e( 'Message', 'intella' ); ?></label>
				<textarea class="field__input" id="<?php echo esc_attr( $p ); ?>-message" name="message" rows="3" placeholder="<?php echo esc_attr( $attributes['messageHint'] ); ?>"></textarea>
			</div>
			<input type="hidden" name="service" value="<?php echo esc_attr( $attributes['service'] ); ?>">
			<div class="hp-field" aria-hidden="true">
				<label for="<?php echo esc_attr( $p ); ?>-company"><?php esc_html_e( 'Leave this empty', 'intella' ); ?></label>
				<input id="<?php echo esc_attr( $p ); ?>-company" name="company_hp" type="text" tabindex="-1" autocomplete="off">
			</div>
			<button class="button button--primary button--lg button--block" type="submit" data-submit><span data-submit-label><?php echo esc_html( $attributes['buttonText'] ); ?></span> <?php echo intella_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
			<?php if ( intella_lines( $attributes['trust'] ) ) : ?>
			<ul class="form-trust" role="list"><?php foreach ( intella_lines( $attributes['trust'] ) as $line ) : ?><li><?php echo $check; // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( $line ); ?></li><?php endforeach; ?></ul>
			<?php endif; ?>
		</form>
	</div>
</section>
