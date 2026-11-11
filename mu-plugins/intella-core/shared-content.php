<?php
/**
 * Shared content edited once and shown by many blocks: Testimonials, Clients (logos) and Team members.
 * Each has its own admin screen with a plain form (no block editor) and a "Placeholder" flag for made-up
 * content that must be replaced before launch. They have no public pages of their own.
 *
 * @package Intella_Core
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'intella_register_shared_content' );
add_action( 'add_meta_boxes', 'intella_shared_content_boxes' );
add_action( 'save_post', 'intella_shared_content_save', 10, 2 );
add_filter( 'use_block_editor_for_post_type', 'intella_shared_content_classic_editor', 10, 2 );

/**
 * Post types and their extra fields. Title = the name; featured image = photo or logo.
 */
function intella_shared_content_types(): array {
	return array(
		'testimonial' => array(
			'labels'   => array( __( 'Testimonials', 'intella' ), __( 'Testimonial', 'intella' ) ),
			'icon'     => 'dashicons-format-quote',
			'supports' => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			'title'    => __( 'Person’s name', 'intella' ),
			'image'    => __( 'Photo (square)', 'intella' ),
			'fields'   => array(
				'role'        => array( __( 'Role and company', 'intella' ), 'text', __( 'Shown under the name, e.g. “CEO & Founder, Cupello”.', 'intella' ) ),
				'company'     => array( __( 'Company', 'intella' ), 'text' ),
				'short_quote' => array( __( 'Short version of the quote', 'intella' ), 'textarea', __( 'Optional. Used where space is tight.', 'intella' ) ),
				'project'     => array( __( 'Project', 'intella' ), 'text', __( 'What we did for them, e.g. “Web app design and build”. Shown on the editorial testimonials spread.', 'intella' ) ),
				'placeholder' => array( __( 'Placeholder', 'intella' ), 'checkbox', __( 'Made-up testimonial: replace or remove before launch.', 'intella' ) ),
			),
		),
		'client'      => array(
			'labels'   => array( __( 'Clients', 'intella' ), __( 'Client', 'intella' ) ),
			'icon'     => 'dashicons-building',
			'supports' => array( 'title', 'thumbnail', 'page-attributes' ),
			'title'    => __( 'Company name', 'intella' ),
			'image'    => __( 'Logo (SVG or PNG)', 'intella' ),
			'fields'   => array(
				'website'     => array( __( 'Website', 'intella' ), 'url' ),
				'industry'    => array( __( 'Industry', 'intella' ), 'text' ),
				'placeholder' => array( __( 'Placeholder', 'intella' ), 'checkbox', __( 'Made-up client: replace or remove before launch.', 'intella' ) ),
			),
		),
		'team_member' => array(
			'labels'   => array( __( 'Team', 'intella' ), __( 'Team member', 'intella' ) ),
			'icon'     => 'dashicons-groups',
			'supports' => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			'title'    => __( 'Name', 'intella' ),
			'image'    => __( 'Photo', 'intella' ),
			'fields'   => array(
				'role'        => array( __( 'Role', 'intella' ), 'text' ),
				'location'    => array( __( 'Location', 'intella' ), 'text' ),
				'years'       => array( __( 'Years of experience', 'intella' ), 'text' ),
				'expertise'   => array( __( 'Expertise', 'intella' ), 'text', __( 'Comma-separated.', 'intella' ) ),
				'placeholder' => array( __( 'Placeholder', 'intella' ), 'checkbox', __( 'Made-up person or stock photo: replace before launch.', 'intella' ) ),
			),
		),
	);
}

/**
 * Register the post types and their meta (meta is also readable over REST for the block editor).
 */
function intella_register_shared_content(): void {
	foreach ( intella_shared_content_types() as $type => $config ) {
		register_post_type(
			$type,
			array(
				'labels'              => array(
					'name'                  => $config['labels'][0],
					'singular_name'         => $config['labels'][1],
					/* translators: %s: content type, e.g. Testimonial */
					'add_new_item'          => sprintf( __( 'Add %s', 'intella' ), $config['labels'][1] ),
					/* translators: %s: content type */
					'edit_item'             => sprintf( __( 'Edit %s', 'intella' ), $config['labels'][1] ),
					'featured_image'        => $config['image'],
					'set_featured_image'    => $config['image'],
					'remove_featured_image' => __( 'Remove image', 'intella' ),
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_rest'        => true,
				'exclude_from_search' => true,
				'menu_icon'           => $config['icon'],
				'menu_position'       => 25,
				'supports'            => $config['supports'],
				'hierarchical'        => false,
			)
		);

		foreach ( $config['fields'] as $key => $field ) {
			register_post_meta(
				$type,
				"_intella_{$key}",
				array(
					'type'          => 'checkbox' === $field[1] ? 'boolean' : 'string',
					'single'        => true,
					'show_in_rest'  => true,
					'auth_callback' => static fn() => current_user_can( 'edit_posts' ),
				)
			);
		}
	}
}

/**
 * Use the simple classic form for these types: they are a few fields, not page layouts.
 *
 * @param bool   $use       Whether to use the block editor.
 * @param string $post_type Post type.
 */
function intella_shared_content_classic_editor( bool $use, string $post_type ): bool {
	return array_key_exists( $post_type, intella_shared_content_types() ) ? false : $use;
}

/**
 * Add the "Details" box to each type.
 */
function intella_shared_content_boxes(): void {
	foreach ( intella_shared_content_types() as $type => $config ) {
		add_meta_box( 'intella-details', __( 'Details', 'intella' ), 'intella_shared_content_box', $type, 'normal', 'high', $config );
	}
}

/**
 * Print the Details box.
 *
 * @param WP_Post $post Current post.
 * @param array   $box  Box arguments (args = the type's config).
 */
function intella_shared_content_box( WP_Post $post, array $box ): void {
	wp_nonce_field( 'intella_details', 'intella_details_nonce' );
	echo '<table class="form-table" role="presentation">';
	foreach ( $box['args']['fields'] as $key => $field ) {
		$value = get_post_meta( $post->ID, "_intella_{$key}", true );
		$id    = "intella-{$key}";
		echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $field[0] ) . '</label></th><td>';
		if ( 'checkbox' === $field[1] ) {
			printf( '<label><input type="checkbox" id="%1$s" name="%1$s" value="1" %2$s> %3$s</label>', esc_attr( $id ), checked( (bool) $value, true, false ), esc_html( $field[2] ?? '' ) );
		} elseif ( 'textarea' === $field[1] ) {
			printf( '<textarea id="%1$s" name="%1$s" rows="3" class="large-text">%2$s</textarea>', esc_attr( $id ), esc_textarea( (string) $value ) );
		} else {
			printf( '<input type="%3$s" id="%1$s" name="%1$s" value="%2$s" class="regular-text">', esc_attr( $id ), esc_attr( (string) $value ), 'url' === $field[1] ? 'url' : 'text' );
		}
		if ( 'checkbox' !== $field[1] && ! empty( $field[2] ) ) {
			echo '<p class="description">' . esc_html( $field[2] ) . '</p>';
		}
		echo '</td></tr>';
	}
	echo '</table>';
}

/**
 * Save the Details box.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post.
 */
function intella_shared_content_save( int $post_id, WP_Post $post ): void {
	$types = intella_shared_content_types();
	if ( ! isset( $types[ $post->post_type ], $_POST['intella_details_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['intella_details_nonce'] ) ), 'intella_details' )
		|| ! current_user_can( 'edit_post', $post_id )
		|| wp_is_post_autosave( $post_id ) ) {
		return;
	}

	foreach ( $types[ $post->post_type ]['fields'] as $key => $field ) {
		$name = "intella-{$key}";
		$raw  = isset( $_POST[ $name ] ) ? wp_unslash( $_POST[ $name ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised below per type.
		switch ( $field[1] ) {
			case 'checkbox':
				$value = '' !== $raw;
				break;
			case 'url':
				$value = esc_url_raw( (string) $raw );
				break;
			case 'textarea':
				$value = sanitize_textarea_field( (string) $raw );
				break;
			default:
				$value = sanitize_text_field( (string) $raw );
		}
		update_post_meta( $post_id, "_intella_{$key}", $value );
	}
}

/**
 * Data for one shared item, ready for templates.
 *
 * @param int|WP_Post $post Post or ID.
 */
function intella_shared_item( $post ): array {
	$post = get_post( $post );
	if ( ! $post ) {
		return array();
	}
	$item = array(
		'id'    => $post->ID,
		'name'  => get_the_title( $post ),
		'text'  => trim( $post->post_content ),
		'image' => (int) get_post_thumbnail_id( $post ),
	);
	foreach ( intella_shared_content_types()[ $post->post_type ]['fields'] ?? array() as $key => $field ) {
		$item[ $key ] = get_post_meta( $post->ID, "_intella_{$key}", true );
	}
	return $item;
}

/**
 * Shared items for a block: the chosen IDs in their chosen order, or all published items in menu order.
 *
 * @param string $type  Post type.
 * @param array  $ids   Chosen post IDs (empty = all).
 * @param int    $limit Max items when showing all (-1 = no limit).
 */
function intella_shared_items( string $type, array $ids = array(), int $limit = -1 ): array {
	$ids  = array_filter( array_map( 'absint', $ids ) );
	$args = array(
		'post_type'      => $type,
		'post_status'    => 'publish',
		'posts_per_page' => $ids ? count( $ids ) : $limit,
		'orderby'        => $ids ? 'post__in' : array(
			'menu_order' => 'ASC',
			'date'       => 'ASC',
		),
		'no_found_rows'  => true,
	);
	if ( $ids ) {
		$args['post__in'] = $ids;
	}
	return array_map( 'intella_shared_item', get_posts( $args ) );
}

add_action( 'init', 'intella_shared_content_columns', 20 );

/**
 * Show a "Placeholder" column in each list, so made-up items are easy to spot before launch.
 */
function intella_shared_content_columns(): void {
	foreach ( array_keys( intella_shared_content_types() ) as $type ) {
		add_filter(
			"manage_{$type}_posts_columns",
			static function ( array $columns ): array {
				$columns['intella_placeholder'] = __( 'Placeholder', 'intella' );
				return $columns;
			}
		);
		add_action(
			"manage_{$type}_posts_custom_column",
			static function ( string $column, int $post_id ): void {
				if ( 'intella_placeholder' === $column ) {
					echo get_post_meta( $post_id, '_intella_placeholder', true ) ? '⚠️ ' . esc_html__( 'Replace before launch', 'intella' ) : '—';
				}
			},
			10,
			2
		);
	}
}
