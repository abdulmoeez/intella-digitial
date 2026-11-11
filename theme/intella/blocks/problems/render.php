<?php
/**
 * Problems and fixes: "the problem → how we fix it" rows, each side optionally with a small table of figures.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'h-problems' );
$items    = intella_items( $attributes['items'] );

/**
 * A small table of figures under a problem or a fix.
 *
 * @param string $title Table heading.
 * @param array  $rows  Rows (label, value, tone).
 * @param string $extra Extra class.
 */
$table = static function ( string $title, array $rows, string $extra = '' ): string {
	if ( ! $rows ) {
		return '';
	}
	$html = '<div class="pmpf__ev' . esc_attr( $extra ) . '"><p class="pmpf__evt">' . esc_html( $title ) . '</p><ul role="list">';
	foreach ( $rows as $row ) {
		$tone  = in_array( $row['tone'] ?? '', array( 'bad', 'warn', 'ok' ), true ) ? $row['tone'] : 'ok';
		$html .= '<li><span>' . esc_html( $row['label'] ?? '' ) . '</span><b class="pmpf__v pmpf__v--' . $tone . '">' . esc_html( $row['value'] ?? '' ) . '</b></li>';
	}
	return $html . '</ul></div>';
};
?>
<section<?php echo intella_section_attrs( $attributes, 'pmprobs' . ( $attributes['alt'] ? ' pmprobs--alt' : '' ), $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="container">
		<?php echo intella_section_head( $attributes, $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php echo intella_visual( $attributes['visual'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- admin-only HTML field. ?>
		<div class="pmpfs">
			<p class="pmpfs__cols" aria-hidden="true"><span><?php echo esc_html( $attributes['problemLabel'] ); ?></span><span></span><span><?php echo esc_html( $attributes['fixLabel'] ); ?></span></p>
			<ul class="pmpfs__list" role="list">
				<?php foreach ( $items as $item ) : ?>
				<li class="pmpf">
					<div class="pmpf__p"><?php if ( ! empty( $item['title'] ) ) : ?><h3><?php echo esc_html( $item['title'] ); ?></h3><?php endif; ?><p><?php echo esc_html( $item['problem'] ?? '' ); ?></p><?php echo $table( (string) ( $item['beforeTitle'] ?? '' ), intella_items( $item['before'] ?? array() ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
					<span class="pmpf__arrow" aria-hidden="true"><?php echo intella_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<div class="pmpf__f"><span class="pmpf__fl" aria-hidden="true"><?php echo esc_html( $attributes['fixLabel'] ); ?></span><p><?php echo esc_html( $item['fix'] ?? '' ); ?></p><?php echo $table( (string) ( $item['afterTitle'] ?? '' ), intella_items( $item['after'] ?? array() ), ' pmpf__ev--after' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
				</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</section>
