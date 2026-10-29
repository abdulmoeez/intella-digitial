<?php
/**
 * Site footer: footer and legal menus, copyright.
 *
 * @package Intella
 */

defined( 'ABSPATH' ) || exit;
?>
<footer class="site-footer">
	<div class="site-footer__inner container">
		<?php
		wp_nav_menu(
			array(
				'theme_location'       => 'footer',
				'container'            => 'nav',
				'container_aria_label' => __( 'Footer', 'intella' ),
				'menu_class'           => 'site-footer__menu',
				'fallback_cb'          => false,
				'depth'                => 1,
			)
		);
		?>
		<p class="site-footer__copyright">
			&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>
		</p>
		<?php
		wp_nav_menu(
			array(
				'theme_location'       => 'legal',
				'container'            => 'nav',
				'container_aria_label' => __( 'Legal', 'intella' ),
				'menu_class'           => 'site-footer__legal',
				'fallback_cb'          => false,
				'depth'                => 1,
			)
		);
		?>
	</div>
</footer>
