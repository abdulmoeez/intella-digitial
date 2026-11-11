<?php
/**
 * Tools we use: a wall of platform logos (names show on hover and focus).
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'tools-title' );
?>
<section<?php echo intella_section_attrs( $attributes, '', $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="container">
		<div class="tools tools--page">
			<h2 class="tools__title section-title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo intella_accent( $attributes['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			<p class="tools__lead"><?php echo esc_html( $attributes['lead'] ); ?></p>
			<ul class="tools__list" role="list"><?php foreach ( intella_lines( $attributes['tools'] ) as $tool ) : ?><li class="tools__item" tabindex="0" data-name="<?php echo esc_attr( $tool ); ?>"><?php echo intella_tool_logo( $tool ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span class="visually-hidden"><?php echo esc_html( $tool ); ?></span></li><?php endforeach; ?></ul>
		</div>
	</div>
</section>
