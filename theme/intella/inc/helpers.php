<?php
/**
 * Small shared helpers.
 *
 * @package Intella
 */

defined( 'ABSPATH' ) || exit;

/**
 * Version string for a theme asset, used to bust browser caches.
 * Locally it is the file's modified time, so edits show up on refresh; elsewhere it is the theme version.
 *
 * @param string $relative_path Path inside the theme, e.g. 'assets/css/base.css'.
 */
function intella_asset_version( string $relative_path ): string {
	$file = INTELLA_DIR . '/' . ltrim( $relative_path, '/' );

	if ( 'local' === wp_get_environment_type() && file_exists( $file ) ) {
		return (string) filemtime( $file );
	}

	return INTELLA_VERSION;
}
