<?php
/**
 * Registers the theme's custom blocks.
 *
 * Blocks are native WordPress blocks. Source lives in src/blocks/<name>/ and is compiled by
 * `npm run build` into build/blocks/<name>/, which is what WordPress loads.
 *
 * @package Intella
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'intella_register_blocks' );
add_filter( 'block_categories_all', 'intella_block_category' );

/**
 * Register each compiled block that has a block.json.
 */
function intella_register_blocks(): void {
	foreach ( glob( INTELLA_DIR . '/build/blocks/*/block.json' ) as $block_json ) {
		register_block_type( dirname( $block_json ) );
	}
}

/**
 * Put our blocks in their own "Intella sections" group at the top of the inserter.
 *
 * @param array $categories Existing block categories.
 */
function intella_block_category( array $categories ): array {
	array_unshift(
		$categories,
		array(
			'slug'  => 'intella',
			'title' => __( 'Intella sections', 'intella' ),
		)
	);
	return $categories;
}
