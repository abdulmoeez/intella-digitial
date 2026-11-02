<?php
/**
 * The panel's URLs, served before the theme loads, so the panel looks the same whatever theme is active:
 *   /admin/                  Dashboard
 *   /admin/admins/           Admins
 *   /admin/boards/<slug>/    A board
 *
 * WordPress normally sends /admin to /wp-admin/, but only when the URL would otherwise be a 404, so these
 * rules take precedence. Visitors who aren't logged in are sent to the WordPress login and back.
 *
 * @package Intella_Admin
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'intella_admin_rewrite_rules' );
add_filter( 'query_vars', 'intella_admin_query_vars' );
add_action( 'template_redirect', 'intella_admin_route', 0 );

/**
 * Add the /admin/ rewrite rules. The catch-all comes last.
 */
function intella_admin_rewrite_rules(): void {
	add_rewrite_rule( '^admin/?$', 'index.php?intella_admin=dashboard', 'top' );
	add_rewrite_rule( '^admin/admins/?$', 'index.php?intella_admin=admins', 'top' );
	add_rewrite_rule( '^admin/boards/([^/]+)/?$', 'index.php?intella_admin=board&intella_board=$matches[1]', 'top' );
	add_rewrite_rule( '^admin/.+$', 'index.php?intella_admin=not-found', 'top' );
}

/**
 * Register the query vars the rules use.
 *
 * @param string[] $vars Query vars.
 * @return string[]
 */
function intella_admin_query_vars( array $vars ): array {
	$vars[] = 'intella_admin';
	$vars[] = 'intella_board';
	return $vars;
}

/**
 * A panel URL.
 *
 * @param string $path Path below /admin/, e.g. "admins/".
 */
function intella_admin_url( string $path = '' ): string {
	return home_url( '/admin/' . ltrim( $path, '/' ) );
}

/**
 * A board's URL.
 *
 * @param object $board Board row.
 */
function intella_admin_board_url( object $board ): string {
	return intella_admin_url( 'boards/' . $board->slug . '/' );
}

/**
 * A file in assets/, with a cache-busting version.
 *
 * @param string $path Path below assets/.
 */
function intella_admin_asset( string $path ): string {
	$file = INTELLA_ADMIN_DIR . '/assets/' . $path;
	return INTELLA_ADMIN_URL . '/assets/' . $path . '?v=' . ( file_exists( $file ) ? filemtime( $file ) : INTELLA_ADMIN_VERSION );
}

/**
 * Serve a panel page and stop, or do nothing for every other URL.
 */
function intella_admin_route(): void {
	$view = (string) get_query_var( 'intella_admin' );
	if ( '' === $view ) {
		return;
	}

	nocache_headers();
	header( 'X-Robots-Tag: noindex, nofollow' );

	if ( ! is_user_logged_in() ) {
		$path = (string) wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH );
		wp_safe_redirect( wp_login_url( home_url( $path ) ) );
		exit;
	}

	$args = array( 'view' => $view );
	if ( ! intella_admin_can_access() ) {
		status_header( 403 );
		$args['view'] = 'no-access';
	} else {
		intella_admin_handle_post( $view );
		if ( 'board' === $view ) {
			$args['board'] = intella_admin_get_board( sanitize_title( (string) get_query_var( 'intella_board' ) ) );
			if ( ! $args['board'] ) {
				$args['view'] = 'not-found';
			}
		}
		status_header( 'not-found' === $args['view'] ? 404 : 200 );
	}

	intella_admin_render( $args );
	exit;
}

/**
 * Print a page: the layout around one view.
 *
 * @param array $args View name and its data.
 */
function intella_admin_render( array $args ): void {
	$args['boards'] = intella_admin_can_access() ? intella_admin_get_boards() : array();
	$args['notice'] = intella_admin_take_notice();
	require INTELLA_ADMIN_DIR . '/views/layout.php';
}

/**
 * Include a view file with its data.
 *
 * @param string $name View name (file in views/).
 * @param array  $args Data for the view.
 */
function intella_admin_view( string $name, array $args = array() ): void {
	$file = INTELLA_ADMIN_DIR . '/views/' . sanitize_key( $name ) . '.php';
	if ( file_exists( $file ) ) {
		require $file;
	}
}
