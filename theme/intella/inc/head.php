<?php
/**
 * Extra <head> output: favicons and the top bar check that must run before the page paints.
 *
 * @package Intella
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', 'intella_head_icons', 2 );
add_action( 'wp_head', 'intella_topbar_check', 1 );

/**
 * Favicons (theme files). If a Site Icon is set in Settings → General, WordPress prints that instead.
 */
function intella_head_icons(): void {
	if ( has_site_icon() ) {
		return;
	}
	$uri = INTELLA_URI . '/assets/images/';
	printf( '<link rel="icon" href="%s" type="image/svg+xml">' . "\n", esc_url( $uri . 'favicon.svg' ) );
	printf( '<link rel="icon" href="%s" sizes="96x96" type="image/png">' . "\n", esc_url( $uri . 'favicon-96x96.png' ) );
	printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( $uri . 'apple-touch-icon.png' ) );
}

/**
 * The browser storage key for "this visitor closed the top bar", including the message version.
 */
function intella_topbar_key(): string {
	return 'intella-topbar-' . sanitize_key( intella_setting( 'topbar_version', '1' ) );
}

/**
 * Hide the top bar straight away for visitors who closed it, so it doesn't flash on load.
 * This one tiny inline script is the exception to "no inline JS": it has to run before the first paint.
 */
function intella_topbar_check(): void {
	if ( ! intella_setting( 'topbar_text' ) ) {
		return;
	}
	wp_print_inline_script_tag(
		sprintf( "try{if(localStorage.getItem(%s)==='closed')document.documentElement.classList.add('topbar-closed')}catch(e){}", wp_json_encode( intella_topbar_key() ) )
	);
}
