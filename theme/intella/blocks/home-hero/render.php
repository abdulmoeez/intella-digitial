<?php
/**
 * Homepage hero: H1, intro and buttons, beside a stack of service cards that deals itself (deck.js),
 * with the tools for the front card fanned out beside it.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'hero-title' );
$cards    = intella_items( $attributes['cards'] );

/**
 * Curve offsets for the fan of tool logos (3, 4 or 5 logos; matches the design).
 *
 * @param int $count Logos in the set.
 */
$arcs = static function ( int $count ): array {
	$table = array(
		3 => array( -0.8, 0.0, -0.8 ),
		4 => array( -0.99, -0.12, -0.12, -0.99 ),
		5 => array( -1.11, -0.31, 0.0, -0.31, -1.11 ),
	);
	if ( isset( $table[ $count ] ) ) {
		return $table[ $count ];
	}
	$mid = ( $count - 1 ) / 2;
	return array_map( static fn( $i ) => round( -0.28 * ( $i - $mid ) ** 2, 2 ), range( 0, max( 0, $count - 1 ) ) );
};
?>
<section class="hero" aria-labelledby="<?php echo esc_attr( $title_id ); ?>">
	<div class="container hero__inner">
		<div class="hero__content">
			<h1 class="hero__title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo intella_accent( $attributes['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>
			<p class="hero__lead"><?php echo esc_html( $attributes['lead'] ); ?></p>
			<div class="hero__actions">
				<?php foreach ( intella_items( $attributes['buttons'] ) as $button ) : ?>
					<?php $primary = 'outline' !== ( $button['style'] ?? 'primary' ); ?>
					<?php echo intella_button( $button, 'button button--' . ( $primary ? 'primary' : 'outline' ) . ' button--lg', $primary ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="hero__visual">
			<ul class="deck" role="list" aria-label="<?php esc_attr_e( 'What we do', 'intella' ); ?>" data-deck>
				<?php foreach ( $cards as $i => $card ) : ?>
				<li class="deck__card" data-deck-card>
					<div class="deck__head">
						<span class="icon-tile"><?php echo intella_icon( $card['icon'] ?? 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						<h2 class="deck__title"><a href="<?php echo esc_url( intella_url( $card['url'] ?? '' ) ); ?>"><?php echo esc_html( $card['title'] ?? '' ); ?></a></h2>
						<span class="deck__num" aria-hidden="true"><?php echo esc_html( intella_num( $i + 1 ) ); ?></span>
					</div>
					<p class="deck__text"><?php echo esc_html( $card['text'] ?? '' ); ?></p>
					<ul class="deck__tags" role="list"><?php foreach ( intella_lines( $card['tags'] ?? array() ) as $tag ) : ?><li><?php echo esc_html( $tag ); ?></li><?php endforeach; ?></ul>
				</li>
				<?php endforeach; ?>
			</ul>
			<div class="deck-tools" aria-hidden="true" data-deck-tools>
				<?php foreach ( $cards as $i => $card ) : ?>
					<?php
					$tools = intella_lines( $card['tools'] ?? array() );
					$arc   = $arcs( count( $tools ) );
					?>
					<ul class="deck-tools__set" role="list" data-tools-set="<?php echo (int) $i; ?>"><?php foreach ( $tools as $j => $tool ) : ?><li class="deck-tool" style="<?php echo esc_attr( sprintf( '--i:%d;--arc:%srem', $j, number_format( $arc[ $j ], 2 - ( 0.0 === fmod( $arc[ $j ] * 10, 1 ) ? 1 : 0 ), '.', '' ) ) ); ?>"><span class="deck-tool__logo"><?php echo intella_tool_logo( $tool ); // phpcs:ignore WordPress.Security.EscapeOutput -- theme SVG file. ?></span><span class="deck-tool__name"><?php echo esc_html( $tool ); ?></span></li><?php endforeach; ?></ul>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
