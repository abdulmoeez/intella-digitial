<?php
/**
 * Testimonials, editorial spread: one large quote at a time (photo, name, role, project) with tabs underneath
 * to switch person (tabs.js). Everything comes from Testimonials in the admin.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'intella_shared_items' ) ) {
	return;
}
$people = intella_shared_items( 'testimonial', (array) $attributes['people'] );
if ( ! $people ) {
	return;
}
$title_id = intella_uid( 'h-voices' );
$prefix   = intella_uid( 'vo' );
$total    = count( $people );
$star     = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 17.3 18.2 21l-1.6-7L22 9.2l-7.2-.6L12 2 9.2 8.6 2 9.2 7.4 14l-1.6 7z"/></svg>';

foreach ( $people as $i => $person ) {
	$people[ $i ]['key']   = $prefix . '-' . ( sanitize_title( strtok( $person['name'], ' ' ) ) ?: $i );
	$src                   = $person['image'] ? wp_get_attachment_image_src( $person['image'], 'medium' ) : false;
	$people[ $i ]['photo'] = $src ? $src[0] : '';
	$people[ $i ]['short'] = $person['company'] ?: $person['role'];
}
?>
<section class="section section--tight cmvo" aria-labelledby="<?php echo esc_attr( $title_id ); ?>">
	<div class="container">
		<div class="cmvo__top">
			<h2 class="section-title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo intella_accent( $attributes['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
		</div>
		<div class="cmvo__box" data-tabs>
			<?php foreach ( $people as $i => $person ) : ?>
			<figure class="cmvo__panel" role="tabpanel" id="<?php echo esc_attr( $person['key'] ); ?>" aria-labelledby="<?php echo esc_attr( str_replace( $prefix . '-', $prefix . '-t-', $person['key'] ) ); ?>" tabindex="0"<?php echo $i ? ' hidden' : ''; ?>>
				<div class="cmvo__words">
					<p class="cmvo__no"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?> <span>/ <?php echo esc_html( sprintf( '%02d', $total ) ); ?></span></p>
					<blockquote class="cmvo__q"><p><?php echo esc_html( trim( $person['text'], '“”" ' ) ); ?></p></blockquote>
					<p class="cmvo__stars" role="img" aria-label="<?php esc_attr_e( 'Rated 5 out of 5', 'intella' ); ?>"><?php echo str_repeat( $star, 5 ); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG. ?></p>
				</div>
				<figcaption class="cmvo__who">
					<?php if ( $person['photo'] ) : ?>
					<div class="cmvo__arch"><img src="<?php echo esc_url( $person['photo'] ); ?>" alt="<?php echo esc_attr( $person['name'] ); ?>" width="300" height="300" loading="lazy" decoding="async"></div>
					<?php endif; ?>
					<p class="cmvo__name"><?php echo esc_html( $person['name'] ); ?></p>
					<p class="cmvo__role"><?php echo esc_html( $person['role'] ); ?></p>
					<?php if ( $person['project'] ) : ?>
					<p class="cmvo__proj"><span><?php esc_html_e( 'Project', 'intella' ); ?></span><?php echo esc_html( $person['project'] ); ?></p>
					<?php endif; ?>
				</figcaption>
			</figure>
			<?php endforeach; ?>
			<div class="cmvo__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Choose a client', 'intella' ); ?>">
				<?php foreach ( $people as $i => $person ) : ?>
				<button class="cmvo__tab" type="button" role="tab" id="<?php echo esc_attr( str_replace( $prefix . '-', $prefix . '-t-', $person['key'] ) ); ?>" aria-controls="<?php echo esc_attr( $person['key'] ); ?>" aria-selected="<?php echo $i ? 'false' : 'true'; ?>">
					<?php if ( $person['photo'] ) : ?>
					<img src="<?php echo esc_url( $person['photo'] ); ?>" alt="" width="300" height="300" loading="lazy" decoding="async">
					<?php endif; ?>
					<span><b><?php echo esc_html( $person['name'] ); ?></b><?php echo esc_html( $person['short'] ); ?></span>
				</button>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
