<?php
/**
 * Small rendering helpers: icons, logo, product cards.
 *
 * @package Forma3D
 */

defined( 'ABSPATH' ) || exit;

/**
 * Inline SVG icon (stroke icons, 24px grid, currentColor).
 *
 * @param string $name Icon name.
 * @param string $extra_class Extra class.
 */
function forma_3d_icon( $name, $extra_class = '' ) {
	$paths = array(
		'bag'      => '<path d="M5 8h14l-1 12H6L5 8Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/>',
		'user'     => '<circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 4.5-6 8-6s6.5 2 8 6"/>',
		'search'   => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
		'arrow'    => '<path d="M4 12h16"/><path d="m14 6 6 6-6 6"/>',
		'arrow-ur' => '<path d="M7 17 17 7"/><path d="M8 7h9v9"/>',
		'cube'     => '<path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z"/><path d="m4 7.5 8 4.5 8-4.5"/><path d="M12 12v9"/>',
		'rotate'   => '<path d="M20 12a8 8 0 1 1-2.34-5.66"/><path d="M20 4v5h-5"/>',
		'ruler'    => '<path d="M3 17 17 3l4 4L7 21l-4-4Z"/><path d="m7 13 2 2M10 10l2 2M13 7l2 2"/>',
		'expand'   => '<path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/>',
		'ar'       => '<path d="M4 8V5a1 1 0 0 1 1-1h3M16 4h3a1 1 0 0 1 1 1v3M20 16v3a1 1 0 0 1-1 1h-3M8 20H5a1 1 0 0 1-1-1v-3"/><path d="m12 7 4 2.3v4.6L12 16l-4-2.1V9.3L12 7Z"/>',
		'close'    => '<path d="M6 6l12 12M18 6 6 18"/>',
		'plus'     => '<path d="M12 5v14M5 12h14"/>',
		'minus'    => '<path d="M5 12h14"/>',
		'truck'    => '<path d="M3 6h11v10H3zM14 10h4l3 3v3h-7"/><circle cx="7" cy="17.5" r="1.5"/><circle cx="17" cy="17.5" r="1.5"/>',
		'return'   => '<path d="M9 14 4 9l5-5"/><path d="M4 9h11a5 5 0 0 1 0 10h-3"/>',
		'shield'   => '<path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6l-8-3Z"/><path d="m9 12 2 2 4-4"/>',
		'menu'     => '<path d="M4 8h16M4 16h16"/>',
		'pause'    => '<path d="M9 6v12M15 6v12"/>',
		'play'     => '<path d="m8 5 11 7-11 7V5Z"/>',
		'reset'    => '<path d="M4 12a8 8 0 1 0 2.34-5.66"/><path d="M4 4v5h5"/>',
		'hand'     => '<path d="M8 13V5.5a1.5 1.5 0 0 1 3 0V12M11 11.5v-2a1.5 1.5 0 0 1 3 0V12M14 10.5a1.5 1.5 0 0 1 3 0V12M17 11.5a1.5 1.5 0 0 1 3 0V15a6 6 0 0 1-6 6h-1.5a6 6 0 0 1-4.8-2.4L4.6 15a1.5 1.5 0 0 1 2.3-1.9L8 14.5"/>',
		'check'    => '<path d="m5 12 5 5 9-10"/>',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return sprintf(
		'<svg class="icon icon--%1$s %2$s" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%3$s</svg>',
		esc_attr( $name ),
		esc_attr( $extra_class ),
		$paths[ $name ]
	);
}

/**
 * Site logo: the custom logo when set, otherwise the site title as a wordmark.
 */
function forma_3d_logo() {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	printf(
		'<a class="logo" href="%1$s" rel="home"><span class="logo__mark" aria-hidden="true"></span><span class="logo__word">%2$s</span></a>',
		esc_url( home_url( '/' ) ),
		esc_html( get_bloginfo( 'name' ) )
	);
}

/**
 * Allowed markup for the inline SVG icons.
 */
function forma_3d_svg_kses() {
	return array(
		'svg'    => array(
			'class'           => true,
			'viewbox'         => true,
			'width'           => true,
			'height'          => true,
			'fill'            => true,
			'stroke'          => true,
			'stroke-width'    => true,
			'stroke-linecap'  => true,
			'stroke-linejoin' => true,
			'aria-hidden'     => true,
			'focusable'       => true,
		),
		'path'   => array( 'd' => true ),
		'circle' => array(
			'cx' => true,
			'cy' => true,
			'r'  => true,
		),
	);
}

/**
 * Echo an icon.
 *
 * @param string $name  Icon name.
 * @param string $extra_class Extra class.
 */
function forma_3d_the_icon( $name, $extra_class = '' ) {
	echo wp_kses( forma_3d_icon( $name, $extra_class ), forma_3d_svg_kses() );
}

/**
 * Finish swatch dots.
 *
 * @param array $finishes Finishes.
 * @param int   $max      Max dots before "+n".
 */
function forma_3d_swatch_dots( $finishes, $max = 5 ) {
	if ( count( $finishes ) < 2 ) {
		return;
	}
	echo '<span class="dots" aria-label="' . esc_attr( sprintf( /* translators: %d finish count */ _n( '%d finish', '%d finishes', count( $finishes ), 'forma-3d' ), count( $finishes ) ) ) . '">';
	foreach ( array_slice( $finishes, 0, $max ) as $f ) {
		printf( '<i style="--c:%s" title="%s"></i>', esc_attr( $f['color'] ), esc_attr( $f['name'] ) );
	}
	if ( count( $finishes ) > $max ) {
		printf( '<em>+%d</em>', count( $finishes ) - $max );
	}
	echo '</span>';
}

/**
 * Product card used on the shop grid, homepage and related products.
 *
 * @param WC_Product $product Product.
 * @param array      $args    Options: 'heading' tag.
 */
function forma_3d_product_card( $product, $args = array() ) {
	if ( ! $product ) {
		return;
	}
	$id       = $product->get_id();
	$link     = get_permalink( $id );
	$heading  = $args['heading'] ?? 'h3';
	$gallery  = $product->get_gallery_image_ids();
	$alt_img  = $gallery ? end( $gallery ) : 0;
	$terms    = get_the_terms( $id, 'product_cat' );
	$cat      = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
	$finishes = forma_3d_get_finishes( $id );
	$model    = forma_3d_get_model_url( $id );
	$data     = forma_3d_product_js_data( $product );
	?>
	<article class="p-card" style="--card-bg:<?php echo esc_attr( forma_3d_get_bg( $id ) ); ?>" data-reveal>
		<a class="p-card__media" href="<?php echo esc_url( $link ); ?>" tabindex="-1" aria-hidden="true">
			<?php
			echo wp_get_attachment_image(
				$product->get_image_id(),
				'woocommerce_thumbnail',
				false,
				array(
					'class'   => 'p-card__img p-card__img--main',
					'loading' => 'lazy',
					'sizes'   => '(max-width: 700px) 90vw, 30vw',
				)
			);
			if ( $alt_img ) {
				echo wp_get_attachment_image(
					$alt_img,
					'woocommerce_thumbnail',
					false,
					array(
						'class'   => 'p-card__img p-card__img--alt',
						'loading' => 'lazy',
						'sizes'   => '(max-width: 700px) 90vw, 30vw',
					)
				);
			}
			?>
		</a>
		<div class="p-card__flags">
			<?php if ( $model ) : ?>
				<span class="flag flag--3d"><?php forma_3d_the_icon( 'cube' ); ?>3D</span>
			<?php endif; ?>
			<?php if ( $product->is_on_sale() ) : ?>
				<span class="flag flag--sale"><?php esc_html_e( 'Sale', 'forma-3d' ); ?></span>
			<?php elseif ( strtotime( $product->get_date_created() ) > strtotime( '-30 days' ) && $product->is_featured() ) : ?>
				<span class="flag"><?php esc_html_e( 'New', 'forma-3d' ); ?></span>
			<?php endif; ?>
		</div>
		<?php if ( $model ) : ?>
			<button type="button" class="p-card__quick" data-quick-view="<?php echo esc_attr( wp_json_encode( $data ) ); ?>">
				<?php forma_3d_the_icon( 'rotate' ); ?>
				<span><?php esc_html_e( 'View in 3D', 'forma-3d' ); ?></span>
			</button>
		<?php endif; ?>
		<div class="p-card__body">
			<div class="p-card__info">
				<?php if ( $cat ) : ?>
					<p class="p-card__cat"><?php echo esc_html( $cat ); ?></p>
				<?php endif; ?>
				<<?php echo tag_escape( $heading ); ?> class="p-card__title"><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></<?php echo tag_escape( $heading ); ?>>
				<?php forma_3d_swatch_dots( $finishes ); ?>
			</div>
			<div class="p-card__side">
				<span class="p-card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
				<?php if ( $data['canBuy'] ) : ?>
					<button type="button" class="p-card__add js-add-to-cart" data-product-id="<?php echo esc_attr( $id ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s product name */ __( 'Add %s to cart', 'forma-3d' ), $product->get_name() ) ); ?>">
						<?php forma_3d_the_icon( 'plus' ); ?>
					</button>
				<?php endif; ?>
			</div>
		</div>
	</article>
	<?php
}

/**
 * Cart item count for the header badge.
 */
function forma_3d_cart_count() {
	return ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
}

/**
 * Product category links for navigation (top-level, non-empty, excluding Uncategorized).
 */
function forma_3d_nav_categories() {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'parent'     => 0,
			'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
			// WooCommerce sorts product_cat by its drag-and-drop term order by default.
		)
	);
	return is_wp_error( $terms ) ? array() : $terms;
}
