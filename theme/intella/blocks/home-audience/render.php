<?php
/**
 * "Ready to grow, but stretched thin?": intro, a mosaic of four video tiles that each cycle through clips
 * (reels.js), problem → fix steps, and the strip of tools we use.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'audiences-title' );
$letters  = array( 'a', 'b', 'c', 'd' );
$chips    = intella_items( $attributes['chips'] );
?>
<section<?php echo intella_section_attrs( $attributes, 'audiences', $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="container">
		<div class="aud">
			<div class="aud__text">
				<h2 class="section-title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo intella_accent( $attributes['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<p class="section-lead"><?php echo esc_html( $attributes['lead'] ); ?></p>
				<?php echo intella_button( array( 'text' => $attributes['buttonText'], 'url' => $attributes['buttonUrl'] ), 'button button--primary button--lg aud__cta' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
			<figure class="aud__media aud__mosaic">
				<?php foreach ( array_slice( intella_items( $attributes['tiles'] ), 0, 4 ) as $i => $tile ) : ?>
					<?php
					$clips = array();
					foreach ( intella_items( $tile['clips'] ?? array() ) as $clip ) {
						$video = wp_get_attachment_url( (int) ( $clip['video'] ?? 0 ) );
						if ( $video ) {
							$clips[] = array(
								'mp4'    => $video,
								'poster' => (string) wp_get_attachment_url( (int) ( $clip['poster'] ?? 0 ) ),
							);
						}
					}
					if ( ! $clips ) {
						continue;
					}
					?>
					<div class="aud__tile aud__tile--<?php echo esc_attr( $letters[ $i ] ); ?> aud__reel" data-reel="<?php echo esc_attr( wp_json_encode( $clips ) ); ?>"><video class="aud__clip is-on" muted loop playsinline preload="none" poster="<?php echo esc_url( $clips[0]['poster'] ); ?>"><source src="<?php echo esc_url( $clips[0]['mp4'] ); ?>" type="video/mp4"></video></div>
				<?php endforeach; ?>
				<?php if ( isset( $chips[0] ) ) : ?>
					<div class="aud__chip aud__chip--a" aria-hidden="true"><span class="aud__dot"></span><b><?php echo esc_html( $chips[0]['title'] ?? '' ); ?></b><small><?php echo esc_html( $chips[0]['text'] ?? '' ); ?></small></div>
				<?php endif; ?>
				<?php if ( isset( $chips[1] ) ) : ?>
					<div class="aud__chip aud__chip--b" aria-hidden="true"><?php echo intella_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><b><?php echo esc_html( $chips[1]['title'] ?? '' ); ?></b><small><?php echo esc_html( $chips[1]['text'] ?? '' ); ?></small></div>
				<?php endif; ?>
			</figure>
		</div>
		<ol class="step-fixes" role="list">
			<?php foreach ( intella_items( $attributes['steps'] ) as $step ) : ?>
			<li class="step-fix">
				<div class="step-fix__head">
					<p class="fix__problem"><?php echo esc_html( $step['problem'] ?? '' ); ?></p>
				</div>
				<p class="step-fix__answer"><?php echo esc_html( $step['answer'] ?? '' ); ?></p>
			</li>
			<?php endforeach; ?>
		</ol>
		<?php if ( intella_lines( $attributes['tools'] ) ) : ?>
		<div class="tools">
			<h3 class="tools__title"><?php echo intella_accent( $attributes['toolsTitle'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h3>
			<p class="tools__lead"><?php echo esc_html( $attributes['toolsLead'] ); ?></p>
			<ul class="tools__list" role="list"><?php foreach ( intella_lines( $attributes['tools'] ) as $tool ) : ?><li class="tools__item" tabindex="0" data-name="<?php echo esc_attr( $tool ); ?>"><?php echo intella_tool_logo( $tool ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span class="visually-hidden"><?php echo esc_html( $tool ); ?></span></li><?php endforeach; ?></ul>
		</div>
		<?php endif; ?>
	</div>
</section>
