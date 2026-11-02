<?php
/**
 * REST API for the board page: /wp-json/intella-admin/v1/...
 * Every route needs a logged-in user with panel access and the wp_rest nonce (sent by the board script).
 *
 * @package Intella_Admin
 */

defined( 'ABSPATH' ) || exit;

add_action( 'rest_api_init', 'intella_admin_register_routes' );

/**
 * Register the routes.
 */
function intella_admin_register_routes(): void {
	$ns  = 'intella-admin/v1';
	$can = static fn() => intella_admin_can_access();
	$id  = array( 'id' => array( 'validate_callback' => static fn( $v ) => is_numeric( $v ) ) );

	$routes = array(
		'/boards/(?P<id>\d+)'          => array(
			array( 'GET', 'intella_admin_rest_get_board' ),
			array( 'PATCH', 'intella_admin_rest_update_board' ),
		),
		'/boards/(?P<id>\d+)/cards'    => array( array( 'POST', 'intella_admin_rest_create_card' ) ),
		'/boards/(?P<id>\d+)/bugs'     => array( array( 'POST', 'intella_admin_rest_create_bugs' ) ),
		'/boards/(?P<id>\d+)/export'   => array( array( 'GET', 'intella_admin_rest_export' ) ),
		'/cards/(?P<id>\d+)'           => array(
			array( 'PATCH', 'intella_admin_rest_update_card' ),
			array( 'DELETE', 'intella_admin_rest_delete_card' ),
		),
		'/cards/(?P<id>\d+)/move'      => array( array( 'POST', 'intella_admin_rest_move_card' ) ),
		'/cards/(?P<id>\d+)/comments'  => array( array( 'POST', 'intella_admin_rest_add_comment' ) ),
		'/comments/(?P<id>\d+)'        => array( array( 'DELETE', 'intella_admin_rest_delete_comment' ) ),
		'/bugs/(?P<id>\d+)'            => array(
			array( 'PATCH', 'intella_admin_rest_update_bug' ),
			array( 'DELETE', 'intella_admin_rest_delete_bug' ),
		),
	);
	foreach ( $routes as $path => $endpoints ) {
		register_rest_route(
			$ns,
			$path,
			array_map(
				static fn( $e ) => array(
					'methods'             => $e[0],
					'callback'            => $e[1],
					'permission_callback' => $can,
					'args'                => $id,
				),
				$endpoints
			)
		);
	}

	// Importing replaces a whole board, so only WordPress administrators can do it.
	register_rest_route(
		$ns,
		'/boards/(?P<id>\d+)/import',
		array(
			'methods'             => 'POST',
			'callback'            => 'intella_admin_rest_import',
			'permission_callback' => static fn() => intella_admin_can_manage(),
			'args'                => $id,
		)
	);
}

/** A 404 error for a missing board, card, comment or bug. */
function intella_admin_rest_not_found(): WP_Error {
	return new WP_Error( 'intella_not_found', __( 'Not found. It may have been deleted by someone else; reload the page.', 'intella' ), array( 'status' => 404 ) );
}

/**
 * The board from the route, or null.
 *
 * @param WP_REST_Request $request Request.
 */
function intella_admin_rest_board( WP_REST_Request $request ): ?object {
	return intella_admin_get_board( (int) $request['id'] );
}

/**
 * Full board data, plus the team for the assignee lists.
 *
 * @param WP_REST_Request $request Request.
 */
function intella_admin_rest_get_board( WP_REST_Request $request ) {
	$board = intella_admin_rest_board( $request );
	if ( ! $board ) {
		return intella_admin_rest_not_found();
	}
	return rest_ensure_response( intella_admin_board_payload( (int) $board->id ) + array( 'people' => intella_admin_people() ) );
}

/**
 * Save board notes or goals.
 *
 * @param WP_REST_Request $request Request.
 */
function intella_admin_rest_update_board( WP_REST_Request $request ) {
	$board = intella_admin_rest_board( $request );
	if ( ! $board ) {
		return intella_admin_rest_not_found();
	}
	intella_admin_update_board( (int) $board->id, array_intersect_key( (array) $request->get_json_params(), array_flip( array( 'notes', 'goals' ) ) ) );
	return rest_ensure_response( array( 'ok' => true ) );
}

/**
 * Add a card to the end of a list.
 *
 * @param WP_REST_Request $request Request.
 */
function intella_admin_rest_create_card( WP_REST_Request $request ) {
	$board  = intella_admin_rest_board( $request );
	$params = (array) $request->get_json_params();
	if ( ! $board || ! intella_admin_get_list( (int) $board->id, (int) ( $params['list_id'] ?? 0 ) ) ) {
		return intella_admin_rest_not_found();
	}
	$id = intella_admin_create_card( (int) $board->id, (int) $params['list_id'], $params );
	return rest_ensure_response( intella_admin_card_payload( intella_admin_get_card( $id ) ) );
}

/**
 * Update a card's fields (title, notes, assignee, tag, due, list_id).
 *
 * @param WP_REST_Request $request Request.
 */
function intella_admin_rest_update_card( WP_REST_Request $request ) {
	$card = intella_admin_get_card( (int) $request['id'] );
	if ( ! $card ) {
		return intella_admin_rest_not_found();
	}
	intella_admin_update_card( $card, (array) $request->get_json_params() );
	return rest_ensure_response( intella_admin_card_payload( intella_admin_get_card( (int) $card->id ) ) );
}

/**
 * Move a card: { list_id, order: [card IDs of that list in their new order] }.
 *
 * @param WP_REST_Request $request Request.
 */
function intella_admin_rest_move_card( WP_REST_Request $request ) {
	$card   = intella_admin_get_card( (int) $request['id'] );
	$params = (array) $request->get_json_params();
	if ( ! $card || ! intella_admin_move_card( $card, (int) ( $params['list_id'] ?? 0 ), (array) ( $params['order'] ?? array() ) ) ) {
		return intella_admin_rest_not_found();
	}
	return rest_ensure_response( array( 'ok' => true ) );
}

/**
 * Delete a card.
 *
 * @param WP_REST_Request $request Request.
 */
function intella_admin_rest_delete_card( WP_REST_Request $request ) {
	$card = intella_admin_get_card( (int) $request['id'] );
	if ( $card ) {
		intella_admin_delete_card( $card );
	}
	return rest_ensure_response( array( 'ok' => true ) );
}

/**
 * Add a comment as the current user.
 *
 * @param WP_REST_Request $request Request.
 */
function intella_admin_rest_add_comment( WP_REST_Request $request ) {
	$card = intella_admin_get_card( (int) $request['id'] );
	$text = (string) ( $request->get_json_params()['text'] ?? '' );
	if ( ! $card ) {
		return intella_admin_rest_not_found();
	}
	if ( '' === trim( $text ) ) {
		return new WP_Error( 'intella_empty', __( 'Write a comment first.', 'intella' ), array( 'status' => 400 ) );
	}
	$id = intella_admin_add_comment( (int) $card->id, $text, intella_admin_board_name( wp_get_current_user() ) );
	return rest_ensure_response( intella_admin_comment_payload( intella_admin_get_comment( $id ) ) );
}

/**
 * Delete a comment.
 *
 * @param WP_REST_Request $request Request.
 */
function intella_admin_rest_delete_comment( WP_REST_Request $request ) {
	global $wpdb;
	$wpdb->delete( intella_admin_table( 'comments' ), array( 'id' => (int) $request['id'] ) );
	return rest_ensure_response( array( 'ok' => true ) );
}

/**
 * Add one bug, or several: { items: [ {description, related, card_id, status, assignee}, ... ] }.
 *
 * @param WP_REST_Request $request Request.
 */
function intella_admin_rest_create_bugs( WP_REST_Request $request ) {
	$board = intella_admin_rest_board( $request );
	if ( ! $board ) {
		return intella_admin_rest_not_found();
	}
	$params = (array) $request->get_json_params();
	$items  = isset( $params['items'] ) && is_array( $params['items'] ) ? $params['items'] : array( $params );
	$bugs   = array();
	foreach ( array_slice( $items, 0, 100 ) as $item ) {
		$bugs[] = intella_admin_bug_payload( intella_admin_get_bug( intella_admin_create_bug( (int) $board->id, (array) $item ) ) );
	}
	return rest_ensure_response( $bugs );
}

/**
 * Update a bug.
 *
 * @param WP_REST_Request $request Request.
 */
function intella_admin_rest_update_bug( WP_REST_Request $request ) {
	$bug = intella_admin_get_bug( (int) $request['id'] );
	if ( ! $bug ) {
		return intella_admin_rest_not_found();
	}
	intella_admin_update_bug( $bug, (array) $request->get_json_params() );
	return rest_ensure_response( intella_admin_bug_payload( intella_admin_get_bug( (int) $bug->id ) ) );
}

/**
 * Delete a bug.
 *
 * @param WP_REST_Request $request Request.
 */
function intella_admin_rest_delete_bug( WP_REST_Request $request ) {
	global $wpdb;
	$wpdb->delete( intella_admin_table( 'bugs' ), array( 'id' => (int) $request['id'] ) );
	return rest_ensure_response( array( 'ok' => true ) );
}

/**
 * Download the board as a JSON file in the tracker-data format (a backup that can be imported again).
 *
 * @param WP_REST_Request $request Request.
 */
function intella_admin_rest_export( WP_REST_Request $request ) {
	$board = intella_admin_rest_board( $request );
	if ( ! $board ) {
		return intella_admin_rest_not_found();
	}
	$response = rest_ensure_response( intella_admin_export_tracker( (int) $board->id ) );
	$response->header( 'Content-Disposition', 'attachment; filename="' . $board->slug . '-board-' . current_time( 'Y-m-d' ) . '.json"' );
	return $response;
}

/**
 * Replace the board with an uploaded tracker-data.js.
 *
 * @param WP_REST_Request $request Request.
 */
function intella_admin_rest_import( WP_REST_Request $request ) {
	$board = intella_admin_rest_board( $request );
	$file  = $request->get_file_params()['file'] ?? null;
	if ( ! $board ) {
		return intella_admin_rest_not_found();
	}
	if ( ! $file || UPLOAD_ERR_OK !== ( $file['error'] ?? -1 ) || ! is_uploaded_file( $file['tmp_name'] ) || $file['size'] > 5 * MB_IN_BYTES ) {
		return new WP_Error( 'intella_no_file', __( 'Choose a tracker-data.js file (5 MB at most).', 'intella' ), array( 'status' => 400 ) );
	}
	$data = intella_admin_parse_tracker( (string) file_get_contents( $file['tmp_name'] ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( is_wp_error( $data ) ) {
		$data->add_data( array( 'status' => 400 ) );
		return $data;
	}
	return rest_ensure_response( intella_admin_import_tracker( (int) $board->id, $data ) );
}
