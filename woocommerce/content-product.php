<?php
/**
 * Product card within loops (shop, shortcodes, related).
 *
 * Overrides woocommerce/templates/content-product.php.
 *
 * @package Forma3D
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! is_a( $product, WC_Product::class ) || ! $product->is_visible() ) {
	return;
}
?>
<li <?php wc_product_class( 'products__item', $product ); ?>>
	<?php forma_3d_product_card( $product, array( 'heading' => 'h2' ) ); ?>
</li>
