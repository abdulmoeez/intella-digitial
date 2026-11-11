<?php
/**
 * Front-end CSS and JS.
 *
 * CSS: global files load on every page in a fixed order. Section CSS lives in each block
 *      (blocks/<name>/style.css) and only loads on pages that use the block.
 * JS:  ES modules, no jQuery, no build step. Every file in assets/js/modules/ is registered as
 *      "@intella/<file-name>". main.js imports the site-wide ones; blocks list the ones they need
 *      in block.json ("viewScriptModule"), so a page only downloads the code its sections use.
 *
 * @package Intella
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'intella_register_script_modules' );
add_action( 'wp_enqueue_scripts', 'intella_enqueue_styles' );
add_action( 'wp_enqueue_scripts', 'intella_enqueue_scripts' );
add_action( 'wp_head', 'intella_preload_fonts', 1 );

/**
 * Global stylesheets, in cascade order.
 */
function intella_enqueue_styles(): void {
	$files    = array( 'fonts', 'tokens', 'base', 'layout', 'mega-menu', 'components', 'utilities' );
	$previous = array();

	foreach ( $files as $name ) {
		$path   = "assets/css/{$name}.css";
		$handle = "intella-{$name}";
		wp_enqueue_style( $handle, INTELLA_URI . '/' . $path, $previous, intella_asset_version( $path ) );
		$previous = array( $handle );
	}
}

/**
 * Register every feature module so main.js and blocks can import it by ID.
 * Registered on init (not wp_enqueue_scripts) because block.json files refer to these IDs.
 */
function intella_register_script_modules(): void {
	foreach ( glob( INTELLA_DIR . '/assets/js/modules/*.js' ) as $module ) {
		$path = 'assets/js/modules/' . basename( $module );
		wp_register_script_module(
			'@intella/' . basename( $module, '.js' ),
			INTELLA_URI . '/' . $path,
			array(),
			intella_asset_version( $path )
		);
	}
}

/**
 * The site-wide entry module (header, menus, forms and other features used on every page).
 */
function intella_enqueue_scripts(): void {
	$main = INTELLA_DIR . '/assets/js/main.js';

	// The modules main.js imports become its dependencies, so WordPress adds them to the import map.
	preg_match_all( "#import\s+(?:[^';]*\sfrom\s+)?'(@intella/[a-z0-9-]+)'#", (string) file_get_contents( $main ), $imports );

	wp_enqueue_script_module(
		'@intella/main',
		INTELLA_URI . '/assets/js/main.js',
		array_values( array_unique( $imports[1] ) ),
		intella_asset_version( 'assets/js/main.js' )
	);
}

/**
 * Preload the two fonts used above the fold so headings and body text don't swap late.
 */
function intella_preload_fonts(): void {
	foreach ( array( 'schibsted-grotesk-variable', 'rubik-variable' ) as $font ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( INTELLA_URI . "/assets/fonts/{$font}.woff2" )
		);
	}
}
