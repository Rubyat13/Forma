<?php
/**
 * "Forma Setup" screen: required-plugin checklist and one-click demo import.
 *
 * The import runs as a series of small AJAX steps (setup → one request per
 * product → finish) so it never hits PHP time limits and can show progress.
 *
 * @package Forma3DToolkit
 */

defined( 'ABSPATH' ) || exit;

const FORMA_3D_TK_SETUP_SLUG = 'forma-setup';

/**
 * Demo catalogue definition shipped with the theme.
 *
 * @return array{categories: array, products: array}
 */
function forma_3d_tk_demo_data() {
	static $data = null;
	if ( null === $data ) {
		$json = file_get_contents( FORMA_3D_TK_DIR . 'demo/demo.json' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$data = json_decode( (string) $json, true );
		if ( ! is_array( $data ) ) {
			$data = array(
				'categories' => array(),
				'products'   => array(),
			);
		}
	}
	return $data;
}

/* ---------------------------------------------------------------------------
 * Menu, welcome redirect and notice.
 * ------------------------------------------------------------------------- */

add_action(
	'admin_menu',
	function () {
		add_theme_page( __( 'Forma Setup', 'forma-3d-toolkit' ), __( 'Forma Setup', 'forma-3d-toolkit' ), 'manage_options', FORMA_3D_TK_SETUP_SLUG, 'forma_3d_tk_setup_page' );
	}
);

/**
 * Whether the Forma 3D theme (or a child theme of it) is the active theme.
 */
function forma_3d_tk_theme_active() {
	return 'forma-3d' === get_template();
}

// Land on the setup screen right after the Forma 3D theme is activated (until the demo has been imported).
add_action(
	'admin_init',
	function () {
		global $pagenow;
		if ( 'themes.php' !== $pagenow || empty( $_GET['activated'] ) || wp_doing_ajax() || ! forma_3d_tk_theme_active() || ! current_user_can( 'manage_options' ) || get_option( 'forma_demo_imported' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}
		wp_safe_redirect( admin_url( 'themes.php?page=' . FORMA_3D_TK_SETUP_SLUG ) );
		exit;
	}
);

/**
 * Whether to show the welcome notice: with the Forma 3D theme, until the demo is imported or the notice is dismissed.
 */
function forma_3d_tk_show_notice() {
	$screen = get_current_screen();
	return forma_3d_tk_theme_active() && current_user_can( 'manage_options' ) && ! get_option( 'forma_demo_imported' ) && ! get_user_meta( get_current_user_id(), 'forma_3d_tk_notice_dismissed', true ) && ! ( $screen && 'appearance_page_' . FORMA_3D_TK_SETUP_SLUG === $screen->id );
}

add_action(
	'admin_enqueue_scripts',
	function () {
		if ( forma_3d_tk_show_notice() ) {
			wp_enqueue_script( 'forma-3d-tk-notice', FORMA_3D_TK_URI . 'assets/notice.js', array(), FORMA_3D_TK_VERSION, true );
		}
	}
);

add_action(
	'admin_notices',
	function () {
		if ( ! forma_3d_tk_show_notice() ) {
			return;
		}
		printf(
			'<div class="notice notice-info is-dismissible" id="forma-3d-tk-notice" data-nonce="%5$s"><p><strong>%1$s</strong> %2$s <a class="button button-primary" style="margin-left:8px" href="%3$s">%4$s</a></p></div>',
			esc_html__( 'Welcome to Forma!', 'forma-3d-toolkit' ),
			esc_html__( 'Import the 3D demo store in one click.', 'forma-3d-toolkit' ),
			esc_url( admin_url( 'themes.php?page=' . FORMA_3D_TK_SETUP_SLUG ) ),
			esc_html__( 'Open Forma Setup', 'forma-3d-toolkit' ),
			esc_attr( wp_create_nonce( 'forma-3d-tk-dismiss' ) )
		);
	}
);

add_action(
	'wp_ajax_forma_3d_tk_dismiss_notice',
	function () {
		check_ajax_referer( 'forma-3d-tk-dismiss', 'nonce' );
		update_user_meta( get_current_user_id(), 'forma_3d_tk_notice_dismissed', 1 );
		wp_send_json_success();
	}
);

/* ---------------------------------------------------------------------------
 * Plugin status helpers.
 * ------------------------------------------------------------------------- */

/**
 * Status of a plugin: 'active', 'installed' or 'missing', plus its basename.
 *
 * @param string $folder Plugin folder prefix to look for (e.g. "woocommerce", "3d-viewer").
 */
function forma_3d_tk_plugin_status( $folder ) {
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	foreach ( get_plugins() as $file => $info ) {
		if ( 0 === strpos( $file, $folder . '/' ) || 0 === strpos( $file, $folder . '-' ) ) {
			return array(
				'status' => is_plugin_active( $file ) ? 'active' : 'installed',
				'file'   => $file,
				'name'   => $info['Name'],
			);
		}
	}
	return array(
		'status' => 'missing',
		'file'   => '',
		'name'   => '',
	);
}

/**
 * The required plugins, with how to get each one.
 */
function forma_3d_tk_required_plugins() {
	return array(
		array(
			'key'     => 'woocommerce',
			'title'   => 'WooCommerce',
			'desc'    => __( 'Products, cart and checkout.', 'forma-3d-toolkit' ),
			'install' => admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ),
			'state'   => forma_3d_tk_plugin_status( 'woocommerce' ),
		),
		array(
			'key'     => '3d-viewer',
			'title'   => '3D Viewer by bPlugins',
			'desc'    => __( 'Renders the 3D models, dimensions and AR on product pages.', 'forma-3d-toolkit' ),
			'install' => admin_url( 'plugin-install.php?s=3d-viewer&tab=search&type=term' ),
			'state'   => forma_3d_tk_plugin_status( '3d-viewer' ),
		),
	);
}

/* ---------------------------------------------------------------------------
 * Screen.
 * ------------------------------------------------------------------------- */

/**
 * Render the setup screen.
 */
function forma_3d_tk_setup_page() {
	$plugins  = forma_3d_tk_required_plugins();
	$ready    = 'active' === $plugins[0]['state']['status'];
	$data     = forma_3d_tk_demo_data();
	$imported = (bool) get_option( 'forma_demo_imported' );
	wp_enqueue_style( 'forma-3d-tk-setup', FORMA_3D_TK_URI . 'assets/setup.css', array(), FORMA_3D_TK_VERSION );
	wp_enqueue_script( 'forma-3d-tk-setup', FORMA_3D_TK_URI . 'assets/setup.js', array(), FORMA_3D_TK_VERSION, true );
	wp_localize_script(
		'forma-3d-tk-setup',
		'FORMA_SETUP',
		array(
			'ajax'     => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'forma-3d-tk-demo' ),
			'products' => wp_list_pluck( $data['products'], 'name' ),
			'i18n'     => array(
				'setup'   => __( 'Creating categories and store settings…', 'forma-3d-toolkit' ),
				'finish'  => __( 'Building the homepage and menus…', 'forma-3d-toolkit' ),
				'done'    => __( 'Your 3D store is ready!', 'forma-3d-toolkit' ),
				'error'   => __( 'Something went wrong. Please try again.', 'forma-3d-toolkit' ),
				/* translators: 1: product name, 2: product number, 3: product count. */
				'product' => __( 'Importing %1$s (%2$d of %3$d)…', 'forma-3d-toolkit' ),
			),
		)
	);
	?>
	<div class="wrap forma-setup">
		<header class="frm-hero">
			<div>
				<p class="frm-eyebrow"><?php esc_html_e( 'Forma 3D', 'forma-3d-toolkit' ); ?></p>
				<h1><?php esc_html_e( 'Set up your 3D furniture store', 'forma-3d-toolkit' ); ?></h1>
				<p><?php esc_html_e( 'Two quick steps: activate the required plugins, then import the demo store — products, 3D models, homepage and all.', 'forma-3d-toolkit' ); ?></p>
			</div>
			<?php if ( wp_get_theme( 'forma-3d' )->get_screenshot() ) : ?>
				<img src="<?php echo esc_url( wp_get_theme( 'forma-3d' )->get_screenshot() ); ?>" alt="">
			<?php endif; ?>
		</header>

		<section class="frm-card">
			<h2><span class="frm-num">1</span><?php esc_html_e( 'Required plugins', 'forma-3d-toolkit' ); ?></h2>
			<ul class="frm-plugins">
				<?php $theme_on = forma_3d_tk_theme_active(); ?>
				<li class="frm-plugin is-<?php echo $theme_on ? 'active' : 'missing'; ?>">
					<span class="frm-dot"></span>
					<div class="frm-plugin__text">
						<strong><?php esc_html_e( 'Forma 3D theme', 'forma-3d-toolkit' ); ?></strong>
						<span><?php esc_html_e( 'Shows the homepage blocks, the 3D shop and the product pages.', 'forma-3d-toolkit' ); ?></span>
					</div>
					<?php if ( $theme_on ) : ?>
						<span class="frm-badge"><?php esc_html_e( 'Active', 'forma-3d-toolkit' ); ?></span>
					<?php else : ?>
						<a class="button button-primary" href="<?php echo esc_url( admin_url( wp_get_theme( 'forma-3d' )->exists() ? 'themes.php?search=forma-3d' : 'theme-install.php?search=forma-3d' ) ); ?>"><?php esc_html_e( 'Get the theme', 'forma-3d-toolkit' ); ?></a>
					<?php endif; ?>
				</li>
				<?php foreach ( $plugins as $p ) : ?>
					<?php $st = $p['state']['status']; ?>
					<li class="frm-plugin is-<?php echo esc_attr( $st ); ?>" data-plugin="<?php echo esc_attr( $p['key'] ); ?>">
						<span class="frm-dot"></span>
						<div class="frm-plugin__text">
							<strong><?php echo esc_html( $p['title'] ); ?></strong>
							<span><?php echo esc_html( $p['desc'] ); ?></span>
						</div>
						<?php if ( 'active' === $st ) : ?>
							<span class="frm-badge"><?php esc_html_e( 'Active', 'forma-3d-toolkit' ); ?></span>
						<?php elseif ( 'installed' === $st ) : ?>
							<a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( admin_url( 'plugins.php?action=activate&plugin=' . rawurlencode( $p['state']['file'] ) ), 'activate-plugin_' . $p['state']['file'] ) ); ?>"><?php esc_html_e( 'Activate', 'forma-3d-toolkit' ); ?></a>
						<?php else : ?>
							<a class="button button-primary" href="<?php echo esc_url( $p['install'] ); ?>"><?php esc_html_e( 'Install', 'forma-3d-toolkit' ); ?></a>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>

		<section class="frm-card" data-import>
			<h2><span class="frm-num">2</span><?php esc_html_e( 'Import the demo store', 'forma-3d-toolkit' ); ?></h2>
			<div class="frm-import">
				<ul class="frm-list">
					<li><?php echo esc_html( sprintf( /* translators: %d count */ __( '%d products, each with a true-to-scale 3D model and 3 photos', 'forma-3d-toolkit' ), count( $data['products'] ) ) ); ?></li>
					<li><?php echo esc_html( sprintf( /* translators: %d count */ __( '%d product categories', 'forma-3d-toolkit' ), count( $data['categories'] ) ) ); ?></li>
					<li><?php esc_html_e( 'Scroll-animated homepage, editable with the block editor', 'forma-3d-toolkit' ); ?></li>
					<li><?php esc_html_e( '3D Viewer set to replace product images, with AR and a dimensions ruler', 'forma-3d-toolkit' ); ?></li>
					<li><?php esc_html_e( 'Pretty permalinks and centimetres as the dimension unit', 'forma-3d-toolkit' ); ?></li>
					<li><?php esc_html_e( 'Store settings from the demo: prices without cents and product reviews off', 'forma-3d-toolkit' ); ?></li>
				</ul>
				<div class="frm-action">
					<?php if ( ! $ready ) : ?>
						<p class="frm-warn"><?php esc_html_e( 'Activate WooCommerce first.', 'forma-3d-toolkit' ); ?></p>
					<?php endif; ?>
					<button type="button" class="button button-primary button-hero" data-import-start <?php disabled( ! $ready ); ?>>
						<?php echo $imported ? esc_html__( 'Import again', 'forma-3d-toolkit' ) : esc_html__( 'Import demo store', 'forma-3d-toolkit' ); ?>
					</button>
					<div class="frm-progress" hidden data-progress>
						<div class="frm-bar"><i data-bar></i></div>
						<p class="frm-status" data-status></p>
					</div>
					<a class="button button-hero frm-view" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" <?php echo $imported ? '' : 'hidden'; ?> data-view><?php esc_html_e( 'View your store →', 'forma-3d-toolkit' ); ?></a>
				</div>
			</div>
		</section>

		<p class="frm-foot"><?php esc_html_e( 'Demo 3D models: Poly Haven (CC0) and Khronos glTF Sample Assets (CC0 / CC BY 4.0). Replace them with your own products any time.', 'forma-3d-toolkit' ); ?></p>
	</div>
	<?php
}

/* ---------------------------------------------------------------------------
 * Import steps.
 * ------------------------------------------------------------------------- */

add_action( 'wp_ajax_forma_3d_tk_demo_step', 'forma_3d_tk_demo_ajax_step' );

/**
 * AJAX entry: step = setup | product (with index) | finish.
 */
function forma_3d_tk_demo_ajax_step() {
	check_ajax_referer( 'forma-3d-tk-demo', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'forbidden', 403 );
	}
	if ( ! class_exists( 'WooCommerce' ) ) {
		wp_send_json_error( 'WooCommerce is not active.' );
	}
	wp_raise_memory_limit( 'admin' );
	if ( function_exists( 'set_time_limit' ) ) {
		set_time_limit( 120 ); // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
	}
	$step = sanitize_key( wp_unslash( $_POST['step'] ?? '' ) );
	try {
		switch ( $step ) {
			case 'setup':
				forma_3d_tk_demo_setup();
				break;
			case 'product':
				forma_3d_tk_demo_product( absint( $_POST['index'] ?? 0 ) );
				break;
			case 'finish':
				forma_3d_tk_demo_finish();
				break;
			default:
				wp_send_json_error( 'unknown step' );
		}
	} catch ( \Throwable $e ) {
		wp_send_json_error( $e->getMessage() );
	}
	wp_send_json_success();
}

/**
 * Copy a bundled demo file into the media library (once) and return its attachment ID.
 *
 * @param string $rel    Path relative to /demo.
 * @param int    $parent_id Parent post.
 * @param string $title  Attachment title.
 */
function forma_3d_tk_demo_media( $rel, $parent_id = 0, $title = '' ) {
	$existing = get_posts(
		array(
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'meta_key'    => '_forma_demo_file', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'  => $rel, // phpcs:ignore WordPress.DB.SlowDBQuery
			'fields'      => 'ids',
			'numberposts' => 1,
		)
	);
	if ( $existing ) {
		return (int) $existing[0];
	}
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$src = FORMA_3D_TK_DIR . 'demo/' . $rel;
	if ( ! file_exists( $src ) ) {
		return 0;
	}
	$tmp = wp_tempnam( basename( $src ) );
	copy( $src, $tmp );
	$id = media_handle_sideload(
		array(
			'name'     => basename( $src ),
			'tmp_name' => $tmp,
		),
		$parent_id,
		$title
	);
	if ( is_wp_error( $id ) ) {
		wp_delete_file( $tmp );
		throw new \RuntimeException( esc_html( basename( $src ) . ': ' . $id->get_error_message() ) );
	}
	update_post_meta( $id, '_forma_demo_file', $rel );
	return (int) $id;
}

/**
 * Step 1: categories and store-wide settings.
 */
function forma_3d_tk_demo_setup() {
	$data = forma_3d_tk_demo_data();
	foreach ( $data['categories'] as $c ) {
		$term = get_term_by( 'slug', $c['slug'], 'product_cat' );
		$tid  = $term ? $term->term_id : 0;
		if ( ! $tid ) {
			$r = wp_insert_term(
				$c['name'],
				'product_cat',
				array(
					'slug'        => $c['slug'],
					'description' => $c['description'],
				)
			);
			if ( is_wp_error( $r ) ) {
				throw new \RuntimeException( esc_html( $r->get_error_message() ) );
			}
			$tid = $r['term_id'];
		}
		update_term_meta( $tid, 'order', (int) $c['order'] );
		update_term_meta( $tid, 'forma_bg', sanitize_hex_color( $c['bg'] ) );
	}

	update_option( 'woocommerce_dimension_unit', 'cm' );
	update_option( 'woocommerce_weight_unit', 'kg' );

	// Store settings that change how the demo looks (set before the photos are imported, so thumbnails use the same crop).
	$allowed = array( 'woocommerce_price_num_decimals', 'woocommerce_enable_reviews', 'woocommerce_thumbnail_cropping' );
	foreach ( (array) ( $data['options'] ?? array() ) as $name => $value ) {
		if ( in_array( $name, $allowed, true ) ) {
			update_option( $name, sanitize_text_field( (string) $value ) );
		}
	}
	// The footer shows the tagline; only fill it in when the site has none.
	if ( ! empty( $data['site']['tagline'] ) && '' === trim( (string) get_option( 'blogdescription' ) ) ) {
		update_option( 'blogdescription', sanitize_text_field( $data['site']['tagline'] ) );
	}

	// 3D Viewer: switch on its WooCommerce integration and a gentle auto-rotate.
	if ( class_exists( '\\BP3D\\Woocommerce\\Product' ) ) {
		$settings = get_option( '_bp3d_settings_', array() );
		$settings = is_array( $settings ) ? $settings : array();
		update_option(
			'_bp3d_settings_',
			array_merge(
				$settings,
				array(
					'3d_woo_switcher'   => '1',
					'bp_camera_control' => '1',
					'bp_3d_zooming'     => '1',
					'bp_3d_rotate'      => '1',
					'3d_rotate_speed'   => 14,
					'3d_rotate_delay'   => 2500,
					'bp_3d_fullscreen'  => '1',
					'bp_3d_progressbar' => '1',
				)
			)
		);
	}
}

/**
 * Step 2..n: one product with its 3D model and photos.
 *
 * @param int $index Index into demo.json products.
 */
function forma_3d_tk_demo_product( $index ) {
	$data = forma_3d_tk_demo_data();
	if ( empty( $data['products'][ $index ] ) ) {
		throw new \RuntimeException( 'No such demo product.' );
	}
	$it  = $data['products'][ $index ];
	$sku = 'FRM-' . strtoupper( substr( md5( $it['slug'] ), 0, 6 ) );

	$pid     = wc_get_product_id_by_sku( $sku );
	$product = $pid ? wc_get_product( $pid ) : new WC_Product_Simple();
	$product->set_name( $it['name'] );
	$product->set_slug( $it['slug'] );
	$product->set_status( 'publish' );
	if ( ! $pid ) {
		$product->set_sku( $sku );
	}
	$product->set_regular_price( (string) $it['regular_price'] );
	$product->set_sale_price( (string) $it['sale_price'] );
	$product->set_short_description( $it['short_description'] );
	$product->set_description( $it['description'] );
	$term = get_term_by( 'slug', $it['category'], 'product_cat' );
	if ( $term ) {
		$product->set_category_ids( array( $term->term_id ) );
	}
	$product->set_featured( ! empty( $it['featured'] ) );
	$product->set_menu_order( (int) $it['menu_order'] );
	// 3D Viewer convention: width = side to side, length = depth.
	$product->set_width( (string) $it['width'] );
	$product->set_length( (string) $it['depth'] );
	$product->set_height( (string) $it['height'] );
	$product->set_weight( (string) $it['weight'] );
	$product->set_stock_status( 'instock' );
	$product->set_reviews_allowed( false );
	$pid = $product->save();

	$model  = forma_3d_tk_demo_media( 'models/' . $it['model'] . '.glb', $pid, $it['name'] . ' 3D model' );
	$images = array();
	foreach ( array( 0, 1, 2 ) as $k ) {
		$img = forma_3d_tk_demo_media( 'images/' . $it['model'] . '-' . $k . '.webp', $pid, $it['name'] );
		update_post_meta( $img, '_wp_attachment_image_alt', $it['name'] );
		$images[] = $img;
	}
	$product->set_image_id( $images[0] );
	$product->set_gallery_image_ids( array_slice( $images, 1 ) );
	$product->update_meta_data( '_forma_model_id', $model );
	$product->update_meta_data( '_forma_bg', $it['bg'] );
	$product->update_meta_data( '_forma_materials', $it['materials'] );
	$product->update_meta_data( '_forma_designer', $it['designer'] );
	$product->update_meta_data( '_forma_finishes', $it['finishes'] ? wp_json_encode( $it['finishes'] ) : '' );
	$product->save();

	// Hand the model to the 3D Viewer plugin (read by it even before it is activated).
	$meta = get_post_meta( $pid, '_bp3d_product_', true );
	$meta = is_array( $meta ) ? $meta : array();
	update_post_meta(
		$pid,
		'_bp3d_product_',
		array_merge(
			$meta,
			array(
				'bp3d_models'                  => array(
					array(
						'model_src'    => wp_get_attachment_url( $model ),
						'poster_src'   => wp_get_attachment_image_url( $images[0], 'woocommerce_single' ),
						'exposure'     => '1',
						'enable_ar'    => '1',
						'ar_placement' => 'floor',
						'ar_mode'      => 'webxr',
						'hotspots'     => array(),
						'initial_view' => '[]',
					),
				),
				'viewer_position'              => 'replace',
				'currentViewer'                => 'modelViewer',
				'bp_model_template'            => 'none',
				'replace_model_with_thumbnail' => '0',
				'show_model_instead_thumbnail' => '0',
				'show_thumbs'                  => '0',
				'show_arrows'                  => '0',
				'bp_3d_zooming'                => '1',
				'bp_3d_dimensions_mode'        => 'button',
				'bp_3d_dimensions_source'      => 'product',
				'bp_3d_dimension_color'        => '#171513',
				'bp_3d_height'                 => array(
					'height' => '640',
					'unit'   => 'px',
				),
				'bp_model_bg'                  => 'transparent',
			)
		)
	);
}

/**
 * Final step: category images, front page, permalinks.
 */
function forma_3d_tk_demo_finish() {
	$data = forma_3d_tk_demo_data();
	foreach ( $data['categories'] as $c ) {
		$term = get_term_by( 'slug', $c['slug'], 'product_cat' );
		if ( $term && $c['image'] ) {
			$img = forma_3d_tk_demo_media( 'images/' . $c['image'], 0, $c['name'] );
			update_term_meta( $term->term_id, 'thumbnail_id', $img );
		}
	}

	// The demo's own homepage (its block settings and text), or every Forma block with its defaults.
	$markup  = ! empty( $data['home'] ) ? (string) $data['home'] : forma_3d_tk_home_markup();
	$home    = get_page_by_path( 'home' );
	$home_id = $home ? $home->ID : wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_title'   => 'Home',
			'post_name'    => 'home',
			'post_status'  => 'publish',
			'post_content' => wp_slash( $markup ), // Block attributes contain < escapes; wp_insert_post() unslashes.
		)
	);
	// An existing, empty Home page gets the Forma blocks so it can be edited in the block editor.
	if ( $home && '' === trim( $home->post_content ) ) {
		wp_update_post(
			array(
				'ID'           => $home->ID,
				'post_content' => wp_slash( $markup ),
			)
		);
	}
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', (int) $home_id );

	// The demo footer has no widget column. On the first import, park the widgets WordPress puts
	// there on a new site under Appearance > Widgets > Inactive widgets (nothing is deleted).
	$sidebars = get_option( 'sidebars_widgets', array() );
	if ( ! get_option( 'forma_demo_imported' ) && ! empty( $sidebars['footer-1'] ) && is_array( $sidebars['footer-1'] ) ) {
		$sidebars['wp_inactive_widgets'] = array_merge( (array) ( $sidebars['wp_inactive_widgets'] ?? array() ), $sidebars['footer-1'] );
		$sidebars['footer-1']            = array();
		update_option( 'sidebars_widgets', $sidebars );
	}

	if ( ! get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}
	flush_rewrite_rules();
	update_option( 'forma_demo_imported', time() );
}
