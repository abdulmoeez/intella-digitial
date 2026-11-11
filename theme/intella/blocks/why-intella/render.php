<?php
/**
 * Why Intella: reasons on the left; key facts and/or a client quote (from Testimonials) on the right.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'h-why' );
$facts    = intella_items( $attributes['facts'] );
$people   = $attributes['quote'] && function_exists( 'intella_shared_items' ) ? intella_shared_items( 'testimonial', array_slice( (array) $attributes['quote'], 0, 1 ) ) : array();
$person   = $people[0] ?? null;
$star     = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 17.3 18.2 21l-1.6-7L22 9.2l-7.2-.6L12 2 9.2 8.6 2 9.2 7.4 14l-1.6 7z"/></svg>';

ob_start();
if ( $person ) :
	$photo = $person['image'] ? wp_get_attachment_image_src( $person['image'], 'medium' ) : false;
	?>
	<figure class="wpwhy__quote">
		<p class="wpwhy__stars" role="img" aria-label="<?php esc_attr_e( 'Rated 5 out of 5', 'intella' ); ?>"><?php echo str_repeat( $star, 5 ); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG. ?></p>
		<blockquote><p><?php echo esc_html( '“' . trim( $person['text'], '“”" ' ) . '”' ); ?></p></blockquote>
		<figcaption><?php if ( $photo ) : ?><img src="<?php echo esc_url( $photo[0] ); ?>" alt="" width="300" height="300" loading="lazy"><?php endif; ?><span><b><?php echo esc_html( $person['name'] ); ?></b><?php echo esc_html( $person['role'] ); ?></span></figcaption>
	</figure>
	<?php
endif;
$quote = ob_get_clean();
?>
<section<?php echo intella_section_attrs( $attributes, '', $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="container">
		<div class="wpwhy">
			<div>
				<h2 class="section-title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo intella_accent( $attributes['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<ul class="wpwhy__list" role="list"><?php foreach ( intella_items( $attributes['items'] ) as $item ) : ?><li><h3><?php echo esc_html( $item['title'] ?? '' ); ?></h3><p><?php echo esc_html( $item['text'] ?? '' ); ?></p></li><?php endforeach; ?></ul>
			</div>
			<?php if ( $facts ) : ?>
			<div class="cmwhy__side">
				<ul class="cmfacts<?php echo $attributes['bigFacts'] ? ' cmfacts--big' : ''; ?>" role="list"><?php foreach ( $facts as $fact ) : ?><li><b><?php echo esc_html( $fact['value'] ?? '' ); ?></b><span><?php echo esc_html( $fact['label'] ?? '' ); ?></span></li><?php endforeach; ?></ul>
				<?php echo $quote; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above. ?>
			</div>
			<?php else : ?>
			<?php echo $quote; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above. ?>
			<?php endif; ?>
		</div>
	</div>
</section>
