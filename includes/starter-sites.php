<?php
/**
 * Starter sites: the demo stores Forma Setup can import.
 *
 * "Furniture" ships inside the plugin (demo/). The other starter sites are
 * downloaded as one zip package only when the user clicks Import, unpacked into
 * uploads/forma-3d-starter/, imported and then deleted, so the plugin stays
 * small. Every starter site uses the same demo.json format.
 *
 * @package Forma3DToolkit
 */

defined( 'ABSPATH' ) || exit;

/**
 * The starter sites, keyed by ID.
 *
 * @return array<string, array<string, mixed>>
 */
function forma_3d_tk_starter_sites() {
	$sites = array(
		'furniture' => array(
			'name'       => __( 'Furniture', 'forma-3d-toolkit' ),
			'desc'       => __( 'Sofas, lounge chairs, tables and storage.', 'forma-3d-toolkit' ),
			'products'   => 16,
			'categories' => 4,
			'remote'     => false,
			'design'     => 'original',
		),
		'lighting'  => array(
			'name'       => __( 'Lighting', 'forma-3d-toolkit' ),
			'desc'       => __( 'Chandeliers, pendants, table lamps, wall lights and lanterns.', 'forma-3d-toolkit' ),
			'products'   => 14,
			'categories' => 5,
			'remote'     => true,
			'size'       => 9,
			'design'     => 'noir',
			'package'    => 2,
		),
		'decor'     => array(
			'name'       => __( 'Home Decor', 'forma-3d-toolkit' ),
			'desc'       => __( 'Vases, faux plants, clocks, mirrors and decorative objects.', 'forma-3d-toolkit' ),
			'products'   => 14,
			'categories' => 4,
			'remote'     => true,
			'size'       => 8,
			'design'     => 'soft',
			'package'    => 1,
		),
	);
	foreach ( $sites as $id => $site ) {
		$sites[ $id ]['thumb'] = FORMA_3D_TK_URI . 'assets/starter-sites/' . $id . '.webp';
	}

	/**
	 * Filters the starter sites shown in Forma Setup.
	 *
	 * @param array $sites Starter sites keyed by ID.
	 */
	return apply_filters( 'forma_3d_tk_starter_sites', $sites );
}

/**
 * One starter site, or null when the ID is unknown.
 *
 * @param string $site Starter site ID.
 * @return array<string, mixed>|null
 */
function forma_3d_tk_starter_site( $site ) {
	$sites = forma_3d_tk_starter_sites();
	return isset( $sites[ $site ] ) ? $sites[ $site ] : null;
}

/**
 * URL of a downloadable starter site's zip package.
 *
 * Each package is a tagged release of the starter site's files in the Forma
 * repository on GitHub, so every plugin version downloads the content it was tested with.
 *
 * @param string $site Starter site ID.
 */
function forma_3d_tk_starter_package( $site ) {
	$info = forma_3d_tk_starter_site( $site );
	$url  = 'https://github.com/Rubyat13/Forma/archive/refs/tags/starter-' . rawurlencode( $site ) . '-v' . (int) ( $info['package'] ?? 1 ) . '.zip';

	/**
	 * Filters where a starter site's zip package is downloaded from.
	 *
	 * @param string $url  Package URL.
	 * @param string $site Starter site ID.
	 */
	return (string) apply_filters( 'forma_3d_tk_starter_package', $url, $site );
}

/**
 * Folder in uploads where a downloaded starter site is unpacked during the import.
 *
 * @param string $site Starter site ID, or '' for the parent folder.
 */
function forma_3d_tk_starter_dir( $site = '' ) {
	$uploads = wp_upload_dir( null, false );
	return trailingslashit( $uploads['basedir'] ) . 'forma-3d-starter/' . ( $site ? $site . '/' : '' );
}

/**
 * Folder that holds a starter site's demo.json, models/ and images/.
 *
 * @param string $site Starter site ID.
 */
function forma_3d_tk_starter_source( $site ) {
	$info = forma_3d_tk_starter_site( $site );
	return empty( $info['remote'] ) ? FORMA_3D_TK_DIR . 'demo/' : forma_3d_tk_starter_dir( $site );
}

/**
 * The starter site that was imported last ('' when none).
 */
function forma_3d_tk_current_site() {
	$site = (string) get_option( 'forma_demo_site', '' );
	if ( '' === $site && get_option( 'forma_demo_imported' ) ) {
		$site = 'furniture'; // Imported with Toolkit 1.0.x, which only had the furniture demo.
	}
	return $site;
}

/**
 * Demo SKU for a product slug.
 *
 * @param string $slug Product slug.
 */
function forma_3d_tk_demo_sku( $slug ) {
	return 'FRM-' . strtoupper( substr( md5( $slug ), 0, 6 ) );
}

/**
 * Prepare WordPress' file system API for writing in uploads.
 *
 * @throws \RuntimeException When WordPress cannot write files directly.
 */
function forma_3d_tk_filesystem() {
	global $wp_filesystem;
	require_once ABSPATH . 'wp-admin/includes/file.php';
	if ( ! WP_Filesystem() || ! $wp_filesystem ) {
		throw new \RuntimeException( esc_html__( 'WordPress cannot write files on this server, so the starter site cannot be unpacked.', 'forma-3d-toolkit' ) );
	}
	return $wp_filesystem;
}

/**
 * Download a starter site's zip package and unpack it for the following import steps.
 *
 * @param string $site Starter site ID.
 * @throws \RuntimeException When the download or unpacking fails, or the package is not valid.
 */
function forma_3d_tk_fetch_package( $site ) {
	$fs  = forma_3d_tk_filesystem();
	$tmp = download_url( forma_3d_tk_starter_package( $site ), 300 );
	if ( is_wp_error( $tmp ) ) {
		throw new \RuntimeException( esc_html( __( 'Could not download the starter site:', 'forma-3d-toolkit' ) . ' ' . $tmp->get_error_message() ) );
	}
	$work = forma_3d_tk_starter_dir() . 'unpack-' . $site . '/';
	$dest = forma_3d_tk_starter_dir( $site );
	$fs->delete( $work, true );
	$fs->delete( $dest, true );
	wp_mkdir_p( $work );
	$unzipped = unzip_file( $tmp, $work );
	wp_delete_file( $tmp );
	if ( is_wp_error( $unzipped ) ) {
		$fs->delete( $work, true );
		throw new \RuntimeException( esc_html( $unzipped->get_error_message() ) );
	}
	// GitHub packages hold one top-level folder; use what is inside it.
	$dirs = glob( $work . '*', GLOB_ONLYDIR );
	$src  = ( is_array( $dirs ) && 1 === count( $dirs ) && ! file_exists( $work . 'demo.json' ) ) ? trailingslashit( $dirs[0] ) : $work;
	if ( ! $fs->move( $src, $dest ) ) {
		$fs->delete( $work, true );
		throw new \RuntimeException( esc_html__( 'Could not unpack the starter site.', 'forma-3d-toolkit' ) );
	}
	$fs->delete( $work, true );

	// A package only needs its data, models and photos: remove anything else.
	$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dest, FilesystemIterator::SKIP_DOTS ) );
	foreach ( $files as $file ) {
		if ( $file->isFile() && ! preg_match( '/\.(json|glb|webp)$/i', $file->getFilename() ) ) {
			wp_delete_file( $file->getPathname() );
		}
	}
	$data = json_decode( (string) file_get_contents( $dest . 'demo.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( ! is_array( $data ) || empty( $data['products'] ) || ! is_array( $data['products'] ) || ! isset( $data['categories'] ) || ! is_array( $data['categories'] ) ) {
		$fs->delete( $dest, true );
		throw new \RuntimeException( esc_html__( 'The starter site package is not valid.', 'forma-3d-toolkit' ) );
	}
}

/**
 * Delete a starter site's unpacked files after the import.
 *
 * @param string $site Starter site ID.
 */
function forma_3d_tk_cleanup_package( $site ) {
	$info = forma_3d_tk_starter_site( $site );
	if ( empty( $info['remote'] ) ) {
		return;
	}
	$fs = forma_3d_tk_filesystem();
	$fs->delete( forma_3d_tk_starter_dir( $site ), true );
	$parent = forma_3d_tk_starter_dir();
	if ( is_dir( $parent ) && ! glob( $parent . '*' ) ) {
		$fs->rmdir( $parent );
	}
}

/**
 * A starter site's data: categories, products, homepage, tagline and store options.
 *
 * @param string $site Starter site ID.
 * @return array<string, mixed>
 * @throws \RuntimeException When the site is unknown or its data is missing.
 */
function forma_3d_tk_demo_data( $site = 'furniture' ) {
	static $cache = array();
	if ( isset( $cache[ $site ] ) ) {
		return $cache[ $site ];
	}
	if ( ! forma_3d_tk_starter_site( $site ) ) {
		throw new \RuntimeException( esc_html__( 'Unknown starter site.', 'forma-3d-toolkit' ) );
	}
	$file = forma_3d_tk_starter_source( $site ) . 'demo.json';
	$data = file_exists( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( ! is_array( $data ) || empty( $data['products'] ) ) {
		throw new \RuntimeException( esc_html__( 'The starter site data is missing. Please start the import again.', 'forma-3d-toolkit' ) );
	}
	$cache[ $site ] = $data;
	return $data;
}

/**
 * Add a demo file (model or photo) to the media library once and return its attachment ID.
 *
 * @param string $site      Starter site ID.
 * @param string $rel       Path inside the starter site, e.g. "images/lamp-0.webp".
 * @param int    $parent_id Parent post.
 * @param string $title     Attachment title.
 * @throws \RuntimeException When the file cannot be added.
 */
function forma_3d_tk_demo_media( $site, $rel, $parent_id = 0, $title = '' ) {
	if ( ! preg_match( '#^(models|images)/[A-Za-z0-9_-]+(\.[A-Za-z0-9_-]+)*\.(glb|webp)$#', $rel ) ) {
		throw new \RuntimeException( esc_html__( 'Invalid demo file path.', 'forma-3d-toolkit' ) );
	}
	$info = forma_3d_tk_starter_site( $site );
	$key  = empty( $info['remote'] ) ? $rel : $site . '/' . $rel; // Furniture keeps the key Toolkit 1.0.x used, so its media is reused.

	$existing = get_posts(
		array(
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'meta_key'    => '_forma_demo_file', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'  => $key, // phpcs:ignore WordPress.DB.SlowDBQuery
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

	$src = forma_3d_tk_starter_source( $site ) . $rel;
	if ( ! file_exists( $src ) ) {
		return 0;
	}
	$tmp = wp_tempnam( basename( $src ) );
	copy( $src, $tmp );
	$id = media_handle_sideload(
		array(
			'name'     => basename( $rel ),
			'tmp_name' => $tmp,
		),
		$parent_id,
		$title
	);
	if ( is_wp_error( $id ) ) {
		wp_delete_file( $tmp );
		throw new \RuntimeException( esc_html( basename( $rel ) . ': ' . $id->get_error_message() ) );
	}
	update_post_meta( $id, '_forma_demo_file', $key );
	return (int) $id;
}

/**
 * Keep only Forma blocks from a homepage's block markup.
 *
 * @param string $markup Block markup.
 */
function forma_3d_tk_clean_home( $markup ) {
	$blocks = array_filter(
		parse_blocks( (string) $markup ),
		static function ( $block ) {
			return ! empty( $block['blockName'] ) && 0 === strpos( $block['blockName'], 'forma/' );
		}
	);
	return implode( "\n\n", array_map( 'serialize_block', $blocks ) );
}

/**
 * Move the products of other starter sites to the Trash.
 *
 * @param string $site The starter site being imported.
 * @return int Number of products moved to the Trash.
 */
function forma_3d_tk_trash_other_demos( $site ) {
	$ids = get_posts(
		array(
			'post_type'   => 'product',
			'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			'numberposts' => -1,
			'fields'      => 'ids',
			'meta_query'  => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array(
					'key'     => '_forma_demo_site',
					'value'   => $site,
					'compare' => '!=',
				),
			),
		)
	);
	// Furniture products imported with Toolkit 1.0.x carry no site tag: find them by their demo SKU.
	if ( 'furniture' !== $site ) {
		foreach ( forma_3d_tk_demo_data( 'furniture' )['products'] as $item ) {
			$pid = wc_get_product_id_by_sku( forma_3d_tk_demo_sku( $item['slug'] ) );
			if ( $pid && ! get_post_meta( $pid, '_forma_demo_site', true ) ) {
				$ids[] = $pid;
			}
		}
	}
	$ids = array_unique( array_map( 'intval', $ids ) );
	foreach ( $ids as $id ) {
		wp_trash_post( $id );
	}
	return count( $ids );
}
