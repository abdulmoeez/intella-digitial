<?php
/**
 * Load the content extracted from the prototype (data/*.json) into WordPress.
 * Run with WP-CLI:   bin/wp eval-file bin/import/import.php <part> [<part> ...]
 * Parts: settings, menus, shared, media, pages (or "all"). Safe to re-run: it updates what is already there.
 *
 * @package Intella
 */

defined( 'ABSPATH' ) || exit;

defined( 'INTELLA_IMPORT_DATA' ) || define( 'INTELLA_IMPORT_DATA', __DIR__ . '/data' );

/**
 * Read a data file.
 *
 * @param string $name File in data/.
 */
function intella_import_json( string $name ): array {
	$file = INTELLA_IMPORT_DATA . "/{$name}";
	if ( ! file_exists( $file ) ) {
		WP_CLI::error( "Missing {$file}. Run the matching extract_*.py first." );
	}
	return json_decode( (string) file_get_contents( $file ), true );
}

/**
 * Site settings → the intella_site option (keeps values already set that the data doesn't have).
 */
function intella_import_settings(): void {
	$current = get_option( 'intella_site', array() );
	update_option( 'intella_site', array_merge( is_array( $current ) ? $current : array(), intella_import_json( 'settings.json' ) ) );
	WP_CLI::success( 'Settings imported.' );
}

/**
 * Rebuild a menu from scratch and assign it to a location.
 *
 * @param string $name     Menu name (shown as the footer heading for footer menus).
 * @param string $location Theme location.
 * @param array  $items    Items: title, url, description, extra fields, children.
 */
function intella_import_menu( string $name, string $location, array $items ): void {
	$existing = wp_get_nav_menu_object( $name );
	if ( $existing ) {
		wp_delete_nav_menu( $existing->term_id );
	}
	$menu_id = wp_create_nav_menu( $name );

	$position = 0; // One running order across the whole menu, as WordPress expects.
	$add      = static function ( array $item, int $parent ) use ( &$add, &$position, $menu_id ): int {
		$id = wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'       => $item['title'],
				'menu-item-url'         => intella_import_url( $item['url'] ?? '' ),
				'menu-item-description' => $item['description'] ?? '',
				'menu-item-parent-id'   => $parent,
				'menu-item-position'    => ++$position,
				'menu-item-type'        => 'custom',
				'menu-item-status'      => 'publish',
			)
		);
		$map = array(
			'icon'       => $item['icon'] ?? '',
			'logo'       => $item['logo'] ?? '',
			'logo_style' => $item['style'] ?? '',
			'link_text'  => $item['link_text'] ?? '',
			'cta_title'  => $item['cta_title'] ?? '',
			'cta_text'   => $item['cta_text'] ?? '',
			'cta_button' => $item['cta_button'] ?? '',
			'cta_url'    => $item['cta_url'] ?? '',
		);
		foreach ( $map as $key => $value ) {
			if ( '' !== $value ) {
				update_post_meta( $id, "_intella_menu_{$key}", $value );
			}
		}
		foreach ( array_merge( $item['children'] ?? array(), $item['links'] ?? array() ) as $child ) {
			$add( $child, $id );
		}
		return $id;
	};

	foreach ( $items as $item ) {
		$add( $item, 0 );
	}

	$locations              = get_theme_mod( 'nav_menu_locations', array() );
	$locations[ $location ] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

/**
 * Site paths in the data ("/contact/") become full URLs for menu items.
 *
 * @param string $url Stored URL.
 */
function intella_import_url( string $url ): string {
	return ( '' === $url || preg_match( '#^[a-z]+:#i', $url ) ) ? $url : home_url( $url );
}

/**
 * All menus.
 */
function intella_import_menus(): void {
	$menus = intella_import_json( 'menus.json' );
	intella_import_menu( 'Primary navigation', 'primary', $menus['primary'] );
	intella_import_menu( $menus['footer_company']['title'], 'footer_company', $menus['footer_company']['items'] );
	intella_import_menu( $menus['footer_resources']['title'], 'footer_resources', $menus['footer_resources']['items'] );
	intella_import_menu( $menus['legal']['title'], 'legal', $menus['legal']['items'] );
	WP_CLI::success( 'Menus imported.' );
}

/**
 * A prototype file (path under Code/frontend) as a Media Library item. Imported once; later runs reuse it.
 *
 * @param string $relative E.g. 'assets/images/team/team.jpg'.
 */
function intella_import_media_file( string $relative ): int {
	$relative = ltrim( $relative, '/' );
	$found    = get_posts(
		array(
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'meta_key'    => '_intella_source', // phpcs:ignore WordPress.DB.SlowDBQuery -- import script.
			'meta_value'  => $relative, // phpcs:ignore WordPress.DB.SlowDBQuery
			'fields'      => 'ids',
			'numberposts' => 1,
		)
	);
	if ( $found ) {
		return (int) $found[0];
	}
	$source = dirname( __DIR__, 3 ) . '/frontend/' . $relative;
	if ( ! file_exists( $source ) ) {
		WP_CLI::warning( "Missing file {$source}" );
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$tmp = wp_tempnam( basename( $source ) );
	copy( $source, $tmp );
	$id = media_handle_sideload( array( 'name' => basename( $source ), 'tmp_name' => $tmp ), 0 );
	if ( is_wp_error( $id ) ) {
		WP_CLI::warning( "{$relative}: " . $id->get_error_message() );
		return 0;
	}
	update_post_meta( $id, '_intella_source', $relative );
	return (int) $id;
}

/**
 * A shared item (testimonial, client) by its name.
 *
 * @param string $type Post type.
 * @param string $name Title.
 */
function intella_import_find( string $type, string $name ): int {
	$found = get_posts(
		array(
			'post_type'   => $type,
			'post_status' => 'any',
			'title'       => $name,
			'fields'      => 'ids',
			'numberposts' => 1,
		)
	);
	return $found ? (int) $found[0] : 0;
}

/**
 * Replace references in block data: "media:<path>" → attachment ID, "post:<type>:<name>" → post ID.
 *
 * @param mixed $value Attribute value.
 * @return mixed
 */
function intella_import_resolve( $value ) {
	if ( is_array( $value ) ) {
		return array_map( 'intella_import_resolve', $value );
	}
	if ( is_string( $value ) && str_starts_with( $value, 'media:' ) ) {
		return intella_import_media_file( substr( $value, 6 ) );
	}
	if ( is_string( $value ) && str_contains( $value, '{{media:' ) ) {
		return preg_replace_callback(
			'/\{\{media:([^}]+)\}\}/',
			static fn( array $m ): string => (string) wp_get_attachment_url( intella_import_media_file( $m[1] ) ),
			$value
		);
	}
	if ( is_string( $value ) && str_starts_with( $value, 'post:' ) ) {
		list( , $type, $name ) = explode( ':', $value, 3 );
		return intella_import_find( $type, $name );
	}
	return $value;
}

/**
 * Testimonials and clients from data/shared.json (made by extract_pages.py).
 */
function intella_import_shared(): void {
	$shared = intella_import_json( 'shared.json' );
	foreach ( $shared['testimonial'] ?? array() as $order => $item ) {
		$id = intella_import_find( 'testimonial', $item['name'] );
		$id = wp_insert_post(
			array(
				'ID'           => $id,
				'post_type'    => 'testimonial',
				'post_status'  => 'publish',
				'post_title'   => $item['name'],
				'post_content' => trim( $item['quote'], '“”"' ),
				'menu_order'   => $order,
			)
		);
		update_post_meta( $id, '_intella_role', $item['role'] );
		update_post_meta( $id, '_intella_company', trim( (string) strrchr( ',' . $item['role'], ',' ), ', ' ) );
		update_post_meta( $id, '_intella_short_quote', trim( $item['short_quote'] ?? '', '“”"' ) );
		update_post_meta( $id, '_intella_project', $item['project'] ?? '' );
		update_post_meta( $id, '_intella_placeholder', (bool) $item['placeholder'] );
		if ( ! empty( $item['photo'] ) ) {
			set_post_thumbnail( $id, intella_import_media_file( $item['photo'] ) );
		}
	}
	foreach ( $shared['client'] ?? array() as $order => $item ) {
		$id = wp_insert_post(
			array(
				'ID'          => intella_import_find( 'client', $item['name'] ),
				'post_type'   => 'client',
				'post_status' => 'publish',
				'post_title'  => $item['name'],
				'menu_order'  => $order,
			)
		);
		update_post_meta( $id, '_intella_placeholder', (bool) $item['placeholder'] );
		set_post_thumbnail( $id, intella_import_media_file( $item['logo'] ) );
	}
	WP_CLI::success( sprintf( '%d testimonials and %d clients imported.', count( $shared['testimonial'] ?? array() ), count( $shared['client'] ?? array() ) ) );
}

/**
 * Find a page by its path, creating it (and any missing parent pages) if needed.
 *
 * @param string $path  Path like /services/digital-marketing/seo/ ("/" = the front page).
 * @param string $title Title for a new page.
 */
function intella_import_page_for_path( string $path, string $title ): int {
	if ( '/' === $path ) {
		$front = (int) get_option( 'page_on_front' );
		if ( $front ) {
			return $front;
		}
	}
	$slugs  = array_values( array_filter( explode( '/', trim( $path, '/' ) ) ) );
	$parent = 0;
	foreach ( $slugs as $i => $slug ) {
		$existing = get_page_by_path( implode( '/', array_slice( $slugs, 0, $i + 1 ) ) );
		if ( $existing ) {
			$parent = $existing->ID;
			continue;
		}
		$is_last = count( $slugs ) - 1 === $i;
		$parent  = wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => $is_last ? $title : ucwords( str_replace( '-', ' ', $slug ) ),
				'post_name'   => $slug,
				'post_parent' => $parent,
			)
		);
	}
	return (int) $parent;
}

/**
 * Pages from data/pages/*.json (made by extract_pages.py). Content is rebuilt from the data each time.
 */
function intella_import_pages(): void {
	$files = glob( INTELLA_IMPORT_DATA . '/pages/*.json' );
	foreach ( $files as $file ) {
		$page   = json_decode( (string) file_get_contents( $file ), true );
		$id     = intella_import_page_for_path( $page['path'], $page['title'] );
		$blocks = array_map(
			static fn( array $block ): string => serialize_block(
				array(
					'blockName'    => $block['name'],
					'attrs'        => intella_import_resolve( $block['attrs'] ),
					'innerBlocks'  => array(),
					'innerHTML'    => '',
					'innerContent' => array(),
				)
			),
			$page['blocks']
		);
		wp_update_post(
			array(
				'ID'           => $id,
				'post_title'   => $page['title'],
				'post_content' => wp_slash( implode( "\n\n", $blocks ) ),
				'post_status'  => $page['status'] ?? 'publish',
			)
		);
		if ( ! empty( $page['noindex'] ) ) {
			update_post_meta( $id, 'rank_math_robots', array( 'noindex', 'follow' ) );
		}
		if ( ! empty( $page['seo']['title'] ) ) {
			update_post_meta( $id, 'rank_math_title', $page['seo']['title'] );
		}
		if ( ! empty( $page['seo']['description'] ) ) {
			update_post_meta( $id, 'rank_math_description', $page['seo']['description'] );
		}
		if ( ! empty( $page['template'] ) ) {
			update_post_meta( $id, '_wp_page_template', $page['template'] );
		}
		WP_CLI::log( sprintf( '%s → %s (%d blocks)', basename( $file ), get_permalink( $id ), count( $blocks ) ) );
	}
	WP_CLI::success( count( $files ) . ' pages imported.' );
}

$intella_parts = $args ? $args : array( 'all' );
$intella_steps = array( 'settings', 'shared', 'menus', 'pages' );
foreach ( $intella_steps as $intella_step ) {
	if ( in_array( 'all', $intella_parts, true ) || in_array( $intella_step, $intella_parts, true ) ) {
		$intella_fn = "intella_import_{$intella_step}";
		if ( function_exists( $intella_fn ) ) {
			$intella_fn();
		} elseif ( ! in_array( 'all', $intella_parts, true ) ) {
			WP_CLI::warning( "Nothing to import for '{$intella_step}' yet." );
		}
	}
}
