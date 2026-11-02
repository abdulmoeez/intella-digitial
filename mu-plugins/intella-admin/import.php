<?php
/**
 * Import a project hub's tracker-data.js into a board, and export a board in the same format.
 *
 * Importing replaces everything on the board (lists, cards, comments, bugs, notes, goals) with the file's
 * contents, keeping the order of lists, cards and bugs. Card links on bugs (cardId) are carried over.
 *
 * Also available from WP-CLI: wp intella-admin import <file> --board=<slug>
 *
 * @package Intella_Admin
 */

defined( 'ABSPATH' ) || exit;

/**
 * Read tracker data from a tracker-data.js file (or the JSON this plugin exports).
 *
 * @param string $text File contents.
 * @return array|WP_Error Decoded data.
 */
function intella_admin_parse_tracker( string $text ) {
	$start = strpos( $text, '{' );
	$end   = strrpos( $text, '}' );
	$data  = ( false === $start || false === $end ) ? null : json_decode( substr( $text, $start, $end - $start + 1 ), true );
	if ( ! is_array( $data ) || ! isset( $data['lists'], $data['cards'] ) || ! is_array( $data['lists'] ) || ! is_array( $data['cards'] ) ) {
		return new WP_Error( 'intella_bad_file', __( "This doesn't look like a tracker-data.js file: it needs \"lists\" and \"cards\".", 'intella' ) );
	}
	return $data;
}

/**
 * Replace a board's content with tracker data.
 *
 * @param int   $board_id Board ID.
 * @param array $data     Data from intella_admin_parse_tracker().
 * @return array{lists: int, cards: int, comments: int, bugs: int} What was imported.
 */
function intella_admin_import_tracker( int $board_id, array $data ): array {
	global $wpdb;
	$counts = array(
		'lists'    => 0,
		'cards'    => 0,
		'comments' => 0,
		'bugs'     => 0,
	);

	$wpdb->query( 'START TRANSACTION' );

	// Clear the board.
	$card_ids = $wpdb->get_col( $wpdb->prepare( 'SELECT id FROM ' . intella_admin_table( 'cards' ) . ' WHERE board_id = %d', $board_id ) );
	if ( $card_ids ) {
		$wpdb->query( 'DELETE FROM ' . intella_admin_table( 'comments' ) . ' WHERE card_id IN (' . implode( ',', array_map( 'intval', $card_ids ) ) . ')' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- integers.
	}
	foreach ( array( 'cards', 'bugs', 'lists' ) as $table ) {
		$wpdb->delete( intella_admin_table( $table ), array( 'board_id' => $board_id ) );
	}

	// Lists, keyed by the file's list ID.
	$list_ids = array();
	foreach ( array_values( $data['lists'] ) as $i => $list ) {
		$key              = (string) ( $list['id'] ?? 'list-' . $i );
		$list_ids[ $key ] = intella_admin_insert_list( $board_id, $key, (string) ( $list['name'] ?? $key ), $i );
		++$counts['lists'];
	}
	if ( ! $list_ids ) {
		$list_ids['backlog'] = intella_admin_insert_list( $board_id, 'backlog', __( 'Backlog', 'intella' ), 0 );
	}
	$first_list = reset( $list_ids );

	// Cards in file order (that is their order within each list), with their comments.
	$card_map = array();
	foreach ( $data['cards'] as $card ) {
		if ( ! is_array( $card ) ) {
			continue;
		}
		$list_id = $list_ids[ (string) ( $card['list'] ?? '' ) ] ?? $first_list;
		$id      = intella_admin_create_card( $board_id, $list_id, $card );
		++$counts['cards'];
		if ( isset( $card['id'] ) ) {
			$card_map[ (string) $card['id'] ] = $id;
		}
		foreach ( (array) ( $card['comments'] ?? array() ) as $comment ) {
			$at = isset( $comment['at'] ) && strtotime( (string) $comment['at'] ) ? gmdate( 'Y-m-d H:i:s', strtotime( (string) $comment['at'] ) ) : null;
			intella_admin_add_comment( $id, (string) ( $comment['text'] ?? '' ), (string) ( $comment['author'] ?? '' ), $at );
			++$counts['comments'];
		}
	}

	// Bugs in file order, linked to their cards.
	foreach ( (array) ( $data['bugs'] ?? array() ) as $bug ) {
		if ( ! is_array( $bug ) ) {
			continue;
		}
		$bug['card_id'] = $card_map[ (string) ( $bug['cardId'] ?? '' ) ] ?? 0;
		intella_admin_create_bug( $board_id, $bug );
		++$counts['bugs'];
	}

	intella_admin_update_board(
		$board_id,
		array(
			'notes' => (string) ( $data['hubNotes'] ?? '' ),
			'goals' => (string) ( $data['hubGoals'] ?? '' ),
		)
	);

	$wpdb->query( 'COMMIT' );
	return $counts;
}

/**
 * A board in the tracker-data.js format, so it can be backed up or imported again.
 *
 * @param int $board_id Board ID.
 */
function intella_admin_export_tracker( int $board_id ): array {
	$payload = intella_admin_board_payload( $board_id );
	$slugs   = array_column( $payload['lists'], 'slug', 'id' );
	return array(
		'board'      => $payload['board']['name'],
		'exportedAt' => gmdate( 'Y-m-d\TH:i:s\Z' ),
		'lists'      => array_map(
			static fn( $l ) => array(
				'id'   => $l['slug'],
				'name' => $l['name'],
			),
			$payload['lists']
		),
		'cards'      => array_map(
			static fn( $c ) => array(
				'id'       => 'card-' . $c['id'],
				'list'     => $slugs[ $c['list_id'] ] ?? '',
				'title'    => $c['title'],
				'notes'    => $c['notes'],
				'assignee' => $c['assignee'],
				'due'      => $c['due'],
				'tag'      => $c['tag'],
				'created'  => $c['created'],
				'comments' => array_map(
					static fn( $m ) => array(
						'author' => $m['author'],
						'at'     => $m['at'],
						'text'   => $m['text'],
					),
					$c['comments']
				),
			),
			$payload['cards']
		),
		'bugs'       => array_map(
			static fn( $b ) => array(
				'id'          => 'bug-' . $b['id'],
				'description' => $b['description'],
				'related'     => $b['related'],
				'status'      => $b['status'],
				'due'         => $b['due'],
				'assignee'    => $b['assignee'],
				'created'     => $b['created'],
				'cardId'      => $b['card_id'] ? 'card-' . $b['card_id'] : '',
			),
			$payload['bugs']
		),
		'hubNotes'   => $payload['board']['notes'],
		'hubGoals'   => $payload['board']['goals'],
	);
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	/**
	 * Import a project hub's tracker-data.js into a board, replacing what the board holds.
	 *
	 * ## OPTIONS
	 *
	 * <file>
	 * : Path to tracker-data.js.
	 *
	 * --board=<slug>
	 * : The board to fill, e.g. bizticle.
	 *
	 * ## EXAMPLES
	 *
	 *     wp intella-admin import ../../clients/Bizticle/tracker-data.js --board=bizticle
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Named arguments.
	 */
	$intella_admin_cli_import = static function ( array $args, array $assoc_args ): void {
		intella_admin_maybe_install();
		$board = intella_admin_get_board( (string) $assoc_args['board'] );
		if ( ! $board ) {
			WP_CLI::error( 'No board with that slug. Boards: ' . implode( ', ', wp_list_pluck( intella_admin_get_boards(), 'slug' ) ) );
		}
		$text = is_readable( $args[0] ) ? file_get_contents( $args[0] ) : false; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( false === $text ) {
			WP_CLI::error( "Can't read {$args[0]}" );
		}
		$data = intella_admin_parse_tracker( $text );
		if ( is_wp_error( $data ) ) {
			WP_CLI::error( $data->get_error_message() );
		}
		$counts = intella_admin_import_tracker( (int) $board->id, $data );
		WP_CLI::success( sprintf( 'Imported into "%s": %d lists, %d cards, %d comments, %d bugs.', $board->name, $counts['lists'], $counts['cards'], $counts['comments'], $counts['bugs'] ) );
	};
	WP_CLI::add_command( 'intella-admin import', $intella_admin_cli_import );
}
