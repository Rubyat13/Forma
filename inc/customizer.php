<?php
/**
 * Customizer: store text the theme shows (footer call to action, product
 * highlights, delivery note). Everything store-specific is empty by default.
 *
 * @package Forma3D
 */

defined( 'ABSPATH' ) || exit;

add_action( 'customize_register', 'forma_3d_customize_register' );

/**
 * Register Customizer settings.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function forma_3d_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'forma_3d_options',
		array(
			'title'    => __( 'Forma 3D options', 'forma-3d' ),
			'priority' => 130,
		)
	);

	$fields = array(
		'forma_3d_footer_cta'         => array( 'checkbox', true, __( 'Show the footer call to action', 'forma-3d' ), 'forma_3d_sanitize_checkbox' ),
		'forma_3d_footer_cta_eyebrow' => array( 'text', __( 'Showroom in your pocket', 'forma-3d' ), __( 'Footer call to action: small heading', 'forma-3d' ), 'sanitize_text_field' ),
		'forma_3d_footer_cta_title'   => array( 'text', __( 'Turn it over.<br><em>Then take it home.</em>', 'forma-3d' ), __( 'Footer call to action: headline (<em> and <br> allowed)', 'forma-3d' ), 'forma_3d_sanitize_inline' ),
		'forma_3d_footer_cta_button'  => array( 'text', __( 'Explore the collection', 'forma-3d' ), __( 'Footer call to action: button text (links to the shop)', 'forma-3d' ), 'sanitize_text_field' ),
		'forma_3d_footer_about'       => array( 'textarea', '', __( 'Footer text (defaults to the site tagline)', 'forma-3d' ), 'sanitize_textarea_field' ),
		'forma_3d_product_perks'      => array( 'textarea', '', __( 'Product page highlights, one per line (e.g. delivery time, returns, warranty)', 'forma-3d' ), 'sanitize_textarea_field' ),
		'forma_3d_delivery_text'      => array( 'textarea', '', __( 'Product page "Delivery & returns" text (hidden when empty)', 'forma-3d' ), 'sanitize_textarea_field' ),
		'forma_3d_care_text'          => array( 'textarea', '', __( 'Product page care instructions (hidden when empty)', 'forma-3d' ), 'sanitize_textarea_field' ),
		'forma_3d_cart_note'          => array( 'text', '', __( 'Note under the cart total (hidden when empty)', 'forma-3d' ), 'sanitize_text_field' ),
		'forma_3d_shop_ar'            => array( 'checkbox', true, __( 'Show the "Try it in your room" section on shop pages', 'forma-3d' ), 'forma_3d_sanitize_checkbox' ),
	);

	foreach ( $fields as $id => $f ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $f[1],
				'sanitize_callback' => $f[3],
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'type'    => $f[0],
				'label'   => $f[2],
				'section' => 'forma_3d_options',
			)
		);
	}
}

/**
 * Sanitize a checkbox.
 *
 * @param mixed $value Value.
 */
function forma_3d_sanitize_checkbox( $value ) {
	return (bool) $value;
}

/**
 * Sanitize short text that may contain <em>, <strong> and <br>.
 *
 * @param string $value Value.
 */
function forma_3d_sanitize_inline( $value ) {
	return wp_kses( (string) $value, forma_3d_inline_kses() );
}

/**
 * Product highlight lines from the Customizer.
 *
 * @return string[]
 */
function forma_3d_product_perks() {
	$lines = preg_split( '/\r\n|\r|\n/', (string) get_theme_mod( 'forma_3d_product_perks', '' ) );
	return array_values( array_filter( array_map( 'trim', $lines ) ) );
}
