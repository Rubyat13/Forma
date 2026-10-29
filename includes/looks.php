<?php
/**
 * "Try the Look" block: a carousel of styled outfits. Each look lists its
 * pieces ("+" adds one) and a "complete the look" bundle that adds them all
 * with a discount applied in the cart.
 *
 * The bundle discount travels with each cart item as a signed token (the
 * products, the percentage and the look name, signed with the site's auth
 * salt), so shoppers cannot change it.
 *
 * @package Forma3DToolkit
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'forma_3d_tk_register_looks_block' );

/**
 * Register the block and its front-end assets.
 */
function forma_3d_tk_register_looks_block() {
	wp_register_style( 'forma-3d-tk-looks', FORMA_3D_TK_URI . 'assets/looks.css', array(), FORMA_3D_TK_VERSION );
	wp_register_script( 'forma-3d-tk-looks', FORMA_3D_TK_URI . 'assets/looks.js', array(), FORMA_3D_TK_VERSION, true );
	wp_localize_script(
		'forma-3d-tk-looks',
		'FORMA_LOOKS',
		array(
			'ajaxUrl' => class_exists( 'WC_AJAX' ) ? WC_AJAX::get_endpoint( 'add_to_cart' ) : '',
			'cartUrl' => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '',
			'i18n'    => array(
				'adding' => __( 'Adding…', 'forma-3d-toolkit' ),
				'added'  => __( 'Added to bag', 'forma-3d-toolkit' ),
				'error'  => __( 'Could not add to bag. Please try again.', 'forma-3d-toolkit' ),
				/* translators: 1: look number, 2: number of looks, 3: look name. */
				'status' => __( 'Look %1$d of %2$d: %3$s', 'forma-3d-toolkit' ),
			),
		)
	);
	register_block_type(
		FORMA_3D_TK_DIR . 'blocks/looks',
		array( 'render_callback' => 'forma_3d_tk_render_looks' )
	);
}

/**
 * A published product from an ID or a slug.
 *
 * @param int|string $ref Product ID or slug.
 * @return WC_Product|null
 */
function forma_3d_tk_looks_product( $ref ) {
	if ( ! function_exists( 'wc_get_product' ) ) {
		return null;
	}
	if ( is_numeric( $ref ) ) {
		$product = wc_get_product( (int) $ref );
	} else {
		$post    = get_page_by_path( sanitize_title( (string) $ref ), OBJECT, 'product' );
		$product = $post ? wc_get_product( $post->ID ) : null;
	}
	return ( $product && 'publish' === $product->get_status() ) ? $product : null;
}

/**
 * Whether a product can be added straight from the look panel.
 *
 * @param WC_Product $product Product.
 */
function forma_3d_tk_looks_buyable( $product ) {
	return $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock();
}

/**
 * Signed token for a look bundle.
 *
 * @param int[]  $ids      Product IDs in the bundle.
 * @param int    $discount Discount in percent.
 * @param string $name     Look name.
 */
function forma_3d_tk_look_token( $ids, $discount, $name ) {
	$ids = array_values( array_unique( array_map( 'intval', $ids ) ) );
	sort( $ids );
	$payload = wp_json_encode(
		array(
			'p' => $ids,
			'd' => (int) $discount,
			'n' => sanitize_text_field( $name ),
		)
	);
	return $payload . '|' . hash_hmac( 'sha256', $payload, wp_salt( 'auth' ) );
}

/**
 * Read and verify a look token.
 *
 * @param string $token Token from forma_3d_tk_look_token().
 * @return array{p: int[], d: int, n: string, key: string}|null
 */
function forma_3d_tk_read_look_token( $token ) {
	$at = strrpos( (string) $token, '|' );
	if ( false === $at ) {
		return null;
	}
	$payload = substr( $token, 0, $at );
	$sig     = substr( $token, $at + 1 );
	if ( ! hash_equals( hash_hmac( 'sha256', $payload, wp_salt( 'auth' ) ), $sig ) ) {
		return null;
	}
	$data = json_decode( $payload, true );
	if ( ! is_array( $data ) || empty( $data['p'] ) || ! is_array( $data['p'] ) ) {
		return null;
	}
	return array(
		'p'   => array_map( 'intval', $data['p'] ),
		'd'   => max( 0, min( 90, (int) ( $data['d'] ?? 0 ) ) ),
		'n'   => sanitize_text_field( (string) ( $data['n'] ?? '' ) ),
		'key' => substr( $sig, 0, 16 ),
	);
}

/**
 * Render the block.
 *
 * @param array $attributes Block attributes.
 */
function forma_3d_tk_render_looks( $attributes ) {
	$a        = wp_parse_args(
		(array) $attributes,
		array(
			'eyebrow'    => '',
			'title'      => '',
			'intro'      => '',
			'looks'      => array(),
			'discount'   => 10,
			'buttonText' => '',
			'align'      => '',
		)
	);
	$discount = max( 0, min( 90, (int) $a['discount'] ) );
	$inline   = array(
		'em'     => array(),
		'strong' => array(),
		'br'     => array(),
	);

	$looks = array();
	foreach ( (array) $a['looks'] as $look ) {
		$products = array();
		foreach ( (array) ( $look['products'] ?? array() ) as $ref ) {
			$product = forma_3d_tk_looks_product( $ref );
			if ( $product ) {
				$products[] = $product;
			}
		}
		$image = '';
		if ( ! empty( $look['imageId'] ) ) {
			$image = (string) wp_get_attachment_image_url( (int) $look['imageId'], 'large' );
		}
		if ( ! $image && ! empty( $look['image'] ) ) {
			$image = (string) $look['image'];
		}
		if ( $image || $products ) {
			$looks[] = array(
				'name'     => (string) ( $look['name'] ?? '' ),
				'image'    => $image,
				'products' => $products,
			);
		}
	}
	if ( ! $looks ) {
		return '';
	}

	$wrapper = get_block_wrapper_attributes(
		array(
			'class'      => 'looks' . ( $a['align'] ? ' align' . sanitize_html_class( $a['align'] ) : '' ),
			'data-looks' => '',
		)
	);
	$count   = count( $looks );
	ob_start();
	?>
	<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() escapes. ?>>
		<div class="looks__inner">
			<header class="looks__head">
				<?php if ( $a['eyebrow'] ) : ?>
					<p class="looks__eyebrow"><?php echo wp_kses( $a['eyebrow'], $inline ); ?></p>
				<?php endif; ?>
				<?php if ( $a['title'] ) : ?>
					<h2 class="looks__title"><?php echo wp_kses( $a['title'], $inline ); ?></h2>
				<?php endif; ?>
				<?php if ( $a['intro'] ) : ?>
					<p class="looks__intro"><?php echo wp_kses( $a['intro'], $inline ); ?></p>
				<?php endif; ?>
			</header>

			<div class="looks__body">
				<div class="looks__stage">
					<ul class="looks__track" aria-label="<?php esc_attr_e( 'Looks', 'forma-3d-toolkit' ); ?>">
						<?php foreach ( $looks as $i => $look ) : ?>
							<li class="looks__fig" data-look-fig style="--o:<?php echo (int) $i; ?>;--a:<?php echo (int) $i; ?>">
								<button type="button" class="looks__pick" data-look-go="<?php echo (int) $i; ?>">
									<?php if ( $look['image'] ) : ?>
										<img src="<?php echo esc_url( $look['image'] ); ?>" alt="<?php echo esc_attr( $look['name'] ); ?>" loading="<?php echo 0 === $i ? 'eager' : 'lazy'; ?>" decoding="async">
									<?php endif; ?>
									<span class="screen-reader-text">
										<?php
										/* translators: %s: look name. */
										echo esc_html( sprintf( __( 'Show the look "%s"', 'forma-3d-toolkit' ), $look['name'] ) );
										?>
									</span>
								</button>
							</li>
						<?php endforeach; ?>
					</ul>

					<div class="looks__nav">
						<button type="button" class="looks__arrow" data-look-prev aria-label="<?php esc_attr_e( 'Previous look', 'forma-3d-toolkit' ); ?>">
							<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path d="M19 12H5m6-6-6 6 6 6" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
						</button>
						<ol class="looks__steps">
							<?php foreach ( $looks as $i => $look ) : ?>
								<li class="looks__step" data-look-step>
									<button type="button" data-look-go="<?php echo (int) $i; ?>">
										<span class="looks__num"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
										<span class="looks__name"><?php echo esc_html( $look['name'] ); ?></span>
									</button>
								</li>
							<?php endforeach; ?>
						</ol>
						<button type="button" class="looks__arrow" data-look-next aria-label="<?php esc_attr_e( 'Next look', 'forma-3d-toolkit' ); ?>">
							<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
						</button>
					</div>
					<p class="screen-reader-text" data-look-status aria-live="polite"></p>
				</div>

				<aside class="looks__panel">
					<?php foreach ( $looks as $i => $look ) : ?>
						<?php
						$buyable = array_filter( $look['products'], 'forma_3d_tk_looks_buyable' );
						$total   = 0.0;
						foreach ( $buyable as $product ) {
							$total += (float) wc_get_price_to_display( $product );
						}
						$bundle = $total * ( 1 - $discount / 100 );
						?>
						<div class="looks__set" data-look-set data-look-name="<?php echo esc_attr( $look['name'] ); ?>" <?php echo 0 === $i ? '' : 'hidden'; ?>>
							<div class="looks__set-head">
								<h3><?php esc_html_e( 'In this look', 'forma-3d-toolkit' ); ?></h3>
								<span>
									<?php
									/* translators: %d: number of pieces. */
									echo esc_html( sprintf( _n( '%d piece', '%d pieces', count( $look['products'] ), 'forma-3d-toolkit' ), count( $look['products'] ) ) );
									?>
								</span>
							</div>
							<ul class="looks__items">
								<?php foreach ( $look['products'] as $product ) : ?>
									<?php
									$terms = get_the_terms( $product->get_id(), 'product_cat' );
									$cat   = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
									?>
									<li class="looks__item">
										<a class="looks__thumb" href="<?php echo esc_url( $product->get_permalink() ); ?>" tabindex="-1" aria-hidden="true">
											<?php echo wp_get_attachment_image( $product->get_image_id(), 'woocommerce_thumbnail', false, array( 'loading' => 'lazy' ) ); ?>
										</a>
										<div class="looks__info">
											<?php if ( $cat ) : ?>
												<span class="looks__cat"><?php echo esc_html( $cat ); ?></span>
											<?php endif; ?>
											<a class="looks__pname" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
											<span class="looks__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
										</div>
										<?php if ( forma_3d_tk_looks_buyable( $product ) ) : ?>
											<button type="button" class="looks__add js-add-to-cart" data-product-id="<?php echo (int) $product->get_id(); ?>">
												<span aria-hidden="true">+</span>
												<span class="screen-reader-text">
													<?php
													/* translators: %s: product name. */
													echo esc_html( sprintf( __( 'Add %s to bag', 'forma-3d-toolkit' ), $product->get_name() ) );
													?>
												</span>
											</button>
										<?php else : ?>
											<a class="looks__add" href="<?php echo esc_url( $product->get_permalink() ); ?>">
												<span aria-hidden="true">→</span>
												<span class="screen-reader-text">
													<?php
													/* translators: %s: product name. */
													echo esc_html( sprintf( __( 'Choose options for %s', 'forma-3d-toolkit' ), $product->get_name() ) );
													?>
												</span>
											</a>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul>
							<?php if ( count( $buyable ) > 1 ) : ?>
								<div class="looks__bundle">
									<div class="looks__bundle-text">
										<strong><?php esc_html_e( 'Complete the look', 'forma-3d-toolkit' ); ?></strong>
										<span>
											<?php
											if ( $discount ) {
												/* translators: 1: number of pieces, 2: discount in percent. */
												echo esc_html( sprintf( __( 'Bundle all %1$d and save %2$d%%', 'forma-3d-toolkit' ), count( $buyable ), $discount ) );
											} else {
												/* translators: %d: number of pieces. */
												echo esc_html( sprintf( __( 'All %d pieces in one tap', 'forma-3d-toolkit' ), count( $buyable ) ) );
											}
											?>
										</span>
									</div>
									<div class="looks__bundle-price">
										<?php if ( $discount ) : ?>
											<del><?php echo wp_kses_post( wc_price( $total ) ); ?></del>
										<?php endif; ?>
										<ins><?php echo wp_kses_post( wc_price( $bundle ) ); ?></ins>
									</div>
									<button type="button" class="looks__buy" data-look-buy data-look-products="<?php echo esc_attr( implode( ',', array_map( static fn( $p ) => $p->get_id(), $buyable ) ) ); ?>" data-look-token="<?php echo esc_attr( forma_3d_tk_look_token( array_map( static fn( $p ) => $p->get_id(), $buyable ), $discount, $look['name'] ) ); ?>">
										<span><?php echo esc_html( $a['buttonText'] ? $a['buttonText'] : __( 'Add full look to bag', 'forma-3d-toolkit' ) ); ?></span>
										<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
									</button>
								</div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</aside>
			</div>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

/* ---------------------------------------------------------------------------
 * Bundle discount in the cart.
 * ------------------------------------------------------------------------- */

// Tag items added with "Add full look to bag".
add_filter(
	'woocommerce_add_cart_item_data',
	function ( $data, $product_id ) {
		if ( empty( $_POST['forma_3d_look'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- signed token; adding to the cart needs no nonce.
			return $data;
		}
		$look = forma_3d_tk_read_look_token( wp_unslash( $_POST['forma_3d_look'] ) ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- verified HMAC token.
		if ( $look && in_array( (int) $product_id, $look['p'], true ) ) {
			$data['forma_look'] = $look;
		}
		return $data;
	},
	10,
	2
);

add_filter(
	'woocommerce_get_item_data',
	function ( $item_data, $cart_item ) {
		if ( ! empty( $cart_item['forma_look']['n'] ) ) {
			$item_data[] = array(
				'key'   => __( 'Look', 'forma-3d-toolkit' ),
				'value' => esc_html( $cart_item['forma_look']['n'] ),
			);
		}
		return $item_data;
	},
	10,
	2
);

// The discount applies while every piece of a look is in the cart.
add_action(
	'woocommerce_cart_calculate_fees',
	function ( $cart ) {
		$groups = array();
		foreach ( $cart->get_cart() as $item ) {
			if ( empty( $item['forma_look']['key'] ) ) {
				continue;
			}
			$look = $item['forma_look'];
			$key  = $look['key'];
			if ( ! isset( $groups[ $key ] ) ) {
				$groups[ $key ] = array(
					'look'     => $look,
					'present'  => array(),
					'subtotal' => 0.0,
				);
			}
			$groups[ $key ]['present'][] = (int) $item['product_id'];
			$groups[ $key ]['subtotal'] += (float) $item['line_subtotal'];
		}
		foreach ( $groups as $group ) {
			$look = $group['look'];
			if ( $look['d'] <= 0 || array_diff( $look['p'], $group['present'] ) ) {
				continue;
			}
			$amount = round( $group['subtotal'] * $look['d'] / 100, wc_get_price_decimals() );
			if ( $amount > 0 ) {
				/* translators: 1: look name, 2: discount in percent. */
				$cart->add_fee( sprintf( __( 'Complete the look: %1$s (−%2$d%%)', 'forma-3d-toolkit' ), $look['n'], $look['d'] ), -$amount, false );
			}
		}
	}
);
