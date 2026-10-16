<?php
/**
 * Forma 3D theme bootstrap.
 *
 * @package Forma3D
 */

defined( 'ABSPATH' ) || exit;

define( 'FORMA_3D_VERSION', '1.0.2' );
define( 'FORMA_3D_DIR', get_template_directory() );
define( 'FORMA_3D_URI', get_template_directory_uri() );

require FORMA_3D_DIR . '/inc/setup.php';
require FORMA_3D_DIR . '/inc/assets.php';
require FORMA_3D_DIR . '/inc/product-3d.php';
require FORMA_3D_DIR . '/inc/template-tags.php';
require FORMA_3D_DIR . '/inc/sections.php';
require FORMA_3D_DIR . '/inc/blocks.php';
require FORMA_3D_DIR . '/inc/customizer.php';
require FORMA_3D_DIR . '/inc/patterns.php';

if ( class_exists( 'WooCommerce' ) ) {
	require FORMA_3D_DIR . '/inc/woocommerce.php';
}
