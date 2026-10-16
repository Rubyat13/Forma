<?php
/**
 * Front-end styles and scripts.
 *
 * @package Forma3D
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'forma_3d_enqueue_assets' );

/**
 * Register and enqueue theme assets per template.
 */
function forma_3d_enqueue_assets() {
	$v      = FORMA_3D_VERSION;
	$vendor = FORMA_3D_URI . '/assets/vendor/';
	// Original (unminified) library builds sit next to the minified ones; load them when debugging.
	$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_enqueue_style( 'forma-3d-main', FORMA_3D_URI . '/assets/css/main.css', array(), $v );
	if ( is_admin_bar_showing() ) {
		wp_add_inline_style( 'forma-3d-main', '.site-header{top:var(--wp-admin--admin-bar--height,32px)}' );
	}

	wp_register_script( 'lenis', $vendor . 'lenis' . $min . '.js', array(), '1.1.13', true );
	wp_enqueue_script( 'forma-3d-app', FORMA_3D_URI . '/assets/js/app.js', array( 'lenis' ), $v, true );

	wp_localize_script(
		'forma-3d-app',
		'forma3dSettings',
		array(
			'ajaxUrl'     => class_exists( 'WC_AJAX' ) ? WC_AJAX::get_endpoint( '%%endpoint%%' ) : '',
			'modelViewer' => $vendor . 'model-viewer' . $min . '.js',
			'cartUrl'     => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '',
			'i18n'        => array(
				'error'    => __( 'Could not add to cart. Please try again.', 'forma-3d' ),
				'noAr'     => __( 'Open this page on a phone or tablet to place it in your room.', 'forma-3d' ),
				/* translators: %s: product name. */
				'modelAlt' => __( '3D model of %s', 'forma-3d' ),
				/* translators: %s: product dimensions, e.g. "82 × 99 × 102 cm". */
				'dims'     => __( 'W × D × H — %s', 'forma-3d' ),
			),
		)
	);

	if ( forma_3d_uses_stage() ) {
		wp_register_script_module( 'three', $vendor . 'three/three.module' . $min . '.js', array(), '0.169.0' );
		wp_enqueue_script_module( 'forma-3d-home', FORMA_3D_URI . '/assets/js/home.js', array( 'three' ), $v );
	}

	if ( function_exists( 'is_product' ) && is_product() ) {
		wp_enqueue_script( 'forma-3d-product', FORMA_3D_URI . '/assets/js/product.js', array( 'forma-3d-app' ), $v, true );
		// The 3D Viewer plugin brings its own model-viewer build; only load ours when it does not render this product.
		if ( ! forma_3d_bp3d_model_url( get_queried_object_id() ) && forma_3d_get_model_url( get_queried_object_id() ) ) {
			wp_enqueue_script_module( 'forma-3d-model-viewer', $vendor . 'model-viewer' . $min . '.js', array(), '4.0.0' );
		}
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}

/*
 * model-viewer reads its meshopt decoder location before the module runs.
 */
add_action(
	'wp_head',
	function () {
		wp_print_inline_script_tag(
			'self.ModelViewerElement=Object.assign(self.ModelViewerElement||{},{meshoptDecoderLocation:' . wp_json_encode( FORMA_3D_URI . '/assets/vendor/meshopt_decoder.js' ) . '});'
		);
	},
	1
);
