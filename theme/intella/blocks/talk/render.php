<?php
/**
 * "Let's talk" call to action: text, buttons and contact details in a coloured panel, with cut-out people
 * breaking out of it (or a photo collage with two notes).
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'cta-title' );
$look     = sanitize_html_class( $attributes['look'] );
$dark     = in_array( $look, array( 'violet', 'plum', 'mark' ), true );
$classes  = 'talk' . ( $look ? " talk--{$look}" : '' ) . ( $dark ? ' talk--on-dark' : '' ) . ( $attributes['left'] ? ' talk--left' : '' );
$shots    = array_filter( intella_items( $attributes['shots'] ), static fn( $s ) => ! empty( $s['image'] ) );
$phone    = intella_setting( 'phone' );
$email    = intella_setting( 'email' );
?>
<section<?php echo intella_section_attrs( $attributes, $classes, $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="container">
		<div class="talk__panel">
			<div class="talk__text">
				<h2 class="talk__title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo intella_accent( $attributes['title'], $dark ? 'accent--light' : '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<p class="talk__lead"><?php echo esc_html( $attributes['lead'] ); ?></p>
				<div class="talk__actions">
					<?php foreach ( intella_items( $attributes['buttons'] ) as $i => $button ) : ?>
						<?php
						$primary = 'outline' !== ( $button['style'] ?? 'primary' );
						$style   = $primary ? ( $dark ? 'light' : 'primary' ) : ( $dark ? 'ghost' : 'outline' );
						echo intella_button( $button, "button button--{$style} button--lg", $primary ); // phpcs:ignore WordPress.Security.EscapeOutput
						?>
					<?php endforeach; ?>
				</div>
				<?php if ( $attributes['contact'] && ( $phone || $email ) ) : ?>
				<p class="talk__contact"><?php if ( $phone ) : ?><a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo intella_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( $phone ); ?></a><?php endif; ?><?php if ( $email ) : ?><a href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo intella_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( $email ); ?></a><?php endif; ?></p>
				<?php endif; ?>
			</div>
			<?php if ( $shots ) : ?>
			<div class="talk__collage" aria-hidden="true">
				<?php foreach ( array_values( $shots ) as $i => $shot ) : ?>
					<?php echo intella_image_full( (int) $shot['image'], array( 'class' => 'talk__shot talk__shot--' . ( 0 === $i ? 'a' : 'b' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php endforeach; ?>
				<?php if ( $attributes['noteReply'] ) : ?><span class="talk__note talk__note--reply"><i></i><?php echo esc_html( $attributes['noteReply'] ); ?></span><?php endif; ?>
				<?php if ( $attributes['noteSkills'] ) : ?><span class="talk__note talk__note--skills"><b><?php echo esc_html( $attributes['noteSkillsTitle'] ); ?></b><?php echo esc_html( $attributes['noteSkills'] ); ?></span><?php endif; ?>
			</div>
			<?php else : ?>
				<?php if ( 'mark' === $look ) : ?><span class="talk__mark" aria-hidden="true">intella</span><?php endif; ?>
				<?php foreach ( intella_items( $attributes['people'] ) as $person ) : ?>
					<?php echo intella_image_full( (int) ( $person['image'] ?? 0 ), array( 'class' => trim( 'talk__person ' . ( ! empty( $person['size'] ) ? 'talk__person--' . sanitize_html_class( $person['size'] ) : '' ) ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
</section>
