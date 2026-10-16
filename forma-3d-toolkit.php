<?php
/**
 * Plugin Name:       Forma 3D Toolkit
 * Description:       Companion plugin for the Forma 3D theme: homepage blocks for the block editor, product finishes and stage colours, .glb uploads and one-click demo store import.
 * Version:           1.0.1
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Al Rubyat
 * Author URI:        https://profiles.wordpress.org/rubyat13/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       forma-3d-toolkit
 *
 * @package Forma3DToolkit
 */

defined( 'ABSPATH' ) || exit;

define( 'FORMA_3D_TK_VERSION', '1.0.1' );
define( 'FORMA_3D_TK_DIR', plugin_dir_path( __FILE__ ) );
define( 'FORMA_3D_TK_URI', plugin_dir_url( __FILE__ ) );

require FORMA_3D_TK_DIR . 'includes/product-fields.php';
require FORMA_3D_TK_DIR . 'includes/blocks.php';

if ( is_admin() ) {
	require FORMA_3D_TK_DIR . 'includes/importer.php';
}
