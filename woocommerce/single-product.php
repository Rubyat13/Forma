<?php
/**
 * Product detail page with interactive 3D / AR viewer.
 *
 * Overrides woocommerce/templates/single-product.php.
 *
 * @package Forma3D
 * @version 1.6.4
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	global $product;
	if ( ! $product instanceof WC_Product ) {
		continue;
	}

	$forma_3d_pid       = $product->get_id();
	$forma_3d_model     = forma_3d_get_model_url( $forma_3d_pid );
	$forma_3d_finishes  = forma_3d_get_finishes( $forma_3d_pid );
	$forma_3d_bg        = forma_3d_get_bg( $forma_3d_pid );
	$forma_3d_terms     = get_the_terms( $forma_3d_pid, 'product_cat' );
	$forma_3d_cat       = ( $forma_3d_terms && ! is_wp_error( $forma_3d_terms ) ) ? $forma_3d_terms[0] : null;
	$forma_3d_image_ids = array_filter( array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() ) );
	$forma_3d_poster    = $product->get_image_id() ? wp_get_attachment_image_url( $product->get_image_id(), 'woocommerce_single' ) : '';
	$forma_3d_unit      = get_option( 'woocommerce_dimension_unit' );
	$forma_3d_dims      = array(
		__( 'Width', 'forma-3d' )  => $product->get_width(),
		__( 'Depth', 'forma-3d' )  => $product->get_length(),
		__( 'Height', 'forma-3d' ) => $product->get_height(),
	);
	$forma_3d_specs     = forma_3d_product_specs( $forma_3d_pid );
	$forma_3d_care      = get_theme_mod( 'forma_3d_care_text', '' );
	$forma_3d_simple    = $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock();
	$forma_3d_first     = $forma_3d_finishes ? $forma_3d_finishes[0]['name'] : '';
	$forma_3d_plugin_3d = forma_3d_bp3d_viewer_html(); // 3D Viewer plugin markup, when it manages this product.
	?>
	<main id="main" class="pdp" style="--stage-bg:<?php echo esc_attr( $forma_3d_bg ); ?>">
		<?php do_action( 'woocommerce_before_single_product' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce hook. ?>

		<div class="pdp__crumbs"><?php woocommerce_breadcrumb(); ?></div>

		<div class="pdp__grid" id="product-<?php echo esc_attr( $forma_3d_pid ); ?>">
			<div class="pdp__media">
				<div class="viewer<?php echo $forma_3d_plugin_3d ? ' viewer--plugin' : ''; ?>" data-viewer>
					<?php if ( $forma_3d_plugin_3d ) : ?>
						<div class="viewer__plugin" data-plugin-viewer data-finishes="<?php echo esc_attr( wp_json_encode( $forma_3d_finishes ) ); ?>" data-lenis-prevent>
							<?php echo wp_kses_post( $forma_3d_plugin_3d ); ?>
						</div>
					<?php elseif ( $forma_3d_model ) : ?>
						<model-viewer
							id="pdp-viewer"
							src="<?php echo esc_url( $forma_3d_model ); ?>"
							poster="<?php echo esc_url( $forma_3d_poster ); ?>"
							alt="<?php echo esc_attr( sprintf( /* translators: %s product */ __( 'Interactive 3D model of %s', 'forma-3d' ), $product->get_name() ) ); ?>"
							camera-controls
							touch-action="pan-y"
							auto-rotate
							auto-rotate-delay="2500"
							rotation-per-second="14deg"
							interaction-prompt="none"
							camera-orbit="28deg 76deg auto"
							min-camera-orbit="auto 20deg auto"
							max-camera-orbit="auto 92deg auto"
							interpolation-decay="110"
							shadow-intensity="1.1"
							shadow-softness="0.85"
							exposure="1.05"
							environment-image="neutral"
							ar
							ar-modes="webxr scene-viewer quick-look"
							ar-scale="fixed"
							data-finishes="<?php echo esc_attr( wp_json_encode( $forma_3d_finishes ) ); ?>"
							data-lenis-prevent
						>
							<button slot="hotspot-dot+X-Y+Z" class="dim-dot" data-position="1 -1 1" data-normal="1 0 0" tabindex="-1"></button>
							<button slot="hotspot-dim+X-Y" class="dim-label" data-position="1 -1 0" data-normal="1 0 0" tabindex="-1"></button>
							<button slot="hotspot-dot+X-Y-Z" class="dim-dot" data-position="1 -1 -1" data-normal="1 0 0" tabindex="-1"></button>
							<button slot="hotspot-dim+X-Z" class="dim-label" data-position="1 0 -1" data-normal="1 0 0" tabindex="-1"></button>
							<button slot="hotspot-dot+X+Y-Z" class="dim-dot" data-position="1 1 -1" data-normal="0 1 0" tabindex="-1"></button>
							<button slot="hotspot-dim+Y-Z" class="dim-label" data-position="0 -1 -1" data-normal="0 1 0" tabindex="-1"></button>
							<button slot="hotspot-dot-X+Y-Z" class="dim-dot" data-position="-1 1 -1" data-normal="0 1 0" tabindex="-1"></button>
							<button slot="hotspot-dim-X-Z" class="dim-label" data-position="-1 0 -1" data-normal="-1 0 0" tabindex="-1"></button>
							<button slot="hotspot-dot-X-Y-Z" class="dim-dot" data-position="-1 -1 -1" data-normal="-1 0 0" tabindex="-1"></button>
							<button slot="hotspot-dim-X-Y" class="dim-label" data-position="-1 -1 0" data-normal="-1 0 0" tabindex="-1"></button>
							<button slot="hotspot-dot-X-Y+Z" class="dim-dot" data-position="-1 -1 1" data-normal="-1 0 0" tabindex="-1"></button>
							<svg class="dim-lines" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
								<line class="dim-line"></line><line class="dim-line"></line><line class="dim-line"></line><line class="dim-line"></line><line class="dim-line"></line>
							</svg>
							<div slot="progress-bar" class="viewer__progress"><i data-viewer-progress></i></div>
							<button slot="ar-button" hidden></button>
						</model-viewer>
					<?php endif; ?>

					<div class="viewer__image" data-viewer-image <?php echo $forma_3d_model ? 'hidden' : ''; ?>>
						<?php echo wp_kses_post( $product->get_image_id() ? wp_get_attachment_image( $product->get_image_id(), 'woocommerce_single', false, array( 'data-viewer-img' => '' ) ) : wc_placeholder_img() ); ?>
					</div>

					<?php if ( $forma_3d_model ) : ?>
						<span class="viewer__badge"><?php forma_3d_the_icon( 'cube' ); ?> <?php esc_html_e( '3D · True to scale', 'forma-3d' ); ?></span>
						<p class="viewer__hint" data-viewer-hint><?php forma_3d_the_icon( 'hand' ); ?> <?php esc_html_e( 'Drag to rotate · Scroll to zoom', 'forma-3d' ); ?></p>
					<?php endif; ?>
					<?php if ( $forma_3d_model && ! $forma_3d_plugin_3d ) : ?>
						<div class="viewer__tools" role="toolbar" aria-label="<?php esc_attr_e( 'Viewer controls', 'forma-3d' ); ?>">
							<button type="button" class="tool" data-tool="rotate" aria-pressed="true" title="<?php esc_attr_e( 'Auto-rotate', 'forma-3d' ); ?>">
							<?php
							forma_3d_the_icon( 'pause', 'when-on' );
							forma_3d_the_icon( 'play', 'when-off' );
							?>
							<span class="screen-reader-text"><?php esc_html_e( 'Toggle auto-rotate', 'forma-3d' ); ?></span></button>
							<button type="button" class="tool" data-tool="dims" aria-pressed="false" title="<?php esc_attr_e( 'Show dimensions', 'forma-3d' ); ?>"><?php forma_3d_the_icon( 'ruler' ); ?><span class="screen-reader-text"><?php esc_html_e( 'Show dimensions', 'forma-3d' ); ?></span></button>
							<button type="button" class="tool" data-tool="reset" title="<?php esc_attr_e( 'Reset view', 'forma-3d' ); ?>"><?php forma_3d_the_icon( 'reset' ); ?><span class="screen-reader-text"><?php esc_html_e( 'Reset view', 'forma-3d' ); ?></span></button>
							<button type="button" class="tool" data-tool="full" title="<?php esc_attr_e( 'Fullscreen', 'forma-3d' ); ?>"><?php forma_3d_the_icon( 'expand' ); ?><span class="screen-reader-text"><?php esc_html_e( 'Fullscreen', 'forma-3d' ); ?></span></button>
						</div>
						<button type="button" class="viewer__ar" data-tool="ar"><?php forma_3d_the_icon( 'ar' ); ?> <?php esc_html_e( 'View in your room', 'forma-3d' ); ?></button>
					<?php endif; ?>
				</div>

				<?php if ( $forma_3d_model || count( $forma_3d_image_ids ) > 1 ) : ?>
					<div class="thumbs" role="group" aria-label="<?php esc_attr_e( 'Product views', 'forma-3d' ); ?>">
						<?php if ( $forma_3d_model ) : ?>
							<button type="button" class="thumb thumb--3d is-active" data-thumb="3d"><?php forma_3d_the_icon( 'cube' ); ?><span>3D</span></button>
						<?php endif; ?>
						<?php foreach ( $forma_3d_image_ids as $forma_3d_i => $forma_3d_img_id ) : ?>
							<button type="button" class="thumb<?php echo ( ! $forma_3d_model && 0 === $forma_3d_i ) ? ' is-active' : ''; ?>" data-thumb="img" data-full="<?php echo esc_url( wp_get_attachment_image_url( $forma_3d_img_id, 'woocommerce_single' ) ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %d image number */ __( 'Image %d', 'forma-3d' ), $forma_3d_i + 1 ) ); ?>">
								<?php echo wp_get_attachment_image( $forma_3d_img_id, 'woocommerce_gallery_thumbnail' ); ?>
							</button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="pdp__info">
				<?php if ( $forma_3d_cat ) : ?>
					<a class="eyebrow pdp__cat" href="<?php echo esc_url( get_term_link( $forma_3d_cat ) ); ?>"><?php echo esc_html( $forma_3d_cat->name ); ?></a>
				<?php endif; ?>
				<h1 class="pdp__title"><?php the_title(); ?></h1>
				<div class="pdp__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
				<?php if ( $product->get_short_description() ) : ?>
					<div class="pdp__short"><?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?></div>
				<?php endif; ?>

				<?php if ( count( $forma_3d_finishes ) > 1 ) : ?>
					<div class="finish" data-pdp-finishes>
						<p class="finish__label"><?php esc_html_e( 'Finish', 'forma-3d' ); ?>: <strong data-finish-name><?php echo esc_html( $forma_3d_first ); ?></strong></p>
						<div class="finish__list">
							<?php foreach ( $forma_3d_finishes as $forma_3d_fi => $forma_3d_f ) : ?>
								<button type="button" class="swatch swatch--lg<?php echo 0 === $forma_3d_fi ? ' is-active' : ''; ?>" style="--c:<?php echo esc_attr( $forma_3d_f['color'] ); ?>" data-finish="<?php echo esc_attr( $forma_3d_fi ); ?>" aria-pressed="<?php echo 0 === $forma_3d_fi ? 'true' : 'false'; ?>" title="<?php echo esc_attr( $forma_3d_f['name'] ); ?>"><span class="screen-reader-text"><?php echo esc_html( $forma_3d_f['name'] ); ?></span></button>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( $forma_3d_simple ) : ?>
					<?php $forma_3d_action = apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce hook. ?>
					<form class="cart pdp__form" action="<?php echo esc_url( $forma_3d_action ); ?>" method="post" data-pdp-form>
						<input type="hidden" name="forma_3d_finish" value="<?php echo esc_attr( $forma_3d_first ); ?>" data-finish-input>
						<div class="qty">
							<button type="button" data-qty="-1" aria-label="<?php esc_attr_e( 'Decrease quantity', 'forma-3d' ); ?>"><?php forma_3d_the_icon( 'minus' ); ?></button>
							<input type="number" name="quantity" value="1" min="1" <?php echo $product->get_max_purchase_quantity() > 0 ? 'max="' . esc_attr( $product->get_max_purchase_quantity() ) . '"' : ''; ?> inputmode="numeric" aria-label="<?php esc_attr_e( 'Quantity', 'forma-3d' ); ?>">
							<button type="button" data-qty="1" aria-label="<?php esc_attr_e( 'Increase quantity', 'forma-3d' ); ?>"><?php forma_3d_the_icon( 'plus' ); ?></button>
						</div>
						<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $forma_3d_pid ); ?>" class="btn btn--lg pdp__add" data-product-id="<?php echo esc_attr( $forma_3d_pid ); ?>">
							<span><?php esc_html_e( 'Add to cart', 'forma-3d' ); ?></span>
							<?php forma_3d_the_icon( 'arrow' ); ?>
						</button>
					</form>
				<?php else : ?>
					<div class="pdp__form pdp__form--wc"><?php woocommerce_template_single_add_to_cart(); ?></div>
				<?php endif; ?>

				<?php $forma_3d_perks = forma_3d_product_perks(); ?>
				<?php if ( $forma_3d_perks ) : ?>
					<ul class="pdp__perks">
						<?php foreach ( $forma_3d_perks as $forma_3d_perk ) : ?>
							<li><?php forma_3d_the_icon( 'check' ); ?><span><?php echo esc_html( $forma_3d_perk ); ?></span></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<div class="accordion">
					<details open>
						<summary><?php esc_html_e( 'Description', 'forma-3d' ); ?><i></i></summary>
						<div class="accordion__body"><?php the_content(); ?></div>
					</details>
					<?php if ( array_filter( $forma_3d_dims ) ) : ?>
						<details>
							<summary><?php esc_html_e( 'Dimensions', 'forma-3d' ); ?><i></i></summary>
							<div class="accordion__body">
								<dl class="spec">
									<?php foreach ( $forma_3d_dims as $forma_3d_label => $forma_3d_val ) : ?>
										<?php if ( $forma_3d_val ) : ?>
											<div><dt><?php echo esc_html( $forma_3d_label ); ?></dt><dd><?php echo esc_html( $forma_3d_val . ' ' . $forma_3d_unit ); ?></dd></div>
										<?php endif; ?>
									<?php endforeach; ?>
									<?php if ( $product->get_weight() ) : ?>
										<div><dt><?php esc_html_e( 'Weight', 'forma-3d' ); ?></dt><dd><?php echo esc_html( $product->get_weight() . ' ' . get_option( 'woocommerce_weight_unit' ) ); ?></dd></div>
									<?php endif; ?>
								</dl>
								<p class="muted"><?php esc_html_e( 'Tip: tap the ruler in the 3D viewer to see these on the model.', 'forma-3d' ); ?></p>
							</div>
						</details>
					<?php endif; ?>
					<?php if ( $forma_3d_specs || $forma_3d_care || $product->get_sku() ) : ?>
						<details>
							<summary><?php esc_html_e( 'Materials & details', 'forma-3d' ); ?><i></i></summary>
							<div class="accordion__body">
								<dl class="spec">
									<?php foreach ( $forma_3d_specs as $forma_3d_label => $forma_3d_value ) : ?>
										<div><dt><?php echo esc_html( $forma_3d_label ); ?></dt><dd><?php echo esc_html( $forma_3d_value ); ?></dd></div>
									<?php endforeach; ?>
									<?php if ( $product->get_sku() ) : ?>
										<div><dt><?php esc_html_e( 'SKU', 'forma-3d' ); ?></dt><dd><?php echo esc_html( $product->get_sku() ); ?></dd></div>
									<?php endif; ?>
								</dl>
								<?php if ( $forma_3d_care ) : ?>
									<p class="muted"><?php echo esc_html( $forma_3d_care ); ?></p>
								<?php endif; ?>
							</div>
						</details>
					<?php endif; ?>
					<?php $forma_3d_delivery = get_theme_mod( 'forma_3d_delivery_text', '' ); ?>
					<?php if ( $forma_3d_delivery ) : ?>
						<details>
							<summary><?php esc_html_e( 'Delivery & returns', 'forma-3d' ); ?><i></i></summary>
							<div class="accordion__body">
								<?php echo wp_kses_post( wpautop( esc_html( $forma_3d_delivery ) ) ); ?>
							</div>
						</details>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<?php if ( count( $forma_3d_image_ids ) > 1 ) : ?>
			<section class="pdp-story<?php echo ( $product->get_height() && (float) $product->get_width() / (float) $product->get_height() > 1.6 ) ? ' is-wide' : ''; ?>">
				<div class="pdp-story__text">
					<p class="eyebrow"><?php esc_html_e( 'Every angle', 'forma-3d' ); ?></p>
					<h2 class="display" data-split><?php echo wp_kses_post( __( 'No hidden sides.<br><em>No surprises.</em>', 'forma-3d' ) ); ?></h2>
					<?php if ( array_filter( $forma_3d_dims ) ) : ?>
						<div class="pdp-story__dims">
							<?php foreach ( $forma_3d_dims as $forma_3d_label => $forma_3d_val ) : ?>
								<?php if ( $forma_3d_val ) : ?>
									<div><span><?php echo esc_html( $forma_3d_label ); ?></span><strong data-count="<?php echo esc_attr( $forma_3d_val ); ?>">0</strong><em><?php echo esc_html( $forma_3d_unit ); ?></em></div>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
				<div class="pdp-story__gallery">
					<?php foreach ( array_slice( $forma_3d_image_ids, 0, 3 ) as $forma_3d_gi => $forma_3d_img_id ) : ?>
						<figure class="pdp-story__fig pdp-story__fig--<?php echo esc_attr( $forma_3d_gi ); ?>" style="--card-bg:<?php echo esc_attr( $forma_3d_bg ); ?>" data-reveal>
							<?php
							echo wp_get_attachment_image(
								$forma_3d_img_id,
								'large',
								false,
								array(
									'loading'       => 'lazy',
									'data-parallax' => '',
								)
							);
							?>
						</figure>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<?php
		$forma_3d_related_ids = wc_get_related_products( $forma_3d_pid, 4 );
		if ( count( $forma_3d_related_ids ) < 4 ) {
			// Small categories: top up with pieces from elsewhere in the collection.
			$forma_3d_related_ids = array_merge(
				$forma_3d_related_ids,
				wc_get_products(
					array(
						'status'  => 'publish',
						'limit'   => 4 - count( $forma_3d_related_ids ),
						'exclude' => array_merge( array( $forma_3d_pid ), $forma_3d_related_ids ),
						'orderby' => 'rand',
						'return'  => 'ids',
					)
				)
			);
		}
		$forma_3d_related = array_filter( array_map( 'wc_get_product', $forma_3d_related_ids ) );
		if ( $forma_3d_related ) :
			?>
			<section class="related">
				<div class="section-head">
					<div>
						<p class="eyebrow"><?php esc_html_e( 'Pairs well with', 'forma-3d' ); ?></p>
						<h2 class="display" data-split><?php echo wp_kses_post( __( 'You may <em>also like</em>', 'forma-3d' ) ); ?></h2>
					</div>
					<?php if ( $forma_3d_cat ) : ?>
						<a class="link-arrow" href="<?php echo esc_url( get_term_link( $forma_3d_cat ) ); ?>"><?php echo esc_html( sprintf( /* translators: %s category */ __( 'All %s', 'forma-3d' ), strtolower( $forma_3d_cat->name ) ) ); ?> <?php forma_3d_the_icon( 'arrow' ); ?></a>
					<?php endif; ?>
				</div>
				<div class="products products--related">
					<?php
					foreach ( $forma_3d_related as $forma_3d_rp ) {
						forma_3d_product_card( $forma_3d_rp );
					}
					?>
				</div>
			</section>
		<?php endif; ?>
	</main>

	<?php if ( $forma_3d_simple ) : ?>
		<div class="buybar" data-buybar aria-hidden="true">
			<div class="buybar__thumb" style="--card-bg:<?php echo esc_attr( $forma_3d_bg ); ?>"><?php echo wp_get_attachment_image( $product->get_image_id(), 'woocommerce_gallery_thumbnail' ); ?></div>
			<div class="buybar__info"><strong><?php the_title(); ?></strong><span><?php echo wp_kses_post( $product->get_price_html() ); ?></span></div>
			<button type="button" class="btn" data-buybar-add><?php esc_html_e( 'Add to cart', 'forma-3d' ); ?></button>
		</div>
	<?php endif; ?>
	<?php
	if ( isset( WC()->structured_data ) ) {
		WC()->structured_data->generate_product_data( $product );
	}
	do_action( 'woocommerce_after_single_product' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce hook.
endwhile;

get_footer();
