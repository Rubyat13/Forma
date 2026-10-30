<?php
/**
 * Forma template library: a "Forma Templates" button in the page editor that
 * opens a library of full pages, homepages and sections. Each one can be
 * previewed live (the real page with the store's current design) and imported.
 *
 * @package Forma3DToolkit
 */

defined( 'ABSPATH' ) || exit;

/**
 * Library items: type ('page', 'home' or 'section'), title, description,
 * preview image and block markup.
 *
 * @return array<string, array<string, string>>
 */
function forma_3d_tk_library_items() {
	static $items = null;
	if ( null !== $items ) {
		return $items;
	}
	$items    = array();
	$registry = WP_Block_Patterns_Registry::get_instance();
	$thumb    = static function ( $id ) {
		return FORMA_3D_TK_URI . 'assets/library/' . $id . '.webp';
	};

	// Pages: the Toolkit's page templates.
	foreach ( array( 'about', 'contact', 'lookbook', 'faq', 'shop-the-room', 'campaign' ) as $slug ) {
		$pattern = $registry->get_registered( 'forma-3d-toolkit/page-' . $slug );
		if ( $pattern ) {
			$items[ 'page-' . $slug ] = array(
				'type'    => 'page',
				'title'   => $pattern['title'],
				'desc'    => $pattern['description'] ?? '',
				'thumb'   => $thumb( 'page-' . $slug ),
				'content' => $pattern['content'],
			);
		}
	}

	// Homepages: the starter sites' homepage layouts.
	$homes = json_decode( (string) file_get_contents( FORMA_3D_TK_DIR . 'library/homepages.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$homes = is_array( $homes ) ? $homes : array();
	$demo  = json_decode( (string) file_get_contents( FORMA_3D_TK_DIR . 'demo/demo.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$homes = array_merge( array( 'furniture' => (string) ( $demo['home'] ?? '' ) ), $homes );
	$names = array(
		'furniture' => array( __( 'Furniture homepage', 'forma-3d-toolkit' ), __( 'The scroll-animated 3D homepage: hero, showcase, rooms, anatomy and more.', 'forma-3d-toolkit' ) ),
		'lighting'  => array( __( 'Lighting homepage', 'forma-3d-toolkit' ), __( 'The 3D homepage written for a lighting store.', 'forma-3d-toolkit' ) ),
		'decor'     => array( __( 'Home Decor homepage', 'forma-3d-toolkit' ), __( 'The 3D homepage written for a home decor store.', 'forma-3d-toolkit' ) ),
		'fashion'   => array( __( 'Fashion homepage', 'forma-3d-toolkit' ), __( 'Try the Look, new arrivals, categories and a promise. Add your looks after importing.', 'forma-3d-toolkit' ) ),
	);
	foreach ( $names as $site => $text ) {
		if ( ! empty( $homes[ $site ] ) ) {
			$items[ 'home-' . $site ] = array(
				'type'    => 'home',
				'title'   => $text[0],
				'desc'    => $text[1],
				'thumb'   => $thumb( 'home-' . $site ),
				'content' => forma_3d_tk_library_clean( $homes[ $site ] ),
			);
		}
	}

	// Sections: every Forma block on its own, then the theme's own patterns.
	$sections = array(
		'hero'      => array( __( 'Hero', 'forma-3d-toolkit' ), __( 'Big headline with a 3D product that turns as you scroll.', 'forma-3d-toolkit' ) ),
		'statement' => array( __( 'Statement', 'forma-3d-toolkit' ), __( 'A large statement that reveals word by word, with three facts.', 'forma-3d-toolkit' ) ),
		'showcase'  => array( __( '3D showcase', 'forma-3d-toolkit' ), __( 'Featured products presented one by one in 3D.', 'forma-3d-toolkit' ) ),
		'rooms'     => array( __( 'Shop by category', 'forma-3d-toolkit' ), __( 'Category cards that scroll sideways.', 'forma-3d-toolkit' ) ),
		'anatomy'   => array( __( 'Anatomy', 'forma-3d-toolkit' ), __( 'One product turning in 3D with three callouts.', 'forma-3d-toolkit' ) ),
		'marquee'   => array( __( 'Marquee', 'forma-3d-toolkit' ), __( 'A band of large scrolling words.', 'forma-3d-toolkit' ) ),
		'arrivals'  => array( __( 'Product grid', 'forma-3d-toolkit' ), __( 'Newest, featured or sale products.', 'forma-3d-toolkit' ) ),
		'promise'   => array( __( 'Promise', 'forma-3d-toolkit' ), __( 'Four reasons to buy, with a customer quote.', 'forma-3d-toolkit' ) ),
		'looks'     => array( __( 'Try the Look', 'forma-3d-toolkit' ), __( 'Styled outfits with a "complete the look" bundle. Add looks after inserting.', 'forma-3d-toolkit' ) ),
	);
	foreach ( $sections as $slug => $text ) {
		$items[ 'section-' . $slug ] = array(
			'type'    => 'section',
			'title'   => $text[0],
			'desc'    => $text[1],
			'thumb'   => $thumb( 'section-' . $slug ),
			'content' => '<!-- wp:forma/' . $slug . ' /-->',
		);
	}
	foreach ( array( 'call-to-action', 'features', 'quote', 'faq' ) as $slug ) {
		$pattern = $registry->get_registered( 'forma-3d/' . $slug );
		if ( $pattern ) {
			$items[ 'theme-' . $slug ] = array(
				'type'    => 'section',
				'title'   => $pattern['title'],
				'desc'    => __( 'Built with core blocks.', 'forma-3d-toolkit' ),
				'thumb'   => $thumb( 'theme-' . $slug ),
				'content' => $pattern['content'],
			);
		}
	}

	/**
	 * Filters the items of the Forma template library.
	 *
	 * @param array $items Items keyed by ID.
	 */
	$items = (array) apply_filters( 'forma_3d_tk_library_items', $items );
	return $items;
}

/**
 * Keep only blocks (drop stray whitespace) from library markup.
 *
 * @param string $markup Block markup.
 */
function forma_3d_tk_library_clean( $markup ) {
	$blocks = array_filter(
		parse_blocks( (string) $markup ),
		static function ( $block ) {
			return ! empty( $block['blockName'] );
		}
	);
	return implode( "\n\n", array_map( 'serialize_block', $blocks ) );
}

// Editor: the button and the library, on pages.
add_action(
	'enqueue_block_editor_assets',
	function () {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'page' !== $screen->post_type || ! current_user_can( 'edit_pages' ) ) {
			return;
		}
		wp_enqueue_style( 'forma-3d-tk-library', FORMA_3D_TK_URI . 'assets/library.css', array( 'wp-components' ), FORMA_3D_TK_VERSION );
		wp_enqueue_script( 'forma-3d-tk-library', FORMA_3D_TK_URI . 'assets/library.js', array( 'wp-element', 'wp-components', 'wp-data', 'wp-blocks', 'wp-block-editor', 'wp-dom-ready', 'wp-i18n', 'wp-notices' ), FORMA_3D_TK_VERSION, true );
		wp_set_script_translations( 'forma-3d-tk-library', 'forma-3d-toolkit' );
		$items = array();
		foreach ( forma_3d_tk_library_items() as $id => $item ) {
			$items[] = array(
				'id'      => $id,
				'type'    => $item['type'],
				'title'   => $item['title'],
				'desc'    => $item['desc'],
				'thumb'   => $item['thumb'],
				'content' => $item['content'],
			);
		}
		wp_localize_script(
			'forma-3d-tk-library',
			'FORMA_LIBRARY',
			array(
				'items'   => $items,
				'preview' => add_query_arg(
					array(
						'forma_template_preview' => '%id%',
						'_wpnonce'               => wp_create_nonce( 'forma-3d-tk-preview' ),
					),
					home_url( '/' )
				),
			)
		);
	}
);

// Live preview: the template rendered as a page on the front end, with the store's current design.
add_action( 'template_redirect', 'forma_3d_tk_library_preview', 0 );

/**
 * Render a library item as a temporary, unsaved page.
 */
function forma_3d_tk_library_preview() {
	if ( empty( $_GET['forma_template_preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- checked below.
		return;
	}
	if ( ! current_user_can( 'edit_pages' ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ?? '' ) ), 'forma-3d-tk-preview' ) ) {
		wp_die( esc_html__( 'This preview link has expired. Close it and open the preview again.', 'forma-3d-toolkit' ), '', array( 'response' => 403 ) );
	}
	$items = forma_3d_tk_library_items();
	$id    = sanitize_key( wp_unslash( $_GET['forma_template_preview'] ) );
	if ( ! isset( $items[ $id ] ) ) {
		wp_die( esc_html__( 'Unknown template.', 'forma-3d-toolkit' ), '', array( 'response' => 404 ) );
	}
	global $wp_query, $post;
	$post                        = new WP_Post( // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- a temporary page for this preview request only.
		(object) array(
			'ID'             => 0,
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'post_title'     => $items[ $id ]['title'],
			'post_name'      => 'forma-template-preview',
			'post_content'   => $items[ $id ]['content'],
			'post_author'    => get_current_user_id(),
			'post_date'      => current_time( 'mysql' ),
			'post_date_gmt'  => current_time( 'mysql', true ),
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'filter'         => 'raw',
		)
	);
	$wp_query->posts             = array( $post );
	$wp_query->post              = $post;
	$wp_query->post_count        = 1;
	$wp_query->found_posts       = 1;
	$wp_query->max_num_pages     = 1;
	$wp_query->queried_object    = $post;
	$wp_query->queried_object_id = 0;
	$wp_query->is_page           = true;
	$wp_query->is_singular       = true;
	$wp_query->is_home           = false;
	$wp_query->is_archive        = false;
	$wp_query->is_404            = false;
	status_header( 200 );
	nocache_headers();
	show_admin_bar( false );
}
