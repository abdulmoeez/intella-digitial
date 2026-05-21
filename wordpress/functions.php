<?php
/**
 * Intella Digital theme functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'INTELLA_THEME_VERSION', '1.0.0' );

/**
 * Enqueue theme styles and scripts.
 */
function intella_enqueue_assets() {
	$uri = get_template_directory_uri();

	wp_enqueue_style(
		'intella-styles',
		$uri . '/assets/styles.css',
		array(),
		INTELLA_THEME_VERSION
	);

	wp_enqueue_style(
		'intella-styles-responsive',
		$uri . '/assets/styles.res.css',
		array( 'intella-styles' ),
		INTELLA_THEME_VERSION
	);

	wp_enqueue_style(
		'intella-google-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@200;300;400;500;600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_script(
		'intella-func',
		$uri . '/assets/func.js',
		array(),
		INTELLA_THEME_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'intella_enqueue_assets' );

/**
 * Theme setup.
 */
function intella_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );
}
add_action( 'after_setup_theme', 'intella_theme_setup' );
