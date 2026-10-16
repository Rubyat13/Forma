<?php
/**
 * Theme supports, menus and widget areas.
 *
 * @package Forma3D
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $content_width ) ) {
	$content_width = 760; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
}

add_action( 'after_setup_theme', 'forma_3d_setup' );

/**
 * Register theme features.
 */
function forma_3d_setup() {
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 80,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support(
		'custom-background',
		array(
			'default-color' => 'eeeae3',
		)
	);
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );
	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 600,
			'single_image_width'    => 1200,
			'product_grid'          => array(
				'default_columns' => 3,
				'default_rows'    => 4,
			),
		)
	);

	register_nav_menus(
		array(
			'primary'  => __( 'Primary', 'forma-3d' ),
			'footer-1' => __( 'Footer column 1', 'forma-3d' ),
			'footer-2' => __( 'Footer column 2', 'forma-3d' ),
			'footer-3' => __( 'Footer column 3', 'forma-3d' ),
		)
	);
}

add_filter(
	'body_class',
	function ( $classes ) {
		if ( function_exists( 'forma_3d_uses_stage' ) && forma_3d_uses_stage() ) {
			$classes[] = 'has-3d-stage';
		}
		return $classes;
	}
);

add_action( 'widgets_init', 'forma_3d_widgets_init' );

/**
 * Footer widget area (shown as an extra footer column).
 */
function forma_3d_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Footer', 'forma-3d' ),
			'id'            => 'footer-1',
			'description'   => __( 'Widgets shown as a column in the footer.', 'forma-3d' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);
}

add_action( 'init', 'forma_3d_block_styles' );

/**
 * Extra styles for core blocks.
 */
function forma_3d_block_styles() {
	register_block_style(
		'core/group',
		array(
			'name'  => 'forma-3d-card',
			'label' => __( 'Card', 'forma-3d' ),
		)
	);
	register_block_style(
		'core/image',
		array(
			'name'  => 'forma-3d-rounded',
			'label' => __( 'Rounded', 'forma-3d' ),
		)
	);
	register_block_style(
		'core/button',
		array(
			'name'  => 'forma-3d-arrow',
			'label' => __( 'With arrow', 'forma-3d' ),
		)
	);
}
