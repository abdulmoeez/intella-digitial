<?php
/**
 * Plugin Name: Intella Core
 * Description: Site functionality that must survive a theme change: post types, shared content, settings, forms, schema.
 * Version:     0.2.0
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
require INTELLA_CORE_DIR . '/shared-content.php';
require INTELLA_CORE_DIR . '/settings.php';
require INTELLA_CORE_DIR . '/svg-uploads.php';
require INTELLA_CORE_DIR . '/schema.php';
require INTELLA_CORE_DIR . '/forms.php';
