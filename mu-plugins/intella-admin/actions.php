<?php
/**
 * Form posts from the panel pages (add a board, add / edit / remove admins). Each form posts back to its
 * own page with a nonce; the router calls intella_admin_handle_post() before showing the page, and the
 * result is shown as a notice after a redirect.
 *
 * @package Intella_Admin
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handle a form post, then redirect back with a notice. Does nothing for GET requests.
 *
 * @param string $view The page the form was on.
 */
function intella_admin_handle_post( string $view ): void {
	if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || empty( $_POST['intella_action'] ) ) {
		return;
	}
	$action = sanitize_key( wp_unslash( $_POST['intella_action'] ) );
	check_admin_referer( 'intella_admin_' . $action );

	$result = match ( $action ) {
		'add_board'    => intella_admin_action_add_board(),
		'add_admin'    => intella_admin_action_add_admin(),
		'update_admin' => intella_admin_action_update_admin(),
		'remove_admin' => intella_admin_action_remove_admin(),
		default        => new WP_Error( 'intella_unknown', __( 'Unknown action.', 'intella' ) ),
	};

	$back = 'dashboard' === $view ? intella_admin_url() : intella_admin_url( $view . '/' );
	if ( is_string( $result ) && str_starts_with( $result, 'http' ) ) {
		$back   = $result;
		$result = '';
	}
	set_transient( 'intella_admin_notice_' . get_current_user_id(), is_wp_error( $result ) ? array( 'error', $result->get_error_message() ) : array( 'ok', $result ), 60 );
	wp_safe_redirect( $back );
	exit;
}

/**
 * The notice left by the last form post, shown once.
 *
 * @return array{0: string, 1: string}|null Type (ok / error) and message.
 */
function intella_admin_take_notice(): ?array {
	$key    = 'intella_admin_notice_' . get_current_user_id();
	$notice = get_transient( $key );
	delete_transient( $key );
	return ( is_array( $notice ) && '' !== $notice[1] ) ? $notice : null;
}

/**
 * A posted text field.
 *
 * @param string $key Field name.
 */
function intella_admin_post_field( string $key ): string {
	return isset( $_POST[ $key ] ) ? trim( sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification -- checked in intella_admin_handle_post().
}

/**
 * Add a board, then go to it.
 *
 * @return string|WP_Error The new board's URL.
 */
function intella_admin_action_add_board() {
	$name = intella_admin_post_field( 'board_name' );
	if ( '' === $name ) {
		return new WP_Error( 'intella_empty', __( 'Give the board a name.', 'intella' ) );
	}
	$board = intella_admin_get_board( intella_admin_create_board( $name ) );
	return intella_admin_url( 'boards/' . $board->slug . '/' );
}

/**
 * Add an admin: an existing WordPress user (matched by email) gets access; anyone else gets a new
 * account with the Intella Team role and an email with a link to set their password.
 *
 * @return string|WP_Error Notice.
 */
function intella_admin_action_add_admin() {
	if ( ! intella_admin_can_manage() ) {
		return new WP_Error( 'intella_forbidden', __( 'Only WordPress administrators can add admins.', 'intella' ) );
	}
	$name  = intella_admin_post_field( 'name' );
	$email = sanitize_email( intella_admin_post_field( 'email' ) );
	$color = intella_admin_post_field( 'color' );
	if ( '' === $name || ! is_email( $email ) ) {
		return new WP_Error( 'intella_invalid', __( 'Enter a name and a valid email address.', 'intella' ) );
	}

	$user = get_user_by( 'email', $email );
	if ( $user ) {
		intella_admin_grant( $user );
		$message = sprintf( /* translators: %s: name */ __( '%s already had a WordPress account and can now open the admin panel.', 'intella' ), $name );
	} else {
		$login = sanitize_user( strtolower( strtok( $name, ' ' ) ), true );
		$base  = '' !== $login ? $login : 'team';
		for ( $i = 2; username_exists( $login ) || '' === $login; $i++ ) {
			$login = $base . $i;
		}
		$parts   = explode( ' ', $name, 2 );
		$user_id = wp_insert_user(
			array(
				'user_login'   => $login,
				'user_email'   => $email,
				'user_pass'    => wp_generate_password( 24 ),
				'display_name' => $name,
				'first_name'   => $parts[0],
				'last_name'    => $parts[1] ?? '',
				'role'         => INTELLA_ADMIN_ROLE,
			)
		);
		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}
		wp_new_user_notification( $user_id, null, 'user' ); // Emails a "set your password" link.
		$user    = get_user_by( 'id', $user_id );
		$message = sprintf( /* translators: 1: name, 2: username */ __( 'Added %1$s (username %2$s). They have been emailed a link to set their password.', 'intella' ), $name, $login );
	}

	update_user_meta( $user->ID, '_intella_board_name', strtok( $name, ' ' ) );
	update_user_meta( $user->ID, '_intella_color', isset( intella_admin_colors()[ $color ] ) ? $color : 'grey' );
	return $message;
}

/**
 * Change an admin's board name and colour.
 *
 * @return string|WP_Error Notice.
 */
function intella_admin_action_update_admin() {
	$user = get_user_by( 'id', (int) intella_admin_post_field( 'user_id' ) );
	if ( ! $user || ( ! intella_admin_can_manage() && get_current_user_id() !== $user->ID ) ) {
		return new WP_Error( 'intella_forbidden', __( 'You can only change your own details.', 'intella' ) );
	}
	$board_name = intella_admin_post_field( 'board_name' );
	$color      = intella_admin_post_field( 'color' );
	if ( '' !== $board_name ) {
		update_user_meta( $user->ID, '_intella_board_name', mb_substr( $board_name, 0, 100 ) );
	}
	if ( isset( intella_admin_colors()[ $color ] ) ) {
		update_user_meta( $user->ID, '_intella_color', $color );
	}
	return sprintf( /* translators: %s: name */ __( 'Saved %s.', 'intella' ), intella_admin_board_name( $user ) );
}

/**
 * Take someone's panel access away (their WordPress account stays).
 *
 * @return string|WP_Error Notice.
 */
function intella_admin_action_remove_admin() {
	$user = get_user_by( 'id', (int) intella_admin_post_field( 'user_id' ) );
	if ( ! intella_admin_can_manage() ) {
		return new WP_Error( 'intella_forbidden', __( 'Only WordPress administrators can remove admins.', 'intella' ) );
	}
	if ( ! $user || get_current_user_id() === $user->ID ) {
		return new WP_Error( 'intella_self', __( "You can't remove yourself.", 'intella' ) );
	}
	intella_admin_revoke( $user );
	return sprintf( /* translators: %s: name */ __( '%s can no longer open the admin panel. Their WordPress account is unchanged.', 'intella' ), intella_admin_board_name( $user ) );
}
