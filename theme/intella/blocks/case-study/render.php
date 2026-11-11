<?php
/**
 * Case study: a dark card with the client, the steps we took, headline numbers and a bar chart.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'h-case' );
$look     = trim( preg_replace( '/[^a-z0-9_ -]/i', '', (string) $attributes['look'] ) );
?>
<section<?php echo intella_section_attrs( $attributes, '', $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="container">
		<article class="<?php echo esc_attr( trim( 'bxcase ' . $look ) ); ?>">
			<div class="bxcase__main">
				<p class="bxcase__kicker"><?php echo esc_html( $attributes['kicker'] ); ?></p>
				<h2 class="bxcase__title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo intella_accent( $attributes['title'], 'accent--light' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<?php if ( $attributes['client'] ) : ?><p class="bxcase__client"><b><?php echo esc_html( $attributes['client'] ); ?></b> <?php echo esc_html( $attributes['clientInfo'] ); ?></p><?php endif; ?>
				<ol class="bxcase__steps" role="list"><?php foreach ( intella_items( $attributes['steps'] ) as $step ) : ?><li><h3><?php echo esc_html( $step['title'] ?? '' ); ?></h3><p><?php echo esc_html( $step['text'] ?? '' ); ?></p></li><?php endforeach; ?></ol>
				<?php echo intella_button( array( 'text' => $attributes['buttonText'], 'url' => $attributes['buttonUrl'] ), 'button button--light' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
			<div class="bxcase__side">
				<ul class="bxcase__stats" role="list"><?php foreach ( intella_items( $attributes['stats'] ) as $stat ) : ?><li><b><?php echo esc_html( $stat['value'] ?? '' ); ?></b><span><?php echo esc_html( $stat['label'] ?? '' ); ?></span></li><?php endforeach; ?></ul>
				<?php if ( intella_items( $attributes['bars'] ) ) : ?>
				<figure class="bxcase__chart">
					<figcaption><?php echo esc_html( $attributes['chartTitle'] ); ?></figcaption>
					<ol class="bxcase__bars" role="list"><?php foreach ( intella_items( $attributes['bars'] ) as $bar ) : ?><li style="--h:<?php echo esc_attr( (string) min( 100, max( 0, (float) ( $bar['height'] ?? 0 ) ) ) ); ?>%"><span class="visually-hidden"><?php echo esc_html( $bar['label'] ?? '' ); ?></span></li><?php endforeach; ?></ol>
					<p class="bxcase__axis"><span><?php echo esc_html( $attributes['axisStart'] ); ?></span><span><?php echo esc_html( $attributes['axisEnd'] ); ?></span></p>
				</figure>
				<?php endif; ?>
			</div>
		</article>
	</div>
</section>
