<?php
/**
 * Signs you need this, as numbered strips that open one at a time on scroll (scroll-accordions.js), each with
 * paragraphs and an optional example picture, then a closing note with a button.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'h-signs' );
$items    = intella_items( $attributes['items'] );
?>
<section<?php echo intella_section_attrs( $attributes, 'pmsigns', $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="container">
		<?php echo intella_section_head( $attributes, $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<ol class="pmsts" role="list" data-acc>
			<?php foreach ( $items as $i => $item ) : ?>
			<?php
			$panel = intella_uid( 'pmst' );
			$paras = '';
			foreach ( array_filter( array_map( 'trim', preg_split( '/\R\s*\R/', (string) ( $item['text'] ?? '' ) ) ) ) as $para ) {
				$paras .= '<p>' . esc_html( $para ) . '</p>';
			}
			?>
			<li class="pmst js-acc<?php echo 0 === $i ? ' is-open' : ''; ?>">
				<span class="pmst__n" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
				<div class="pmst__c">
					<h3><button class="pmst__h js-acc-btn" type="button" aria-expanded="<?php echo 0 === $i ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr( $panel ); ?>"><?php echo esc_html( $item['title'] ?? '' ); ?><span class="pmst__x" aria-hidden="true"></span></button></h3>
					<div class="pmst__b" id="<?php echo esc_attr( $panel ); ?>"><div><?php if ( ! empty( $item['picture'] ) ) : ?><div class="pmst__in"><div class="pmst__txt"><?php echo $paras; // phpcs:ignore WordPress.Security.EscapeOutput ?></div><div class="pmst__pic" aria-hidden="true"><?php echo intella_visual( $item['picture'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- admin-only HTML field. ?></div></div><?php else : ?><?php echo $paras; // phpcs:ignore WordPress.Security.EscapeOutput ?><?php endif; ?></div></div>
				</div>
			</li>
			<?php endforeach; ?>
		</ol>
		<?php if ( $attributes['noteTitle'] || $attributes['buttonText'] ) : ?>
		<div class="pmsg__aside">
			<div><p class="pmsg__at"><?php echo esc_html( $attributes['noteTitle'] ); ?></p><p><?php echo esc_html( $attributes['noteText'] ); ?></p></div>
			<?php echo intella_button( array( 'text' => $attributes['buttonText'], 'url' => $attributes['buttonUrl'] ), 'button button--light' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
		<?php endif; ?>
	</div>
</section>
