<?php
/**
 * Menus: the main navigation with the Services mega menu, and the footer link lists.
 *
 * Everything is edited in Appearance → Menus. The "Primary navigation" menu holds the top items; the item
 * that has children becomes the Services mega menu:
 *   Services (links to /services/)
 *     └ Service group, e.g. "SEO and Content" (a tab; Description = the small line under its name)
 *         └ Service link, e.g. "SEO" (Description = the line under the link)
 * Extra fields on each item (icon, logo, the group's offer strip) are added below the normal fields.
 * The footer's Services columns reuse the same groups, so the two can't drift apart.
 *
 * @package Intella
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_nav_menu_item_custom_fields', 'intella_menu_item_fields', 10, 2 );
add_action( 'wp_update_nav_menu_item', 'intella_menu_item_save', 10, 2 );
add_filter( 'manage_nav-menus_columns', 'intella_menu_columns', 20 );

/**
 * Extra menu item fields: key => array( label, help, which items they apply to ).
 */
function intella_menu_item_field_list(): array {
	return array(
		'icon'       => array( __( 'Icon', 'intella' ), __( 'Groups: sprite icon name, e.g. search, megaphone, bot.', 'intella' ) ),
		'logo'       => array( __( 'Logo or icon', 'intella' ), __( 'Service links: a logo file from assets/images/logos (e.g. google-ads) or a sprite icon name.', 'intella' ) ),
		'logo_style' => array( __( 'Logo colours', 'intella' ), __( 'Optional CSS, e.g. --c:#0d8a7e;--bg:#e7f6f3', 'intella' ) ),
		'link_text'  => array( __( '“Explore” link text', 'intella' ), __( 'Services item and groups: the link to the overview page.', 'intella' ) ),
		'cta_title'  => array( __( 'Offer strip heading', 'intella' ), __( 'Wrap the highlighted words in <em>…</em>.', 'intella' ) ),
		'cta_text'   => array( __( 'Offer strip text', 'intella' ), '' ),
		'cta_button' => array( __( 'Offer strip button', 'intella' ), '' ),
		'cta_url'    => array( __( 'Offer strip button URL', 'intella' ), '' ),
	);
}

/**
 * Show the Description field by default (it holds the small text under each menu name).
 *
 * @param array $columns Screen option columns.
 */
function intella_menu_columns( array $columns ): array {
	$hidden = get_user_option( 'managenav-menuscolumnshidden' );
	if ( is_array( $hidden ) && in_array( 'description', $hidden, true ) ) {
		update_user_option( get_current_user_id(), 'managenav-menuscolumnshidden', array_values( array_diff( $hidden, array( 'description' ) ) ) );
	}
	return $columns;
}

/**
 * Print the extra fields inside each menu item's settings.
 *
 * @param int     $item_id Menu item ID.
 * @param WP_Post $item    Menu item.
 */
function intella_menu_item_fields( int $item_id, $item ): void {
	wp_nonce_field( 'intella_menu_item', 'intella_menu_item_nonce' );
	foreach ( intella_menu_item_field_list() as $key => $field ) {
		$id    = "intella-menu-{$key}-{$item_id}";
		$value = (string) get_post_meta( $item_id, "_intella_menu_{$key}", true );
		printf(
			'<p class="field-intella-%1$s description description-wide"><label for="%2$s">%3$s<br><input type="text" id="%2$s" class="widefat" name="intella_menu[%4$d][%1$s]" value="%5$s"></label><span class="description">%6$s</span></p>',
			esc_attr( $key ),
			esc_attr( $id ),
			esc_html( $field[0] ),
			(int) $item_id,
			esc_attr( $value ),
			esc_html( $field[1] )
		);
	}
}

/**
 * Save the extra fields.
 *
 * @param int $menu_id Menu ID.
 * @param int $item_id Menu item ID.
 */
function intella_menu_item_save( int $menu_id, int $item_id ): void {
	if ( ! isset( $_POST['intella_menu_item_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['intella_menu_item_nonce'] ) ), 'intella_menu_item' ) ) {
		return;
	}
	$values = isset( $_POST['intella_menu'][ $item_id ] ) ? wp_unslash( $_POST['intella_menu'][ $item_id ] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised per field below.
	foreach ( array_keys( intella_menu_item_field_list() ) as $key ) {
		$value = isset( $values[ $key ] ) ? (string) $values[ $key ] : '';
		$value = 'cta_title' === $key ? wp_kses( $value, array( 'em' => array() ) ) : sanitize_text_field( $value );
		update_post_meta( $item_id, "_intella_menu_{$key}", $value );
	}
}

/**
 * A menu location as a tree: each item has title, url, description, the extra fields and children.
 *
 * @param string $location Theme location.
 */
function intella_menu_tree( string $location ): array {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		return array();
	}
	$items = wp_get_nav_menu_items( $locations[ $location ], array( 'update_post_term_cache' => false ) );
	if ( ! $items ) {
		return array();
	}

	$nodes    = array();
	$children = array();
	foreach ( $items as $item ) {
		$children[ (int) $item->menu_item_parent ][] = (int) $item->ID;
		$node                                        = array(
			'id'          => (int) $item->ID,
			'parent'      => (int) $item->menu_item_parent,
			'title'       => $item->title,
			'url'         => $item->url,
			'description' => $item->description,
			'current'     => intella_menu_is_current( $item->url ),
			'children'    => array(),
		);
		foreach ( array_keys( intella_menu_item_field_list() ) as $key ) {
			$node[ $key ] = (string) get_post_meta( $item->ID, "_intella_menu_{$key}", true );
		}
		$nodes[ $item->ID ] = $node;
	}

	// Items come sorted by menu order; build each branch from the top down.
	$build = static function ( int $parent ) use ( &$build, $nodes, $children ): array {
		$branch = array();
		foreach ( $children[ $parent ] ?? array() as $id ) {
			$node             = $nodes[ $id ];
			$node['children'] = $build( $id );
			$branch[]         = $node;
		}
		return $branch;
	};
	return $build( 0 );
}

/**
 * Whether a menu link points at the page being viewed.
 *
 * @param string $url Menu link.
 */
function intella_menu_is_current( string $url ): bool {
	$path    = wp_parse_url( $url, PHP_URL_PATH );
	$request = wp_parse_url( home_url( add_query_arg( array() ) ), PHP_URL_PATH );
	return $path && ! wp_parse_url( $url, PHP_URL_FRAGMENT ) && trailingslashit( $path ) === trailingslashit( (string) $request );
}

/**
 * Highlighted words: <em>…</em> in a field becomes the design's gradient accent.
 *
 * @param string $html    Field value (already allowed to contain <em>).
 * @param string $variant Extra accent class, e.g. 'accent--light' on dark backgrounds.
 */
function intella_accent( string $html, string $variant = '' ): string {
	$class = trim( 'accent ' . $variant );
	return str_replace( array( '<em>', '</em>' ), array( '<span class="' . esc_attr( $class ) . '">', '</span>' ), intella_inline( $html ) );
}

/**
 * The logo or icon in front of a mega menu service link.
 *
 * @param array $link Menu node.
 */
function intella_mega_logo( array $link ): string {
	$name = $link['logo'];
	$svg  = intella_logo_svg( $name );
	if ( $svg ) {
		$variant = str_contains( $link['logo_style'], 'background' ) ? ' mega__link-logo--badge' : '';
		return sprintf( '<span class="mega__link-logo%s"%s aria-hidden="true">%s</span>', $variant, $link['logo_style'] ? ' style="' . esc_attr( $link['logo_style'] ) . '"' : '', $svg );
	}
	return sprintf(
		'<span class="mega__link-logo mega__link-logo--icon"%s aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><use href="#i-%s"/></svg></span>',
		$link['logo_style'] ? ' style="' . esc_attr( $link['logo_style'] ) . '"' : '',
		esc_attr( $name ? $name : 'arrow-right' )
	);
}
