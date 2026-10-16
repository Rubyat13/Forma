<?php
/**
 * Fallback template (blog index, archives, search).
 *
 * @package Forma3D
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="page-wrap">
	<header class="page__head">
		<h1 class="page__title" data-split>
			<?php
			if ( is_search() ) {
				/* translators: %s search query */
				printf( esc_html__( 'Search: %s', 'forma-3d' ), esc_html( get_search_query() ) );
			} elseif ( is_archive() ) {
				echo esc_html( wp_strip_all_tags( get_the_archive_title() ) );
			} else {
				esc_html_e( 'Journal', 'forma-3d' );
			}
			?>
		</h1>
	</header>
	<div class="page__content">
		<?php if ( have_posts() ) : ?>
			<div class="post-list">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article <?php post_class( 'post-list__item' ); ?> data-reveal>
						<?php if ( has_post_thumbnail() ) : ?>
							<a class="post-list__thumb" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php the_post_thumbnail( 'medium_large' ); ?></a>
						<?php endif; ?>
						<p class="eyebrow"><?php echo esc_html( get_the_date() ); ?></p>
						<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<?php the_excerpt(); ?>
					</article>
				<?php endwhile; ?>
			</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Nothing found.', 'forma-3d' ); ?></p>
			<?php get_search_form(); ?>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
