<?php
/**
 * Custom post types.
 * case-study keeps the same name and URL (/case-studies/<slug>/) as version 1, so existing content keeps working.
 *
 * @package Intella_Core
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'intella_register_post_types' );

/**
 * Register the case-study post type.
 */
function intella_register_post_types(): void {
	register_post_type(
		'case-study',
		array(
			'labels'       => array(
				'name'          => __( 'Case Studies', 'intella' ),
				'singular_name' => __( 'Case Study', 'intella' ),
				'add_new_item'  => __( 'Add New Case Study', 'intella' ),
				'edit_item'     => __( 'Edit Case Study', 'intella' ),
				'view_item'     => __( 'View Case Study', 'intella' ),
				'search_items'  => __( 'Search Case Studies', 'intella' ),
				'not_found'     => __( 'No case studies found', 'intella' ),
				'menu_name'     => __( 'Case Studies', 'intella' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'show_in_rest' => true, // Needed for the block editor.
			'menu_icon'    => 'dashicons-portfolio',
			'rewrite'      => array(
				'slug'       => 'case-studies',
				'with_front' => false,
			),
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ),
		)
	);
}
