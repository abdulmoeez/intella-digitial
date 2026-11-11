<?php
/**
 * Page hero: H1, intro and buttons. Centred (with jump links) or split (service cards on the right).
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'page-title' );
$buttons  = intella_items( $attributes['buttons'] );
$center   = 'center' === $attributes['layout'];
ob_start();
?>
<h1 class="page-hero__title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo intella_accent( $attributes['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>
<p class="page-hero__lead"><?php echo esc_html( $attributes['lead'] ); ?></p>
<?php if ( $buttons ) : ?>
<div class="hero__actions">
	<?php foreach ( $buttons as $button ) : ?>
		<?php $primary = 'outline' !== ( $button['style'] ?? 'primary' ); ?>
		<?php echo intella_button( $button, 'button button--' . ( $primary ? 'primary' : 'outline' ) . ' button--lg', $primary ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<?php endforeach; ?>
</div>
<?php endif; ?>
<?php
$intro = ob_get_clean();
?>
<?php if ( $center ) : ?>
<section class="page-hero page-hero--center" aria-labelledby="<?php echo esc_attr( $title_id ); ?>">
	<div class="container">
		<?php echo $intro; // phpcs:ignore WordPress.Security.EscapeOutput -- built above from escaped parts. ?>
		<?php if ( intella_items( $attributes['chips'] ) ) : ?>
		<ul class="chip-row" role="list" aria-label="<?php echo esc_attr( $attributes['chipsLabel'] ); ?>">
			<?php foreach ( intella_items( $attributes['chips'] ) as $chip ) : ?>
			<li><a class="chip" href="<?php echo esc_url( intella_url( $chip['url'] ?? '' ) ); ?>"><?php echo esc_html( $chip['label'] ?? '' ); ?></a></li>
			<?php endforeach; ?>
		</ul>
		<?php endif; ?>
	</div>
</section>
<?php else : ?>
<section class="page-hero" aria-labelledby="<?php echo esc_attr( $title_id ); ?>">
	<div class="container page-hero__inner page-hero__inner--center">
		<div>
			<?php echo $intro; // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
		<ul class="bento" role="list" aria-label="<?php esc_attr_e( 'What we do', 'intella' ); ?>">
			<?php foreach ( intella_items( $attributes['cards'] ) as $card ) : ?>
				<?php $dark = ! empty( $card['dark'] ); ?>
			<li class="bento__card<?php echo $dark ? ' bento__card--dark' : ''; ?>">
				<span class="icon-tile<?php echo $dark ? ' icon-tile--dark' : ''; ?>"><?php echo intella_icon( ! empty( $card['icon'] ) ? $card['icon'] : 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				<h2 class="bento__title"><a class="bento__link" href="<?php echo esc_url( intella_url( $card['url'] ?? '' ) ); ?>"><?php echo esc_html( $card['title'] ?? '' ); ?></a></h2>
				<p><?php echo esc_html( $card['text'] ?? '' ); ?></p>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
<?php endif; ?>
