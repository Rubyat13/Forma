<?php
/**
 * Site header.
 *
 * @package Forma3D
 */

defined( 'ABSPATH' ) || exit;
$forma_3d_shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$forma_3d_cats     = forma_3d_nav_categories();
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#eeeae3">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'forma-3d' ); ?></a>

<header class="site-header" data-header>
	<div class="site-header__inner">
		<?php forma_3d_logo(); ?>

		<nav class="site-nav" aria-label="<?php esc_attr_e( 'Primary', 'forma-3d' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'site-nav__list',
						'depth'          => 1,
					)
				);
			} else {
				echo '<ul class="site-nav__list">';
				if ( function_exists( 'wc_get_page_permalink' ) ) {
					printf( '<li class="%s"><a href="%s">%s</a></li>', ( function_exists( 'is_shop' ) && is_shop() ) ? 'current-menu-item' : '', esc_url( $forma_3d_shop_url ), esc_html__( 'Shop all', 'forma-3d' ) );
					foreach ( $forma_3d_cats as $forma_3d_cat ) {
						printf( '<li class="%s"><a href="%s">%s</a></li>', is_tax( 'product_cat', $forma_3d_cat->term_id ) ? 'current-menu-item' : '', esc_url( get_term_link( $forma_3d_cat ) ), esc_html( $forma_3d_cat->name ) );
					}
				} else {
					wp_list_pages(
						array(
							'title_li' => '',
							'depth'    => 1,
							'number'   => 6,
						)
					);
				}
				echo '</ul>';
			}
			?>
		</nav>

		<div class="site-header__actions">
			<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
				<a class="icon-btn hide-sm" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" aria-label="<?php esc_attr_e( 'Account', 'forma-3d' ); ?>"><?php forma_3d_the_icon( 'user' ); ?></a>
				<button class="icon-btn cart-btn" type="button" data-drawer-open aria-label="<?php esc_attr_e( 'Open cart', 'forma-3d' ); ?>">
					<?php forma_3d_the_icon( 'bag' ); ?>
					<?php echo wp_kses_post( forma_3d_cart_count_html() ); ?>
				</button>
			<?php endif; ?>
			<button class="icon-btn menu-btn" type="button" data-menu-toggle aria-expanded="false" aria-controls="mobile-menu" aria-label="<?php esc_attr_e( 'Menu', 'forma-3d' ); ?>">
				<span></span><span></span>
			</button>
		</div>
	</div>
</header>

<div class="mobile-menu" id="mobile-menu" data-mobile-menu hidden>
	<ul>
		<li><a href="<?php echo esc_url( $forma_3d_shop_url ); ?>"><?php esc_html_e( 'Shop all', 'forma-3d' ); ?></a></li>
		<?php foreach ( $forma_3d_cats as $forma_3d_cat ) : ?>
			<li><a href="<?php echo esc_url( get_term_link( $forma_3d_cat ) ); ?>"><?php echo esc_html( $forma_3d_cat->name ); ?></a></li>
		<?php endforeach; ?>
		<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
			<li><a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php esc_html_e( 'Account', 'forma-3d' ); ?></a></li>
		<?php endif; ?>
	</ul>
</div>
