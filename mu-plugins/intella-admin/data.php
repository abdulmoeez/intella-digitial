<?php
/**
 * Board data: reading and writing boards, lists, cards, comments and bugs in the intella_* tables.
 * All input is sanitised here, so the REST API and the importer share the same rules.
 *
 * @package Intella_Admin
 */

defined( 'ABSPATH' ) || exit;

/**
 * Full table name, e.g. wp_intella_cards.
 *
 * @param string $name boards, lists, cards, comments or bugs.
 */
function intella_admin_table( string $name ): string {
	global $wpdb;
	return $wpdb->prefix . 'intella_' . $name;
}

/**
 * The lists a new board starts with (the same as the project hubs).
 *
 * @return array<int, array{slug: string, name: string}>
 */
function intella_admin_default_lists(): array {
	return array(
		array( 'slug' => 'backlog', 'name' => __( 'Backlog', 'intella' ) ),
		array( 'slug' => 'in-progress', 'name' => __( 'In Progress', 'intella' ) ),
		array( 'slug' => 'due-next-call', 'name' => __( 'Due by Next call', 'intella' ) ),
		array( 'slug' => 'awaiting-qa', 'name' => __( 'Awaiting QA', 'intella' ) ),
		array( 'slug' => 'completed', 'name' => __( 'Completed', 'intella' ) ),
		array( 'slug' => 'abandoned', 'name' => __( 'Abandoned', 'intella' ) ),
	);
}

/**
 * Lists whose cards count as finished (never overdue, not counted as open).
 *
 * @return string[] List slugs.
 */
function intella_admin_done_lists(): array {
	return array( 'completed', 'abandoned' );
}

/**
 * Bug statuses, in the order the bug tracker shows them.
 *
 * @return array<string, string> Key => label.
 */
function intella_admin_bug_statuses(): array {
	return array(
		'open'        => __( 'Open', 'intella' ),
		'in-progress' => __( 'In progress', 'intella' ),
		'awaiting-qa' => __( 'Awaiting QA', 'intella' ),
		'revalidate'  => __( 'Fixed, Awaiting Re-validation', 'intella' ),
		'fixed'       => __( 'Fixed', 'intella' ),
		'wont-fix'    => __( "Won't fix", 'intella' ),
	);
}

/**
 * Statuses that close a bug.
 *
 * @return string[]
 */
function intella_admin_closed_bug_statuses(): array {
	return array( 'fixed', 'wont-fix' );
}

/** Current time for datetime columns (UTC). */
function intella_admin_now(): string {
	return current_time( 'mysql', true );
}

/**
 * A UTC datetime column as an ISO 8601 string for JavaScript.
 *
 * @param string|null $datetime Y-m-d H:i:s in UTC.
 */
function intella_admin_iso( ?string $datetime ): string {
	return $datetime ? gmdate( 'Y-m-d\TH:i:s\Z', strtotime( $datetime . ' UTC' ) ) : '';
}

// ----- Sanitising -----

/**
 * Card notes, board notes and goals: the same small set of formatting tags the hub editor allows.
 *
 * @param mixed $html Raw HTML.
 */
function intella_admin_clean_html( $html ): string {
	$allowed = array_fill_keys( array( 'p', 'div', 'br', 'b', 'strong', 'i', 'em', 'u', 's', 'strike', 'h3', 'h4', 'ul', 'ol', 'li', 'blockquote', 'code', 'pre' ), array() );
	$allowed['a'] = array(
		'href'   => true,
		'target' => true,
		'rel'    => true,
	);
	return trim( wp_kses( (string) $html, $allowed, array( 'http', 'https', 'mailto' ) ) );
}

/**
 * A Y-m-d date, or null when empty or invalid.
 *
 * @param mixed $value Date string.
 */
function intella_admin_clean_date( $value ): ?string {
	$value = substr( trim( (string) $value ), 0, 10 );
	if ( ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m ) || ! checkdate( (int) $m[2], (int) $m[3], (int) $m[1] ) ) {
		return null;
	}
	return $value;
}

/**
 * One-line text, trimmed to a column's length.
 *
 * @param mixed $value Text.
 * @param int   $max   Maximum length.
 */
function intella_admin_clean_line( $value, int $max = 255 ): string {
	return mb_substr( trim( preg_replace( '/\s+/u', ' ', intella_admin_clean_text( $value ) ) ), 0, $max );
}

/**
 * Plain text that keeps its line breaks (comments, bug descriptions). It is always escaped on output, so
 * only invalid UTF-8 and control characters are removed. WordPress's sanitize_*_field() functions are
 * avoided on purpose: they delete "%xx" sequences, which breaks pasted URLs.
 *
 * @param mixed $value Text.
 */
function intella_admin_clean_text( $value ): string {
	$value = wp_check_invalid_utf8( str_replace( "\r\n", "\n", (string) $value ) );
	return trim( preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value ) );
}

// ----- Boards -----

/**
 * All boards, in display order.
 *
 * @return object[]
 */
function intella_admin_get_boards(): array {
	global $wpdb;
	return $wpdb->get_results( 'SELECT * FROM ' . intella_admin_table( 'boards' ) . ' ORDER BY position, id' );
}

/**
 * One board by ID or slug.
 *
 * @param int|string $id_or_slug Board ID or slug.
 */
function intella_admin_get_board( $id_or_slug ): ?object {
	global $wpdb;
	$table = intella_admin_table( 'boards' );
	$board = is_numeric( $id_or_slug )
		? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", (int) $id_or_slug ) )
		: $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE slug = %s", (string) $id_or_slug ) );
	return $board ? $board : null;
}

/**
 * Create a board with the default lists.
 *
 * @param string $name Board name.
 * @return int Board ID.
 */
function intella_admin_create_board( string $name ): int {
	global $wpdb;
	$name = intella_admin_clean_line( $name, 200 );
	$base = sanitize_title( $name );
	$base = '' !== $base ? $base : 'board';
	$slug = $base;
	for ( $i = 2; intella_admin_get_board( $slug ); $i++ ) {
		$slug = $base . '-' . $i;
	}
	$now = intella_admin_now();
	$wpdb->insert(
		intella_admin_table( 'boards' ),
		array(
			'slug'       => $slug,
			'name'       => $name,
			'notes'      => '',
			'goals'      => '',
			'position'   => count( intella_admin_get_boards() ),
			'created_at' => $now,
			'updated_at' => $now,
		)
	);
	$board_id = (int) $wpdb->insert_id;
	foreach ( intella_admin_default_lists() as $i => $list ) {
		intella_admin_insert_list( $board_id, $list['slug'], $list['name'], $i );
	}
	return $board_id;
}

/**
 * Update a board's name, notes or goals.
 *
 * @param int   $board_id Board ID.
 * @param array $fields   Any of name, notes, goals.
 */
function intella_admin_update_board( int $board_id, array $fields ): void {
	global $wpdb;
	$row = array();
	if ( isset( $fields['name'] ) && '' !== trim( (string) $fields['name'] ) ) {
		$row['name'] = intella_admin_clean_line( $fields['name'], 200 );
	}
	foreach ( array( 'notes', 'goals' ) as $key ) {
		if ( array_key_exists( $key, $fields ) ) {
			$row[ $key ] = intella_admin_clean_html( $fields[ $key ] );
		}
	}
	if ( $row ) {
		$row['updated_at'] = intella_admin_now();
		$wpdb->update( intella_admin_table( 'boards' ), $row, array( 'id' => $board_id ) );
	}
}

/**
 * Card and bug counts for each board, for the dashboard.
 *
 * @return array<int, array{open: int, overdue: int, bugs: int}> Keyed by board ID.
 */
function intella_admin_board_stats(): array {
	global $wpdb;
	$cards = intella_admin_table( 'cards' );
	$lists = intella_admin_table( 'lists' );
	$bugs  = intella_admin_table( 'bugs' );
	$done  = "'" . implode( "','", array_map( 'esc_sql', intella_admin_done_lists() ) ) . "'";
	$today = current_time( 'Y-m-d' );
	$stats = array();

	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT c.board_id, COUNT(*) AS open_cards, SUM( c.due IS NOT NULL AND c.due < %s ) AS overdue
			FROM {$cards} c JOIN {$lists} l ON l.id = c.list_id
			WHERE l.slug NOT IN ({$done}) GROUP BY c.board_id", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- fixed slugs.
			$today
		)
	);
	foreach ( $rows as $r ) {
		$stats[ (int) $r->board_id ] = array(
			'open'    => (int) $r->open_cards,
			'overdue' => (int) $r->overdue,
			'bugs'    => 0,
		);
	}
	$closed = "'" . implode( "','", array_map( 'esc_sql', intella_admin_closed_bug_statuses() ) ) . "'";
	foreach ( $wpdb->get_results( "SELECT board_id, COUNT(*) AS n FROM {$bugs} WHERE status NOT IN ({$closed}) GROUP BY board_id" ) as $r ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$stats[ (int) $r->board_id ]['bugs']     = (int) $r->n;
		$stats[ (int) $r->board_id ]['open']    ??= 0;
		$stats[ (int) $r->board_id ]['overdue'] ??= 0;
	}
	return $stats;
}

/**
 * Everything the board page needs, in one array.
 *
 * @param int $board_id Board ID.
 */
function intella_admin_board_payload( int $board_id ): array {
	global $wpdb;
	$board = intella_admin_get_board( $board_id );
	$lists = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . intella_admin_table( 'lists' ) . ' WHERE board_id = %d ORDER BY position, id', $board_id ) );
	$cards = $wpdb->get_results(
		$wpdb->prepare(
			'SELECT c.* FROM ' . intella_admin_table( 'cards' ) . ' c JOIN ' . intella_admin_table( 'lists' ) . ' l ON l.id = c.list_id
			WHERE c.board_id = %d ORDER BY l.position, l.id, c.position, c.id',
			$board_id
		)
	);
	$comments = $wpdb->get_results(
		$wpdb->prepare(
			'SELECT m.* FROM ' . intella_admin_table( 'comments' ) . ' m JOIN ' . intella_admin_table( 'cards' ) . ' c ON c.id = m.card_id
			WHERE c.board_id = %d ORDER BY m.created_at, m.id',
			$board_id
		)
	);
	$bugs = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . intella_admin_table( 'bugs' ) . ' WHERE board_id = %d ORDER BY position, id', $board_id ) );

	$by_card = array();
	foreach ( $comments as $m ) {
		$by_card[ (int) $m->card_id ][] = intella_admin_comment_payload( $m );
	}

	return array(
		'board' => array(
			'id'    => (int) $board->id,
			'slug'  => $board->slug,
			'name'  => $board->name,
			'notes' => $board->notes,
			'goals' => $board->goals,
		),
		'lists' => array_map(
			static fn( $l ) => array(
				'id'   => (int) $l->id,
				'slug' => $l->slug,
				'name' => $l->name,
				'done' => in_array( $l->slug, intella_admin_done_lists(), true ),
			),
			$lists
		),
		'cards' => array_map( static fn( $c ) => intella_admin_card_payload( $c, $by_card[ (int) $c->id ] ?? array() ), $cards ),
		'bugs'  => array_map( 'intella_admin_bug_payload', $bugs ),
	);
}

// ----- Lists -----

/**
 * Add a list to a board.
 *
 * @param int    $board_id Board ID.
 * @param string $slug     Slug (used for colours and the "finished" lists).
 * @param string $name     Name.
 * @param int    $position Position.
 * @return int List ID.
 */
function intella_admin_insert_list( int $board_id, string $slug, string $name, int $position ): int {
	global $wpdb;
	$wpdb->insert(
		intella_admin_table( 'lists' ),
		array(
			'board_id' => $board_id,
			'slug'     => sanitize_title( $slug ),
			'name'     => intella_admin_clean_line( $name, 200 ),
			'position' => $position,
		)
	);
	return (int) $wpdb->insert_id;
}

/**
 * A list, only if it belongs to the board.
 *
 * @param int $board_id Board ID.
 * @param int $list_id  List ID.
 */
function intella_admin_get_list( int $board_id, int $list_id ): ?object {
	global $wpdb;
	$list = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . intella_admin_table( 'lists' ) . ' WHERE id = %d AND board_id = %d', $list_id, $board_id ) );
	return $list ? $list : null;
}

// ----- Cards -----

/**
 * A card as the board page needs it.
 *
 * @param object $c        Card row.
 * @param array  $comments Comment payloads.
 */
function intella_admin_card_payload( object $c, array $comments = array() ): array {
	return array(
		'id'       => (int) $c->id,
		'list_id'  => (int) $c->list_id,
		'title'    => $c->title,
		'notes'    => $c->notes,
		'assignee' => $c->assignee,
		'tag'      => $c->tag,
		'due'      => $c->due ? $c->due : '',
		'created'  => $c->created ? $c->created : '',
		'comments' => $comments,
	);
}

/**
 * One card row.
 *
 * @param int $card_id Card ID.
 */
function intella_admin_get_card( int $card_id ): ?object {
	global $wpdb;
	$card = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . intella_admin_table( 'cards' ) . ' WHERE id = %d', $card_id ) );
	return $card ? $card : null;
}

/**
 * Card fields from user input. Only keys that are present are returned.
 *
 * @param array $fields Raw fields.
 */
function intella_admin_card_fields( array $fields ): array {
	$row = array();
	if ( array_key_exists( 'title', $fields ) ) {
		$row['title'] = intella_admin_clean_line( $fields['title'], 500 );
	}
	if ( array_key_exists( 'notes', $fields ) ) {
		$row['notes'] = intella_admin_clean_html( $fields['notes'] );
	}
	if ( array_key_exists( 'assignee', $fields ) ) {
		$row['assignee'] = intella_admin_clean_line( $fields['assignee'], 100 );
	}
	if ( array_key_exists( 'tag', $fields ) ) {
		$row['tag'] = intella_admin_clean_line( $fields['tag'], 100 );
	}
	if ( array_key_exists( 'due', $fields ) ) {
		$row['due'] = intella_admin_clean_date( $fields['due'] );
	}
	return $row;
}

/**
 * Next free position at the end of a list.
 *
 * @param int $list_id List ID.
 */
function intella_admin_next_card_position( int $list_id ): int {
	global $wpdb;
	return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COALESCE( MAX(position) + 1, 0 ) FROM ' . intella_admin_table( 'cards' ) . ' WHERE list_id = %d', $list_id ) );
}

/**
 * Add a card to the end of a list.
 *
 * @param int   $board_id Board ID.
 * @param int   $list_id  List ID (must belong to the board).
 * @param array $fields   Card fields.
 * @return int Card ID.
 */
function intella_admin_create_card( int $board_id, int $list_id, array $fields = array() ): int {
	global $wpdb;
	$row = array_merge(
		array(
			'title'    => '',
			'notes'    => '',
			'assignee' => '',
			'tag'      => '',
			'due'      => null,
		),
		intella_admin_card_fields( $fields )
	);
	$wpdb->insert(
		intella_admin_table( 'cards' ),
		$row + array(
			'board_id'   => $board_id,
			'list_id'    => $list_id,
			'position'   => intella_admin_next_card_position( $list_id ),
			'created'    => intella_admin_clean_date( $fields['created'] ?? '' ) ?? current_time( 'Y-m-d' ),
			'created_by' => get_current_user_id(),
			'updated_at' => intella_admin_now(),
			'updated_by' => get_current_user_id(),
		)
	);
	return (int) $wpdb->insert_id;
}

/**
 * Update a card's fields. A new list_id moves it to the end of that list.
 *
 * @param object $card   Card row.
 * @param array  $fields Fields.
 */
function intella_admin_update_card( object $card, array $fields ): void {
	global $wpdb;
	$row = intella_admin_card_fields( $fields );
	if ( isset( $fields['list_id'] ) && (int) $fields['list_id'] !== (int) $card->list_id && intella_admin_get_list( (int) $card->board_id, (int) $fields['list_id'] ) ) {
		$row['list_id']  = (int) $fields['list_id'];
		$row['position'] = intella_admin_next_card_position( (int) $fields['list_id'] );
	}
	if ( $row ) {
		$row['updated_at'] = intella_admin_now();
		$row['updated_by'] = get_current_user_id();
		$wpdb->update( intella_admin_table( 'cards' ), $row, array( 'id' => (int) $card->id ) );
	}
}

/**
 * Move a card into a list and save that list's order.
 *
 * @param object $card    Card row.
 * @param int    $list_id Target list (must belong to the card's board).
 * @param int[]  $order   Card IDs of the target list, in their new order (including the moved card).
 */
function intella_admin_move_card( object $card, int $list_id, array $order ): bool {
	global $wpdb;
	if ( ! intella_admin_get_list( (int) $card->board_id, $list_id ) ) {
		return false;
	}
	$table = intella_admin_table( 'cards' );
	$wpdb->update(
		$table,
		array(
			'list_id'    => $list_id,
			'updated_at' => intella_admin_now(),
			'updated_by' => get_current_user_id(),
		),
		array( 'id' => (int) $card->id )
	);
	$position = 0;
	foreach ( array_unique( array_map( 'intval', $order ) ) as $id ) {
		$wpdb->query( $wpdb->prepare( "UPDATE {$table} SET position = %d WHERE id = %d AND list_id = %d", $position++, $id, $list_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}
	return true;
}

/**
 * Delete a card and its comments. Bugs linked to it keep their "related to" text.
 *
 * @param object $card Card row.
 */
function intella_admin_delete_card( object $card ): void {
	global $wpdb;
	$wpdb->delete( intella_admin_table( 'comments' ), array( 'card_id' => (int) $card->id ) );
	$wpdb->update( intella_admin_table( 'bugs' ), array( 'card_id' => null ), array( 'card_id' => (int) $card->id ) );
	$wpdb->delete( intella_admin_table( 'cards' ), array( 'id' => (int) $card->id ) );
}

// ----- Comments -----

/**
 * A comment as the board page needs it.
 *
 * @param object $m Comment row.
 */
function intella_admin_comment_payload( object $m ): array {
	return array(
		'id'     => (int) $m->id,
		'author' => $m->author,
		'at'     => intella_admin_iso( $m->created_at ),
		'text'   => $m->text,
	);
}

/**
 * Add a comment to a card.
 *
 * @param int         $card_id Card ID.
 * @param string      $text    Plain text (line breaks kept).
 * @param string      $author  Author's board name.
 * @param string|null $at      UTC datetime; now when null.
 * @return int Comment ID.
 */
function intella_admin_add_comment( int $card_id, string $text, string $author, ?string $at = null ): int {
	global $wpdb;
	$wpdb->insert(
		intella_admin_table( 'comments' ),
		array(
			'card_id'    => $card_id,
			'author'     => intella_admin_clean_line( $author, 100 ),
			'user_id'    => get_current_user_id(),
			'text'       => intella_admin_clean_text( $text ),
			'created_at' => $at ? $at : intella_admin_now(),
		)
	);
	return (int) $wpdb->insert_id;
}

/**
 * One comment, with its card's board ID.
 *
 * @param int $comment_id Comment ID.
 */
function intella_admin_get_comment( int $comment_id ): ?object {
	global $wpdb;
	$comment = $wpdb->get_row(
		$wpdb->prepare(
			'SELECT m.*, c.board_id FROM ' . intella_admin_table( 'comments' ) . ' m JOIN ' . intella_admin_table( 'cards' ) . ' c ON c.id = m.card_id WHERE m.id = %d',
			$comment_id
		)
	);
	return $comment ? $comment : null;
}

// ----- Bugs -----

/**
 * A bug as the board page needs it.
 *
 * @param object $b Bug row.
 */
function intella_admin_bug_payload( object $b ): array {
	return array(
		'id'          => (int) $b->id,
		'card_id'     => $b->card_id ? (int) $b->card_id : 0,
		'description' => $b->description,
		'related'     => $b->related,
		'status'      => $b->status,
		'due'         => $b->due ? $b->due : '',
		'assignee'    => $b->assignee,
		'created'     => $b->created ? $b->created : '',
	);
}

/**
 * One bug row.
 *
 * @param int $bug_id Bug ID.
 */
function intella_admin_get_bug( int $bug_id ): ?object {
	global $wpdb;
	$bug = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . intella_admin_table( 'bugs' ) . ' WHERE id = %d', $bug_id ) );
	return $bug ? $bug : null;
}

/**
 * Bug fields from user input. Only keys that are present are returned.
 *
 * @param int   $board_id Board ID (a linked card must belong to it).
 * @param array $fields   Raw fields.
 */
function intella_admin_bug_fields( int $board_id, array $fields ): array {
	$row = array();
	if ( array_key_exists( 'description', $fields ) ) {
		$row['description'] = intella_admin_clean_text( $fields['description'] );
	}
	if ( array_key_exists( 'related', $fields ) ) {
		$row['related'] = intella_admin_clean_line( $fields['related'] );
	}
	if ( array_key_exists( 'status', $fields ) ) {
		$row['status'] = isset( intella_admin_bug_statuses()[ $fields['status'] ] ) ? $fields['status'] : 'open';
	}
	if ( array_key_exists( 'due', $fields ) ) {
		$row['due'] = intella_admin_clean_date( $fields['due'] );
	}
	if ( array_key_exists( 'assignee', $fields ) ) {
		$row['assignee'] = intella_admin_clean_line( $fields['assignee'], 100 );
	}
	if ( array_key_exists( 'card_id', $fields ) ) {
		$card           = $fields['card_id'] ? intella_admin_get_card( (int) $fields['card_id'] ) : null;
		$row['card_id'] = ( $card && (int) $card->board_id === $board_id ) ? (int) $card->id : null;
	}
	return $row;
}

/**
 * Add a bug to the end of a board's bug tracker.
 *
 * @param int   $board_id Board ID.
 * @param array $fields   Bug fields.
 * @return int Bug ID.
 */
function intella_admin_create_bug( int $board_id, array $fields = array() ): int {
	global $wpdb;
	$table    = intella_admin_table( 'bugs' );
	$position = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COALESCE( MAX(position) + 1, 0 ) FROM {$table} WHERE board_id = %d", $board_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$row      = array_merge(
		array(
			'description' => '',
			'related'     => '',
			'status'      => 'open',
			'due'         => null,
			'assignee'    => '',
			'card_id'     => null,
		),
		intella_admin_bug_fields( $board_id, $fields )
	);
	$wpdb->insert(
		$table,
		$row + array(
			'board_id'   => $board_id,
			'position'   => $position,
			'created'    => intella_admin_clean_date( $fields['created'] ?? '' ) ?? current_time( 'Y-m-d' ),
			'updated_at' => intella_admin_now(),
		)
	);
	return (int) $wpdb->insert_id;
}

/**
 * Update a bug.
 *
 * @param object $bug    Bug row.
 * @param array  $fields Fields.
 */
function intella_admin_update_bug( object $bug, array $fields ): void {
	global $wpdb;
	$row = intella_admin_bug_fields( (int) $bug->board_id, $fields );
	if ( $row ) {
		$row['updated_at'] = intella_admin_now();
		$wpdb->update( intella_admin_table( 'bugs' ), $row, array( 'id' => (int) $bug->id ) );
	}
}
