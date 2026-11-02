<?php
/**
 * Page frame: header with navigation, the notice from the last form post, then the view.
 *
 * @package Intella_Admin
 * @var array $args View name, boards, notice and the view's own data.
 */

defined( 'ABSPATH' ) || exit;

$intella_view   = $args['view'];
$intella_user   = wp_get_current_user();
$intella_titles = array(
	'dashboard' => __( 'Dashboard', 'intella' ),
	'admins'    => __( 'Admins', 'intella' ),
	'board'     => isset( $args['board'] ) ? $args['board']->name : '',
	'no-access' => __( 'No access', 'intella' ),
	'not-found' => __( 'Page not found', 'intella' ),
);
$intella_title  = $intella_titles[ $intella_view ] ?? '';
$intella_board  = $args['board'] ?? null;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<title><?php echo esc_html( $intella_title . ' · ' . __( 'Intella Admin', 'intella' ) ); ?></title>
	<link rel="stylesheet" href="<?php echo esc_url( intella_admin_asset( 'css/admin.css' ) ); ?>">
	<?php if ( 'board' === $intella_view ) : ?>
		<script type="module" src="<?php echo esc_url( intella_admin_asset( 'js/board.js' ) ); ?>"></script>
	<?php endif; ?>
</head>
<body class="panel">
<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'intella' ); ?></a>

<header class="topbar">
	<a class="topbar__brand" href="<?php echo esc_url( intella_admin_url() ); ?>">
		<span class="topbar__logo" aria-hidden="true">I</span>
		<?php esc_html_e( 'Intella Admin', 'intella' ); ?>
	</a>
	<?php if ( intella_admin_can_access() ) : ?>
		<nav class="topbar__nav" aria-label="<?php esc_attr_e( 'Panel', 'intella' ); ?>">
			<a href="<?php echo esc_url( intella_admin_url() ); ?>" <?php echo 'dashboard' === $intella_view ? 'aria-current="page"' : ''; ?>><?php esc_html_e( 'Dashboard', 'intella' ); ?></a>
			<?php foreach ( $args['boards'] as $intella_b ) : ?>
				<a href="<?php echo esc_url( intella_admin_board_url( $intella_b ) ); ?>" <?php echo ( $intella_board && (int) $intella_board->id === (int) $intella_b->id ) ? 'aria-current="page"' : ''; ?>><?php echo esc_html( $intella_b->name ); ?></a>
			<?php endforeach; ?>
			<a href="<?php echo esc_url( intella_admin_url( 'admins/' ) ); ?>" <?php echo 'admins' === $intella_view ? 'aria-current="page"' : ''; ?>><?php esc_html_e( 'Admins', 'intella' ); ?></a>
		</nav>
	<?php endif; ?>
	<div class="topbar__user">
		<span class="topbar__name"><?php echo esc_html( intella_admin_board_name( $intella_user ) ); ?></span>
		<?php if ( current_user_can( 'manage_options' ) ) : ?>
			<a href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'WordPress', 'intella' ); ?></a>
		<?php endif; ?>
		<a href="<?php echo esc_url( wp_logout_url( intella_admin_url() ) ); ?>"><?php esc_html_e( 'Log out', 'intella' ); ?></a>
	</div>
</header>

<main id="main" class="page<?php echo 'board' === $intella_view ? ' page--wide' : ''; ?>">
	<?php if ( $args['notice'] ) : ?>
		<p class="notice notice--<?php echo esc_attr( $args['notice'][0] ); ?>" role="status"><?php echo esc_html( $args['notice'][1] ); ?></p>
	<?php endif; ?>
	<?php intella_admin_view( $intella_view, $args ); ?>
</main>
</body>
</html>
