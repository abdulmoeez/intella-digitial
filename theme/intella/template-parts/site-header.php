<?php
/**
 * Site header: logo and primary navigation.
 *
 * @package Intella
 */

defined( 'ABSPATH' ) || exit;
?>
<header class="site-header">
	<div class="site-header__inner container">
		<a class="site-header__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php bloginfo( 'name' ); ?>
		</a>

		<nav class="site-nav" aria-label="<?php esc_attr_e( 'Primary', 'intella' ); ?>">
			<button class="site-nav__toggle" type="button" aria-expanded="false" aria-controls="site-nav-menu" data-nav-toggle>
				<?php esc_html_e( 'Menu', 'intella' ); ?>
			</button>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_id'        => 'site-nav-menu',
					'menu_class'     => 'site-nav__menu',
					'fallback_cb'    => false,
					'depth'          => 3,
				)
			);
			?>
		</nav>
	</div>
</header>
