<?php
/**
 * Page templates: full-page block patterns (About, Contact, Lookbook, FAQ, Shop the room, Campaign).
 *
 * They are offered in the "Choose a pattern" window when a new page is created,
 * and in the inserter under "Forma 3D pages". The photos are the demo renders
 * that ship with the plugin; replace them with your own.
 *
 * @package Forma3DToolkit
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'forma_3d_tk_register_page_patterns' );

/**
 * URL of a bundled demo photo.
 *
 * @param string $name File name without extension, e.g. "GlamVelvetSofa-1".
 */
function forma_3d_tk_pattern_img( $name ) {
	return esc_url( FORMA_3D_TK_URI . 'demo/images/' . $name . '.webp' );
}

/**
 * Markup helpers for the patterns below.
 */
function forma_3d_tk_pattern_p( $text, $attrs = '' ) {
	$classes = array();
	$json    = array();
	if ( 'lead' === $attrs ) {
		$json['fontSize'] = 'large';
		$classes[]        = 'has-large-font-size';
	} elseif ( 'muted' === $attrs ) {
		$json['textColor'] = 'muted';
		$classes[]         = 'has-muted-color has-text-color';
	}
	$open  = $json ? ' ' . wp_json_encode( $json ) : '';
	$class = $classes ? ' class="' . implode( ' ', $classes ) . '"' : '';
	return "<!-- wp:paragraph{$open} -->\n<p{$class}>{$text}</p>\n<!-- /wp:paragraph -->";
}

/**
 * Heading block.
 *
 * @param string $text  Heading text (already escaped).
 * @param int    $level Heading level.
 */
function forma_3d_tk_pattern_h( $text, $level = 2 ) {
	$open = 2 === $level ? '' : ' {"level":' . (int) $level . '}';
	return "<!-- wp:heading{$open} -->\n<h{$level} class=\"wp-block-heading\">{$text}</h{$level}>\n<!-- /wp:heading -->";
}

/**
 * Image block.
 *
 * @param string $name    Demo photo name.
 * @param string $alt     Alt text (already escaped).
 * @param string $caption Optional caption (already escaped).
 */
function forma_3d_tk_pattern_img_block( $name, $alt, $caption = '' ) {
	$cap = $caption ? '<figcaption class="wp-element-caption">' . $caption . '</figcaption>' : '';
	return '<!-- wp:image {"sizeSlug":"large","linkDestination":"none"} -->' . "\n" . '<figure class="wp-block-image size-large"><img src="' . forma_3d_tk_pattern_img( $name ) . '" alt="' . $alt . '"/>' . $cap . '</figure>' . "\n" . '<!-- /wp:image -->';
}

/**
 * Buttons block.
 *
 * @param array $buttons List of [ text, url, style ] (text already escaped; style '' | 'outline' | 'arrow').
 */
function forma_3d_tk_pattern_buttons( $buttons ) {
	$out = '';
	foreach ( $buttons as $b ) {
		$class = 'outline' === $b[2] ? 'is-style-outline' : ( 'arrow' === $b[2] ? 'is-style-forma-3d-arrow' : '' );
		$json  = $class ? ' {"className":"' . $class . '"}' : '';
		$out  .= "<!-- wp:button{$json} -->\n<div class=\"wp-block-button" . ( $class ? ' ' . $class : '' ) . '"><a class="wp-block-button__link wp-element-button" href="' . esc_url( $b[1] ) . '">' . $b[0] . "</a></div>\n<!-- /wp:button -->";
	}
	return "<!-- wp:buttons -->\n<div class=\"wp-block-buttons\">{$out}</div>\n<!-- /wp:buttons -->";
}

/**
 * Group block with an optional background colour and the theme's card style.
 *
 * @param string $inner Inner block markup.
 * @param string $bg    Palette slug for the background, or ''.
 * @param bool   $wide  Wide alignment.
 * @param bool   $card  Use the "Card" block style.
 */
function forma_3d_tk_pattern_group( $inner, $bg = '', $wide = false, $card = false ) {
	$json    = array();
	$classes = array( 'wp-block-group' );
	if ( $wide ) {
		$json['align'] = 'wide';
		$classes[]     = 'alignwide';
	}
	if ( $card ) {
		$json['className'] = 'is-style-forma-3d-card';
		$classes[]         = 'is-style-forma-3d-card';
	}
	if ( $bg ) {
		$json['backgroundColor'] = $bg;
		$classes[]               = 'has-' . $bg . '-background-color has-background';
	}
	$json['layout'] = array( 'type' => 'constrained' );
	return '<!-- wp:group ' . wp_json_encode( $json ) . " -->\n<div class=\"" . implode( ' ', $classes ) . "\">{$inner}</div>\n<!-- /wp:group -->";
}

/**
 * Columns block.
 *
 * @param string[] $columns Inner markup of each column.
 * @param bool     $center  Vertically centre the columns.
 */
function forma_3d_tk_pattern_columns( $columns, $center = false ) {
	$out = '';
	foreach ( $columns as $col ) {
		$out .= $center
			? "<!-- wp:column {\"verticalAlignment\":\"center\"} -->\n<div class=\"wp-block-column is-vertically-aligned-center\">{$col}</div>\n<!-- /wp:column -->"
			: "<!-- wp:column -->\n<div class=\"wp-block-column\">{$col}</div>\n<!-- /wp:column -->";
	}
	$json  = $center ? '{"verticalAlignment":"center","align":"wide"}' : '{"align":"wide"}';
	$class = $center ? 'wp-block-columns alignwide are-vertically-aligned-center' : 'wp-block-columns alignwide';
	return "<!-- wp:columns {$json} -->\n<div class=\"{$class}\">{$out}</div>\n<!-- /wp:columns -->";
}

/**
 * Details (accordion) block.
 *
 * @param string $summary Question (already escaped).
 * @param string $answer  Answer (already escaped).
 */
function forma_3d_tk_pattern_details( $summary, $answer ) {
	return "<!-- wp:details -->\n<details class=\"wp-block-details\"><summary>{$summary}</summary>" . forma_3d_tk_pattern_p( $answer ) . "</details>\n<!-- /wp:details -->";
}

/**
 * Quote block.
 *
 * @param string $text Quote (already escaped).
 * @param string $cite Citation (already escaped).
 */
function forma_3d_tk_pattern_quote( $text, $cite ) {
	return "<!-- wp:quote -->\n<blockquote class=\"wp-block-quote\">" . forma_3d_tk_pattern_p( $text ) . "<cite>{$cite}</cite></blockquote>\n<!-- /wp:quote -->";
}

/**
 * Spacer block.
 *
 * @param int $px Height in pixels.
 */
function forma_3d_tk_pattern_spacer( $px = 48 ) {
	return '<!-- wp:spacer {"height":"' . (int) $px . 'px"} -->' . "\n" . '<div style="height:' . (int) $px . 'px" aria-hidden="true" class="wp-block-spacer"></div>' . "\n<!-- /wp:spacer -->";
}

/**
 * Register the page pattern category and the page patterns.
 */
function forma_3d_tk_register_page_patterns() {
	register_block_pattern_category( 'forma-pages', array( 'label' => __( 'Forma 3D pages', 'forma-3d-toolkit' ) ) );

	$shop    = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
	$contact = home_url( '/contact/' );
	$common  = array(
		'categories' => array( 'forma-pages' ),
		'blockTypes' => array( 'core/post-content' ),
		'postTypes'  => array( 'page' ),
	);
	$t       = static function ( $text ) {
		return esc_html( $text );
	};

	$patterns = array();

	// About.
	$patterns['about'] = array(
		'title'       => __( 'About us', 'forma-3d-toolkit' ),
		'description' => __( 'Your story, what you care about, a customer quote and a call to action.', 'forma-3d-toolkit' ),
		'content'     => implode(
			"\n\n",
			array(
				forma_3d_tk_pattern_p( $t( __( 'We started with a simple idea: you should be able to see a piece from every side before it comes home. So we put every product in 3D.', 'forma-3d-toolkit' ) ), 'lead' ),
				forma_3d_tk_pattern_group( forma_3d_tk_pattern_img_block( 'GlamVelvetSofa-1', $t( __( 'A velvet sofa from the collection', 'forma-3d-toolkit' ) ) ), 'bg-2', true ),
				forma_3d_tk_pattern_columns(
					array(
						forma_3d_tk_pattern_h( $t( __( 'Our story', 'forma-3d-toolkit' ) ) ),
						forma_3d_tk_pattern_p( $t( __( 'We began in a small studio, choosing pieces we would want in our own homes. Customers kept asking the same question: what does it look like from the back?', 'forma-3d-toolkit' ) ) )
						. "\n\n" . forma_3d_tk_pattern_p( $t( __( 'Today every product we sell is a true-to-scale 3D model. Turn it around, zoom into the details and, on your phone, place it in your own room.', 'forma-3d-toolkit' ) ) ),
					)
				),
				forma_3d_tk_pattern_spacer( 32 ),
				forma_3d_tk_pattern_h( $t( __( 'What we care about', 'forma-3d-toolkit' ) ) ),
				forma_3d_tk_pattern_columns(
					array(
						forma_3d_tk_pattern_h( $t( __( 'Made to last', 'forma-3d-toolkit' ) ), 3 ) . "\n\n" . forma_3d_tk_pattern_p( $t( __( 'Solid materials and honest construction, chosen to be used every day for years.', 'forma-3d-toolkit' ) ) ),
						forma_3d_tk_pattern_h( $t( __( 'Seen in full', 'forma-3d-toolkit' ) ), 3 ) . "\n\n" . forma_3d_tk_pattern_p( $t( __( 'A live 3D model on every product page, so nothing about a piece is a surprise.', 'forma-3d-toolkit' ) ) ),
						forma_3d_tk_pattern_h( $t( __( 'Fair prices', 'forma-3d-toolkit' ) ), 3 ) . "\n\n" . forma_3d_tk_pattern_p( $t( __( 'We sell directly, so you pay for the piece, not for the showroom.', 'forma-3d-toolkit' ) ) ),
					)
				),
				forma_3d_tk_pattern_spacer( 32 ),
				forma_3d_tk_pattern_quote( $t( __( 'I turned the sofa around on my phone at midnight, then placed it in my living room. It fits exactly.', 'forma-3d-toolkit' ) ), $t( __( 'Customer name, City', 'forma-3d-toolkit' ) ) ),
				forma_3d_tk_pattern_buttons(
					array(
						array( $t( __( 'Explore the collection', 'forma-3d-toolkit' ) ), $shop, 'arrow' ),
						array( $t( __( 'Get in touch', 'forma-3d-toolkit' ) ), $contact, 'outline' ),
					)
				),
			)
		),
	);

	// Contact.
	$patterns['contact'] = array(
		'title'       => __( 'Contact', 'forma-3d-toolkit' ),
		'description' => __( 'Email, phone and showroom cards, opening hours and quick answers.', 'forma-3d-toolkit' ),
		'content'     => implode(
			"\n\n",
			array(
				forma_3d_tk_pattern_p( $t( __( 'Questions about a piece, a delivery or an order? We usually reply within one working day.', 'forma-3d-toolkit' ) ), 'lead' ),
				forma_3d_tk_pattern_columns(
					array(
						forma_3d_tk_pattern_group( forma_3d_tk_pattern_h( $t( __( 'Write to us', 'forma-3d-toolkit' ) ), 3 ) . "\n\n" . forma_3d_tk_pattern_p( 'hello@example.com' ) . "\n\n" . forma_3d_tk_pattern_p( $t( __( 'For orders, include your order number.', 'forma-3d-toolkit' ) ), 'muted' ), '', false, true ),
						forma_3d_tk_pattern_group( forma_3d_tk_pattern_h( $t( __( 'Call us', 'forma-3d-toolkit' ) ), 3 ) . "\n\n" . forma_3d_tk_pattern_p( '+1 (555) 010-0199' ) . "\n\n" . forma_3d_tk_pattern_p( $t( __( 'Monday to Friday, 9:00–17:00', 'forma-3d-toolkit' ) ), 'muted' ), '', false, true ),
						forma_3d_tk_pattern_group( forma_3d_tk_pattern_h( $t( __( 'Visit the showroom', 'forma-3d-toolkit' ) ), 3 ) . "\n\n" . forma_3d_tk_pattern_p( $t( __( '12 Example Street, Your City', 'forma-3d-toolkit' ) ) ) . "\n\n" . forma_3d_tk_pattern_p( $t( __( 'Tuesday to Saturday, 10:00–18:00', 'forma-3d-toolkit' ) ), 'muted' ), '', false, true ),
					)
				),
				forma_3d_tk_pattern_p( $t( __( 'Add a contact form here with the form plugin you prefer.', 'forma-3d-toolkit' ) ), 'muted' ),
				forma_3d_tk_pattern_spacer( 32 ),
				forma_3d_tk_pattern_h( $t( __( 'Quick answers', 'forma-3d-toolkit' ) ) ),
				forma_3d_tk_pattern_details( $t( __( 'Where is my order?', 'forma-3d-toolkit' ) ), $t( __( 'You get an email with tracking as soon as your order leaves us. Your account page shows its status too.', 'forma-3d-toolkit' ) ) ),
				forma_3d_tk_pattern_details( $t( __( 'Can I see a piece before I buy it?', 'forma-3d-toolkit' ) ), $t( __( 'Every product page has a true-to-scale 3D model. On a phone, tap “View in your room” to place it in your space.', 'forma-3d-toolkit' ) ) ),
				forma_3d_tk_pattern_details( $t( __( 'How do returns work?', 'forma-3d-toolkit' ) ), $t( __( 'Write to us within 30 days of delivery and we arrange the collection.', 'forma-3d-toolkit' ) ) ),
			)
		),
	);

	// Lookbook.
	$gallery = '';
	foreach ( array(
		array( 'mid_century_lounge_chair-0', __( 'The reading corner', 'forma-3d-toolkit' ) ),
		array( 'SheenWoodLeatherSofa-0', __( 'The living room', 'forma-3d-toolkit' ) ),
		array( 'coffee_table_round_01-0', __( 'Marble and brass', 'forma-3d-toolkit' ) ),
		array( 'dining_chair_02-0', __( 'The dining room', 'forma-3d-toolkit' ) ),
		array( 'wooden_display_shelves_01-0', __( 'Open shelving', 'forma-3d-toolkit' ) ),
		array( 'SheenChair-0', __( 'A splash of colour', 'forma-3d-toolkit' ) ),
	) as $img ) {
		$gallery .= forma_3d_tk_pattern_img_block( $img[0], $t( $img[1] ) ); // No captions: the gallery caption overlay shows as a grey band on transparent photos.
	}
	$patterns['lookbook'] = array(
		'title'       => __( 'Lookbook', 'forma-3d-toolkit' ),
		'description' => __( 'A photo gallery with captions and two "shop the look" stories.', 'forma-3d-toolkit' ),
		'content'     => implode(
			"\n\n",
			array(
				forma_3d_tk_pattern_p( $t( __( 'Rooms styled with pieces from the collection. Open any of them on its product page and turn it around in 3D.', 'forma-3d-toolkit' ) ), 'lead' ),
				'<!-- wp:gallery {"columns":3,"linkTo":"none","align":"wide"} -->' . "\n" . '<figure class="wp-block-gallery alignwide has-nested-images columns-3 is-cropped">' . $gallery . "</figure>\n<!-- /wp:gallery -->",
				forma_3d_tk_pattern_spacer( 48 ),
				forma_3d_tk_pattern_columns(
					array(
						forma_3d_tk_pattern_group( forma_3d_tk_pattern_img_block( 'ArmChair_01-0', $t( __( 'An upholstered armchair', 'forma-3d-toolkit' ) ) ), 'bg-2' ),
						forma_3d_tk_pattern_h( $t( __( 'The reading corner', 'forma-3d-toolkit' ) ) ) . "\n\n" . forma_3d_tk_pattern_p( $t( __( 'A deep armchair, a low table for the tea and a lamp at shoulder height. Everything you need for a long chapter.', 'forma-3d-toolkit' ) ) ) . "\n\n" . forma_3d_tk_pattern_buttons( array( array( $t( __( 'Shop the look', 'forma-3d-toolkit' ) ), $shop, 'arrow' ) ) ),
					),
					true
				),
				forma_3d_tk_pattern_columns(
					array(
						forma_3d_tk_pattern_h( $t( __( 'The dining room', 'forma-3d-toolkit' ) ) ) . "\n\n" . forma_3d_tk_pattern_p( $t( __( 'Solid wood, honest joints and chairs you can sit on for a whole evening.', 'forma-3d-toolkit' ) ) ) . "\n\n" . forma_3d_tk_pattern_buttons( array( array( $t( __( 'Shop the look', 'forma-3d-toolkit' ) ), $shop, 'arrow' ) ) ),
						forma_3d_tk_pattern_group( forma_3d_tk_pattern_img_block( 'dining_chair_02-1', $t( __( 'A wooden dining chair', 'forma-3d-toolkit' ) ) ), 'bg-2' ),
					),
					true
				),
			)
		),
	);

	// FAQ.
	$patterns['faq'] = array(
		'title'       => __( 'FAQ', 'forma-3d-toolkit' ),
		'description' => __( 'Questions and answers grouped by orders, returns and 3D, with a contact card.', 'forma-3d-toolkit' ),
		'content'     => implode(
			"\n\n",
			array(
				forma_3d_tk_pattern_p( $t( __( 'Everything you need to know about ordering, delivery, returns and our 3D product views.', 'forma-3d-toolkit' ) ), 'lead' ),
				forma_3d_tk_pattern_h( $t( __( 'Orders & delivery', 'forma-3d-toolkit' ) ) ),
				forma_3d_tk_pattern_details( $t( __( 'How long does delivery take?', 'forma-3d-toolkit' ) ), $t( __( 'Most pieces arrive within 5–10 working days. The product page shows the time for each piece.', 'forma-3d-toolkit' ) ) ),
				forma_3d_tk_pattern_details( $t( __( 'How much does delivery cost?', 'forma-3d-toolkit' ) ), $t( __( 'You see the delivery cost in the cart before you pay.', 'forma-3d-toolkit' ) ) ),
				forma_3d_tk_pattern_details( $t( __( 'Can I change my order?', 'forma-3d-toolkit' ) ), $t( __( 'Yes, until it ships. Write to us with your order number.', 'forma-3d-toolkit' ) ) ),
				forma_3d_tk_pattern_h( $t( __( 'Returns', 'forma-3d-toolkit' ) ) ),
				forma_3d_tk_pattern_details( $t( __( 'Can I return a piece?', 'forma-3d-toolkit' ) ), $t( __( 'Yes, within 30 days of delivery, in its original condition.', 'forma-3d-toolkit' ) ) ),
				forma_3d_tk_pattern_details( $t( __( 'What if something arrives damaged?', 'forma-3d-toolkit' ) ), $t( __( 'Send us a photo within 48 hours and we replace it.', 'forma-3d-toolkit' ) ) ),
				forma_3d_tk_pattern_h( $t( __( '3D & AR', 'forma-3d-toolkit' ) ) ),
				forma_3d_tk_pattern_details( $t( __( 'How do I view a product in 3D?', 'forma-3d-toolkit' ) ), $t( __( 'Open any product page. Drag to turn the model around and scroll or pinch to zoom.', 'forma-3d-toolkit' ) ) ),
				forma_3d_tk_pattern_details( $t( __( 'How does “View in your room” work?', 'forma-3d-toolkit' ) ), $t( __( 'On a phone or tablet, tap the AR button on a product page and point the camera at the floor. The piece appears in your room at its real size.', 'forma-3d-toolkit' ) ) ),
				forma_3d_tk_pattern_details( $t( __( 'Are the 3D models true to size?', 'forma-3d-toolkit' ) ), $t( __( 'Yes. Each model uses the real dimensions of the piece, shown with the dimensions ruler.', 'forma-3d-toolkit' ) ) ),
				forma_3d_tk_pattern_spacer( 32 ),
				forma_3d_tk_pattern_group(
					forma_3d_tk_pattern_h( $t( __( 'Still have a question?', 'forma-3d-toolkit' ) ), 3 ) . "\n\n" . forma_3d_tk_pattern_p( $t( __( 'We usually reply within one working day.', 'forma-3d-toolkit' ) ) ) . "\n\n" . forma_3d_tk_pattern_buttons( array( array( $t( __( 'Contact us', 'forma-3d-toolkit' ) ), $contact, 'arrow' ) ) ),
					'',
					false,
					true
				),
			)
		),
	);

	// Shop the room.
	$cards = array();
	foreach ( array(
		array( 'GlamVelvetSofa-0', __( 'The sofa', 'forma-3d-toolkit' ), __( 'Deep, soft and wide enough to stretch out.', 'forma-3d-toolkit' ) ),
		array( 'modern_arm_chair_01-0', __( 'The armchair', 'forma-3d-toolkit' ), __( 'A reading chair with a high back.', 'forma-3d-toolkit' ) ),
		array( 'modern_coffee_table_01-0', __( 'The coffee table', 'forma-3d-toolkit' ), __( 'Low, solid and easy to live with.', 'forma-3d-toolkit' ) ),
	) as $card ) {
		$cards[] = forma_3d_tk_pattern_group(
			forma_3d_tk_pattern_img_block( $card[0], $t( $card[1] ) ) . "\n\n" . forma_3d_tk_pattern_h( $t( $card[1] ), 3 ) . "\n\n" . forma_3d_tk_pattern_p( $t( $card[2] ) ) . "\n\n" . forma_3d_tk_pattern_buttons( array( array( $t( __( 'View in 3D', 'forma-3d-toolkit' ) ), $shop, 'outline' ) ) ),
			'',
			false,
			true
		);
	}
	$patterns['shop-the-room'] = array(
		'title'       => __( 'Shop the room', 'forma-3d-toolkit' ),
		'description' => __( 'One styled room with product cards that link to the shop.', 'forma-3d-toolkit' ),
		'content'     => implode(
			"\n\n",
			array(
				forma_3d_tk_pattern_p( $t( __( 'A calm living room in warm neutrals. Every piece in it is in the shop, in 3D.', 'forma-3d-toolkit' ) ), 'lead' ),
				forma_3d_tk_pattern_group( forma_3d_tk_pattern_img_block( 'SheenWoodLeatherSofa-1', $t( __( 'A leather sofa in a living room', 'forma-3d-toolkit' ) ) ), 'bg-2', true ),
				forma_3d_tk_pattern_spacer( 32 ),
				forma_3d_tk_pattern_h( $t( __( 'In this room', 'forma-3d-toolkit' ) ) ),
				forma_3d_tk_pattern_columns( $cards ),
				forma_3d_tk_pattern_buttons( array( array( $t( __( 'See the whole collection', 'forma-3d-toolkit' ) ), $shop, 'arrow' ) ) ),
			)
		),
	);

	// Campaign: built from the Forma homepage blocks, so it gets the full-width 3D canvas.
	$hero                 = array(
		'eyebrow'    => __( 'Limited time', 'forma-3d-toolkit' ),
		'line1'      => __( 'The', 'forma-3d-toolkit' ),
		'line2'      => '<em>' . __( 'season', 'forma-3d-toolkit' ) . '</em> ' . __( 'sale,', 'forma-3d-toolkit' ),
		'line3'      => __( 'in 3D.', 'forma-3d-toolkit' ),
		'buttonText' => __( 'Shop the sale', 'forma-3d-toolkit' ),
	);
	$sale                 = array(
		'eyebrow'  => __( '(01) — On sale now', 'forma-3d-toolkit' ),
		'title'    => '<em>' . __( 'Sale', 'forma-3d-toolkit' ) . '</em> ' . __( 'pieces', 'forma-3d-toolkit' ),
		'linkText' => __( 'Shop everything', 'forma-3d-toolkit' ),
		'source'   => 'sale',
	);
	$patterns['campaign'] = array(
		'title'       => __( 'Campaign landing page (3D)', 'forma-3d-toolkit' ),
		'description' => __( 'A full-width 3D landing page for a sale or a new collection, built from the Forma homepage blocks.', 'forma-3d-toolkit' ),
		'content'     => implode(
			"\n\n",
			array(
				'<!-- wp:forma/hero ' . serialize_block_attributes( $hero ) . ' /-->',
				'<!-- wp:forma/arrivals ' . serialize_block_attributes( $sale ) . ' /-->',
				'<!-- wp:forma/showcase /-->',
				'<!-- wp:forma/promise /-->',
			)
		),
	);

	foreach ( $patterns as $slug => $pattern ) {
		register_block_pattern( 'forma-3d-toolkit/page-' . $slug, array_merge( $common, $pattern ) );
	}
}
