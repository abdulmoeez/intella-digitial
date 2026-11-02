<?php
/**
 * Creates and upgrades the database tables, the Intella Team role and the starting boards.
 * Must-use plugins have no activation hook, so this runs once whenever INTELLA_ADMIN_VERSION changes.
 *
 * @package Intella_Admin
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'intella_admin_maybe_install', 20 ); // After the /admin/ rewrite rules are added.

/**
 * Run the installer when the plugin version changes, then rebuild the rewrite rules.
 */
function intella_admin_maybe_install(): void {
	if ( get_option( 'intella_admin_version' ) === INTELLA_ADMIN_VERSION ) {
		return;
	}
	intella_admin_install();
	update_option( 'intella_admin_version', INTELLA_ADMIN_VERSION, false );
	flush_rewrite_rules( false );
}

/**
 * Tables, role, capabilities, starting boards and team colours. Safe to run again.
 */
function intella_admin_install(): void {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$charset = $wpdb->get_charset_collate();

	// dbDelta needs this exact layout: one column per line, two spaces after PRIMARY KEY.
	dbDelta(
		"CREATE TABLE {$wpdb->prefix}intella_boards (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  slug varchar(100) NOT NULL,
  name varchar(200) NOT NULL,
  notes longtext NOT NULL,
  goals longtext NOT NULL,
  position int(11) NOT NULL DEFAULT 0,
  created_at datetime NOT NULL,
  updated_at datetime NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY slug (slug)
) {$charset};
CREATE TABLE {$wpdb->prefix}intella_lists (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  board_id bigint(20) unsigned NOT NULL,
  slug varchar(100) NOT NULL,
  name varchar(200) NOT NULL,
  position int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY  (id),
  KEY board_id (board_id)
) {$charset};
CREATE TABLE {$wpdb->prefix}intella_cards (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  board_id bigint(20) unsigned NOT NULL,
  list_id bigint(20) unsigned NOT NULL,
  title varchar(500) NOT NULL DEFAULT '',
  notes longtext NOT NULL,
  assignee varchar(100) NOT NULL DEFAULT '',
  tag varchar(100) NOT NULL DEFAULT '',
  due date DEFAULT NULL,
  created date DEFAULT NULL,
  position int(11) NOT NULL DEFAULT 0,
  created_by bigint(20) unsigned NOT NULL DEFAULT 0,
  updated_at datetime NOT NULL,
  updated_by bigint(20) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY  (id),
  KEY board_id (board_id),
  KEY list_position (list_id,position)
) {$charset};
CREATE TABLE {$wpdb->prefix}intella_comments (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  card_id bigint(20) unsigned NOT NULL,
  author varchar(100) NOT NULL DEFAULT '',
  user_id bigint(20) unsigned NOT NULL DEFAULT 0,
  text longtext NOT NULL,
  created_at datetime NOT NULL,
  PRIMARY KEY  (id),
  KEY card_id (card_id)
) {$charset};
CREATE TABLE {$wpdb->prefix}intella_bugs (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  board_id bigint(20) unsigned NOT NULL,
  card_id bigint(20) unsigned DEFAULT NULL,
  description text NOT NULL,
  related varchar(255) NOT NULL DEFAULT '',
  status varchar(20) NOT NULL DEFAULT 'open',
  due date DEFAULT NULL,
  assignee varchar(100) NOT NULL DEFAULT '',
  created date DEFAULT NULL,
  position int(11) NOT NULL DEFAULT 0,
  updated_at datetime NOT NULL,
  PRIMARY KEY  (id),
  KEY board_id (board_id),
  KEY card_id (card_id)
) {$charset};"
	);

	// Team members who aren't WordPress administrators: they can open the panel and edit their profile, nothing else.
	if ( ! get_role( INTELLA_ADMIN_ROLE ) ) {
		add_role(
			INTELLA_ADMIN_ROLE,
			__( 'Intella Team', 'intella' ),
			array(
				'read'            => true,
				INTELLA_ADMIN_CAP => true,
			)
		);
	}
	get_role( 'administrator' )?->add_cap( INTELLA_ADMIN_CAP );

	// The two project hubs that existed before the panel. Their cards are brought in with the importer.
	if ( ! intella_admin_get_boards() ) {
		intella_admin_create_board( 'Intella Digital' );
		intella_admin_create_board( 'Bizticle' );
	}

	intella_admin_seed_team_colors();
}

/**
 * Give Amit and Moeez the colours they had in the project hubs (blue and green), matched on first name,
 * display name or login. Only fills in people who have no colour yet.
 */
function intella_admin_seed_team_colors(): void {
	$known = array(
		'amit'  => array( 'Amit', 'blue' ),
		'moeez' => array( 'Moeez', 'green' ),
	);
	foreach ( intella_admin_team() as $user ) {
		if ( get_user_meta( $user->ID, '_intella_color', true ) ) {
			continue;
		}
		foreach ( array( $user->first_name, $user->display_name, $user->user_login ) as $name ) {
			$first = strtolower( strtok( trim( (string) $name ), " \t" ) ?: '' );
			if ( isset( $known[ $first ] ) ) {
				update_user_meta( $user->ID, '_intella_board_name', $known[ $first ][0] );
				update_user_meta( $user->ID, '_intella_color', $known[ $first ][1] );
				break;
			}
		}
	}
}
