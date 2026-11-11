<?php
/**
 * Link list: a heading and links with a short description each.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'links-title' );
?>
<section<?php echo intella_section_attrs( $attributes, '', $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="container nf__links">
		<h2 class="nf__links-title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo esc_html( $attributes['title'] ); ?></h2>
		<ul class="svc-links" role="list">
			<?php foreach ( intella_items( $attributes['links'] ) as $link ) : ?>
			<li><a class="svc-link" href="<?php echo esc_url( intella_url( $link['url'] ?? '' ) ); ?>"><span class="svc-link__text"><span class="svc-link__title"><?php echo esc_html( $link['title'] ?? '' ); ?></span><span class="svc-link__desc"><?php echo esc_html( $link['description'] ?? '' ); ?></span></span><span class="svc-link__arrow"><?php echo intella_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
