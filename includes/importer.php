<?php
/**
 * "Forma Setup" screen: required-plugin checklist and one-click starter site import.
 *
 * The import runs as a series of small AJAX steps (setup → one request per
 * product → finish) so it never hits PHP time limits and can show progress.
 *
 * @package Forma3DToolkit
 */

defined( 'ABSPATH' ) || exit;

const FORMA_3D_TK_SETUP_SLUG = 'forma-setup';

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

// Land on the setup screen right after the Forma 3D theme is activated (until a demo has been imported).
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
 * Whether to show the welcome notice: with the Forma 3D theme, until a demo is imported or the notice is dismissed.
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
			esc_html__( 'Pick a starter site and import it in one click.', 'forma-3d-toolkit' ),
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
	$sites    = forma_3d_tk_starter_sites();
	$current  = forma_3d_tk_current_site();
	$selected = isset( $sites[ $current ] ) ? $current : (string) array_key_first( $sites );
	wp_enqueue_style( 'forma-3d-tk-setup', FORMA_3D_TK_URI . 'assets/setup.css', array(), FORMA_3D_TK_VERSION );
	wp_enqueue_script( 'forma-3d-tk-setup', FORMA_3D_TK_URI . 'assets/setup.js', array(), FORMA_3D_TK_VERSION, true );
	wp_localize_script(
		'forma-3d-tk-setup',
		'FORMA_SETUP',
		array(
			'ajax'    => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'forma-3d-tk-demo' ),
			'current' => $current,
			'i18n'    => array(
				'setup'    => __( 'Creating categories and store settings…', 'forma-3d-toolkit' ),
				'fetch'    => __( 'Downloading the starter site…', 'forma-3d-toolkit' ),
				'finish'   => __( 'Building the homepage…', 'forma-3d-toolkit' ),
				'done'     => __( 'Your 3D store is ready!', 'forma-3d-toolkit' ),
				'error'    => __( 'Something went wrong. Please try again.', 'forma-3d-toolkit' ),
				/* translators: 1: product name, 2: product number, 3: product count. */
				'product'  => __( 'Importing %1$s (%2$d of %3$d)…', 'forma-3d-toolkit' ),
				/* translators: %s: starter site name. */
				'import'   => __( 'Import %s', 'forma-3d-toolkit' ),
				/* translators: %s: starter site name. */
				'again'    => __( 'Import %s again', 'forma-3d-toolkit' ),
				'imported' => __( 'Imported', 'forma-3d-toolkit' ),
			),
		)
	);
	?>
	<div class="wrap forma-setup">
		<hr class="wp-header-end"><?php // Admin notices go here, above the header. ?>
		<header class="frm-hero">
			<div>
				<p class="frm-eyebrow"><?php esc_html_e( 'Forma 3D', 'forma-3d-toolkit' ); ?></p>
				<h1><?php esc_html_e( 'Set up your 3D store', 'forma-3d-toolkit' ); ?></h1>
				<p><?php esc_html_e( 'Two quick steps: activate the required plugins, then import a starter site — products, 3D models, homepage and all.', 'forma-3d-toolkit' ); ?></p>
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
			<h2><span class="frm-num">2</span><?php esc_html_e( 'Choose a starter site', 'forma-3d-toolkit' ); ?></h2>
			<div class="frm-sites" role="radiogroup" aria-label="<?php esc_attr_e( 'Starter sites', 'forma-3d-toolkit' ); ?>">
				<?php foreach ( $sites as $id => $site ) : ?>
					<label class="frm-site<?php echo $id === $selected ? ' is-selected' : ''; ?>">
						<input type="radio" name="forma_site" value="<?php echo esc_attr( $id ); ?>" data-name="<?php echo esc_attr( $site['name'] ); ?>" data-remote="<?php echo empty( $site['remote'] ) ? '' : '1'; ?>" <?php checked( $id, $selected ); ?>>
						<span class="frm-site__img"><img src="<?php echo esc_url( $site['thumb'] ); ?>" alt="" loading="lazy"></span>
						<span class="frm-site__body">
							<strong>
								<?php echo esc_html( $site['name'] ); ?>
								<?php if ( $id === $current ) : ?>
									<em class="frm-site__badge"><?php esc_html_e( 'Imported', 'forma-3d-toolkit' ); ?></em>
								<?php endif; ?>
							</strong>
							<span><?php echo esc_html( $site['desc'] ); ?></span>
							<small>
								<?php
								/* translators: 1: number of products, 2: number of categories. */
								echo esc_html( sprintf( __( '%1$d products · %2$d categories', 'forma-3d-toolkit' ), $site['products'], $site['categories'] ) );
								$designs = forma_3d_tk_designs();
								if ( ! empty( $site['design'] ) && isset( $designs[ $site['design'] ] ) ) {
									echo ' · ';
									/* translators: %s: store design name, e.g. "Noir". */
									echo esc_html( sprintf( __( '%s design', 'forma-3d-toolkit' ), $designs[ $site['design'] ]['name'] ) );
								}
								if ( ! empty( $site['remote'] ) ) {
									echo ' · ';
									/* translators: %d: download size in megabytes. */
									echo esc_html( sprintf( __( '%d MB download from GitHub', 'forma-3d-toolkit' ), $site['size'] ) );
								}
								?>
							</small>
						</span>
					</label>
				<?php endforeach; ?>
			</div>

			<div class="frm-import">
				<ul class="frm-list">
					<li><?php esc_html_e( 'Products with true-to-scale 3D models and 3 photos each, in their categories', 'forma-3d-toolkit' ); ?></li>
					<li><?php esc_html_e( 'The scroll-animated homepage, editable with the block editor', 'forma-3d-toolkit' ); ?></li>
					<li><?php esc_html_e( '3D Viewer set to replace product images, with AR and a dimensions ruler', 'forma-3d-toolkit' ); ?></li>
					<li><?php esc_html_e( 'Store settings from the demo: centimetres, prices without cents and product reviews off', 'forma-3d-toolkit' ); ?></li>
				</ul>
				<div class="frm-action">
					<?php if ( ! $ready ) : ?>
						<p class="frm-warn"><?php esc_html_e( 'Activate WooCommerce first.', 'forma-3d-toolkit' ); ?></p>
					<?php endif; ?>
					<label class="frm-replace" data-replace hidden>
						<input type="checkbox" checked data-replace-input>
						<span><?php esc_html_e( 'Move the products of the previous starter site to the Trash. Your Home page is replaced; the current version stays in its revisions.', 'forma-3d-toolkit' ); ?></span>
					</label>
					<button type="button" class="button button-primary button-hero" data-import-start <?php disabled( ! $ready ); ?>>
						<?php esc_html_e( 'Import starter site', 'forma-3d-toolkit' ); ?>
					</button>
					<div class="frm-progress" hidden data-progress>
						<div class="frm-bar"><i data-bar></i></div>
						<p class="frm-status" data-status role="status"></p>
					</div>
					<a class="button button-hero frm-view" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" <?php echo $current ? '' : 'hidden'; ?> data-view><?php esc_html_e( 'View your store →', 'forma-3d-toolkit' ); ?></a>
				</div>
			</div>
		</section>

		<p class="frm-foot"><?php esc_html_e( 'Demo 3D models: Poly Haven (CC0) and Khronos glTF Sample Assets (CC0 / CC BY 4.0). The Lighting and Home Decor starter sites are downloaded from GitHub when you import them. Replace the demo products with your own any time.', 'forma-3d-toolkit' ); ?></p>
	</div>
	<?php
}

/* ---------------------------------------------------------------------------
 * Import steps.
 * ------------------------------------------------------------------------- */

add_action( 'wp_ajax_forma_3d_tk_demo_step', 'forma_3d_tk_demo_ajax_step' );

/**
 * AJAX entry: step = setup | product (with index) | finish, for one starter site.
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
		set_time_limit( 180 ); // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
	}
	$step = sanitize_key( wp_unslash( $_POST['step'] ?? '' ) );
	$site = sanitize_key( wp_unslash( $_POST['site'] ?? 'furniture' ) );
	if ( ! forma_3d_tk_starter_site( $site ) ) {
		wp_send_json_error( __( 'Unknown starter site.', 'forma-3d-toolkit' ) );
	}
	try {
		switch ( $step ) {
			case 'setup':
				wp_send_json_success( forma_3d_tk_demo_setup( $site, ! empty( $_POST['replace'] ) ) );
				break;
			case 'product':
				forma_3d_tk_demo_product( $site, absint( $_POST['index'] ?? 0 ) );
				break;
			case 'finish':
				forma_3d_tk_demo_finish( $site );
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
 * Step 1: download the starter site (when remote), clear the previous one, categories and store-wide settings.
 *
 * @param string $site    Starter site ID.
 * @param bool   $replace Move the products of other starter sites to the Trash.
 * @return array{products: string[], trashed: int}
 */
function forma_3d_tk_demo_setup( $site, $replace ) {
	$info = forma_3d_tk_starter_site( $site );
	if ( ! empty( $info['remote'] ) ) {
		forma_3d_tk_fetch_package( $site );
	}
	$data    = forma_3d_tk_demo_data( $site );
	$trashed = 0;
	if ( $replace && forma_3d_tk_current_site() && forma_3d_tk_current_site() !== $site ) {
		$trashed = forma_3d_tk_trash_other_demos( $site );
	}

	foreach ( $data['categories'] as $c ) {
		$slug = sanitize_title( $c['slug'] );
		$term = get_term_by( 'slug', $slug, 'product_cat' );
		$tid  = $term ? $term->term_id : 0;
		if ( ! $tid ) {
			$r = wp_insert_term(
				sanitize_text_field( $c['name'] ),
				'product_cat',
				array(
					'slug'        => $slug,
					'description' => sanitize_text_field( $c['description'] ),
				)
			);
			if ( is_wp_error( $r ) ) {
				throw new \RuntimeException( esc_html( $r->get_error_message() ) );
			}
			$tid = $r['term_id'];
		}
		update_term_meta( $tid, 'order', (int) $c['order'] );
		update_term_meta( $tid, 'forma_bg', sanitize_hex_color( $c['bg'] ) );
		update_term_meta( $tid, 'forma_demo_site', $site );
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
	// The footer shows the tagline: fill it in when the site has none, or when it is still the previous demo's.
	$tagline = sanitize_text_field( $data['site']['tagline'] ?? '' );
	$now     = trim( (string) get_option( 'blogdescription' ) );
	$old     = array( (string) get_option( 'forma_3d_tk_demo_tagline' ), (string) ( forma_3d_tk_demo_data( 'furniture' )['site']['tagline'] ?? '' ) );
	if ( $tagline && ( '' === $now || in_array( $now, array_filter( $old ), true ) ) ) {
		update_option( 'blogdescription', $tagline );
		update_option( 'forma_3d_tk_demo_tagline', $tagline );
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

	return array(
		'products' => array_map( 'sanitize_text_field', wp_list_pluck( $data['products'], 'name' ) ),
		'trashed'  => $trashed,
	);
}

/**
 * Step 2..n: one product with its 3D model and photos.
 *
 * @param string $site  Starter site ID.
 * @param int    $index Index into the starter site's products.
 * @throws \RuntimeException When the product does not exist.
 */
function forma_3d_tk_demo_product( $site, $index ) {
	$data = forma_3d_tk_demo_data( $site );
	if ( empty( $data['products'][ $index ] ) ) {
		throw new \RuntimeException( 'No such demo product.' );
	}
	$it   = $data['products'][ $index ];
	$slug = sanitize_title( $it['slug'] );
	$name = sanitize_text_field( $it['name'] );
	$file = sanitize_file_name( $it['model'] );
	$sku  = forma_3d_tk_demo_sku( $slug );

	$pid     = wc_get_product_id_by_sku( $sku );
	$product = $pid ? wc_get_product( $pid ) : new WC_Product_Simple();
	$product->set_name( $name );
	$product->set_slug( $slug );
	$product->set_status( 'publish' );
	if ( ! $pid ) {
		$product->set_sku( $sku );
	}
	$product->set_regular_price( wc_format_decimal( $it['regular_price'] ) );
	$product->set_sale_price( wc_format_decimal( $it['sale_price'] ) );
	$product->set_short_description( wp_kses_post( $it['short_description'] ) );
	$product->set_description( wp_kses_post( $it['description'] ) );
	$term = get_term_by( 'slug', sanitize_title( $it['category'] ), 'product_cat' );
	if ( $term ) {
		$product->set_category_ids( array( $term->term_id ) );
	}
	$product->set_featured( ! empty( $it['featured'] ) );
	$product->set_menu_order( (int) $it['menu_order'] );
	// 3D Viewer convention: width = side to side, length = depth.
	$product->set_width( wc_format_decimal( $it['width'] ) );
	$product->set_length( wc_format_decimal( $it['depth'] ) );
	$product->set_height( wc_format_decimal( $it['height'] ) );
	$product->set_weight( wc_format_decimal( $it['weight'] ) );
	$product->set_stock_status( 'instock' );
	$product->set_reviews_allowed( false );
	$pid = $product->save();

	// The 3D model is optional (the Fashion starter site has photos only).
	$model = $file ? forma_3d_tk_demo_media( $site, 'models/' . $file . '.glb', $pid, $name . ' 3D model' ) : 0;
	// Photos: an explicit list of files in images/, or "<model>-0/1/2.webp".
	$files  = ! empty( $it['images'] ) && is_array( $it['images'] )
		? array_map( 'sanitize_file_name', $it['images'] )
		: array( $file . '-0.webp', $file . '-1.webp', $file . '-2.webp' );
	$images = array();
	foreach ( $files as $image_file ) {
		$img = forma_3d_tk_demo_media( $site, 'images/' . $image_file, $pid, $name );
		if ( $img ) {
			update_post_meta( $img, '_wp_attachment_image_alt', $name );
			$images[] = $img;
		}
	}
	$finishes = forma_3d_tk_sanitize_finishes( $it['finishes'] ?? array() );
	$product->set_image_id( $images[0] ?? 0 );
	$product->set_gallery_image_ids( array_slice( $images, 1 ) );
	$product->update_meta_data( '_forma_model_id', $model );
	$product->update_meta_data( '_forma_bg', (string) sanitize_hex_color( $it['bg'] ) );
	$product->update_meta_data( '_forma_materials', sanitize_text_field( $it['materials'] ) );
	$product->update_meta_data( '_forma_designer', sanitize_text_field( $it['designer'] ) );
	$product->update_meta_data( '_forma_finishes', $finishes ? wp_json_encode( $finishes ) : '' );
	$product->update_meta_data( '_forma_demo_site', $site );
	$product->save();

	if ( ! $model ) {
		return;
	}
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
 *
 * @param string $site Starter site ID.
 */
function forma_3d_tk_demo_finish( $site ) {
	$data      = forma_3d_tk_demo_data( $site );
	$switching = '' !== forma_3d_tk_current_site() && forma_3d_tk_current_site() !== $site;
	foreach ( $data['categories'] as $c ) {
		$term = get_term_by( 'slug', sanitize_title( $c['slug'] ), 'product_cat' );
		if ( $term && ! empty( $c['image'] ) ) {
			$img = forma_3d_tk_demo_media( $site, 'images/' . sanitize_file_name( $c['image'] ), 0, sanitize_text_field( $c['name'] ) );
			update_term_meta( $term->term_id, 'thumbnail_id', $img );
		}
	}

	// The starter site's own homepage (its block settings and text), or every Forma block with its defaults.
	$markup  = ! empty( $data['home'] ) ? forma_3d_tk_prepare_looks( $site, forma_3d_tk_clean_home( $data['home'] ) ) : '';
	$markup  = '' !== $markup ? $markup : forma_3d_tk_home_markup();
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
	// When switching starter sites the Home page gets the new design (the old one stays in its revisions);
	// an existing, empty Home page gets the Forma blocks so it can be edited in the block editor.
	if ( $home && ( $switching || '' === trim( $home->post_content ) ) ) {
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
	update_option( 'forma_demo_site', $site );
	$info = forma_3d_tk_starter_site( $site );
	update_option( 'forma_3d_tk_design', forma_3d_tk_sanitize_design( $info['design'] ?? 'original' ) );
	// The shop's "Try it in your room" (AR) section only makes sense for starter sites with 3D models.
	set_theme_mod( 'forma_3d_shop_ar', ! isset( $info['models'] ) || (bool) $info['models'] );
	update_option( 'forma_demo_imported', time() );
	forma_3d_tk_cleanup_package( $site );
}
