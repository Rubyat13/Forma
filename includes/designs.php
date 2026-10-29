<?php
/**
 * Store designs: colour, type and shape presets for the Forma 3D theme.
 *
 * "Original" is the theme's own design. "Noir" (dark, gold, high-contrast
 * serif) and "Soft" (oat and sage, terracotta, rounded serif, arched cards)
 * load one extra stylesheet after the theme's. Importing a starter site
 * switches to its design; Appearance > Customize > Forma 3D options changes it.
 *
 * @package Forma3DToolkit
 */

defined( 'ABSPATH' ) || exit;

/**
 * The store designs, keyed by ID.
 *
 * @return array<string, array{name: string, desc: string}>
 */
function forma_3d_tk_designs() {
	return array(
		'original' => array(
			'name' => __( 'Original', 'forma-3d-toolkit' ),
			'desc' => __( 'Warm stone, ink and terracotta: the theme as designed.', 'forma-3d-toolkit' ),
		),
		'noir'     => array(
			'name' => __( 'Noir', 'forma-3d-toolkit' ),
			'desc' => __( 'A dark showroom with gold accents and a high-contrast serif.', 'forma-3d-toolkit' ),
		),
		'soft'     => array(
			'name' => __( 'Soft', 'forma-3d-toolkit' ),
			'desc' => __( 'Oat and sage with terracotta, a rounded serif and arched cards.', 'forma-3d-toolkit' ),
		),
	);
}

/**
 * The active store design ID.
 */
function forma_3d_tk_design() {
	$design = (string) get_option( 'forma_3d_tk_design', 'original' );
	return array_key_exists( $design, forma_3d_tk_designs() ) ? $design : 'original';
}

/**
 * Sanitize a store design ID.
 *
 * @param mixed $value Value.
 */
function forma_3d_tk_sanitize_design( $value ) {
	$value = sanitize_key( (string) $value );
	return array_key_exists( $value, forma_3d_tk_designs() ) ? $value : 'original';
}

/**
 * Whether the active theme is Forma 3D (or a child theme of it).
 */
function forma_3d_tk_is_forma_theme() {
	return 'forma-3d' === get_template();
}

// Load the design's stylesheet after the theme's main stylesheet.
add_action(
	'wp_enqueue_scripts',
	function () {
		$design = forma_3d_tk_design();
		if ( 'original' === $design || ! forma_3d_tk_is_forma_theme() ) {
			return;
		}
		wp_enqueue_style( 'forma-3d-tk-design', FORMA_3D_TK_URI . 'assets/designs/' . $design . '.css', array( 'forma-3d-main' ), FORMA_3D_TK_VERSION );
	},
	20
);

add_filter(
	'body_class',
	function ( $classes ) {
		if ( forma_3d_tk_is_forma_theme() ) {
			$classes[] = 'forma-design-' . forma_3d_tk_design();
		}
		return $classes;
	}
);

// Appearance > Customize: pick the store design (in the theme's "Forma 3D options" section).
add_action(
	'customize_register',
	function ( $wp_customize ) {
		if ( ! forma_3d_tk_is_forma_theme() ) {
			return;
		}
		$section = $wp_customize->get_section( 'forma_3d_options' ) ? 'forma_3d_options' : 'forma_3d_tk_design';
		if ( 'forma_3d_tk_design' === $section ) {
			$wp_customize->add_section(
				'forma_3d_tk_design',
				array(
					'title'    => __( 'Store design', 'forma-3d-toolkit' ),
					'priority' => 130,
				)
			);
		}
		$wp_customize->add_setting(
			'forma_3d_tk_design',
			array(
				'type'              => 'option',
				'default'           => 'original',
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => 'forma_3d_tk_sanitize_design',
			)
		);
		$choices = array();
		foreach ( forma_3d_tk_designs() as $id => $design ) {
			$choices[ $id ] = $design['name'] . ' — ' . $design['desc'];
		}
		$wp_customize->add_control(
			'forma_3d_tk_design',
			array(
				'type'        => 'radio',
				'label'       => __( 'Store design', 'forma-3d-toolkit' ),
				'description' => __( 'Colours, fonts and shapes for the whole store. Importing a starter site in Forma Setup switches to its design.', 'forma-3d-toolkit' ),
				'section'     => $section,
				'priority'    => 1,
				'choices'     => $choices,
			)
		);
	},
	20
);
