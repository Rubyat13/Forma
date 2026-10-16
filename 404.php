<?php
/**
 * Not found.
 *
 * @package Forma3D
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="page-wrap page-wrap--404">
	<header class="page__head">
		<p class="eyebrow">404</p>
		<h1 class="page__title" data-split><?php echo wp_kses_post( __( 'This room is <em>empty.</em>', 'forma-3d' ) ); ?></h1>
		<p><?php esc_html_e( 'The page you were looking for has moved or never existed.', 'forma-3d' ); ?></p>
		<a class="btn" href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) ); ?>"><?php esc_html_e( 'Browse furniture', 'forma-3d' ); ?></a>
	</header>
</main>
<?php
get_footer();
