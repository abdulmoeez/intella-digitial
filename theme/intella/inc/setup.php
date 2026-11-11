<?php
/**
 * Theme supports, menus and image sizes.
 *
 * @package Intella
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', 'intella_setup' );

/**
 * Register theme features.
 */
function intella_setup(): void {
	load_theme_textdomain( 'intella', INTELLA_DIR . '/languages' );

	add_theme_support( 'title-tag' ); // WordPress (or the SEO plugin) writes <title>.
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support(
		'html5',
		array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);

	// Use the front-end tokens and base styles inside the editor so content looks the same there.
	add_theme_support( 'editor-styles' );
	add_editor_style( array( 'assets/css/tokens.css', 'assets/css/base.css' ) );

	// We build our own sections; don't offer WordPress's sample patterns.
	remove_theme_support( 'core-block-patterns' );

	register_nav_menus(
		array(
			'primary'          => __( 'Primary navigation (with the Services mega menu)', 'intella' ),
			'footer_company'   => __( 'Footer: Company', 'intella' ),
			'footer_resources' => __( 'Footer: Resources', 'intella' ),
			'legal'            => __( 'Legal links', 'intella' ),
		)
	);

	// Image sizes are added here once the redesign defines them, e.g.:
	// add_image_size( 'intella-card', 800, 500, true );
}
