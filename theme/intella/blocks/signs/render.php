<?php
/**
 * Signs you need this: a ticked checklist beside a dark card with a button, or a photo card with a speech bubble.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'h-signs' );
$photo    = $attributes['photo'] ? wp_get_attachment_image_src( (int) $attributes['photo'], 'full' ) : false;
$check    = intella_icon( 'check' );
$button   = intella_button( array( 'text' => $attributes['buttonText'], 'url' => $attributes['buttonUrl'] ), 'button button--light' );
?>
<section<?php echo intella_section_attrs( $attributes, '', $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="container">
		<div class="signs<?php echo $photo ? ' signs--human' : ''; ?>">
			<div>
				<div class="section-head">
					<h2 class="section-title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo intella_accent( $attributes['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
					<?php if ( $attributes['lead'] ) : ?><p class="section-lead"><?php echo esc_html( $attributes['lead'] ); ?></p><?php endif; ?>
				</div>
				<ul class="signs__list" role="list">
					<?php foreach ( intella_lines( $attributes['items'] ) as $item ) : ?>
					<li class="signs__item"><span class="signs__check"><?php echo $check; // phpcs:ignore WordPress.Security.EscapeOutput ?></span><span><?php echo esc_html( $item ); ?></span></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php if ( $photo ) : ?>
			<aside class="signs__aside signs__aside--photo">
				<div class="signs__photo<?php echo $attributes['shortPhoto'] ? ' signs__photo--short' : ''; ?>">
					<img src="<?php echo esc_url( $photo[0] ); ?>" alt="<?php echo esc_attr( $attributes['photoAlt'] ); ?>" width="<?php echo esc_attr( $photo[1] ); ?>" height="<?php echo esc_attr( $photo[2] ); ?>" loading="lazy" decoding="async">
					<?php if ( $attributes['bubble'] ) : ?><p class="signs__bubble"><?php echo esc_html( $attributes['bubble'] ); ?></p><?php endif; ?>
				</div>
				<div class="signs__aside-body">
					<p class="signs__aside-title"><?php echo esc_html( $attributes['asideTitle'] ); ?></p>
					<p><?php echo esc_html( $attributes['asideText'] ); ?></p>
					<?php echo $button; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>
			</aside>
			<?php else : ?>
			<aside class="signs__aside">
				<span class="icon-tile icon-tile--dark"><?php echo intella_icon( $attributes['icon'] ?: 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				<p class="signs__aside-title"><?php echo esc_html( $attributes['asideTitle'] ); ?></p>
				<p><?php echo esc_html( $attributes['asideText'] ); ?></p>
				<?php echo $button; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</aside>
			<?php endif; ?>
		</div>
	</div>
</section>
