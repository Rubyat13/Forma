<?php
/**
 * Shop / product category archive.
 *
 * Overrides woocommerce/templates/archive-product.php.
 *
 * @package Forma3D
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header();

$forma_3d_shop_url  = wc_get_page_permalink( 'shop' );
$forma_3d_current   = is_product_category() ? get_queried_object() : null;
$forma_3d_cats      = forma_3d_nav_categories();
$forma_3d_total     = (int) wc_get_loop_prop( 'total' );
$forma_3d_all_count = (int) array_sum( wp_list_pluck( $forma_3d_cats, 'count' ) );
?>
<main id="main" class="shop">
	<header class="shop-hero">
		<?php woocommerce_breadcrumb(); ?>
		<div class="shop-hero__row">
			<h1 class="shop-hero__title" data-split>
				<?php
				if ( $forma_3d_current ) {
					echo esc_html( $forma_3d_current->name );
				} elseif ( is_search() ) {
					/* translators: %s search query */
					printf( esc_html__( 'Results for “%s”', 'forma-3d' ), esc_html( get_search_query() ) );
				} else {
					echo wp_kses_post( __( 'The <em>collection</em>', 'forma-3d' ) );
				}
				?>
			</h1>
			<p class="shop-hero__intro" data-reveal>
				<?php
				if ( $forma_3d_current && $forma_3d_current->description ) {
					echo esc_html( $forma_3d_current->description );
				} else {
					esc_html_e( 'Products with a 3D model can be turned around right here: hover for another angle, or tap “View in 3D”.', 'forma-3d' );
				}
				?>
			</p>
		</div>
	</header>

	<div class="shop-bar" data-shop-bar>
		<nav class="pills" aria-label="<?php esc_attr_e( 'Categories', 'forma-3d' ); ?>">
			<a class="pill<?php echo $forma_3d_current ? '' : ' is-active'; ?>" href="<?php echo esc_url( $forma_3d_shop_url ); ?>"><?php esc_html_e( 'All', 'forma-3d' ); ?> <sup><?php echo esc_html( $forma_3d_all_count ); ?></sup></a>
			<?php foreach ( $forma_3d_cats as $forma_3d_cat ) : ?>
				<a class="pill<?php echo ( $forma_3d_current && $forma_3d_current->term_id === $forma_3d_cat->term_id ) ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_term_link( $forma_3d_cat ) ); ?>"><?php echo esc_html( $forma_3d_cat->name ); ?> <sup><?php echo esc_html( $forma_3d_cat->count ); ?></sup></a>
			<?php endforeach; ?>
		</nav>
		<div class="shop-bar__right">
			<?php
			if ( woocommerce_product_loop() ) {
				woocommerce_result_count();
				woocommerce_catalog_ordering();
			}
			?>
		</div>
	</div>

	<div class="shop-notices"><?php woocommerce_output_all_notices(); ?></div>

	<?php if ( woocommerce_product_loop() ) : ?>
		<?php woocommerce_product_loop_start(); ?>
		<?php
		while ( have_posts() ) {
			the_post();
			do_action( 'woocommerce_shop_loop' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce hook.
			wc_get_template_part( 'content', 'product' );
		}
		?>
		<?php woocommerce_product_loop_end(); ?>
		<div class="shop-pagination"><?php woocommerce_pagination(); ?></div>
	<?php else : ?>
		<div class="shop-empty">
			<?php forma_3d_the_icon( 'cube' ); ?>
			<p><?php esc_html_e( 'Nothing here yet — new pieces are on their way.', 'forma-3d' ); ?></p>
			<a class="btn" href="<?php echo esc_url( $forma_3d_shop_url ); ?>"><?php esc_html_e( 'See all furniture', 'forma-3d' ); ?></a>
		</div>
	<?php endif; ?>

	<?php if ( get_theme_mod( 'forma_3d_shop_ar', true ) && forma_3d_bp3d_active() ) : ?>
	<section class="shop-ar" data-reveal>
		<div class="shop-ar__text">
			<p class="eyebrow"><?php esc_html_e( 'Augmented reality', 'forma-3d' ); ?></p>
			<h2 class="display"><?php echo wp_kses_post( __( 'Try it in <em>your</em> room.', 'forma-3d' ) ); ?></h2>
			<p><?php esc_html_e( 'Open any product on your phone and tap “View in your room” to place a true-scale model on your floor — no app needed.', 'forma-3d' ); ?></p>
		</div>
		<ul class="shop-ar__steps">
			<li><span>01</span><?php esc_html_e( 'Open a product on your phone', 'forma-3d' ); ?></li>
			<li><span>02</span><?php esc_html_e( 'Tap “View in your room”', 'forma-3d' ); ?></li>
			<li><span>03</span><?php esc_html_e( 'Point at the floor and place it', 'forma-3d' ); ?></li>
		</ul>
	</section>
	<?php endif; ?>
</main>
<?php
get_footer();
