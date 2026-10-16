<?php
/**
 * Front page.
 *
 * With "Your homepage displays: Your latest posts" this shows the blog index.
 * With a static front page it shows that page on the full-width 3D canvas:
 * its blocks when it has content, otherwise the default Forma homepage.
 *
 * @package Forma3D
 */

defined( 'ABSPATH' ) || exit;

if ( 'page' !== get_option( 'show_on_front' ) ) {
	get_template_part( 'index' );
	return;
}

get_header();

$forma_3d_html = '';
while ( have_posts() ) {
	the_post();
	$forma_3d_content = get_the_content();
	// Forma blocks saved earlier but their plugin is no longer active: fall back to the default homepage.
	$forma_3d_orphan = false !== strpos( $forma_3d_content, '<!-- wp:forma/' ) && ! forma_3d_has_forma_blocks();
	if ( '' === trim( $forma_3d_content ) || $forma_3d_orphan ) {
		$forma_3d_html = forma_3d_default_home_html();
	} else {
		ob_start();
		the_content();
		$forma_3d_html = ob_get_clean();
	}
}

forma_3d_render_canvas( $forma_3d_html );

get_footer();
