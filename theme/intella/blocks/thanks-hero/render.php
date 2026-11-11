<?php
/**
 * Thank-you hero shown after a form is sent. {name} becomes the visitor's first name (thanks-name.js);
 * {phone} and {email} come from Settings → Intella site.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'page-title' );
$phone    = intella_setting( 'phone' );
$email    = intella_setting( 'email' );
$urgent   = esc_html( $attributes['urgent'] );
$urgent   = str_replace(
	array( '{phone}', '{email}' ),
	array(
		'<a href="' . esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) ) . '">' . esc_html( $phone ) . '</a>',
		'<a href="' . esc_url( 'mailto:' . $email ) . '">' . esc_html( $email ) . '</a>',
	),
	$urgent
);
?>
<section class="page-hero page-hero--center thanks-hero" aria-labelledby="<?php echo esc_attr( $title_id ); ?>">
	<div class="container">
		<span class="success-badge" aria-hidden="true"><?php echo intella_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
		<h1 class="page-hero__title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo str_replace( '{name}', '<span data-thanks-name></span>', intella_accent( $attributes['title'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>
		<p class="page-hero__lead"><?php echo esc_html( $attributes['lead'] ); ?></p>
		<div class="hero__actions">
			<?php foreach ( intella_items( $attributes['buttons'] ) as $button ) : ?>
				<?php $primary = 'outline' !== ( $button['style'] ?? 'primary' ); ?>
				<?php echo intella_button( $button, 'button button--' . ( $primary ? 'primary' : 'outline' ) . ' button--lg', $primary ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php endforeach; ?>
		</div>
		<?php if ( $attributes['urgent'] ) : ?>
		<p class="thanks-hero__urgent"><?php echo $urgent; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above. ?></p>
		<?php endif; ?>
	</div>
</section>
