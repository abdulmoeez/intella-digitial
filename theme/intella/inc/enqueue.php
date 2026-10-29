<?php
/**
 * Front-end CSS and JS.
 *
 * CSS: global files load in a fixed order (tokens → base → layout → components → utilities).
 *      Block CSS is declared in each block.json and only loads where the block is used.
 * JS:  ES modules, no jQuery, no build step. Every file in assets/js/modules/ is registered
 *      as "@intella/<file-name>" so main.js (and blocks) can import it by that name.
 *
 * @package Intella
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'intella_enqueue_styles' );
add_action( 'wp_enqueue_scripts', 'intella_enqueue_scripts' );

/**
 * Global stylesheets, in cascade order.
 */
function intella_enqueue_styles(): void {
	$files = array( 'assets/css/tokens.css', 'assets/css/base.css', 'assets/css/layout.css' );

	$components = glob( INTELLA_DIR . '/assets/css/components/*.css' );
	sort( $components );
	foreach ( $components as $component ) {
		$files[] = 'assets/css/components/' . basename( $component );
	}

	$files[] = 'assets/css/utilities.css';

	$previous = array();
	foreach ( $files as $file ) {
		$handle = 'intella-' . str_replace( array( 'assets/css/', 'components/', '.css' ), array( '', 'c-', '' ), $file );
		wp_enqueue_style( $handle, INTELLA_URI . '/' . $file, $previous, intella_asset_version( $file ) );
		$previous = array( $handle );
	}
}

/**
 * Register feature modules and load the main entry module.
 */
function intella_enqueue_scripts(): void {
	$module_ids = array();

	foreach ( glob( INTELLA_DIR . '/assets/js/modules/*.js' ) as $module ) {
		$id   = '@intella/' . basename( $module, '.js' );
		$path = 'assets/js/modules/' . basename( $module );
		wp_register_script_module( $id, INTELLA_URI . '/' . $path, array(), intella_asset_version( $path ) );
		$module_ids[] = $id;
	}

	wp_enqueue_script_module(
		'@intella/main',
		INTELLA_URI . '/assets/js/main.js',
		$module_ids,
		intella_asset_version( 'assets/js/main.js' )
	);
}
