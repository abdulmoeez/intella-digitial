<?php
/**
 * Story and mission: heading beside a few paragraphs, then a mission statement band.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id   = intella_uid( 'story-title' );
$paragraphs = array_filter( array_map( 'trim', preg_split( '/\R\s*\R/', (string) $attributes['body'] ) ) );
?>
<section<?php echo intella_section_attrs( $attributes, '', $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="container">
		<div class="story">
			<div>
				<h2 class="section-title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo intella_accent( $attributes['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			</div>
			<div class="story__body">
				<?php foreach ( $paragraphs as $paragraph ) : ?>
				<p><?php echo intella_inline( $paragraph ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endforeach; ?>
			</div>
		</div>
		<?php if ( $attributes['mission'] ) : ?>
		<div class="mission">
			<p class="mission__text"><?php echo intella_accent( $attributes['mission'], 'accent--light' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
		</div>
		<?php endif; ?>
	</div>
</section>
