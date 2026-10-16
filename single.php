<?php
/**
 * Single post.
 *
 * @package Forma3D
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="page-wrap">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="page__head">
				<p class="eyebrow"><?php echo esc_html( get_the_date() ); ?> · <?php the_author(); ?></p>
				<?php the_title( '<h1 class="page__title" data-split>', '</h1>' ); ?>
			</header>
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="post-thumbnail"><?php the_post_thumbnail( 'large' ); ?></figure>
			<?php endif; ?>
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
			<footer class="entry-footer">
				<?php
				$forma_3d_cats = get_the_category_list( ', ' );
				$forma_3d_tags = get_the_tag_list( '', ', ' );
				if ( $forma_3d_cats ) {
					/* translators: %s: list of categories. */
					printf( '<p class="entry-meta">' . esc_html__( 'Posted in %s', 'forma-3d' ) . '</p>', wp_kses_post( $forma_3d_cats ) );
				}
				if ( $forma_3d_tags ) {
					/* translators: %s: list of tags. */
					printf( '<p class="entry-meta">' . esc_html__( 'Tagged %s', 'forma-3d' ) . '</p>', wp_kses_post( $forma_3d_tags ) );
				}
				?>
			</footer>
		</article>
		<?php
		the_post_navigation(
			array(
				'prev_text' => '<span class="eyebrow">' . esc_html__( 'Previous', 'forma-3d' ) . '</span> %title',
				'next_text' => '<span class="eyebrow">' . esc_html__( 'Next', 'forma-3d' ) . '</span> %title',
			)
		);
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
	endwhile;
	?>
</main>
<?php
get_footer();
