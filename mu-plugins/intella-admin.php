<?php
/**
 * Plugin Name: Intella Admin
 * Description: Intella Digital's team panel at /admin/: project boards, bug trackers and the team's admins.
 * Version:     0.1.0
 * Author:      Intella Digital
 * Text Domain: intella
 *
 * Must-use plugin. It works on its own (no theme or other plugin needed), so it can run on the live
 * version 1 site as well as on version 2. Deploy: copy this file and the intella-admin/ folder into
 * wp-content/mu-plugins/. The database tables are created on the first page load after that.
 *
 * @package Intella_Admin
 */

defined( 'ABSPATH' ) || exit;

define( 'INTELLA_ADMIN_VERSION', '0.1.0' );
define( 'INTELLA_ADMIN_DIR', __DIR__ . '/intella-admin' );
// Built from WPMU_PLUGIN_URL rather than plugins_url( __FILE__ ), which breaks when mu-plugins is a symlink.
define( 'INTELLA_ADMIN_URL', WPMU_PLUGIN_URL . '/intella-admin' );

require INTELLA_ADMIN_DIR . '/access.php';
require INTELLA_ADMIN_DIR . '/data.php';
require INTELLA_ADMIN_DIR . '/install.php';
require INTELLA_ADMIN_DIR . '/import.php';
require INTELLA_ADMIN_DIR . '/rest.php';
require INTELLA_ADMIN_DIR . '/actions.php';
require INTELLA_ADMIN_DIR . '/router.php';
