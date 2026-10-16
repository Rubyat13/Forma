<?php
/**
 * Homepage section renderers, shared by the Forma blocks and the default homepage.
 *
 * Each renderer takes the block's attributes (already merged with block.json
 * defaults) and returns HTML. Sections that drive the WebGL stage register
 * their product data through forma_3d_home_data(), which is printed once as
 * window.forma3dHome for assets/js/home.js.
 *
 * @package Forma3D
 */

defined( 'ABSPATH' ) || exit;

/**
 * Collector for the 3D stage data of the current page.
 *
 * @param string|null $key   Key to set (hero|showcase|anatomy); null to read all.
 * @param mixed       $value Value.
 * @return array
 */
function forma_3d_home_data( $key = null, $value = null ) {
	static $data = array(
		'hero'     => null,
		'showcase' => array(),
		'anatomy'  => null,
	);
	if ( null !== $key ) {
		$data[ $key ] = $value;
	}
	return $data;
}

/**
 * Inline tags allowed in short headline/eyebrow fields.
 */
function forma_3d_inline_kses() {
	return array(
		'em'     => array(),
		'strong' => array(),
		'br'     => array(),
		'a'      => array( 'href' => true ),
	);
}

/**
 * Plain text version of a RichText value (for aria-labels).
 *
 * @param string $html Value.
 */
function forma_3d_plain( $html ) {
	return trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( str_replace( '<br>', ' ', (string) $html ) ) ) );
}

/**
 * Product JS bundle for a slug, only when it has a 3D model.
 *
 * @param string $slug Product slug.
 * @return array|null
 */
function forma_3d_product_by_slug( $slug ) {
	if ( ! $slug || ! function_exists( 'wc_get_product' ) ) {
		return null;
	}
	$post = get_page_by_path( sanitize_title( $slug ), OBJECT, 'product' );
	$prod = $post ? wc_get_product( $post ) : null;
	return ( $prod && 'publish' === $prod->get_status() && forma_3d_get_model_url( $prod->get_id() ) ) ? forma_3d_product_js_data( $prod ) : null;
}

/**
 * First featured 3D product, used when a section's product is not set.
 *
 * @param array $skip Slugs to skip.
 */
function forma_3d_fallback_product( $skip = array() ) {
	if ( ! function_exists( 'forma_3d_showcase_products' ) ) {
		return null;
	}
	foreach ( forma_3d_showcase_products( 12 ) as $p ) {
		if ( ! in_array( $p->get_slug(), $skip, true ) && forma_3d_get_model_url( $p->get_id() ) ) {
			return forma_3d_product_js_data( $p );
		}
	}
	return null;
}

/**
 * Wrapper attributes for a section (block classes, anchor, custom class).
 *
 * @param string $classes Base classes.
 * @param array  $extra Extra attributes.
 */
function forma_3d_section_attrs( $classes, $extra = array() ) {
	$attrs = array_merge( array( 'class' => $classes ), $extra );
	if ( function_exists( 'get_block_wrapper_attributes' ) && WP_Block_Supports::$block_to_render ) {
		return get_block_wrapper_attributes( $attrs );
	}
	$out = '';
	foreach ( $attrs as $k => $v ) {
		$out .= sprintf( ' %s="%s"', esc_attr( $k ), esc_attr( $v ) );
	}
	return ltrim( $out );
}

/**
 * Shop URL helper.
 */
function forma_3d_shop_url() {
	return function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
}

/**
 * Default content for each homepage section (used when a section has no saved settings).
 *
 * @param string $slug Section slug.
 * @return array
 */
function forma_3d_section_defaults( $slug ) {
	$d = array(
		'hero'      => array(
			'eyebrow'    => __( 'New collection', 'forma-3d' ),
			'note'       => __( 'Every product is a true-to-scale<br>3D model. Spin it, zoom in<br>and see every detail.', 'forma-3d' ),
			'line1'      => __( 'Furniture', 'forma-3d' ),
			'line2'      => __( '<em>you can</em> turn', 'forma-3d' ),
			'line3'      => __( 'around.', 'forma-3d' ),
			'buttonText' => __( 'Shop the collection', 'forma-3d' ),
			'buttonUrl'  => '',
			'product'    => '',
		),
		'statement' => array(
			'eyebrow' => __( '(01) — Why 3D', 'forma-3d' ),
			'text'    => __( 'A photo shows one side of a product. A 3D model shows all of them. Shoppers can turn every piece around, zoom into the details and check the real size before they buy.', 'forma-3d' ),
			'facts'   => array(
				array(
					'value' => '360°',
					'label' => __( 'Every angle, rendered live', 'forma-3d' ),
				),
				array(
					'value' => '1:1',
					'label' => __( 'True-to-scale dimensions', 'forma-3d' ),
				),
				array(
					'value' => 'AR',
					'label' => __( 'View it in your room', 'forma-3d' ),
				),
			),
		),
		'showcase'  => array(
			'eyebrow'    => __( '(02) — The collection in 3D', 'forma-3d' ),
			'count'      => 4,
			'products'   => array(),
			'buttonText' => __( 'Explore this piece', 'forma-3d' ),
		),
		'rooms'     => array(
			'eyebrow'  => __( '(03) — Shop by category', 'forma-3d' ),
			'title'    => __( 'Pieces that<br><em>belong together.</em>', 'forma-3d' ),
			'linkText' => __( 'View all products', 'forma-3d' ),
			'endText'  => __( 'Browse the full collection.', 'forma-3d' ),
		),
		'anatomy'   => array(
			'eyebrow'    => __( '(04) — Up close', 'forma-3d' ),
			'title'      => __( 'Made to be seen<br><em>from every side.</em>', 'forma-3d' ),
			'product'    => '',
			'buttonText' => __( 'Open in 3D viewer', 'forma-3d' ),
			'callouts'   => array(
				array(
					'title' => __( 'Every angle', 'forma-3d' ),
					'text'  => __( 'Drag to turn the product and see it from any side.', 'forma-3d' ),
				),
				array(
					'title' => __( 'Every detail', 'forma-3d' ),
					'text'  => __( 'Zoom in on materials, stitching and finishes.', 'forma-3d' ),
				),
				array(
					'title' => __( 'Real size', 'forma-3d' ),
					'text'  => __( 'Dimensions are shown right on the model.', 'forma-3d' ),
				),
			),
		),
		'marquee'   => array(
			'items' => array( __( 'Rotate', 'forma-3d' ), __( 'Zoom in', 'forma-3d' ), __( 'Place in your room', 'forma-3d' ), __( 'Every angle', 'forma-3d' ), __( 'True to scale', 'forma-3d' ) ),
		),
		'arrivals'  => array(
			'eyebrow'  => __( '(05) — Just landed', 'forma-3d' ),
			'title'    => __( 'New <em>arrivals</em>', 'forma-3d' ),
			'linkText' => __( 'Shop everything', 'forma-3d' ),
			'count'    => 8,
			'source'   => 'latest',
		),
		'promise'   => array(
			'eyebrow' => __( '(06) — Shop with confidence', 'forma-3d' ),
			'title'   => __( 'What you see<br>is <em>what arrives.</em>', 'forma-3d' ),
			'items'   => array(
				array(
					'icon'  => 'cube',
					'title' => __( 'Live 3D views', 'forma-3d' ),
					'text'  => __( 'Every product page shows a model you can turn and zoom.', 'forma-3d' ),
				),
				array(
					'icon'  => 'ruler',
					'title' => __( 'True to scale', 'forma-3d' ),
					'text'  => __( 'Real dimensions on the model, so you know it fits.', 'forma-3d' ),
				),
				array(
					'icon'  => 'ar',
					'title' => __( 'View in your room', 'forma-3d' ),
					'text'  => __( 'Place products in your space on supported phones.', 'forma-3d' ),
				),
				array(
					'icon'  => 'bag',
					'title' => __( 'Easy checkout', 'forma-3d' ),
					'text'  => __( 'A fast, familiar checkout powered by WooCommerce.', 'forma-3d' ),
				),
			),
			'quote'   => '',
			'cite'    => '',
		),
	);
	return $d[ $slug ] ?? array();
}

/* ---------------------------------------------------------------------------
 * Sections
 * ------------------------------------------------------------------------- */

/**
 * 01 · Hero with the turning 3D product.
 *
 * @param array $a Attributes.
 */
function forma_3d_section_hero( $a ) {
	$a    = wp_parse_args( (array) $a, forma_3d_section_defaults( 'hero' ) );
	$hero = ( forma_3d_product_by_slug( $a['product'] ) ? forma_3d_product_by_slug( $a['product'] ) : forma_3d_fallback_product() );
	forma_3d_home_data( 'hero', $hero );
	$url  = $a['buttonUrl'] ? $a['buttonUrl'] : forma_3d_shop_url();
	$aria = forma_3d_plain( $a['line1'] . ' ' . $a['line2'] . ' ' . $a['line3'] );
	ob_start();
	?>
	<section <?php echo forma_3d_section_attrs( 'hero', array( 'data-hero' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in forma_3d_section_attrs(). ?>>
		<div class="hero__meta">
			<p class="eyebrow"><?php echo wp_kses( $a['eyebrow'], forma_3d_inline_kses() ); ?></p>
			<p class="hero__note"><?php echo wp_kses( $a['note'], forma_3d_inline_kses() ); ?></p>
		</div>
		<h1 class="hero__title" aria-label="<?php echo esc_attr( $aria ); ?>">
			<span class="line"><span class="line__in"><?php echo wp_kses( $a['line1'], forma_3d_inline_kses() ); ?></span></span>
			<span class="line line--indent"><span class="line__in"><?php echo wp_kses( $a['line2'], forma_3d_inline_kses() ); ?></span></span>
			<span class="line"><span class="line__in"><?php echo wp_kses( $a['line3'], forma_3d_inline_kses() ); ?></span></span>
		</h1>
		<div class="hero__foot">
			<?php if ( $a['buttonText'] ) : ?>
				<a class="btn btn--lg" href="<?php echo esc_url( $url ); ?>" data-magnetic><?php echo esc_html( wp_strip_all_tags( $a['buttonText'] ) ); ?> <?php forma_3d_the_icon( 'arrow' ); ?></a>
			<?php endif; ?>
			<?php if ( $hero ) : ?>
				<a class="hero__tag" href="<?php echo esc_url( $hero['url'] ); ?>">
					<span class="hero__tag-dot"></span>
					<span><strong><?php echo esc_html( $hero['name'] ); ?></strong><span class="hero__tag-price"><?php echo wp_kses_post( $hero['priceHtml'] ); ?></span></span>
				</a>
			<?php endif; ?>
			<div class="hero__scroll" aria-hidden="true"><span><?php esc_html_e( 'Scroll', 'forma-3d' ); ?></span><i></i></div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * 02 · Statement: words light up on scroll, three facts.
 *
 * @param array $a Attributes.
 */
function forma_3d_section_statement( $a ) {
	$a     = wp_parse_args( (array) $a, forma_3d_section_defaults( 'statement' ) );
	$facts = is_array( $a['facts'] ) ? $a['facts'] : array();
	ob_start();
	?>
	<section <?php echo forma_3d_section_attrs( 'statement', array( 'data-statement' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in forma_3d_section_attrs(). ?>>
		<div class="statement__inner">
			<p class="eyebrow"><?php echo wp_kses( $a['eyebrow'], forma_3d_inline_kses() ); ?></p>
			<p class="statement__text" data-words><?php echo esc_html( forma_3d_plain( $a['text'] ) ); ?></p>
			<?php if ( $facts ) : ?>
				<div class="statement__facts">
					<?php foreach ( $facts as $f ) : ?>
						<?php
						$value = forma_3d_plain( $f['value'] ?? '' );
						// Animate pure numbers ("360°" counts up to 360 then keeps its suffix).
						$num = preg_match( '/^(\d+)(.*)$/u', $value, $m ) ? $m : null;
						?>
						<div>
							<?php if ( $num ) : ?>
								<strong data-count="<?php echo esc_attr( $num[1] ); ?>">0</strong><span><?php echo esc_html( $num[2] ); ?></span>
							<?php else : ?>
								<strong><?php echo esc_html( $value ); ?></strong>
							<?php endif; ?>
							<p><?php echo esc_html( forma_3d_plain( $f['label'] ?? '' ) ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * 03 · Pinned 3D showcase.
 *
 * @param array $a Attributes.
 */
function forma_3d_section_showcase( $a ) {
	$a     = wp_parse_args( (array) $a, forma_3d_section_defaults( 'showcase' ) );
	$items = array();
	$slugs = array_filter( (array) $a['products'] );
	if ( $slugs ) {
		foreach ( $slugs as $slug ) {
			$d = forma_3d_product_by_slug( $slug );
			if ( $d ) {
				$items[] = $d;
			}
		}
	} elseif ( function_exists( 'forma_3d_showcase_products' ) ) {
		foreach ( forma_3d_showcase_products( max( 1, (int) $a['count'] ) ) as $p ) {
			if ( forma_3d_get_model_url( $p->get_id() ) ) {
				$items[] = forma_3d_product_js_data( $p );
			}
		}
	}
	$items = array_slice( $items, 0, 6 );
	forma_3d_home_data( 'showcase', $items );
	if ( ! $items ) {
		return '';
	}
	$n     = count( $items );
	$attrs = forma_3d_section_attrs(
		'showcase',
		array(
			'data-showcase' => '',
			'style'         => '--n:' . $n,
		)
	);
	ob_start();
	?>
	<section <?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in forma_3d_section_attrs(). ?>>
		<div class="showcase__pin">
			<div class="showcase__head">
				<p class="eyebrow"><?php echo wp_kses( $a['eyebrow'], forma_3d_inline_kses() ); ?></p>
				<p class="showcase__count"><span data-sc-index>01</span> / <?php echo esc_html( str_pad( (string) $n, 2, '0', STR_PAD_LEFT ) ); ?></p>
			</div>
			<div class="showcase__num" aria-hidden="true" data-sc-bignum>01</div>
			<div class="showcase__items">
				<?php foreach ( $items as $i => $item ) : ?>
					<article class="sc-item<?php echo 0 === $i ? ' is-active' : ''; ?>" data-sc-item="<?php echo esc_attr( $i ); ?>">
						<p class="sc-item__cat"><?php echo esc_html( $item['category'] ); ?></p>
						<h2 class="sc-item__title"><?php echo esc_html( $item['name'] ); ?></h2>
						<p class="sc-item__desc"><?php echo esc_html( $item['excerpt'] ); ?></p>
						<div class="sc-item__meta">
							<span class="sc-item__price"><?php echo wp_kses_post( $item['priceHtml'] ); ?></span>
							<?php if ( $item['dims'] ) : ?>
								<span class="sc-item__dims"><?php echo esc_html( $item['dims'] ); ?></span>
							<?php endif; ?>
						</div>
						<?php if ( count( $item['finishes'] ) > 1 ) : ?>
							<div class="swatches" role="group" aria-label="<?php esc_attr_e( 'Try a finish', 'forma-3d' ); ?>">
								<?php foreach ( $item['finishes'] as $fi => $f ) : ?>
									<button type="button" class="swatch<?php echo 0 === $fi ? ' is-active' : ''; ?>" style="--c:<?php echo esc_attr( $f['color'] ); ?>" data-sc-finish="<?php echo esc_attr( $i . ':' . $fi ); ?>" aria-label="<?php echo esc_attr( $f['name'] ); ?>" title="<?php echo esc_attr( $f['name'] ); ?>"></button>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
						<a class="btn btn--ghost" href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( wp_strip_all_tags( $a['buttonText'] ) ); ?> <?php forma_3d_the_icon( 'arrow' ); ?></a>
					</article>
				<?php endforeach; ?>
			</div>
			<ol class="showcase__nav">
				<?php foreach ( $items as $i => $item ) : ?>
					<li class="<?php echo 0 === $i ? 'is-active' : ''; ?>" data-sc-nav="<?php echo esc_attr( $i ); ?>"><span><?php echo esc_html( $item['name'] ); ?></span><i></i></li>
				<?php endforeach; ?>
			</ol>
			<p class="showcase__hint"><?php forma_3d_the_icon( 'rotate' ); ?> <?php esc_html_e( 'Keep scrolling to turn it', 'forma-3d' ); ?></p>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * 04 · Horizontal category scroller.
 *
 * @param array $a Attributes.
 */
function forma_3d_section_rooms( $a ) {
	$a    = wp_parse_args( (array) $a, forma_3d_section_defaults( 'rooms' ) );
	$cats = forma_3d_nav_categories();
	if ( ! $cats ) {
		return '';
	}
	$shop_url = forma_3d_shop_url();
	ob_start();
	?>
	<section <?php echo forma_3d_section_attrs( 'rooms', array( 'data-hscroll' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in forma_3d_section_attrs(). ?>>
		<div class="rooms__pin">
			<div class="rooms__intro">
				<p class="eyebrow"><?php echo wp_kses( $a['eyebrow'], forma_3d_inline_kses() ); ?></p>
				<h2 class="display" data-split><?php echo wp_kses( $a['title'], forma_3d_inline_kses() ); ?></h2>
				<?php if ( $a['linkText'] ) : ?>
					<a class="link-arrow" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html( wp_strip_all_tags( $a['linkText'] ) ); ?> <?php forma_3d_the_icon( 'arrow' ); ?></a>
				<?php endif; ?>
			</div>
			<div class="rooms__track" data-hscroll-track>
				<?php
				foreach ( $cats as $ci => $cat ) :
					$thumb_id = (int) get_term_meta( $cat->term_id, 'thumbnail_id', true );
					$bg       = apply_filters( 'forma_3d_category_color', '#e3ddd3', $cat->term_id );
					?>
					<a class="room-card" href="<?php echo esc_url( get_term_link( $cat ) ); ?>" style="--card-bg:<?php echo esc_attr( $bg ); ?>">
						<span class="room-card__num">0<?php echo esc_html( $ci + 1 ); ?></span>
						<div class="room-card__media">
							<?php
							echo $thumb_id ? wp_get_attachment_image(
								$thumb_id,
								'large',
								false,
								array(
									'loading'        => 'lazy',
									'data-hparallax' => '',
								)
							) : '';
							?>
						</div>
						<div class="room-card__foot">
							<h3><?php echo esc_html( $cat->name ); ?></h3>
							<span><?php echo esc_html( sprintf( /* translators: %d count */ _n( '%d piece', '%d pieces', $cat->count, 'forma-3d' ), $cat->count ) ); ?></span>
							<span class="room-card__go"><?php forma_3d_the_icon( 'arrow-ur' ); ?></span>
						</div>
					</a>
				<?php endforeach; ?>
				<div class="rooms__end">
					<p><?php echo wp_kses( $a['endText'], forma_3d_inline_kses() ); ?></p>
					<a class="btn" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Shop all', 'forma-3d' ); ?></a>
				</div>
			</div>
			<div class="rooms__progress"><i data-hscroll-progress></i></div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * 05 · Anatomy: pinned 3D product with projected callouts.
 *
 * @param array $a Attributes.
 */
function forma_3d_section_anatomy( $a ) {
	$a    = wp_parse_args( (array) $a, forma_3d_section_defaults( 'anatomy' ) );
	$prod = ( forma_3d_product_by_slug( $a['product'] ) ? forma_3d_product_by_slug( $a['product'] ) : forma_3d_fallback_product() );
	forma_3d_home_data( 'anatomy', $prod );
	if ( ! $prod ) {
		return '';
	}
	$callouts = array_slice( (array) $a['callouts'], 0, 3 );
	ob_start();
	?>
	<section <?php echo forma_3d_section_attrs( 'anatomy', array( 'data-anatomy' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in forma_3d_section_attrs(). ?>>
		<div class="anatomy__pin">
			<div class="anatomy__head">
				<p class="eyebrow"><?php echo wp_kses( $a['eyebrow'], forma_3d_inline_kses() ); ?></p>
				<h2 class="display" data-split><?php echo wp_kses( $a['title'], forma_3d_inline_kses() ); ?></h2>
			</div>
			<div class="callouts">
				<?php foreach ( $callouts as $ci => $c ) : ?>
					<div class="callout" data-callout="<?php echo esc_attr( $ci ); ?>">
						<span class="callout__dot"></span>
						<div class="callout__card"><span class="callout__n">0<?php echo esc_html( $ci + 1 ); ?></span><strong><?php echo esc_html( forma_3d_plain( $c['title'] ?? '' ) ); ?></strong><p><?php echo esc_html( forma_3d_plain( $c['text'] ?? '' ) ); ?></p></div>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="anatomy__foot">
				<div>
					<strong><?php echo esc_html( $prod['name'] ); ?></strong>
					<span><?php echo wp_kses_post( $prod['priceHtml'] ); ?></span>
				</div>
				<a class="btn btn--ghost" href="<?php echo esc_url( $prod['url'] ); ?>"><?php echo esc_html( wp_strip_all_tags( $a['buttonText'] ) ); ?> <?php forma_3d_the_icon( 'arrow' ); ?></a>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * 06 · Velocity marquee.
 *
 * @param array $a Attributes.
 */
function forma_3d_section_marquee( $a ) {
	$a     = wp_parse_args( (array) $a, forma_3d_section_defaults( 'marquee' ) );
	$items = array_values( array_filter( array_map( 'forma_3d_plain', (array) $a['items'] ) ) );
	if ( ! $items ) {
		return '';
	}
	ob_start();
	?>
	<section <?php echo forma_3d_section_attrs( 'marquee', array( 'aria-label' => forma_3d_plain( implode( ', ', $items ) ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in forma_3d_section_attrs(). ?>>
		<div class="marquee__track" data-marquee>
			<?php for ( $m = 0; $m < 2; $m++ ) : ?>
				<div class="marquee__group" <?php echo $m ? 'aria-hidden="true"' : ''; ?>>
					<?php foreach ( $items as $k => $item ) : ?>
						<span><?php echo $k % 2 ? '<em>' . esc_html( $item ) . '</em>' : esc_html( $item ); ?></span><i>✳</i>
					<?php endforeach; ?>
				</div>
			<?php endfor; ?>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * 07 · Latest products grid.
 *
 * @param array $a Attributes.
 */
function forma_3d_section_arrivals( $a ) {
	$a = wp_parse_args( (array) $a, forma_3d_section_defaults( 'arrivals' ) );
	if ( ! function_exists( 'wc_get_products' ) ) {
		return '';
	}
	$query = array(
		'status' => 'publish',
		'limit'  => max( 1, min( 12, (int) $a['count'] ) ),
	);
	if ( 'featured' === $a['source'] ) {
		$query['featured'] = true;
		$query['orderby']  = 'menu_order';
		$query['order']    = 'ASC';
	} elseif ( 'sale' === $a['source'] ) {
		$query['include'] = array_merge( array( 0 ), wc_get_product_ids_on_sale() );
	} else {
		$query['orderby'] = 'date';
		$query['order']   = 'DESC';
	}
	$products = wc_get_products( $query );
	if ( ! $products ) {
		return '';
	}
	ob_start();
	?>
	<section <?php echo forma_3d_section_attrs( 'arrivals' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in forma_3d_section_attrs(). ?>>
		<div class="section-head">
			<div>
				<p class="eyebrow"><?php echo wp_kses( $a['eyebrow'], forma_3d_inline_kses() ); ?></p>
				<h2 class="display" data-split><?php echo wp_kses( $a['title'], forma_3d_inline_kses() ); ?></h2>
			</div>
			<?php if ( $a['linkText'] ) : ?>
				<a class="link-arrow" href="<?php echo esc_url( forma_3d_shop_url() ); ?>"><?php echo esc_html( wp_strip_all_tags( $a['linkText'] ) ); ?> <?php forma_3d_the_icon( 'arrow' ); ?></a>
			<?php endif; ?>
		</div>
		<div class="products products--home">
			<?php
			foreach ( $products as $p ) {
				forma_3d_product_card( $p );
			}
			?>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * 08 · Promise list and testimonial.
 *
 * @param array $a Attributes.
 */
function forma_3d_section_promise( $a ) {
	$a     = wp_parse_args( (array) $a, forma_3d_section_defaults( 'promise' ) );
	$icons = array( 'cube', 'truck', 'return', 'shield', 'ruler', 'ar', 'bag', 'check' );
	ob_start();
	?>
	<section <?php echo forma_3d_section_attrs( 'promise' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in forma_3d_section_attrs(). ?>>
		<div class="promise__grid">
			<div class="promise__lead">
				<p class="eyebrow"><?php echo wp_kses( $a['eyebrow'], forma_3d_inline_kses() ); ?></p>
				<h2 class="display" data-split><?php echo wp_kses( $a['title'], forma_3d_inline_kses() ); ?></h2>
			</div>
			<ul class="promise__list">
				<?php foreach ( (array) $a['items'] as $item ) : ?>
					<?php $icon = in_array( $item['icon'] ?? '', $icons, true ) ? $item['icon'] : 'check'; ?>
					<li data-reveal><?php forma_3d_the_icon( $icon ); ?><div><strong><?php echo esc_html( forma_3d_plain( $item['title'] ?? '' ) ); ?></strong><p><?php echo esc_html( forma_3d_plain( $item['text'] ?? '' ) ); ?></p></div></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php if ( forma_3d_plain( $a['quote'] ) ) : ?>
			<blockquote class="quote" data-reveal>
				<p data-words><?php echo esc_html( forma_3d_plain( $a['quote'] ) ); ?></p>
				<cite><?php echo esc_html( forma_3d_plain( $a['cite'] ) ); ?></cite>
			</blockquote>
		<?php endif; ?>
	</section>
	<?php
	return ob_get_clean();
}
