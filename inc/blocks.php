<?php
/**
 * Block editor support: the full-width 3D canvas for pages built with the
 * Forma homepage blocks (provided by the optional Forma 3D Toolkit plugin)
 * and the default homepage shown when the front page has no content.
 *
 * @package Forma3D
 */

defined( 'ABSPATH' ) || exit;

/**
 * Homepage section slugs, in order.
 */
function forma_3d_section_slugs() {
	return array( 'hero', 'statement', 'showcase', 'rooms', 'anatomy', 'marquee', 'arrivals', 'promise' );
}

/**
 * Whether a post uses any Forma homepage block that is registered on this site.
 *
 * @param int|WP_Post|null $post Post.
 */
function forma_3d_has_forma_blocks( $post = null ) {
	$post = get_post( $post );
	if ( ! $post || ! has_blocks( $post->post_content ) ) {
		return false;
	}
	$registry = WP_Block_Type_Registry::get_instance();
	foreach ( forma_3d_section_slugs() as $slug ) {
		if ( $registry->is_registered( 'forma/' . $slug ) && has_block( 'forma/' . $slug, $post ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Whether the current request renders the 3D canvas.
 */
function forma_3d_uses_stage() {
	if ( is_front_page() && 'page' === get_option( 'show_on_front' ) ) {
		return true;
	}
	if ( is_front_page() && is_home() ) {
		return false;
	}
	return is_page() && forma_3d_has_forma_blocks( get_queried_object_id() );
}

/**
 * Default homepage: every section rendered with the theme's default content.
 */
function forma_3d_default_home_html() {
	$html = '';
	foreach ( forma_3d_section_slugs() as $slug ) {
		$fn    = 'forma_3d_section_' . $slug;
		$html .= $fn( array() );
	}
	return $html;
}

/**
 * Print the 3D stage, loader and page content.
 *
 * @param string $html Rendered content (blocks already processed).
 */
function forma_3d_render_canvas( $html ) {
	$data = forma_3d_home_data();
	$has3 = $data['hero'] || $data['showcase'] || $data['anatomy'];
	?>
	<div class="stage" aria-hidden="true"><canvas id="stage-canvas"></canvas></div>
	<?php if ( $has3 ) : ?>
		<div class="loader" data-loader aria-hidden="true">
			<div class="loader__top"><span><?php bloginfo( 'name' ); ?></span><span><?php esc_html_e( 'Loading', 'forma-3d' ); ?></span></div>
			<div class="loader__count"><span data-loader-count>0</span><sup>%</sup></div>
			<div class="loader__bar"><i data-loader-bar></i></div>
		</div>
	<?php endif; ?>
	<main id="main" class="home-wrap">
		<?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered blocks / escaped section templates. ?>
	</main>
	<?php
	wp_print_inline_script_tag( 'window.forma3dHome = ' . wp_json_encode( $data ) . ';' );
}
