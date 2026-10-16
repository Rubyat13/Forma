<?php
/**
 * Generic page (cart, checkout, account and content pages).
 *
 * @package Forma3D
 */

defined( 'ABSPATH' ) || exit;

get_header();

// Pages built with Forma blocks get the full-width 3D canvas instead of the text layout.
if ( forma_3d_has_forma_blocks() ) {
	while ( have_posts() ) {
		the_post();
		ob_start();
		the_content();
		forma_3d_render_canvas( ob_get_clean() );
	}
	get_footer();
	return;
}
?>
<main id="main" class="page-wrap">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<header class="page__head">
			<?php if ( function_exists( 'woocommerce_breadcrumb' ) && ( is_cart() || is_checkout() || is_account_page() ) ) : ?>
				<?php woocommerce_breadcrumb(); ?>
			<?php endif; ?>
			<h1 class="page__title" data-split><?php the_title(); ?></h1>
		</header>
		<div class="page__content entry">
			<?php
			the_content();
			wp_link_pages(
				array(
					'before' => '<nav class="page-links" aria-label="' . esc_attr__( 'Page', 'forma-3d' ) . '">' . esc_html__( 'Pages:', 'forma-3d' ),
					'after'  => '</nav>',
				)
			);
			?>
		</div>
		<?php
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
	endwhile;
	?>
</main>
<?php
get_footer();
