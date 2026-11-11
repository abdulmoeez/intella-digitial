<?php
/**
 * Site footer: closing call to action, brand and contact details, Company and Resources links,
 * every service grouped like the Services menu, and the legal links.
 * Content: Settings → Intella site, and Appearance → Menus (Footer: Company, Footer: Resources, Legal links,
 * and the groups inside Primary navigation → Services).
 *
 * @package Intella
 */

defined( 'ABSPATH' ) || exit;

$arrow    = intella_icon( 'arrow-right' );
$services = null;
foreach ( intella_menu_tree( 'primary' ) as $top ) {
	if ( $top['children'] ) {
		$services = $top;
		break;
	}
}
$phone   = intella_setting( 'phone' );
$email   = intella_setting( 'email' );
$address = intella_setting( 'address' );
$social  = array_filter(
	array(
		'linkedin'  => array( 'LinkedIn', intella_setting( 'linkedin' ) ),
		'facebook'  => array( 'Facebook', intella_setting( 'facebook' ) ),
		'instagram' => array( 'Instagram', intella_setting( 'instagram' ) ),
	),
	static fn( $network ) => '' !== $network[1]
);

/**
 * A footer link list from a menu location.
 *
 * @param string $location Menu location.
 * @param string $class    Wrapper class.
 * @param string $id       Heading ID.
 */
$link_list = static function ( string $location, string $class, string $id ): void {
	$items = intella_menu_tree( $location );
	if ( ! $items ) {
		return;
	}
	$locations = get_nav_menu_locations();
	$menu      = wp_get_nav_menu_object( $locations[ $location ] );
	?>
	<nav class="<?php echo esc_attr( $class ); ?>" aria-labelledby="<?php echo esc_attr( $id ); ?>">
		<h2 class="site-footer__heading" id="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $menu ? $menu->name : '' ); ?></h2>
		<ul class="site-footer__links" role="list"><?php foreach ( $items as $item ) : ?><li><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a></li><?php endforeach; ?></ul>
	</nav>
	<?php
};
?>
<footer class="site-footer">
	<div class="container">
		<?php if ( intella_setting( 'footer_cta_title' ) ) : ?>
		<div class="site-footer__cta">
			<div>
				<p class="site-footer__cta-title"><?php echo intella_accent( intella_setting( 'footer_cta_title' ), 'accent--light' ); // phpcs:ignore WordPress.Security.EscapeOutput -- filtered by intella_inline(). ?></p>
				<p class="site-footer__cta-text"><?php echo esc_html( intella_setting( 'footer_cta_text' ) ); ?></p>
			</div>
			<div class="site-footer__cta-actions">
				<?php
				echo intella_button( array( 'text' => intella_setting( 'footer_cta_1_text' ), 'url' => intella_setting( 'footer_cta_1_url' ) ), 'button button--light button--lg' ); // phpcs:ignore WordPress.Security.EscapeOutput
				echo intella_button( array( 'text' => intella_setting( 'footer_cta_2_text' ), 'url' => intella_setting( 'footer_cta_2_url' ) ), 'button button--ghost-light button--lg', false ); // phpcs:ignore WordPress.Security.EscapeOutput
				?>
			</div>
		</div>
		<?php endif; ?>

		<div class="site-footer__main">
			<div class="site-footer__brand">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( INTELLA_URI . '/assets/images/logo-white.png' ); ?>" width="395" height="183" alt="<?php echo esc_attr( get_bloginfo( 'name' ) . ' ' . __( 'home', 'intella' ) ); ?>" loading="lazy"></a>
				<p class="site-footer__about"><?php echo esc_html( intella_setting( 'footer_about' ) ); ?></p>
				<ul class="site-footer__contact" role="list">
					<?php if ( $email ) : ?>
						<li><?php echo intella_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><a href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo esc_html( $email ); ?></a></li>
					<?php endif; ?>
					<?php if ( $phone ) : ?>
						<li><?php echo intella_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></li>
					<?php endif; ?>
					<?php if ( $address ) : ?>
						<li><?php echo intella_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $address ); ?></span></li>
					<?php endif; ?>
				</ul>
				<?php if ( $social ) : ?>
					<ul class="site-footer__social" role="list"><?php foreach ( $social as $key => $network ) : ?><li><a class="site-footer__social-link" href="<?php echo esc_url( $network[1] ); ?>" aria-label="<?php /* translators: %s: social network */ echo esc_attr( sprintf( __( 'Intella Digital on %s', 'intella' ), $network[0] ) ); ?>"><?php echo intella_logo_svg( 'social-' . $key ); // phpcs:ignore WordPress.Security.EscapeOutput -- theme SVG file. ?></a></li><?php endforeach; ?></ul>
				<?php endif; ?>
			</div>
			<?php
			$link_list( 'footer_company', 'site-footer__company', 'footer-company' );
			$link_list( 'footer_resources', 'site-footer__resources', 'footer-resources' );
			?>
		</div>

		<?php if ( $services ) : ?>
		<nav class="site-footer__services" aria-labelledby="footer-services">
			<div class="site-footer__services-head">
				<h2 class="site-footer__heading" id="footer-services"><button class="site-footer__toggle" type="button" aria-expanded="true" aria-controls="footer-groups" data-footer-toggle><?php echo esc_html( $services['title'] ); ?><?php echo intella_icon( 'chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button></h2>
				<a class="site-footer__all" href="<?php echo esc_url( $services['url'] ); ?>"><?php esc_html_e( 'See all services', 'intella' ); ?> <?php echo $arrow; // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			</div>
			<div class="site-footer__groups" id="footer-groups">
				<?php foreach ( $services['children'] as $group ) : ?>
					<details class="footer-group" open data-footer-group>
						<summary class="footer-group__title"><?php echo esc_html( $group['title'] ); ?><?php echo intella_icon( 'chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></summary>
						<ul class="footer-group__links" role="list"><?php foreach ( $group['children'] as $link ) : ?><li><a href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['title'] ); ?></a></li><?php endforeach; ?></ul>
					</details>
				<?php endforeach; ?>
			</div>
		</nav>
		<?php endif; ?>

		<div class="site-footer__bottom">
			<p>&copy; <span data-year><?php echo esc_html( wp_date( 'Y' ) ); ?></span> <?php echo esc_html( get_bloginfo( 'name' ) ); ?>. <?php esc_html_e( 'All rights reserved.', 'intella' ); ?></p>
			<?php $legal = intella_menu_tree( 'legal' ); ?>
			<?php if ( $legal ) : ?>
				<ul class="site-footer__legal" role="list">
					<?php foreach ( $legal as $item ) : ?>
						<li><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<a class="site-footer__top" href="#main"><?php esc_html_e( 'Back to top', 'intella' ); ?> <?php echo $arrow; // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		</div>
	</div>
</footer>
