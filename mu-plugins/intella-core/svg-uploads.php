<?php
/**
 * Allow SVG uploads (client and tool logos) for administrators only, and strip anything executable on upload.
 *
 * @package Intella_Core
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'upload_mimes', 'intella_svg_mime' );
add_filter( 'wp_check_filetype_and_ext', 'intella_svg_filetype', 10, 4 );
add_filter( 'wp_handle_upload_prefilter', 'intella_svg_sanitize' );

/**
 * Add SVG to the allowed types for users who can already add raw HTML.
 *
 * @param array $mimes Allowed types.
 */
function intella_svg_mime( array $mimes ): array {
	if ( current_user_can( 'unfiltered_html' ) ) {
		$mimes['svg'] = 'image/svg+xml';
	}
	return $mimes;
}

/**
 * WordPress can't detect SVG from file contents; trust the extension for allowed users.
 *
 * @param array  $data     File data.
 * @param string $file     Path.
 * @param string $filename Name.
 * @param mixed  $mimes    Allowed types.
 */
function intella_svg_filetype( array $data, string $file, string $filename, $mimes ): array {
	if ( current_user_can( 'unfiltered_html' ) && str_ends_with( strtolower( $filename ), '.svg' ) ) {
		$data['ext']  = 'svg';
		$data['type'] = 'image/svg+xml';
	}
	return $data;
}

/**
 * Remove scripts, event handlers and external references from an uploaded SVG.
 *
 * @param array $file Upload.
 */
function intella_svg_sanitize( array $file ): array {
	if ( 'image/svg+xml' !== ( $file['type'] ?? '' ) || ! is_readable( $file['tmp_name'] ) ) {
		return $file;
	}
	$svg   = (string) file_get_contents( $file['tmp_name'] );
	$clean = preg_replace(
		array( '#<script\b.*?</script>#is', '#<foreignObject\b.*?</foreignObject>#is', '#\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\')#i', '#(href|xlink:href)\s*=\s*("|\')\s*javascript:[^"\']*\2#i' ),
		'',
		$svg
	);
	if ( ! str_contains( $clean, '<svg' ) ) {
		$file['error'] = __( 'This SVG could not be read.', 'intella' );
		return $file;
	}
	file_put_contents( $file['tmp_name'], $clean );
	return $file;
}
