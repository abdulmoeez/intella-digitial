<?php
/**
 * Cards in a row: numbered steps (optionally with a time label) or ideas with icons.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'cards-title' );
$numbered = ! empty( $attributes['numbered'] );
$list     = $numbered ? 'ol' : 'ul';
$classes  = 'tilt-grid' . ( '3' === (string) $attributes['columns'] ? ' tilt-grid--3' : '' );
?>
<section<?php echo intella_section_attrs( $attributes, $numbered ? 'process' : '', $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="container">
		<?php echo intella_section_head( $attributes, $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<<?php echo esc_attr( $list ); ?> class="<?php echo esc_attr( $classes ); ?>" role="list">
			<?php foreach ( intella_items( $attributes['items'] ) as $i => $item ) : ?>
			<li class="tilt-card">
				<?php if ( ! empty( $item['time'] ) ) : ?><span class="step-time"><?php echo esc_html( $item['time'] ); ?></span><?php endif; ?>
				<?php if ( $numbered ) : ?><span class="tilt-card__num" aria-hidden="true"><?php echo esc_html( intella_num( $i + 1 ) ); ?></span><?php endif; ?>
				<?php if ( ! empty( $item['icon'] ) ) : ?><span class="icon-tile"><?php echo intella_icon( $item['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><?php endif; ?>
				<h3 class="tilt-card__title"><?php echo esc_html( $item['title'] ?? '' ); ?></h3>
				<p class="tilt-card__text"><?php echo esc_html( $item['text'] ?? '' ); ?></p>
			</li>
			<?php endforeach; ?>
		</<?php echo esc_attr( $list ); ?>>
	</div>
</section>
