<?php
/**
 * Testimonials: a large rotating quote with progress bars (testimonials.js), and small quote cards that
 * change three at a time. Quotes come from Testimonials in the admin.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'intella_shared_items' ) ) {
	return;
}
$title_id = intella_uid( 'testimonials-title' );
$spot     = intella_shared_items( 'testimonial', (array) $attributes['spotlight'] );
$cards    = $attributes['cards'] ? intella_shared_items( 'testimonial', (array) $attributes['cards'] ) : array();
$total    = count( $spot );
$looks    = array( ' quote-card--dark', '', ' quote-card--lavender' );

/**
 * Photo or initials for a person.
 *
 * @param array $person Shared item.
 */
$avatar = static function ( array $person ): string {
	if ( $person['image'] ) {
		// Shown as a square by the CSS: photos are declared 300×300, small SVG placeholders 140×140 (as designed).
		$src  = wp_get_attachment_image_src( $person['image'], 'medium' );
		$side = 'image/svg+xml' === get_post_mime_type( $person['image'] ) ? 140 : 300;
		return sprintf( '<img src="%s" width="%d" height="%d" alt="" loading="lazy">', esc_url( $src ? $src[0] : '' ), $side, $side );
	}
	$initials = implode( '', array_map( static fn( $part ) => mb_substr( $part, 0, 1 ), array_slice( preg_split( '/\s+/', $person['name'] ), 0, 2 ) ) );
	return '<span class="person__initials" aria-hidden="true">' . esc_html( strtoupper( $initials ) ) . '</span>';
};
?>
<section<?php echo intella_section_attrs( $attributes, 'testimonials', $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?> data-testimonials>
	<div class="container">
		<div class="section-head section-head--center">
			<h2 class="section-title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo intella_accent( $attributes['title'], 'accent--plain' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			<p class="section-lead"><?php echo intella_inline( $attributes['lead'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
		</div>
		<div class="testimonials__frame">
			<div class="testimonials__slides" aria-live="polite">
				<?php foreach ( $spot as $i => $person ) : ?>
				<figure class="spotlight<?php echo 0 === $i ? ' is-active' : ''; ?>" data-slide aria-label="<?php echo esc_attr( sprintf( /* translators: 1: number, 2: total */ __( 'Testimonial %1$d of %2$d', 'intella' ), $i + 1, $total ) ); ?>">
					<figcaption class="spotlight__person">
						<?php echo $avatar( $person ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<p class="spotlight__name"><?php echo esc_html( $person['name'] ); ?></p>
						<p class="spotlight__role"><?php echo esc_html( $person['role'] ); ?></p>
					</figcaption>
					<blockquote class="spotlight__quote"><p><?php echo esc_html( '“' . trim( $person['text'], '“”"' ) . '”' ); ?></p></blockquote>
				</figure>
				<?php endforeach; ?>
			</div>
			<?php if ( $cards ) : ?>
			<div class="quote-row">
				<?php foreach ( $cards as $i => $person ) : ?>
				<figure class="quote-card<?php echo $i < 3 ? esc_attr( $looks[ $i ] ) : ''; ?>" data-quote<?php echo $i < 3 ? '' : ' hidden'; ?>>
					<blockquote><p><?php echo esc_html( '“' . trim( $person['short_quote'] ? $person['short_quote'] : $person['text'], '“”"' ) . '”' ); ?></p></blockquote>
					<figcaption class="person">
						<?php echo $avatar( $person ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<div><p class="person__name"><?php echo esc_html( $person['name'] ); ?></p><p class="person__role"><?php echo esc_html( $person['role'] ); ?></p></div>
					</figcaption>
				</figure>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
			<?php if ( $total > 1 ) : ?>
			<div class="slider-bar">
				<div class="slider-bar__progress">
					<?php for ( $i = 0; $i < $total; $i++ ) : ?>
						<button class="slider-bar__track<?php echo 0 === $i ? ' is-active' : ''; ?>" type="button" data-slide-to="<?php echo (int) $i; ?>"><span class="visually-hidden"><?php echo esc_html( sprintf( /* translators: %d: number */ __( 'Show testimonial %d', 'intella' ), $i + 1 ) ); ?></span><span class="slider-bar__fill"></span></button>
					<?php endfor; ?>
				</div>
				<p class="slider-bar__count"><strong data-slide-current>01</strong> / <?php echo esc_html( (string) $total ); ?></p>
				<div class="slider-bar__arrows">
					<button class="round-button" type="button" data-slide-prev><span class="visually-hidden"><?php esc_html_e( 'Previous testimonial', 'intella' ); ?></span><?php echo intella_icon( 'arrow-left' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
					<button class="round-button round-button--dark" type="button" data-slide-next><span class="visually-hidden"><?php esc_html_e( 'Next testimonial', 'intella' ); ?></span><?php echo intella_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
				</div>
			</div>
			<?php endif; ?>
		</div>
	</div>
</section>
