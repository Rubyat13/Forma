<?php
/**
 * WooCommerce integration: styles, cart fragments, finish selection on cart items.
 *
 * @package Forma3D
 */

defined( 'ABSPATH' ) || exit;

// The theme ships its own styling for shop, product and account screens.
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

// No sidebar anywhere.
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

add_filter(
	'loop_shop_per_page',
	function () {
		return 12;
	}
);

add_filter(
	'woocommerce_breadcrumb_defaults',
	function ( $args ) {
		$args['delimiter']   = '<span class="crumbs__sep">/</span>';
		$args['wrap_before'] = '<nav class="crumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'forma-3d' ) . '">';
		$args['wrap_after']  = '</nav>';
		$args['home']        = __( 'Home', 'forma-3d' );
		return $args;
	}
);

/*
 * Header cart badge + drawer contents refresh after AJAX add-to-cart.
 */
add_filter(
	'woocommerce_add_to_cart_fragments',
	function ( $fragments ) {
		$fragments['span.cart-count'] = forma_3d_cart_count_html();
		ob_start();
		forma_3d_mini_cart();
		$fragments['div.drawer__content'] = ob_get_clean();
		return $fragments;
	}
);

/**
 * Header cart count badge markup.
 */
function forma_3d_cart_count_html() {
	$count = forma_3d_cart_count();
	return sprintf( '<span class="cart-count%1$s" data-count="%2$d">%2$d</span>', $count ? '' : ' is-empty', (int) $count );
}

/**
 * Side drawer cart contents.
 */
function forma_3d_mini_cart() {
	echo '<div class="drawer__content">';
	if ( WC()->cart && ! WC()->cart->is_empty() ) {
		echo '<ul class="mini-cart">';
		foreach ( WC()->cart->get_cart() as $key => $item ) {
			$product = $item['data'];
			if ( ! $product || ! $product->exists() || $item['quantity'] <= 0 ) {
				continue;
			}
			$link = $product->get_permalink( $item );
			$meta = wp_strip_all_tags( wc_get_formatted_cart_item_data( $item, true ) );
			?>
			<li class="mini-cart__item" style="--card-bg:<?php echo esc_attr( forma_3d_get_bg( ( $product->get_parent_id() ? $product->get_parent_id() : $product->get_id() ) ) ); ?>">
				<a class="mini-cart__thumb" href="<?php echo esc_url( $link ); ?>"><?php echo wp_kses_post( $product->get_image( 'woocommerce_gallery_thumbnail' ) ); ?></a>
				<div class="mini-cart__info">
					<a class="mini-cart__name" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
					<?php if ( $meta ) : ?>
						<span class="mini-cart__meta"><?php echo esc_html( $meta ); ?></span>
					<?php endif; ?>
					<span class="mini-cart__meta"><?php echo esc_html( sprintf( /* translators: %d quantity */ __( 'Qty %d', 'forma-3d' ), $item['quantity'] ) ); ?></span>
				</div>
				<div class="mini-cart__end">
					<span class="mini-cart__price"><?php echo wp_kses_post( WC()->cart->get_product_subtotal( $product, $item['quantity'] ) ); ?></span>
					<a class="mini-cart__remove" href="<?php echo esc_url( wc_get_cart_remove_url( $key ) ); ?>" data-cart-remove="<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Remove', 'forma-3d' ); ?></a>
				</div>
			</li>
			<?php
		}
		echo '</ul>';
		?>
		<div class="drawer__foot">
			<div class="drawer__total"><span><?php esc_html_e( 'Subtotal', 'forma-3d' ); ?></span><strong><?php echo wp_kses_post( WC()->cart->get_cart_subtotal() ); ?></strong></div>
			<?php $forma_3d_note = get_theme_mod( 'forma_3d_cart_note', '' ); ?>
			<?php if ( $forma_3d_note ) : ?>
				<p class="drawer__note"><?php echo esc_html( $forma_3d_note ); ?></p>
			<?php endif; ?>
			<a class="btn btn--block" href="<?php echo esc_url( wc_get_checkout_url() ); ?>"><?php esc_html_e( 'Checkout', 'forma-3d' ); ?></a>
			<a class="btn btn--ghost btn--block" href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'View cart', 'forma-3d' ); ?></a>
		</div>
		<?php
	} else {
		?>
		<div class="drawer__empty">
			<?php forma_3d_the_icon( 'bag' ); ?>
			<p><?php esc_html_e( 'Your cart is empty.', 'forma-3d' ); ?></p>
			<a class="btn" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Browse the collection', 'forma-3d' ); ?></a>
		</div>
		<?php
	}
	echo '</div>';
}

/**
 * Featured products for the homepage 3D showcase, in menu order.
 *
 * @param int $limit Max products.
 * @return WC_Product[]
 */
function forma_3d_showcase_products( $limit = 4 ) {
	return wc_get_products(
		array(
			'status'   => 'publish',
			'featured' => true,
			'limit'    => $limit,
			'orderby'  => 'menu_order',
			'order'    => 'ASC',
		)
	);
}
