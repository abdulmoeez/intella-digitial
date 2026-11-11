<?php
/**
 * Settings → Intella site: details shown site-wide (top bar, header buttons, footer, contact details, social links).
 * Stored as one option, 'intella_site'. Templates read values with intella_setting( 'key' ).
 *
 * @package Intella_Core
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', 'intella_settings_menu' );
add_action( 'admin_init', 'intella_settings_register' );

/**
 * The settings, grouped into sections. Each field: key => array( label, type, help ).
 */
function intella_settings_fields(): array {
	return array(
		'contact' => array(
			'title'  => __( 'Contact details', 'intella' ),
			'fields' => array(
				'email'   => array( __( 'Email', 'intella' ), 'email' ),
				'phone'   => array( __( 'Phone (as shown)', 'intella' ), 'text', __( 'e.g. +1 (431) 293-2026. The call link is made from the digits.', 'intella' ) ),
				'address' => array( __( 'Address (footer)', 'intella' ), 'text' ),
				'form_to' => array( __( 'Send form enquiries to', 'intella' ), 'email', __( 'Leave empty to use the site admin email.', 'intella' ) ),
			),
		),
		'social'  => array(
			'title'  => __( 'Social profiles', 'intella' ),
			'fields' => array(
				'linkedin'  => array( 'LinkedIn', 'url' ),
				'facebook'  => array( 'Facebook', 'url' ),
				'instagram' => array( 'Instagram', 'url' ),
			),
		),
		'topbar'  => array(
			'title'  => __( 'Top bar (above the header)', 'intella' ),
			'fields' => array(
				'topbar_text'      => array( __( 'Message', 'intella' ), 'text', __( 'Leave empty to hide the top bar.', 'intella' ) ),
				'topbar_link_text' => array( __( 'Link text', 'intella' ), 'text' ),
				'topbar_link_url'  => array( __( 'Link URL', 'intella' ), 'text', __( 'A path like /contact/ or #free-review.', 'intella' ) ),
				'topbar_version'   => array( __( 'Message version', 'intella' ), 'text', __( 'Visitors who closed the bar stay closed until this changes. Change it when the message changes (e.g. 2).', 'intella' ) ),
			),
		),
		'header'  => array(
			'title'  => __( 'Header buttons', 'intella' ),
			'fields' => array(
				'header_contact_text' => array( __( 'Text link', 'intella' ), 'text' ),
				'header_contact_url'  => array( __( 'Text link URL', 'intella' ), 'text' ),
				'header_cta_text'     => array( __( 'Button', 'intella' ), 'text' ),
				'header_cta_url'      => array( __( 'Button URL', 'intella' ), 'text' ),
			),
		),
		'footer'  => array(
			'title'  => __( 'Footer', 'intella' ),
			'fields' => array(
				'footer_cta_title'   => array( __( 'Call to action heading', 'intella' ), 'text', __( 'Wrap the accent words in <em>…</em>.', 'intella' ) ),
				'footer_cta_text'    => array( __( 'Call to action text', 'intella' ), 'textarea' ),
				'footer_cta_1_text'  => array( __( 'First button', 'intella' ), 'text' ),
				'footer_cta_1_url'   => array( __( 'First button URL', 'intella' ), 'text' ),
				'footer_cta_2_text'  => array( __( 'Second button', 'intella' ), 'text' ),
				'footer_cta_2_url'   => array( __( 'Second button URL', 'intella' ), 'text' ),
				'footer_about'       => array( __( 'About text', 'intella' ), 'textarea' ),
			),
		),
	);
}

/**
 * Add the page under Settings.
 */
function intella_settings_menu(): void {
	add_options_page( __( 'Intella site', 'intella' ), __( 'Intella site', 'intella' ), 'manage_options', 'intella-site', 'intella_settings_page' );
}

/**
 * Register the option and its fields with the Settings API.
 */
function intella_settings_register(): void {
	register_setting(
		'intella_site',
		'intella_site',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'intella_settings_sanitize',
			'default'           => array(),
		)
	);

	foreach ( intella_settings_fields() as $section_id => $section ) {
		add_settings_section( "intella_{$section_id}", $section['title'], '__return_null', 'intella-site' );
		foreach ( $section['fields'] as $key => $field ) {
			add_settings_field(
				$key,
				esc_html( $field[0] ),
				'intella_settings_field',
				'intella-site',
				"intella_{$section_id}",
				array(
					'key'       => $key,
					'type'      => $field[1],
					'help'      => $field[2] ?? '',
					'label_for' => "intella-{$key}",
				)
			);
		}
	}
}

/**
 * Print one field.
 *
 * @param array $args Field arguments.
 */
function intella_settings_field( array $args ): void {
	$value = get_option( 'intella_site', array() )[ $args['key'] ] ?? '';
	$name  = sprintf( 'intella_site[%s]', $args['key'] );

	if ( 'textarea' === $args['type'] ) {
		printf( '<textarea id="%s" name="%s" rows="3" class="large-text">%s</textarea>', esc_attr( $args['label_for'] ), esc_attr( $name ), esc_textarea( $value ) );
	} else {
		printf( '<input id="%s" name="%s" type="%s" value="%s" class="regular-text">', esc_attr( $args['label_for'] ), esc_attr( $name ), esc_attr( 'url' === $args['type'] ? 'url' : ( 'email' === $args['type'] ? 'email' : 'text' ) ), esc_attr( $value ) );
	}
	if ( $args['help'] ) {
		printf( '<p class="description">%s</p>', esc_html( $args['help'] ) );
	}
}

/**
 * Clean every value before saving.
 *
 * @param mixed $input Submitted values.
 */
function intella_settings_sanitize( $input ): array {
	$clean = array();
	foreach ( intella_settings_fields() as $section ) {
		foreach ( $section['fields'] as $key => $field ) {
			$value = is_array( $input ) && isset( $input[ $key ] ) ? (string) $input[ $key ] : '';
			switch ( $field[1] ) {
				case 'email':
					$clean[ $key ] = sanitize_email( $value );
					break;
				case 'url':
					$clean[ $key ] = esc_url_raw( $value );
					break;
				case 'textarea':
					$clean[ $key ] = sanitize_textarea_field( $value );
					break;
				default:
					$clean[ $key ] = wp_kses( $value, array( 'em' => array() ) );
			}
		}
	}
	return $clean;
}

/**
 * The settings screen.
 */
function intella_settings_page(): void {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Intella site', 'intella' ); ?></h1>
		<p><?php esc_html_e( 'Details used across the whole site. Menus are under Appearance → Menus; testimonials, clients and team members have their own screens.', 'intella' ); ?></p>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'intella_site' );
			do_settings_sections( 'intella-site' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}
