<?php
/**
 * The theme's section blocks (blocks/<name>/).
 *
 * Each block folder holds:
 *   block.json   Name, title and the editor fields under "intella": { "fields": [...] }.
 *   render.php   The section's HTML. Design lives here, so a design change is a code change (git pull),
 *                and saved pages only store the content (attributes), never markup.
 *   style.css    The section's CSS. WordPress loads it only on pages that use the block.
 *
 * Attributes are built from the field list, so fields are declared once. The editor form for every block
 * comes from one shared script (assets/js/editor/blocks.js) reading the same list: no build step.
 *
 * Field types: text, inline (allows <em> accent words), textarea, url, image, toggle, select, number,
 * code (raw HTML/SVG visuals), list (one item per line), repeater (fields: [...]), posts (postType: ...).
 *
 * @package Intella
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'block_type_metadata', 'intella_block_attributes_from_fields' );
add_action( 'init', 'intella_register_blocks' );
add_filter( 'block_categories_all', 'intella_block_category' );
add_action( 'enqueue_block_editor_assets', 'intella_block_editor_assets' );
add_action( 'enqueue_block_assets', 'intella_block_editor_canvas_styles' );
add_filter( 'should_load_separate_core_block_assets', '__return_true' );
add_action( 'wp_enqueue_scripts', 'intella_enqueue_page_block_styles' );

/**
 * Load the CSS of only the blocks on this page, in <head>.
 * WordPress would otherwise load every block's CSS everywhere (classic themes) or, with separate loading on,
 * print it at the bottom of the page after the blocks render, which makes sections flash unstyled.
 */
function intella_enqueue_page_block_styles(): void {
	if ( ! is_singular() ) {
		return;
	}
	$registry = WP_Block_Type_Registry::get_instance();
	$walk     = static function ( array $blocks ) use ( &$walk, $registry ): void {
		foreach ( $blocks as $block ) {
			$type = $block['blockName'] ? $registry->get_registered( $block['blockName'] ) : null;
			foreach ( $type ? $type->style_handles : array() as $handle ) {
				wp_enqueue_style( $handle );
			}
			// Custom sections name their stylesheet in assets/css/custom/.
			$css = 'intella/custom-section' === $block['blockName'] ? sanitize_file_name( (string) ( $block['attrs']['css'] ?? '' ) ) : '';
			if ( $css && file_exists( INTELLA_DIR . "/assets/css/custom/{$css}.css" ) ) {
				$path = "assets/css/custom/{$css}.css";
				wp_enqueue_style( "intella-custom-{$css}", INTELLA_URI . '/' . $path, array( 'intella-utilities' ), intella_asset_version( $path ) );
			}
			$walk( $block['innerBlocks'] );
		}
	};
	$walk( parse_blocks( (string) get_post_field( 'post_content', get_queried_object_id() ) ) );
}

/**
 * Register each block folder that has a block.json.
 */
function intella_register_blocks(): void {
	foreach ( glob( INTELLA_DIR . '/blocks/*/block.json' ) as $block_json ) {
		register_block_type( dirname( $block_json ) );
	}
}

/**
 * WordPress attribute types for each field type.
 *
 * @param array $field Field definition.
 */
function intella_field_attribute( array $field ): array {
	$types = array(
		'repeater' => 'array',
		'posts'    => 'array',
		'list'     => 'array',
		'image'    => 'number',
		'number'   => 'number',
		'toggle'   => 'boolean',
	);
	$attribute = array( 'type' => $types[ $field['type'] ] ?? 'string' );
	if ( array_key_exists( 'default', $field ) ) {
		$attribute['default'] = $field['default'];
	}
	return $attribute;
}

/**
 * A block's fields: its own, plus the shared "Section" settings when block.json has "intella": { "section": "cream" }
 * (the value is the default background).
 *
 * @param array $intella The "intella" part of block.json.
 */
function intella_block_fields( array $intella ): array {
	$fields = $intella['fields'] ?? array();
	if ( ! empty( $intella['section'] ) ) {
		$fields[] = array(
			'key'     => 'background',
			'type'    => 'select',
			'label'   => __( 'Background', 'intella' ),
			'options' => array(
				array( 'label' => __( 'Cream', 'intella' ), 'value' => 'cream' ),
				array( 'label' => __( 'White', 'intella' ), 'value' => 'white' ),
			),
			'default' => $intella['section'],
			'panel'   => __( 'Section', 'intella' ),
		);
		$fields[] = array(
			'key'   => 'anchor',
			'type'  => 'text',
			'label' => __( 'Anchor (for #links)', 'intella' ),
			'help'  => __( 'Optional, e.g. free-review, so links to /#free-review jump here.', 'intella' ),
			'panel' => __( 'Section', 'intella' ),
		);
	}
	return $fields;
}

/**
 * Opening attributes for a block's <section>: class="section section--{background} …", id and aria-labelledby.
 *
 * @param array  $attributes Block attributes.
 * @param string $extra      Extra classes.
 * @param string $labelledby ID of the section's heading.
 */
function intella_section_attrs( array $attributes, string $extra = '', string $labelledby = '' ): string {
	$classes = trim( 'section section--' . sanitize_html_class( $attributes['background'] ?? 'cream' ) . ' ' . $extra );
	$html    = ' class="' . esc_attr( $classes ) . '"';
	if ( ! empty( $attributes['anchor'] ) ) {
		$html .= ' id="' . esc_attr( sanitize_title( $attributes['anchor'] ) ) . '"';
	}
	if ( $labelledby ) {
		$html .= ' aria-labelledby="' . esc_attr( $labelledby ) . '"';
	}
	return $html;
}

/**
 * Turn "intella.fields" in a block.json into block attributes.
 *
 * @param array $metadata Parsed block.json.
 */
function intella_block_attributes_from_fields( array $metadata ): array {
	if ( empty( $metadata['intella'] ) ) {
		return $metadata;
	}
	$metadata['attributes'] = $metadata['attributes'] ?? array();
	foreach ( intella_block_fields( $metadata['intella'] ) as $field ) {
		if ( ! empty( $field['key'] ) && ! isset( $metadata['attributes'][ $field['key'] ] ) ) {
			$metadata['attributes'][ $field['key'] ] = intella_field_attribute( $field );
		}
	}
	return $metadata;
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

/**
 * The shared editor script plus each block's fields.
 */
function intella_block_editor_assets(): void {
	$path = 'assets/js/editor/blocks.js';
	wp_enqueue_script(
		'intella-block-editor',
		INTELLA_URI . '/' . $path,
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-data', 'wp-core-data', 'wp-i18n' ),
		intella_asset_version( $path ),
		true
	);
	wp_enqueue_style( 'intella-block-editor', INTELLA_URI . '/assets/css/editor.css', array(), intella_asset_version( 'assets/css/editor.css' ) );

	$blocks = array();
	foreach ( WP_Block_Type_Registry::get_instance()->get_all_registered() as $name => $type ) {
		if ( str_starts_with( $name, 'intella/' ) ) {
			$json            = json_decode( (string) file_get_contents( INTELLA_DIR . '/blocks/' . substr( $name, 8 ) . '/block.json' ), true );
			$blocks[ $name ] = array(
				'title'       => $type->title,
				'description' => $type->description,
				'icon'        => $type->icon,
				'category'    => $type->category,
				'attributes'  => $type->attributes,
				'supports'    => $type->supports,
				'fields'      => intella_block_fields( $json['intella'] ?? array() ),
				'help'        => $json['intella']['help'] ?? '',
			);
		}
	}
	wp_add_inline_script(
		'intella-block-editor',
		'window.intellaBlocks = ' . wp_json_encode( $blocks ) . '; window.intellaSprite = ' . wp_json_encode( file_get_contents( INTELLA_DIR . '/assets/images/icons.svg' ) ) . ';',
		'before'
	);
}

/**
 * Inside the editor's page preview, load the same global CSS as the front end, so blocks look identical.
 * (Each block's own style.css is added by WordPress automatically.)
 */
function intella_block_editor_canvas_styles(): void {
	if ( ! is_admin() ) {
		return;
	}
	foreach ( array( 'fonts', 'tokens', 'base', 'layout', 'components', 'utilities' ) as $name ) {
		$path = "assets/css/{$name}.css";
		wp_enqueue_style( "intella-editor-{$name}", INTELLA_URI . '/' . $path, array(), intella_asset_version( $path ) );
	}
}

/**
 * Attribute helpers for render.php files.
 */

/**
 * A repeater's items, always an array of arrays.
 *
 * @param mixed $value Attribute value.
 */
function intella_items( $value ): array {
	return is_array( $value ) ? array_values( array_filter( $value, 'is_array' ) ) : array();
}

/**
 * A "list" field's lines, always an array of strings.
 *
 * @param mixed $value Attribute value.
 */
function intella_lines( $value ): array {
	if ( is_string( $value ) ) {
		$value = preg_split( '/\R/', $value );
	}
	return is_array( $value ) ? array_values( array_filter( array_map( 'strval', $value ), 'strlen' ) ) : array();
}

/**
 * A unique ID for elements a block needs to link up (tabs, panels, headings), stable within one page view.
 *
 * @param string $prefix Prefix.
 */
function intella_uid( string $prefix ): string {
	static $counts = array();
	$counts[ $prefix ] = ( $counts[ $prefix ] ?? 0 ) + 1;
	return $prefix . '-' . $counts[ $prefix ];
}
