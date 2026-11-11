<?php
/**
 * Custom section: a one-off section stored as HTML (admins only can save it).
 * Its stylesheet (assets/css/custom/<css>.css) is loaded in <head> by intella_enqueue_page_block_styles(),
 * its scripts are loaded here, and any form inside is pointed at the lead handler.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

foreach ( intella_lines( $attributes['modules'] ) as $module ) {
	if ( str_starts_with( $module, '@intella/' ) ) {
		wp_enqueue_script_module( $module );
	}
}

$html = (string) $attributes['html'];

// Lead forms: send them to the form handler with its hidden fields. Search and filter forms are left alone.
if ( function_exists( 'intella_form_action' ) ) {
	$html = preg_replace_callback(
		'#<form\b([^>]*)>#i',
		static function ( array $m ): string {
			$attrs = $m[1];
			if ( preg_match( '/role="search"|data-page-search|filter-search/', $attrs ) || ! preg_match( '/method="post"/i', $attrs ) ) {
				return $m[0];
			}
			$attrs = preg_replace( '/\s(action|data-success)="[^"]*"/', '', $attrs );
			$name  = preg_match( '/data-form="([a-z0-9-]+)"/', $attrs, $f ) ? $f[1] : 'proposal';
			return '<form' . $attrs . ' action="' . esc_url( intella_form_action() ) . '">' . intella_form_hidden_fields( $name );
		},
		$html
	);
}

echo intella_visual( $html ); // phpcs:ignore WordPress.Security.EscapeOutput -- admin-only HTML field.
