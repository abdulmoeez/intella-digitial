<?php
/**
 * Small shared helpers.
 *
 * @package Intella
 */

defined( 'ABSPATH' ) || exit;

/**
 * Version string for a theme asset, used to bust browser caches.
 * It is the file's modified time, which changes whenever the file is edited or updated by `git pull`,
 * so visitors get the new CSS/JS straight after a deploy without anyone bumping a version number.
 *
 * @param string $relative_path Path inside the theme, e.g. 'assets/css/base.css'.
 */
function intella_asset_version( string $relative_path ): string {
	$file = INTELLA_DIR . '/' . ltrim( $relative_path, '/' );

	return file_exists( $file ) ? (string) filemtime( $file ) : INTELLA_VERSION;
}
