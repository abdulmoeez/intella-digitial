<?php
/**
 * Packages: engagement options side by side; a featured one gets a badge and a dark card.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'ways-title' );
$check    = intella_icon( 'check' );
?>
<section<?php echo intella_section_attrs( $attributes, '', $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="container">
		<?php echo intella_section_head( $attributes, $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<ul class="packages" role="list">
			<?php foreach ( intella_items( $attributes['items'] ) as $item ) : ?>
				<?php $featured = ! empty( $item['featured'] ); ?>
			<li class="package<?php echo $featured ? ' package--featured' : ''; ?>"><?php if ( $featured && ! empty( $item['badge'] ) ) : ?><span class="package__badge"><?php echo esc_html( $item['badge'] ); ?></span><?php endif; ?><h3 class="package__name"><?php echo esc_html( $item['name'] ?? '' ); ?></h3><p class="package__for"><?php echo esc_html( $item['for'] ?? '' ); ?></p><p class="package__text"><?php echo esc_html( $item['text'] ?? '' ); ?></p><ul class="tick-list tick-list--plain" role="list"><?php foreach ( intella_lines( $item['ticks'] ?? array() ) as $tick ) : ?><li><?php echo $check; // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( $tick ); ?></li><?php endforeach; ?></ul><?php echo intella_button( array( 'text' => $item['buttonText'] ?? '', 'url' => $item['buttonUrl'] ?? '' ), 'button button--' . ( $featured ? 'light' : 'outline' ) . ' button--block' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
			<?php endforeach; ?>
		</ul>
		<?php if ( $attributes['note'] ) : ?>
		<p class="packages__note"><?php echo esc_html( $attributes['note'] ); ?></p>
		<?php endif; ?>
	</div>
</section>
