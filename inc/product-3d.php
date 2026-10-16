<?php
/**
 * Product helpers for the 3D presentation.
 *
 * The 3D model comes from the "3D Viewer" plugin by bPlugins. Optional extras
 * (finishes, stage colour, specs, a model fallback) can be supplied by any
 * plugin through these filters:
 *  - forma_3d_model_url     ( string $url, int $product_id )
 *  - forma_3d_finishes      ( array $finishes, int $product_id )
 *  - forma_3d_stage_color   ( string $hex, int $product_id )
 *  - forma_3d_product_specs ( array $label_value_pairs, int $product_id )
 *  - forma_3d_category_color( string $hex, int $term_id )
 *
 * @package Forma3D
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the 3D Viewer plugin is active and can render products.
 */
function forma_3d_bp3d_active() {
	return class_exists( '\BP3D\Woocommerce\Product' );
}

/**
 * First model URL the 3D Viewer plugin has for a product, or ''.
 *
 * @param int $product_id Product ID.
 */
function forma_3d_bp3d_model_url( $product_id ) {
	if ( ! forma_3d_bp3d_active() ) {
		return '';
	}
	$meta = get_post_meta( $product_id, '_bp3d_product_', true );
	$meta = \BP3D\Woocommerce\Product::withLegacyFreeModel( is_array( $meta ) ? $meta : array() );
	$src  = $meta['bp3d_models'][0]['model_src'] ?? '';
	if ( is_array( $src ) ) {
		$src = $src['url'] ?? '';
	}
	return is_string( $src ) ? trim( $src ) : '';
}

/**
 * URL of the product's GLB model, or ''.
 *
 * @param int $product_id Product ID.
 */
function forma_3d_get_model_url( $product_id ) {
	return (string) apply_filters( 'forma_3d_model_url', forma_3d_bp3d_model_url( $product_id ), $product_id );
}

/**
 * 3D Viewer plugin markup for the current global product, or ''.
 */
function forma_3d_bp3d_viewer_html() {
	global $product;
	if ( ! forma_3d_bp3d_active() || ! $product || ! forma_3d_bp3d_model_url( $product->get_id() ) ) {
		return '';
	}
	return \BP3D\Woocommerce\Product::instance()->get_3d_model_html( true, 'product-gallery' );
}

/*
 * Let the 3D Viewer fill the theme's product stage instead of using its fixed height.
 */
add_filter(
	'bp3d_woocommerce_model_attribute',
	function ( $data ) {
		if ( ( $data['placement'] ?? '' ) === 'product-gallery' && function_exists( 'is_product' ) && is_product() ) {
			$data['styles']['height']  = '100%';
			$data['styles']['bgColor'] = 'transparent';
		}
		return $data;
	}
);

/**
 * Finishes (colour swatches) for a product.
 *
 * @param int $product_id Product ID.
 * @return array<int, array<string, mixed>>
 */
function forma_3d_get_finishes( $product_id ) {
	$list = apply_filters( 'forma_3d_finishes', array(), $product_id );
	return is_array( $list ) ? array_values( $list ) : array();
}

/**
 * Background colour behind the product on cards and the 3D stage.
 *
 * @param int $product_id Product ID.
 */
function forma_3d_get_bg( $product_id ) {
	$bg = sanitize_hex_color( (string) apply_filters( 'forma_3d_stage_color', '#e6e0d6', $product_id ) );
	return $bg ? $bg : '#e6e0d6';
}

/**
 * Extra label/value rows for the "Materials & details" panel.
 *
 * @param int $product_id Product ID.
 * @return array<string, string>
 */
function forma_3d_product_specs( $product_id ) {
	$specs = apply_filters( 'forma_3d_product_specs', array(), $product_id );
	return is_array( $specs ) ? $specs : array();
}

/**
 * Compact data bundle passed to JavaScript (quick view, homepage stage).
 *
 * @param WC_Product $product Product.
 */
function forma_3d_product_js_data( $product ) {
	$id    = $product->get_id();
	$image = wp_get_attachment_image_url( $product->get_image_id(), 'woocommerce_single' );
	$terms = get_the_terms( $id, 'product_cat' );
	$cat   = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
	// Same convention as the 3D Viewer plugin: width = X, length = depth.
	$width  = $product->get_width();
	$depth  = $product->get_length();
	$height = $product->get_height();

	return array(
		'id'        => $id,
		'name'      => $product->get_name(),
		'url'       => get_permalink( $id ),
		'model'     => forma_3d_get_model_url( $id ),
		'poster'    => $image ? $image : '',
		'priceHtml' => $product->get_price_html(),
		'category'  => $cat,
		'finishes'  => forma_3d_get_finishes( $id ),
		'bg'        => forma_3d_get_bg( $id ),
		'excerpt'   => wp_strip_all_tags( $product->get_short_description() ),
		'dims'      => ( $width && $depth && $height ) ? sprintf( '%s × %s × %s %s', $width, $depth, $height, get_option( 'woocommerce_dimension_unit' ) ) : '',
		'canBuy'    => $product->is_purchasable() && $product->is_in_stock() && $product->is_type( 'simple' ),
	);
}
