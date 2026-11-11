<?php
/**
 * Small shared helpers used by templates and block render files.
 *
 * @package Intella
 */

defined( 'ABSPATH' ) || exit;

/**
 * Version string for a theme asset, used to bust browser caches.
 * It is the file's modified time, which changes whenever the file is edited or updated by `git pull`,
 * so visitors get the new CSS/JS straight after a deploy without anyone bumping a version number.
 *
 * @param string $relative_path Path inside the theme, e.g. 'assets/css/base.css'.
 */
function intella_asset_version( string $relative_path ): string {
	$file = INTELLA_DIR . '/' . ltrim( $relative_path, '/' );

	return file_exists( $file ) ? (string) filemtime( $file ) : INTELLA_VERSION;
}

/**
 * An icon from the sprite in assets/images/icons.svg (printed once per page by header.php).
 *
 * @param string $name  Icon name without the "i-" prefix, e.g. 'arrow-right'.
 * @param array  $attrs Extra attributes, e.g. array( 'class' => 'x', 'width' => 16, 'height' => 16 ).
 */
function intella_icon( string $name, array $attrs = array() ): string {
	$attrs = array_merge( array( 'viewBox' => '0 0 24 24' ), $attrs, array( 'aria-hidden' => 'true' ) );
	$html  = '';
	foreach ( $attrs as $key => $value ) {
		$html .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( (string) $value ) );
	}
	return sprintf( '<svg%s><use href="#i-%s"/></svg>', $html, esc_attr( $name ) );
}

/**
 * Inline text from a block field: allows the few tags the design uses inside headings and sentences
 * (italic accent words, bold, line breaks, links), strips everything else.
 *
 * @param string|null $text Field value.
 */
function intella_inline( ?string $text ): string {
	static $allowed = array(
		'em'     => array( 'class' => true ),
		'strong' => array( 'class' => true ),
		'span'   => array( 'class' => true ),
		'br'     => array(),
		'a'      => array(
			'href'   => true,
			'class'  => true,
			'target' => true,
			'rel'    => true,
		),
	);
	return wp_kses( (string) $text, $allowed );
}

/**
 * A "visual" field: hand-drawn HTML/SVG illustrations (dashboards, report mock-ups) stored in a block.
 * Only editors with the unfiltered_html capability can save one; it is printed as saved.
 *
 * @param string|null $html Field value.
 */
function intella_visual( ?string $html ): string {
	return (string) $html;
}

/**
 * Image from a block field. Fields store an attachment ID; WordPress adds srcset, sizes and lazy loading.
 *
 * @param int|string|null $id    Attachment ID.
 * @param string          $size  Image size.
 * @param array           $attrs Extra attributes (class, alt, loading, fetchpriority…).
 */
function intella_image( $id, string $size = 'large', array $attrs = array() ): string {
	$id = absint( $id );
	return $id ? wp_get_attachment_image( $id, $size, false, $attrs ) : '';
}

/**
 * A button link in the theme's style: <a class="button button--primary">Text <arrow></a>.
 *
 * @param array  $button  array( 'text' => '', 'url' => '' ).
 * @param string $classes Button classes.
 * @param bool   $arrow   Add the arrow icon after the text.
 */
function intella_button( array $button, string $classes = 'button button--primary', bool $arrow = true ): string {
	if ( empty( $button['text'] ) ) {
		return '';
	}
	return sprintf(
		'<a class="%s" href="%s">%s%s</a>',
		esc_attr( $classes ),
		esc_url( intella_url( $button['url'] ?? '' ) ),
		esc_html( $button['text'] ),
		$arrow ? ' ' . intella_icon( 'arrow-right' ) : ''
	);
}

/**
 * Turn a stored link into a full URL. Content stores site links as paths ("/contact/", "#free-review")
 * so the same content works locally and on the live domain.
 *
 * @param string|null $url Stored URL.
 */
function intella_url( ?string $url ): string {
	$url = trim( (string) $url );
	if ( '' === $url || str_starts_with( $url, '#' ) || preg_match( '#^[a-z][a-z0-9+.-]*:#i', $url ) ) {
		return $url;
	}
	return home_url( '/' . ltrim( $url, '/' ) );
}

/**
 * A site setting from Settings → Intella site (stored by the intella-core plugin).
 *
 * @param string $key     Setting key, e.g. 'phone'.
 * @param string $default Fallback.
 */
function intella_setting( string $key, string $default = '' ): string {
	$settings = get_option( 'intella_site', array() );
	return isset( $settings[ $key ] ) && '' !== $settings[ $key ] ? (string) $settings[ $key ] : $default;
}

/**
 * Inline SVG logo from assets/images/logos/<name>.svg (tool and platform logos used in menus and sections).
 *
 * @param string $name Logo file name without extension.
 */
function intella_logo_svg( string $name ): string {
	$name = sanitize_file_name( $name );
	$file = INTELLA_DIR . "/assets/images/logos/{$name}.svg";
	return ( $name && file_exists( $file ) ) ? trim( (string) file_get_contents( $file ) ) : '';
}

/**
 * A tool or platform logo by name ("Google Ads" → assets/images/tools/google-ads.svg).
 *
 * @param string $name Tool name.
 */
function intella_tool_logo( string $name ): string {
	$slug = trim( (string) preg_replace( '/[^a-z0-9]+/', '-', strtolower( $name ) ), '-' );
	$file = INTELLA_DIR . "/assets/images/tools/{$slug}.svg";
	return ( $slug && file_exists( $file ) ) ? trim( (string) file_get_contents( $file ) ) : '';
}

/**
 * Two-digit number for card counters: 1 → "01".
 *
 * @param int $n Number.
 */
function intella_num( int $n ): string {
	return str_pad( (string) $n, 2, '0', STR_PAD_LEFT );
}

/**
 * An image's natural width and height. SVGs have no size in WordPress's metadata, so it is read from the file.
 *
 * @param int $id      Attachment ID.
 * @param int $default Size to use when unknown.
 * @return int[] array( width, height )
 */
function intella_image_size( int $id, int $default = 300 ): array {
	$meta = wp_get_attachment_metadata( $id );
	if ( ! empty( $meta['width'] ) && ! empty( $meta['height'] ) ) {
		return array( (int) $meta['width'], (int) $meta['height'] );
	}
	$file = get_attached_file( $id );
	if ( $file && str_ends_with( strtolower( $file ), '.svg' ) && is_readable( $file ) ) {
		$svg = (string) file_get_contents( $file, false, null, 0, 2000 );
		if ( preg_match( '/\bwidth="([\d.]+)".*?\bheight="([\d.]+)"/s', $svg, $m ) || preg_match( '/viewBox="[\d.\s-]+?\s([\d.]+)\s([\d.]+)"/', $svg, $m ) ) {
			return array( (int) $m[1], (int) $m[2] );
		}
	}
	return array( $default, $default );
}

/**
 * A testimonial's photo (square: photos 300×300, small SVG placeholders 140×140, as designed) or their initials.
 *
 * @param array $person Shared item from intella_shared_item().
 */
function intella_avatar( array $person ): string {
	if ( ! empty( $person['image'] ) ) {
		$src  = wp_get_attachment_image_src( $person['image'], 'medium' );
		$side = 'image/svg+xml' === get_post_mime_type( $person['image'] ) ? 140 : 300;
		return sprintf( '<img src="%s" width="%d" height="%d" alt="" loading="lazy">', esc_url( $src ? $src[0] : '' ), $side, $side );
	}
	$initials = implode( '', array_map( static fn( $part ) => mb_substr( $part, 0, 1 ), array_slice( preg_split( '/\s+/', (string) $person['name'] ), 0, 2 ) ) );
	return '<span class="person__initials" aria-hidden="true">' . esc_html( strtoupper( $initials ) ) . '</span>';
}

/**
 * A section heading block: <div class="section-head section-head--center"><h2 …>…</h2><p class="section-lead">…</p></div>.
 *
 * @param array  $attributes Block attributes (title, lead).
 * @param string $id         Heading ID.
 * @param string $variant    Accent variant.
 */
function intella_section_head( array $attributes, string $id, string $variant = '' ): string {
	$html  = '<div class="section-head section-head--center">';
	$html .= '<h2 class="section-title" id="' . esc_attr( $id ) . '">' . intella_accent( (string) ( $attributes['title'] ?? '' ), $variant ) . '</h2>';
	if ( ! empty( $attributes['lead'] ) ) {
		$html .= '<p class="section-lead">' . intella_inline( $attributes['lead'] ) . '</p>';
	}
	return $html . '</div>';
}

/**
 * An image from the Media Library at its full size with exact width/height (cut-outs, collage photos).
 *
 * @param int   $id    Attachment ID.
 * @param array $attrs Attributes (class, alt…).
 */
function intella_image_full( int $id, array $attrs ): string {
	return $id ? wp_get_attachment_image( $id, 'full', false, $attrs + array( 'alt' => '', 'loading' => 'lazy', 'decoding' => 'async' ) ) : '';
}
