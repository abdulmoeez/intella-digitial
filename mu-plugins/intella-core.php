<?php
/**
 * Plugin Name: Intella Core
 * Description: Site functionality that must survive a theme change: post types, custom fields, forms, schema.
 * Version:     0.1.0
 * Author:      Intella Digital
 * Text Domain: intella
 *
 * Must-use plugin: WordPress loads it automatically and it can't be deactivated from the admin.
 *
 * @package Intella_Core
 */

defined( 'ABSPATH' ) || exit;

define( 'INTELLA_CORE_DIR', __DIR__ . '/intella-core' );

require INTELLA_CORE_DIR . '/post-types.php';
