<?php
/**
 * Comparison table: us (first column, highlighted) against the alternatives.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'compare-title' );

if ( ! function_exists( 'intella_compare_cell' ) ) {
	/**
	 * A table cell: "yes" / "no" (optionally "yes:Label") become a tick or a dash; anything else is text.
	 *
	 * @param string $cell Cell value.
	 */
	function intella_compare_cell( string $cell ): string {
		if ( preg_match( '/^(yes|no)(?::(.+))?$/i', trim( $cell ), $m ) ) {
			$yes   = 'yes' === strtolower( $m[1] );
			$label = ! empty( $m[2] ) ? $m[2] : ( $yes ? __( 'Yes', 'intella' ) : __( 'No', 'intella' ) );
			return $yes
				? '<span class="compare__yes" aria-label="' . esc_attr( $label ) . '">' . intella_icon( 'check' ) . '</span>'
				: '<span class="compare__no" aria-label="' . esc_attr( $label ) . '">–</span>';
		}
		return esc_html( $cell );
	}
}
$columns  = intella_lines( $attributes['columns'] );
?>
<section<?php echo intella_section_attrs( $attributes, '', $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="container">
		<?php echo intella_section_head( $attributes, $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<div class="compare">
			<div class="compare__scroll">
				<table class="compare__table">
					<thead><tr><th scope="col"><span class="visually-hidden"><?php echo esc_html( $attributes['corner'] ); ?></span></th><?php foreach ( $columns as $i => $column ) : ?><th scope="col"<?php echo 0 === $i ? ' class="compare__us"' : ''; ?>><?php echo esc_html( $column ); ?></th><?php endforeach; ?></tr></thead>
					<tbody><?php foreach ( intella_items( $attributes['rows'] ) as $row ) : ?><tr><th scope="row"><?php echo esc_html( $row['label'] ?? '' ); ?></th><?php foreach ( intella_lines( $row['cells'] ?? array() ) as $i => $cell ) : ?><td<?php echo 0 === $i ? ' class="compare__us"' : ''; ?>><?php echo intella_compare_cell( $cell ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the helper. ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody>
				</table>
			</div>
		</div>
		<?php if ( $attributes['note'] ) : ?>
		<p class="compare__note"><?php echo esc_html( $attributes['note'] ); ?></p>
		<?php endif; ?>
	</div>
</section>
