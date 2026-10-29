<?php
/**
 * Forma homepage blocks for the block editor.
 *
 * The blocks are server-rendered by the Forma 3D theme's section templates
 * (forma_3d_section_{slug}()); with another theme active they render nothing.
 *
 * @package Forma3DToolkit
 */

defined( 'ABSPATH' ) || exit;

/**
 * Block slugs, in homepage order.
 */
function forma_3d_tk_block_slugs() {
	return array( 'hero', 'statement', 'showcase', 'rooms', 'anatomy', 'marquee', 'arrivals', 'promise' );
}

/**
 * Whether the Forma 3D theme (or a child theme of it) can render the blocks.
 */
function forma_3d_tk_theme_ready() {
	return function_exists( 'forma_3d_section_hero' );
}

add_filter(
	'block_categories_all',
	function ( $categories ) {
		array_unshift(
			$categories,
			array(
				'slug'  => 'forma',
				'title' => __( 'Forma 3D', 'forma-3d-toolkit' ),
				'icon'  => null,
			)
		);
		return $categories;
	}
);

add_action( 'init', 'forma_3d_tk_register_blocks' );

/**
 * Register scripts, blocks and the homepage pattern.
 */
function forma_3d_tk_register_blocks() {
	wp_register_script(
		'forma-3d-tk-blocks',
		FORMA_3D_TK_URI . 'assets/blocks.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-data' ),
		FORMA_3D_TK_VERSION,
		true
	);
	wp_register_style( 'forma-3d-tk-blocks-editor', FORMA_3D_TK_URI . 'assets/blocks-editor.css', array(), FORMA_3D_TK_VERSION );

	foreach ( forma_3d_tk_block_slugs() as $slug ) {
		register_block_type(
			FORMA_3D_TK_DIR . 'blocks/' . $slug,
			array(
				'render_callback' => static function ( $attributes ) use ( $slug ) {
					$fn = 'forma_3d_section_' . $slug;
					return function_exists( $fn ) ? $fn( $attributes ) : '';
				},
			)
		);
	}

	register_block_pattern_category( 'forma', array( 'label' => __( 'Forma 3D', 'forma-3d-toolkit' ) ) );
	register_block_pattern(
		'forma-3d-toolkit/homepage',
		array(
			'title'       => __( 'Forma 3D homepage', 'forma-3d-toolkit' ),
			'description' => __( 'The complete scroll-animated 3D homepage: hero, statement, 3D showcase, rooms, anatomy, marquee, products and promise.', 'forma-3d-toolkit' ),
			'categories'  => array( 'forma' ),
			'blockTypes'  => array( 'core/post-content' ),
			'postTypes'   => array( 'page' ),
			'content'     => forma_3d_tk_home_markup(),
		)
	);
}

/**
 * Block markup of the full homepage (every Forma block with its defaults).
 */
function forma_3d_tk_home_markup() {
	return implode(
		"\n\n",
		array_map(
			static function ( $slug ) {
				return '<!-- wp:forma/' . $slug . ' /-->';
			},
			forma_3d_tk_block_slugs()
		)
	);
}

/*
 * Editor data for the block UIs (products with 3D models, categories).
 */
add_action(
	'enqueue_block_editor_assets',
	function () {
		$products = array();
		$cats     = array();
		if ( function_exists( 'wc_get_products' ) ) {
			$model = static function ( $id ) {
				return function_exists( 'forma_3d_get_model_url' ) ? forma_3d_get_model_url( $id ) : '';
			};
			foreach ( wc_get_products(
				array(
					'status'  => 'publish',
					'limit'   => 100,
					'orderby' => 'menu_order',
					'order'   => 'ASC',
				)
			) as $p ) {
				$products[] = array(
					'id'       => $p->get_id(),
					'slug'     => $p->get_slug(),
					'name'     => $p->get_name(),
					'thumb'    => (string) wp_get_attachment_image_url( $p->get_image_id(), 'woocommerce_thumbnail' ),
					'has3d'    => (bool) $model( $p->get_id() ),
					'featured' => $p->is_featured(),
					'bg'       => (string) apply_filters( 'forma_3d_stage_color', '#e6e0d6', $p->get_id() ),
				);
			}
			$terms = get_terms(
				array(
					'taxonomy'   => 'product_cat',
					'hide_empty' => true,
					'parent'     => 0,
				)
			);
			foreach ( is_wp_error( $terms ) ? array() : $terms as $c ) {
				if ( (int) get_option( 'default_product_cat' ) === $c->term_id ) {
					continue; // "Uncategorized".
				}
				$thumb  = (int) get_term_meta( $c->term_id, 'thumbnail_id', true );
				$cats[] = array(
					'name'  => $c->name,
					'thumb' => $thumb ? (string) wp_get_attachment_image_url( $thumb, 'thumbnail' ) : '',
					'bg'    => (string) get_term_meta( $c->term_id, 'forma_bg', true ),
				);
			}
		}
		wp_localize_script(
			'forma-3d-tk-blocks',
			'FORMA_BLOCKS',
			array(
				'products'   => $products,
				'categories' => $cats,
				'shopUrl'    => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ),
				'themeReady' => forma_3d_tk_theme_ready(),
			)
		);
	}
);
