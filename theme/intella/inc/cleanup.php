<?php
/**
 * Remove WordPress output we don't use.
 *
 * @package Intella
 */

defined( 'ABSPATH' ) || exit;

// Head clutter.
remove_action( 'wp_head', 'wp_generator' );                 // WordPress version number.
remove_action( 'wp_head', 'rsd_link' );                     // Really Simple Discovery.
remove_action( 'wp_head', 'wp_shortlink_wp_head' );         // ?p=123 shortlink.
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

// Emoji detection script and styles (browsers render emoji natively).
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
