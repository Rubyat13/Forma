<?php
/**
 * Site footer, cart drawer and quick-view modal.
 *
 * @package Forma3D
 */

defined( 'ABSPATH' ) || exit;
$forma_3d_shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
?>
<footer class="site-footer">
	<?php if ( get_theme_mod( 'forma_3d_footer_cta', true ) ) : ?>
		<div class="site-footer__cta">
			<p class="eyebrow"><?php echo esc_html( get_theme_mod( 'forma_3d_footer_cta_eyebrow', __( 'Showroom in your pocket', 'forma-3d' ) ) ); ?></p>
			<h2 class="site-footer__title" data-split><?php echo wp_kses( get_theme_mod( 'forma_3d_footer_cta_title', __( 'Turn it over.<br><em>Then take it home.</em>', 'forma-3d' ) ), forma_3d_inline_kses() ); ?></h2>
			<?php $forma_3d_cta = get_theme_mod( 'forma_3d_footer_cta_button', __( 'Explore the collection', 'forma-3d' ) ); ?>
			<?php if ( $forma_3d_cta ) : ?>
				<a class="btn btn--light btn--lg" href="<?php echo esc_url( $forma_3d_shop_url ); ?>" data-magnetic><?php echo esc_html( $forma_3d_cta ); ?> <?php forma_3d_the_icon( 'arrow' ); ?></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="site-footer__grid">
		<div class="site-footer__col site-footer__about">
			<?php forma_3d_logo(); ?>
			<?php $forma_3d_about = get_theme_mod( 'forma_3d_footer_about', '' ); ?>
			<?php if ( $forma_3d_about || get_bloginfo( 'description' ) ) : ?>
				<p><?php echo esc_html( $forma_3d_about ? $forma_3d_about : get_bloginfo( 'description' ) ); ?></p>
			<?php endif; ?>
		</div>
		<?php
		$forma_3d_has_menus = false;
		foreach ( array( 'footer-1', 'footer-2', 'footer-3' ) as $forma_3d_location ) :
			if ( ! has_nav_menu( $forma_3d_location ) ) {
				continue;
			}
			$forma_3d_has_menus = true;
			?>
			<nav class="site-footer__col" aria-label="<?php echo esc_attr( wp_get_nav_menu_name( $forma_3d_location ) ); ?>">
				<h3><?php echo esc_html( wp_get_nav_menu_name( $forma_3d_location ) ); ?></h3>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => $forma_3d_location,
						'container'      => false,
						'depth'          => 1,
					)
				);
				?>
			</nav>
		<?php endforeach; ?>
		<?php if ( ! $forma_3d_has_menus && function_exists( 'wc_get_page_permalink' ) ) : ?>
			<nav class="site-footer__col" aria-label="<?php esc_attr_e( 'Shop', 'forma-3d' ); ?>">
				<h3><?php esc_html_e( 'Shop', 'forma-3d' ); ?></h3>
				<ul>
					<li><a href="<?php echo esc_url( $forma_3d_shop_url ); ?>"><?php esc_html_e( 'All products', 'forma-3d' ); ?></a></li>
					<?php foreach ( forma_3d_nav_categories() as $forma_3d_cat ) : ?>
						<li><a href="<?php echo esc_url( get_term_link( $forma_3d_cat ) ); ?>"><?php echo esc_html( $forma_3d_cat->name ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
		<?php endif; ?>
		<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
			<div class="site-footer__col site-footer__widgets"><?php dynamic_sidebar( 'footer-1' ); ?></div>
		<?php endif; ?>
	</div>

	<div class="site-footer__word" aria-hidden="true"><?php bloginfo( 'name' ); ?></div>

	<div class="site-footer__legal">
		<span>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></span>
		<span class="site-footer__credits">
			<?php
			printf(
				/* translators: 1: theme name, 2: theme author link. */
				esc_html__( 'Theme: %1$s by %2$s', 'forma-3d' ),
				'Forma 3D',
				'<a href="' . esc_url( 'https://profiles.wordpress.org/rubyat13/' ) . '">Al Rubyat</a>'
			);
			?>
		</span>
	</div>
</footer>

<?php if ( function_exists( 'WC' ) ) : ?>
<div class="drawer" data-drawer aria-hidden="true">
	<div class="drawer__scrim" data-drawer-close></div>
	<aside class="drawer__panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Cart', 'forma-3d' ); ?>" data-lenis-prevent>
		<header class="drawer__head">
			<h2><?php esc_html_e( 'Your cart', 'forma-3d' ); ?> <?php echo wp_kses_post( forma_3d_cart_count_html() ); ?></h2>
			<button class="icon-btn" type="button" data-drawer-close aria-label="<?php esc_attr_e( 'Close cart', 'forma-3d' ); ?>"><?php forma_3d_the_icon( 'close' ); ?></button>
		</header>
		<?php forma_3d_mini_cart(); ?>
	</aside>
</div>

<div class="qv" data-qv aria-hidden="true">
	<div class="qv__scrim" data-qv-close></div>
	<div class="qv__panel" role="dialog" aria-modal="true" aria-labelledby="qv-title" data-lenis-prevent>
		<button class="icon-btn qv__close" type="button" data-qv-close aria-label="<?php esc_attr_e( 'Close', 'forma-3d' ); ?>"><?php forma_3d_the_icon( 'close' ); ?></button>
		<div class="qv__stage" data-qv-stage>
			<div class="qv__progress"><i data-qv-progress></i></div>
			<p class="qv__hint"><?php forma_3d_the_icon( 'hand' ); ?> <?php esc_html_e( 'Drag to rotate · Pinch or scroll to zoom', 'forma-3d' ); ?></p>
		</div>
		<div class="qv__info">
			<p class="eyebrow" data-qv-cat></p>
			<h2 class="qv__title" id="qv-title" data-qv-title></h2>
			<div class="qv__price" data-qv-price></div>
			<p class="qv__desc" data-qv-desc></p>
			<div class="finish" data-qv-finishes hidden>
				<p class="finish__label"><?php esc_html_e( 'Finish', 'forma-3d' ); ?>: <strong data-finish-name></strong></p>
				<div class="finish__list" data-finish-list></div>
			</div>
			<p class="qv__dims" data-qv-dims></p>
			<div class="qv__actions">
				<button type="button" class="btn js-add-to-cart" data-qv-add><?php esc_html_e( 'Add to cart', 'forma-3d' ); ?></button>
				<a class="btn btn--ghost" data-qv-link href="#"><?php esc_html_e( 'Full details', 'forma-3d' ); ?></a>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>

<div class="toast" data-toast role="status" aria-live="polite"></div>

<?php wp_footer(); ?>
</body>
</html>
