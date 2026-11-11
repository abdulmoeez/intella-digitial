<?php
/**
 * Top bar and site header with the main navigation and the Services mega menu.
 * Content: Settings → Intella site (top bar, buttons) and Appearance → Menus (Primary navigation).
 *
 * @package Intella
 */

defined( 'ABSPATH' ) || exit;

$menu          = intella_menu_tree( 'primary' );
$topbar_text   = intella_setting( 'topbar_text' );
$contact_text  = intella_setting( 'header_contact_text', __( 'Contact us', 'intella' ) );
$contact_url   = intella_url( intella_setting( 'header_contact_url', '/contact/' ) );
$cta           = array(
	'text' => intella_setting( 'header_cta_text', __( 'Free Consultation', 'intella' ) ),
	'url'  => intella_setting( 'header_cta_url', '/contact/' ),
);
?>
<?php if ( $topbar_text ) : ?>
<div class="topbar">
	<div class="container topbar__inner">
		<span class="topbar__text"><?php echo esc_html( $topbar_text ); ?></span>
		<?php if ( intella_setting( 'topbar_link_text' ) ) : ?>
			<a class="topbar__link" href="<?php echo esc_url( intella_url( intella_setting( 'topbar_link_url' ) ) ); ?>"><?php echo esc_html( intella_setting( 'topbar_link_text' ) ); ?> <?php echo intella_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts. ?></a>
		<?php endif; ?>
		<button class="topbar__close" type="button" aria-label="<?php esc_attr_e( 'Close this message', 'intella' ); ?>" data-topbar-close data-topbar-key="<?php echo esc_attr( intella_topbar_key() ); ?>"><?php echo intella_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
	</div>
</div>
<?php endif; ?>

<header class="site-header" data-site-header>
	<div class="container site-header__inner">
		<a class="site-header__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<img src="<?php echo esc_url( INTELLA_URI . '/assets/images/logo.png' ); ?>" width="395" height="183" alt="<?php echo esc_attr( get_bloginfo( 'name' ) . ' ' . __( 'home', 'intella' ) ); ?>">
		</a>

		<nav class="primary-nav" id="primary-nav" aria-label="<?php esc_attr_e( 'Main', 'intella' ); ?>" data-nav>
			<ul class="primary-nav__list" role="list">
				<?php foreach ( $menu as $item ) : ?>
					<?php if ( $item['children'] ) : ?>
						<li class="primary-nav__item primary-nav__item--mega">
							<button class="primary-nav__link" type="button" aria-expanded="false" aria-controls="submenu-<?php echo (int) $item['id']; ?>" data-submenu-toggle>
								<?php echo esc_html( $item['title'] ); ?> <?php echo intella_icon( 'chevron-down', array( 'width' => 14, 'height' => 14 ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</button>
							<?php get_template_part( 'template-parts/mega-menu', null, array( 'item' => $item ) ); ?>
						</li>
					<?php else : ?>
						<li class="primary-nav__item"><a class="primary-nav__link" href="<?php echo esc_url( $item['url'] ); ?>"<?php echo $item['current'] ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $item['title'] ); ?></a></li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
			<div class="primary-nav__mobile-cta">
				<a class="button button--outline" href="<?php echo esc_url( $contact_url ); ?>"><?php echo esc_html( $contact_text ); ?></a>
				<?php echo intella_button( $cta, 'button button--primary', false ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		</nav>

		<div class="site-header__actions">
			<a class="site-header__contact" href="<?php echo esc_url( $contact_url ); ?>"><?php echo esc_html( $contact_text ); ?></a>
			<?php echo intella_button( $cta, 'button button--primary', false ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-nav" data-nav-toggle>
			<span class="visually-hidden"><?php esc_html_e( 'Menu', 'intella' ); ?></span>
			<?php
			echo intella_icon( 'menu', array( 'class' => 'nav-toggle__open', 'width' => 20, 'height' => 20 ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			echo intella_icon( 'close', array( 'class' => 'nav-toggle__close', 'width' => 20, 'height' => 20 ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			?>
		</button>
	</div>
</header>
