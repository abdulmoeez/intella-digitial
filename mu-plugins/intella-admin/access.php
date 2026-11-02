<?php
/**
 * Who can use the panel, and the per-person settings shown on boards (board name and colour).
 *
 * Access is the `intella_panel` capability. WordPress administrators get it through their role, and
 * team members without full WordPress rights get the "Intella Team" role. Access can be taken away from
 * a single administrator with a per-user "deny" of the capability, so the Admins page can manage everyone.
 *
 * @package Intella_Admin
 */

defined( 'ABSPATH' ) || exit;

const INTELLA_ADMIN_CAP  = 'intella_panel';
const INTELLA_ADMIN_ROLE = 'intella_team';

/**
 * Colours a person can have on the boards. Keys are stored in user meta; the CSS has a class for each.
 *
 * @return array<string, string> Key => label.
 */
function intella_admin_colors(): array {
	return array(
		'blue'   => __( 'Blue', 'intella' ),
		'green'  => __( 'Green', 'intella' ),
		'purple' => __( 'Purple', 'intella' ),
		'orange' => __( 'Orange', 'intella' ),
		'pink'   => __( 'Pink', 'intella' ),
		'teal'   => __( 'Teal', 'intella' ),
		'grey'   => __( 'Grey', 'intella' ),
	);
}

/**
 * Can this user open the panel?
 *
 * @param int $user_id User ID; 0 for the current user.
 */
function intella_admin_can_access( int $user_id = 0 ): bool {
	return $user_id ? user_can( $user_id, INTELLA_ADMIN_CAP ) : current_user_can( INTELLA_ADMIN_CAP );
}

/**
 * Can the current user add and remove admins and import board data? Only WordPress administrators.
 */
function intella_admin_can_manage(): bool {
	return current_user_can( INTELLA_ADMIN_CAP ) && current_user_can( 'manage_options' );
}

/**
 * Everyone who can open the panel, sorted by board name.
 *
 * @return WP_User[]
 */
function intella_admin_team(): array {
	$users = get_users(
		array(
			'role__in' => array( 'administrator', INTELLA_ADMIN_ROLE ),
			'orderby'  => 'display_name',
		)
	);
	$users = array_values( array_filter( $users, static fn( WP_User $u ) => $u->has_cap( INTELLA_ADMIN_CAP ) ) );
	usort( $users, static fn( $a, $b ) => strcasecmp( intella_admin_board_name( $a ), intella_admin_board_name( $b ) ) );
	return $users;
}

/**
 * The short name used for a person on cards and bugs (e.g. "Amit").
 *
 * @param WP_User $user User.
 */
function intella_admin_board_name( WP_User $user ): string {
	$name = (string) get_user_meta( $user->ID, '_intella_board_name', true );
	if ( '' !== $name ) {
		return $name;
	}
	return $user->first_name ? $user->first_name : $user->display_name;
}

/**
 * A person's colour key (see intella_admin_colors()).
 *
 * @param WP_User $user User.
 */
function intella_admin_user_color( WP_User $user ): string {
	$color = (string) get_user_meta( $user->ID, '_intella_color', true );
	return isset( intella_admin_colors()[ $color ] ) ? $color : 'grey';
}

/**
 * The team as the boards need it: name and colour for each person.
 *
 * @return array<int, array{name: string, color: string}>
 */
function intella_admin_people(): array {
	return array_map(
		static fn( WP_User $u ) => array(
			'name'  => intella_admin_board_name( $u ),
			'color' => intella_admin_user_color( $u ),
		),
		intella_admin_team()
	);
}

/**
 * Give a user access to the panel.
 *
 * @param WP_User $user User.
 */
function intella_admin_grant( WP_User $user ): void {
	$user->remove_cap( INTELLA_ADMIN_CAP ); // Clears a previous per-user deny.
	if ( ! $user->has_cap( INTELLA_ADMIN_CAP ) ) {
		$user->add_role( INTELLA_ADMIN_ROLE );
	}
}

/**
 * Take a user's panel access away. Their WordPress account and other roles stay as they are.
 *
 * @param WP_User $user User.
 */
function intella_admin_revoke( WP_User $user ): void {
	$user->remove_role( INTELLA_ADMIN_ROLE );
	if ( $user->has_cap( INTELLA_ADMIN_CAP ) ) {
		$user->add_cap( INTELLA_ADMIN_CAP, false ); // Administrators: deny for this user only.
	}
}
