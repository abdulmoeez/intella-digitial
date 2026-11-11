<?php
/**
 * FAQ: heading, intro and button on the left; questions on the right (first one open).
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$items    = array_filter( intella_items( $attributes['items'] ), static fn( $item ) => ! empty( $item['question'] ) );
$title_id = intella_uid( 'faq-title' );

if ( $attributes['schema'] && function_exists( 'intella_schema_faq' ) ) {
	intella_schema_faq( $items );
}
?>
<section<?php echo intella_section_attrs( $attributes, '', $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the helper. ?>>
	<div class="container faq">
		<div>
			<h2 class="section-title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo intella_accent( $attributes['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- filtered. ?></h2>
			<?php if ( $attributes['lead'] ) : ?>
				<p class="section-lead"><?php echo esc_html( $attributes['lead'] ); ?></p>
			<?php endif; ?>
			<?php if ( $attributes['buttonText'] ) : ?>
				<div class="faq__actions">
					<?php $primary = 'primary' === $attributes['buttonStyle']; ?>
					<?php echo intella_button( array( 'text' => $attributes['buttonText'], 'url' => $attributes['buttonUrl'] ), 'button button--' . ( $primary ? 'primary' : 'outline' ) . ' button--lg', $primary ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>
			<?php endif; ?>
		</div>
		<div class="faq__list">
			<?php foreach ( array_values( $items ) as $i => $item ) : ?>
				<details class="faq-item"<?php echo 0 === $i ? ' open' : ''; ?>>
					<summary><?php echo esc_html( $item['question'] ); ?> <span class="faq-item__icon" aria-hidden="true"></span></summary>
					<p class="faq-item__answer"><?php echo intella_inline( $item['answer'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput -- filtered. ?></p>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>
