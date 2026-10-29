<?php
/**
 * Theme bootstrap. Only loads files from inc/; keep logic out of this file.
 *
 * @package Intella
 */

defined( 'ABSPATH' ) || exit;

define( 'INTELLA_VERSION', '0.1.0' );
define( 'INTELLA_DIR', get_template_directory() );
define( 'INTELLA_URI', get_template_directory_uri() );

foreach ( array( 'helpers', 'setup', 'cleanup', 'enqueue', 'blocks' ) as $intella_file ) {
	require INTELLA_DIR . "/inc/{$intella_file}.php";
}
