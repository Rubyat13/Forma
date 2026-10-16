<?php
/**
 * Block patterns built from core blocks.
 *
 * @package Forma3D
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'forma_3d_register_patterns' );

/**
 * Register the pattern category and patterns.
 */
function forma_3d_register_patterns() {
	register_block_pattern_category( 'forma-3d', array( 'label' => __( 'Forma 3D', 'forma-3d' ) ) );

	register_block_pattern(
		'forma-3d/call-to-action',
		array(
			'title'      => __( 'Dark call to action', 'forma-3d' ),
			'categories' => array( 'forma-3d', 'call-to-action' ),
			'content'    => '<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"64px","bottom":"64px","left":"48px","right":"48px"}}},"backgroundColor":"dark","textColor":"cream","layout":{"type":"constrained","contentSize":"880px"}} -->
<div class="wp-block-group alignwide has-cream-color has-dark-background-color has-text-color has-background" style="padding-top:64px;padding-right:48px;padding-bottom:64px;padding-left:48px"><!-- wp:paragraph {"style":{"typography":{"textTransform":"uppercase","letterSpacing":"0.14em"}},"textColor":"muted","fontSize":"small"} -->
<p class="has-muted-color has-text-color has-small-font-size" style="letter-spacing:0.14em;text-transform:uppercase">' . esc_html__( 'Visit the collection', 'forma-3d' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"display"} -->
<h2 class="wp-block-heading has-display-font-size">' . wp_kses( __( 'See every piece <em>in 3D.</em>', 'forma-3d' ), array( 'em' => array() ) ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">' . esc_html__( 'Turn products around, zoom into the details and check the real size before you buy.', 'forma-3d' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"cream","textColor":"ink"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-ink-color has-cream-background-color has-text-color has-background wp-element-button">' . esc_html__( 'Shop now', 'forma-3d' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->',
		)
	);

	$features = '';
	foreach (
		array(
			array( __( 'Every angle', 'forma-3d' ), __( 'Spin each product a full 360° and look at it from any side.', 'forma-3d' ) ),
			array( __( 'True to scale', 'forma-3d' ), __( 'Real dimensions are shown on the model, so you know it fits.', 'forma-3d' ) ),
			array( __( 'In your room', 'forma-3d' ), __( 'On supported phones, place the product in your own space.', 'forma-3d' ) ),
		) as $item
	) {
		$features .= '<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . esc_html( $item[0] ) . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"ink-2"} -->
<p class="has-ink-2-color has-text-color">' . esc_html( $item[1] ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->';
	}
	register_block_pattern(
		'forma-3d/features',
		array(
			'title'      => __( 'Three features', 'forma-3d' ),
			'categories' => array( 'forma-3d', 'features' ),
			'content'    => '<!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide">' . $features . '</div>
<!-- /wp:columns -->',
		)
	);

	register_block_pattern(
		'forma-3d/quote',
		array(
			'title'      => __( 'Large quote', 'forma-3d' ),
			'categories' => array( 'forma-3d', 'testimonials' ),
			'content'    => '<!-- wp:quote {"className":"is-style-plain"} -->
<blockquote class="wp-block-quote is-style-plain"><!-- wp:paragraph {"fontSize":"x-large"} -->
<p class="has-x-large-font-size">' . esc_html__( 'Add a short quote from a customer or your studio here.', 'forma-3d' ) . '</p>
<!-- /wp:paragraph --><cite>' . esc_html__( 'Name, City', 'forma-3d' ) . '</cite></blockquote>
<!-- /wp:quote -->',
		)
	);

	$faq = '';
	foreach (
		array(
			__( 'How do I view a product in 3D?', 'forma-3d' ),
			__( 'Can I see the product in my room?', 'forma-3d' ),
			__( 'Are the dimensions accurate?', 'forma-3d' ),
		) as $question
	) {
		$faq .= '

<!-- wp:details -->
<details class="wp-block-details"><summary>' . esc_html( $question ) . '</summary><!-- wp:paragraph {"placeholder":"' . esc_attr__( 'Write the answer…', 'forma-3d' ) . '"} -->
<p></p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->';
	}
	register_block_pattern(
		'forma-3d/faq',
		array(
			'title'      => __( 'Questions and answers', 'forma-3d' ),
			'categories' => array( 'forma-3d', 'text' ),
			'content'    => '<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">' . esc_html__( 'Questions', 'forma-3d' ) . '</h2>
<!-- /wp:heading -->' . $faq,
		)
	);
}
