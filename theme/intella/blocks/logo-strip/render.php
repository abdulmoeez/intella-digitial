<?php
/**
 * Client logos scrolling in a loop. The list is printed twice so the loop is seamless (the copy is hidden
 * from screen readers). Logos come from Clients in the admin.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$clients = function_exists( 'intella_shared_items' ) ? intella_shared_items( 'client', (array) $attributes['clients'] ) : array();
$clients = array_filter( $clients, static fn( $client ) => $client['image'] );
if ( ! $clients ) {
	return;
}
?>
<?php if ( ! $attributes['marquee'] ) : ?>
<section class="logo-strip" aria-label="<?php esc_attr_e( 'Clients', 'intella' ); ?>">
	<div class="container logo-strip__inner">
		<p class="logo-strip__label"><?php echo esc_html( $attributes['label'] ); ?></p>
		<ul class="logo-strip__list" role="list">
			<?php foreach ( $clients as $client ) : ?>
				<li><img src="<?php echo esc_url( (string) wp_get_attachment_url( $client['image'] ) ); ?>" alt="<?php echo esc_attr( $client['name'] ); ?>" loading="lazy"></li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
<?php return; endif; ?>
<section class="logo-strip logo-strip--marquee" aria-label="<?php esc_attr_e( 'Clients', 'intella' ); ?>">
	<div class="container logo-strip__inner">
		<p class="logo-strip__label"><?php echo esc_html( $attributes['label'] ); ?></p>
		<div class="marquee" data-marquee>
			<div class="marquee__track">
				<?php foreach ( array( false, true ) as $copy ) : ?>
				<ul class="logo-strip__list marquee__list" role="list"<?php echo $copy ? ' aria-hidden="true"' : ''; ?>>
					<?php foreach ( $clients as $client ) : ?>
						<li><img src="<?php echo esc_url( (string) wp_get_attachment_url( $client['image'] ) ); ?>" alt="<?php echo esc_attr( $copy ? '' : $client['name'] ); ?>" loading="lazy"></li>
					<?php endforeach; ?>
				</ul>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
