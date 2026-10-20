<?php
/**
 * Product data for the Forma 3D theme: finishes, stage colour, materials,
 * designer and an optional model fallback, plus .glb uploads and the chosen
 * finish on cart lines and orders.
 *
 * Meta keys:
 *  _forma_model_id   Attachment ID of a .glb model (used when the 3D Viewer plugin has none).
 *  _forma_model_url  External model URL fallback.
 *  _forma_finishes   JSON list of finishes, e.g.
 *                    [{"name":"Navy","color":"#22305a","variant":"Navy"},
 *                     {"name":"Smoked Oak","color":"#7a5c44","tint":[0.62,0.55,0.48],"material":"legs"}]
 *  _forma_bg         Card/stage background colour.
 *  _forma_materials  Materials line.
 *  _forma_designer   Designer credit.
 *
 * @package Forma3DToolkit
 */

defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------------
 * Data for the theme.
 * ------------------------------------------------------------------------- */

add_filter(
	'forma_3d_model_url',
	function ( $url, $product_id ) {
		if ( $url ) {
			return $url;
		}
		$attachment = (int) get_post_meta( $product_id, '_forma_model_id', true );
		if ( $attachment && wp_get_attachment_url( $attachment ) ) {
			return wp_get_attachment_url( $attachment );
		}
		return (string) get_post_meta( $product_id, '_forma_model_url', true );
	},
	10,
	2
);

add_filter(
	'forma_3d_finishes',
	function ( $finishes, $product_id ) {
		return forma_3d_tk_finishes( $product_id );
	},
	10,
	2
);

add_filter(
	'forma_3d_stage_color',
	function ( $color, $product_id ) {
		$bg = sanitize_hex_color( get_post_meta( $product_id, '_forma_bg', true ) );
		return $bg ? $bg : $color;
	},
	10,
	2
);

add_filter(
	'forma_3d_product_specs',
	function ( $specs, $product_id ) {
		$materials = get_post_meta( $product_id, '_forma_materials', true );
		$designer  = get_post_meta( $product_id, '_forma_designer', true );
		if ( $materials ) {
			$specs[ __( 'Materials', 'forma-3d-toolkit' ) ] = $materials;
		}
		if ( $designer ) {
			$specs[ __( 'Designer', 'forma-3d-toolkit' ) ] = $designer;
		}
		return $specs;
	},
	10,
	2
);

/**
 * Decoded, sanitised finish list for a product.
 *
 * @param int $product_id Product ID.
 * @return array<int, array<string, mixed>>
 */
function forma_3d_tk_finishes( $product_id ) {
	return forma_3d_tk_sanitize_finishes( json_decode( (string) get_post_meta( $product_id, '_forma_finishes', true ), true ) );
}

/**
 * Keep only the known finish fields, sanitised.
 *
 * @param mixed $items Decoded finish list.
 * @return array<int, array<string, mixed>>
 */
function forma_3d_tk_sanitize_finishes( $items ) {
	if ( ! is_array( $items ) ) {
		return array();
	}
	$clean = array();
	foreach ( $items as $item ) {
		if ( ! is_array( $item ) || empty( $item['name'] ) ) {
			continue;
		}
		$color  = sanitize_hex_color( (string) ( $item['color'] ?? '' ) );
		$finish = array(
			'name'  => sanitize_text_field( $item['name'] ),
			'color' => $color ? $color : '#cccccc',
		);
		if ( ! empty( $item['variant'] ) ) {
			$finish['variant'] = sanitize_text_field( $item['variant'] );
		}
		if ( isset( $item['tint'] ) && is_array( $item['tint'] ) && 3 === count( $item['tint'] ) ) {
			$finish['tint'] = array_map( 'floatval', $item['tint'] );
		}
		if ( ! empty( $item['material'] ) ) {
			$finish['material'] = sanitize_text_field( $item['material'] );
		}
		$clean[] = $finish;
	}
	return $clean;
}

/* ---------------------------------------------------------------------------
 * .glb uploads.
 * ------------------------------------------------------------------------- */

add_filter(
	'upload_mimes',
	function ( $mimes ) {
		$mimes['glb'] = 'model/gltf-binary';
		return $mimes;
	}
);

add_filter(
	'wp_check_filetype_and_ext',
	function ( $data, $file, $filename ) {
		if ( 'glb' === strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) ) ) {
			$data['ext']             = 'glb';
			$data['type']            = 'model/gltf-binary';
			$data['proper_filename'] = $filename;
		}
		return $data;
	},
	10,
	3
);

/* ---------------------------------------------------------------------------
 * Admin: "Forma Display" product data tab.
 * ------------------------------------------------------------------------- */

add_filter(
	'woocommerce_product_data_tabs',
	function ( $tabs ) {
		$tabs['forma_3d'] = array(
			'label'    => __( 'Forma Display', 'forma-3d-toolkit' ),
			'target'   => 'forma_3d_panel',
			'class'    => array(),
			'priority' => 15,
		);
		return $tabs;
	}
);

add_action(
	'woocommerce_product_data_panels',
	function () {
		global $post;
		$bp3d     = class_exists( '\BP3D\Woocommerce\Product' );
		$model_id = (int) get_post_meta( $post->ID, '_forma_model_id', true );
		$file     = $model_id ? basename( (string) get_attached_file( $model_id ) ) : '';
		wp_nonce_field( 'forma_3d_tk_save', 'forma_3d_tk_nonce' );
		?>
		<div id="forma_3d_panel" class="panel woocommerce_options_panel">
			<div class="options_group">
				<?php if ( $bp3d ) : ?>
					<p class="form-field">
						<label><?php esc_html_e( '3D model', 'forma-3d-toolkit' ); ?></label>
						<span><?php esc_html_e( 'Managed by the 3D Viewer plugin — see its "3D Product Settings" box on this screen.', 'forma-3d-toolkit' ); ?></span>
					</p>
				<?php else : ?>
					<p class="form-field">
						<label><?php esc_html_e( '3D model (.glb)', 'forma-3d-toolkit' ); ?></label>
						<input type="hidden" id="forma_model_id" name="_forma_model_id" value="<?php echo esc_attr( $model_id ? $model_id : '' ); ?>" />
						<span id="forma_model_name" style="display:inline-block;min-width:160px"><?php echo $file ? esc_html( $file ) : esc_html__( 'No model selected', 'forma-3d-toolkit' ); ?></span>
						<button type="button" class="button" id="forma_model_pick"><?php esc_html_e( 'Select model', 'forma-3d-toolkit' ); ?></button>
						<button type="button" class="button-link-delete" id="forma_model_clear"><?php esc_html_e( 'Remove', 'forma-3d-toolkit' ); ?></button>
					</p>
					<?php
					woocommerce_wp_text_input(
						array(
							'id'          => '_forma_model_url',
							'label'       => __( 'Or model URL', 'forma-3d-toolkit' ),
							'placeholder' => 'https://…/model.glb',
						)
					);
					?>
				<?php endif; ?>
				<?php
				woocommerce_wp_text_input(
					array(
						'id'          => '_forma_bg',
						'label'       => __( 'Stage colour', 'forma-3d-toolkit' ),
						'placeholder' => '#e6e0d6',
						'desc_tip'    => true,
						'description' => __( 'Background colour behind the product on cards and in the viewer.', 'forma-3d-toolkit' ),
					)
				);
				woocommerce_wp_text_input(
					array(
						'id'    => '_forma_materials',
						'label' => __( 'Materials', 'forma-3d-toolkit' ),
					)
				);
				woocommerce_wp_text_input(
					array(
						'id'    => '_forma_designer',
						'label' => __( 'Designer', 'forma-3d-toolkit' ),
					)
				);
				woocommerce_wp_textarea_input(
					array(
						'id'          => '_forma_finishes',
						'label'       => __( 'Finishes (JSON)', 'forma-3d-toolkit' ),
						'style'       => 'min-height:120px;font-family:monospace',
						'desc_tip'    => true,
						'description' => __( 'List of {"name","color"} plus either "variant" (glTF material variant name) or "tint" [r,g,b] with optional "material" name.', 'forma-3d-toolkit' ),
					)
				);
				?>
			</div>
		</div>
		<?php
	}
);

add_action(
	'woocommerce_admin_process_product_object',
	function ( $product ) {
		if ( ! isset( $_POST['forma_3d_tk_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['forma_3d_tk_nonce'] ), 'forma_3d_tk_save' ) ) {
			return;
		}
		if ( isset( $_POST['_forma_model_id'] ) ) {
			$product->update_meta_data( '_forma_model_id', absint( $_POST['_forma_model_id'] ) );
			$product->update_meta_data( '_forma_model_url', esc_url_raw( wp_unslash( $_POST['_forma_model_url'] ?? '' ) ) );
		}
		$product->update_meta_data( '_forma_bg', (string) sanitize_hex_color( wp_unslash( $_POST['_forma_bg'] ?? '' ) ) );
		$product->update_meta_data( '_forma_materials', sanitize_text_field( wp_unslash( $_POST['_forma_materials'] ?? '' ) ) );
		$product->update_meta_data( '_forma_designer', sanitize_text_field( wp_unslash( $_POST['_forma_designer'] ?? '' ) ) );
		$finishes = forma_3d_tk_sanitize_finishes( json_decode( wp_unslash( $_POST['_forma_finishes'] ?? '' ), true ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- every field is sanitised by forma_3d_tk_sanitize_finishes().
		$product->update_meta_data( '_forma_finishes', $finishes ? wp_json_encode( $finishes ) : '' );
	}
);

add_action(
	'admin_enqueue_scripts',
	function () {
		$screen = get_current_screen();
		if ( ! $screen || 'product' !== $screen->post_type ) {
			return;
		}
		wp_enqueue_media();
		// With the 3D Viewer plugin active, the tab has no model picker.
		if ( class_exists( '\BP3D\Woocommerce\Product' ) ) {
			return;
		}
		wp_enqueue_script( 'forma-3d-tk-product-fields', FORMA_3D_TK_URI . 'assets/product-fields.js', array( 'jquery', 'media-editor' ), FORMA_3D_TK_VERSION, true );
		wp_localize_script(
			'forma-3d-tk-product-fields',
			'FORMA_PRODUCT_FIELDS',
			array(
				'i18n' => array(
					'title'  => __( 'Select 3D model', 'forma-3d-toolkit' ),
					'button' => __( 'Use this model', 'forma-3d-toolkit' ),
				),
			)
		);
	}
);

/* ---------------------------------------------------------------------------
 * The finish picked on the product page travels with the cart line and order.
 * The theme posts it as "forma_3d_finish".
 * ------------------------------------------------------------------------- */

add_filter(
	'woocommerce_add_cart_item_data',
	function ( $data, $product_id ) {
		if ( empty( $_POST['forma_3d_finish'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $data;
		}
		$choice  = sanitize_text_field( wp_unslash( $_POST['forma_3d_finish'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		$allowed = wp_list_pluck( forma_3d_tk_finishes( $product_id ), 'name' );
		if ( in_array( $choice, $allowed, true ) ) {
			$data['forma_finish'] = $choice;
		}
		return $data;
	},
	10,
	2
);

add_filter(
	'woocommerce_get_item_data',
	function ( $item_data, $cart_item ) {
		if ( ! empty( $cart_item['forma_finish'] ) ) {
			$item_data[] = array(
				'key'   => __( 'Finish', 'forma-3d-toolkit' ),
				'value' => $cart_item['forma_finish'],
			);
		}
		return $item_data;
	},
	10,
	2
);

add_action(
	'woocommerce_checkout_create_order_line_item',
	function ( $item, $cart_item_key, $values ) {
		if ( ! empty( $values['forma_finish'] ) ) {
			$item->add_meta_data( __( 'Finish', 'forma-3d-toolkit' ), $values['forma_finish'] );
		}
	},
	10,
	3
);

add_filter(
	'forma_3d_category_color',
	function ( $color, $term_id ) {
		$bg = sanitize_hex_color( get_term_meta( $term_id, 'forma_bg', true ) );
		return $bg ? $bg : $color;
	},
	10,
	2
);
