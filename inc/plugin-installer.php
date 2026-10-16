<?php
/**
 * Store-kit edition: one-click install of the plugins the demo store needs.
 *
 * Right after the theme is activated a dialog offers to install and activate
 * WooCommerce and 3D Viewer (from WordPress.org) and Forma 3D Toolkit (from
 * GitHub), then opens Appearance > Forma Setup to import the demo store.
 * Until everything is active, a dismissible notice offers the same dialog.
 *
 * @package Forma3D
 */

defined( 'ABSPATH' ) || exit;

/**
 * Plugins the demo store needs, in install order.
 *
 * Each item has a name, a description, the plugin folders that count as
 * installed (the first one is used for a new install) and either a
 * WordPress.org slug or a zip URL.
 *
 * @return array<string, array<string, mixed>>
 */
function forma_3d_installer_plugins() {
	return apply_filters(
		'forma_3d_installer_plugins',
		array(
			'woocommerce'      => array(
				'name'    => 'WooCommerce',
				'desc'    => __( 'Products, cart and checkout', 'forma-3d' ),
				'folders' => array( 'woocommerce' ),
				'slug'    => 'woocommerce',
			),
			'3d-viewer'        => array(
				'name'    => '3D Viewer',
				'desc'    => __( '3D models, dimensions and AR on product pages', 'forma-3d' ),
				'folders' => array( '3d-viewer', '3d-viewer-premium' ),
				'slug'    => '3d-viewer',
			),
			'forma-3d-toolkit' => array(
				'name'    => 'Forma 3D Toolkit',
				'desc'    => __( 'Homepage blocks and the one-click demo store', 'forma-3d' ),
				'folders' => array( 'forma-3d-toolkit' ),
				'zip'     => 'https://github.com/Rubyat13/Forma/archive/refs/heads/toolkit.zip',
			),
		)
	);
}

/**
 * Install state of a plugin: 'active', 'installed' or 'missing', with its main file.
 *
 * @param array $plugin Item from forma_3d_installer_plugins().
 * @return array{state: string, file: string}
 */
function forma_3d_installer_status( $plugin ) {
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$found = '';
	foreach ( array_keys( get_plugins() ) as $file ) {
		if ( in_array( dirname( $file ), $plugin['folders'], true ) ) {
			if ( is_plugin_active( $file ) ) {
				return array(
					'state' => 'active',
					'file'  => $file,
				);
			}
			$found = $found ? $found : $file;
		}
	}
	return array(
		'state' => $found ? 'installed' : 'missing',
		'file'  => $found,
	);
}

/**
 * Whether the dialog and notice apply: the user can install plugins, one of
 * them is not active yet and the demo has not been imported.
 */
function forma_3d_installer_needed() {
	if ( ! current_user_can( 'install_plugins' ) || ! current_user_can( 'activate_plugins' ) || get_option( 'forma_demo_imported' ) ) {
		return false;
	}
	foreach ( forma_3d_installer_plugins() as $plugin ) {
		if ( 'active' !== forma_3d_installer_status( $plugin )['state'] ) {
			return true;
		}
	}
	return false;
}

/**
 * Whether Forma 3D Toolkit (which brings the real Forma Setup screen) is active.
 */
function forma_3d_installer_toolkit_active() {
	$plugins = forma_3d_installer_plugins();
	return isset( $plugins['forma-3d-toolkit'] ) && 'active' === forma_3d_installer_status( $plugins['forma-3d-toolkit'] )['state'];
}

// Open the dialog on the first admin screen after the theme is activated.
add_action(
	'after_switch_theme',
	function () {
		set_transient( 'forma_3d_installer_popup', 1, HOUR_IN_SECONDS );
	}
);

// Until the Toolkit is active, Appearance > Forma Setup opens the install dialog.
add_action(
	'admin_menu',
	function () {
		if ( forma_3d_installer_needed() && ! forma_3d_installer_toolkit_active() ) {
			add_theme_page( __( 'Forma Setup', 'forma-3d' ), __( 'Forma Setup', 'forma-3d' ), 'install_plugins', 'forma-setup', 'forma_3d_installer_page' );
		}
	}
);

/**
 * Placeholder Forma Setup screen shown before the Toolkit is installed.
 */
function forma_3d_installer_page() {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Forma Setup', 'forma-3d' ); ?></h1>
		<p><?php esc_html_e( 'Install the plugins first. Forma Setup then imports the 3D demo store.', 'forma-3d' ); ?></p>
		<p><button type="button" class="button button-primary button-hero" data-forma-installer-open><?php esc_html_e( 'Install plugins', 'forma-3d' ); ?></button></p>
	</div>
	<?php
}

add_action( 'admin_enqueue_scripts', 'forma_3d_installer_enqueue' );

/**
 * Load the dialog script and styles when they are needed.
 *
 * @param string $hook Current admin screen.
 */
function forma_3d_installer_enqueue( $hook ) {
	if ( ! forma_3d_installer_needed() ) {
		return;
	}
	$on_page = 'appearance_page_forma-setup' === $hook && ! forma_3d_installer_toolkit_active();
	$popup   = $on_page || get_transient( 'forma_3d_installer_popup' );
	if ( ! $popup && get_user_meta( get_current_user_id(), 'forma_3d_installer_dismissed', true ) ) {
		return;
	}
	delete_transient( 'forma_3d_installer_popup' );

	wp_enqueue_style( 'forma-3d-installer', FORMA_3D_URI . '/assets/css/plugin-installer.css', array(), FORMA_3D_VERSION );
	wp_enqueue_script( 'forma-3d-installer', FORMA_3D_URI . '/assets/js/plugin-installer.js', array(), FORMA_3D_VERSION, true );
	wp_localize_script(
		'forma-3d-installer',
		'forma3dInstaller',
		array(
			'ajax'     => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'forma-3d-installer' ),
			'autoOpen' => (bool) $popup,
			'setupUrl' => admin_url( 'themes.php?page=forma-setup' ),
			'i18n'     => array(
				'working' => __( 'Installing…', 'forma-3d' ),
				'active'  => __( 'Active', 'forma-3d' ),
				'error'   => __( 'Something went wrong. Please try again.', 'forma-3d' ),
				'retry'   => __( 'Try again', 'forma-3d' ),
				'done'    => __( 'All set. Opening Forma Setup…', 'forma-3d' ),
			),
		)
	);
	if ( ! $on_page ) {
		add_action( 'admin_notices', 'forma_3d_installer_notice' );
	}
	add_action( 'admin_footer', 'forma_3d_installer_dialog' );
}

/**
 * Dismissible notice that opens the dialog.
 */
function forma_3d_installer_notice() {
	?>
	<div class="notice notice-info is-dismissible" id="forma-3d-installer-notice">
		<p>
			<strong><?php esc_html_e( 'Forma 3D:', 'forma-3d' ); ?></strong>
			<?php esc_html_e( 'Install the plugins for the shop and the 3D demo store in one click.', 'forma-3d' ); ?>
			<button type="button" class="button button-primary" data-forma-installer-open><?php esc_html_e( 'Install plugins', 'forma-3d' ); ?></button>
		</p>
	</div>
	<?php
}

/**
 * The install dialog.
 */
function forma_3d_installer_dialog() {
	?>
	<dialog id="forma-3d-installer" class="forma-installer" aria-labelledby="forma-3d-installer-title">
		<p class="forma-installer__eyebrow"><?php esc_html_e( 'Forma 3D', 'forma-3d' ); ?></p>
		<h2 id="forma-3d-installer-title"><?php esc_html_e( 'Set up your 3D store', 'forma-3d' ); ?></h2>
		<p><?php esc_html_e( 'The shop and the demo import need these plugins. Install and activate them in one click, then import the demo store.', 'forma-3d' ); ?></p>
		<ul class="forma-installer__list">
			<?php foreach ( forma_3d_installer_plugins() as $key => $plugin ) : ?>
				<?php $state = forma_3d_installer_status( $plugin )['state']; ?>
				<li data-plugin="<?php echo esc_attr( $key ); ?>" data-state="<?php echo esc_attr( $state ); ?>">
					<span class="forma-installer__dot" aria-hidden="true"></span>
					<span class="forma-installer__text">
						<strong><?php echo esc_html( $plugin['name'] ); ?></strong>
						<span><?php echo esc_html( $plugin['desc'] ); ?></span>
					</span>
					<span class="forma-installer__state" data-label>
						<?php
						if ( 'active' === $state ) {
							esc_html_e( 'Active', 'forma-3d' );
						} elseif ( 'installed' === $state ) {
							esc_html_e( 'Installed', 'forma-3d' );
						} else {
							esc_html_e( 'Not installed', 'forma-3d' );
						}
						?>
					</span>
				</li>
			<?php endforeach; ?>
		</ul>
		<p class="forma-installer__message" data-message role="status"></p>
		<div class="forma-installer__actions">
			<button type="button" class="button button-primary button-hero" data-install><?php esc_html_e( 'Install & activate plugins', 'forma-3d' ); ?></button>
			<button type="button" class="button button-link" data-close><?php esc_html_e( 'Not now', 'forma-3d' ); ?></button>
		</div>
	</dialog>
	<?php
}

add_action( 'wp_ajax_forma_3d_installer_dismiss', 'forma_3d_installer_dismiss' );

/**
 * Remember that the user dismissed the notice.
 */
function forma_3d_installer_dismiss() {
	check_ajax_referer( 'forma-3d-installer', 'nonce' );
	update_user_meta( get_current_user_id(), 'forma_3d_installer_dismissed', 1 );
	wp_send_json_success();
}

add_action( 'wp_ajax_forma_3d_install_plugin', 'forma_3d_installer_ajax' );

/**
 * Install (when missing) and activate one plugin from the list.
 */
function forma_3d_installer_ajax() {
	check_ajax_referer( 'forma-3d-installer', 'nonce' );
	if ( ! current_user_can( 'install_plugins' ) || ! current_user_can( 'activate_plugins' ) ) {
		wp_send_json_error( array( 'message' => __( 'You are not allowed to install plugins on this site.', 'forma-3d' ) ), 403 );
	}
	$plugins = forma_3d_installer_plugins();
	$key     = sanitize_key( wp_unslash( $_POST['plugin'] ?? '' ) );
	if ( ! isset( $plugins[ $key ] ) ) {
		wp_send_json_error( array( 'message' => __( 'Unknown plugin.', 'forma-3d' ) ) );
	}
	$plugin = $plugins[ $key ];

	$status = forma_3d_installer_status( $plugin );
	if ( 'missing' === $status['state'] ) {
		$installed = forma_3d_installer_install( $plugin );
		if ( is_wp_error( $installed ) ) {
			wp_send_json_error( array( 'message' => $installed->get_error_message() ) );
		}
		wp_clean_plugins_cache();
		$status = forma_3d_installer_status( $plugin );
	}
	if ( 'installed' === $status['state'] ) {
		ob_start(); // Some plugins print notices while they activate.
		$activated = activate_plugin( $status['file'] );
		ob_end_clean();
		if ( is_wp_error( $activated ) ) {
			wp_send_json_error( array( 'message' => $activated->get_error_message() ) );
		}
		// Go to Forma Setup next, not to the WooCommerce or 3D Viewer welcome screens.
		delete_transient( '_wc_activation_redirect' );
		delete_option( 'bp3d_onboarding_redirect' );
		// Plugins using the Freemius SDK (3D Viewer) redirect to their opt-in screen once; it is offered later as a notice.
		delete_transient( 'fs_plugin_' . dirname( $status['file'] ) . '_activated' );
		if ( ! empty( $plugin['slug'] ) ) {
			delete_transient( 'fs_plugin_' . $plugin['slug'] . '_activated' );
		}
		$status['state'] = 'active';
	}
	if ( 'active' !== $status['state'] ) {
		/* translators: %s: plugin name. */
		wp_send_json_error( array( 'message' => sprintf( __( '%s could not be installed. Install it from Plugins > Add New Plugin instead.', 'forma-3d' ), $plugin['name'] ) ) );
	}
	wp_send_json_success( array( 'state' => 'active' ) );
}

/**
 * Download and unpack a plugin.
 *
 * @param array $plugin Item from forma_3d_installer_plugins().
 * @return true|WP_Error
 */
function forma_3d_installer_install( $plugin ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';

	if ( ! empty( $plugin['slug'] ) ) {
		$api = plugins_api(
			'plugin_information',
			array(
				'slug'   => $plugin['slug'],
				'fields' => array( 'sections' => false ),
			)
		);
		if ( is_wp_error( $api ) ) {
			return $api;
		}
		$package = $api->download_link;
		$rename  = null;
	} else {
		$package = $plugin['zip'];
		// A GitHub branch zip unpacks to "<repo>-<branch>/"; install it under the plugin's own folder.
		$rename = static function ( $source, $remote_source ) use ( $plugin ) {
			global $wp_filesystem;
			$target = trailingslashit( $remote_source ) . $plugin['folders'][0] . '/';
			if ( untrailingslashit( $source ) === untrailingslashit( $target ) ) {
				return $source;
			}
			return $wp_filesystem->move( $source, $target ) ? $target : new WP_Error( 'forma_3d_unpack', __( 'Could not unpack the plugin.', 'forma-3d' ) );
		};
		add_filter( 'upgrader_source_selection', $rename, 10, 2 );
	}

	$skin     = new WP_Ajax_Upgrader_Skin();
	$upgrader = new Plugin_Upgrader( $skin );
	$result   = $upgrader->install( $package );
	if ( $rename ) {
		remove_filter( 'upgrader_source_selection', $rename, 10 );
	}

	if ( is_wp_error( $result ) ) {
		return $result;
	}
	if ( is_wp_error( $skin->result ) ) {
		return $skin->result;
	}
	if ( $skin->get_errors()->has_errors() ) {
		return new WP_Error( 'forma_3d_install', $skin->get_error_messages() );
	}
	if ( ! $result ) {
		// No direct file access (the site asks for FTP details): use the normal plugin screens instead.
		return new WP_Error( 'forma_3d_filesystem', __( 'WordPress could not write the plugin files. Install the plugins from Plugins > Add New Plugin instead.', 'forma-3d' ) );
	}
	return true;
}
