<?php
/**
 * Template kits: ready-made pages for other kinds of sites (Personal profile,
 * eCommerce, IT company and Photography), built with core blocks.
 *
 * Each kit page is a block pattern in its own category and an item in the
 * Forma template library. The photos are CC0 photos that ship with the plugin
 * (assets/kits); replace them with your own.
 *
 * @package Forma3DToolkit
 */

defined( 'ABSPATH' ) || exit;

/**
 * The kits: ID => label.
 *
 * @return array<string, string>
 */
function forma_3d_tk_kits() {
	return array(
		'personal' => __( 'Personal profile', 'forma-3d-toolkit' ),
		'shop'     => __( 'eCommerce', 'forma-3d-toolkit' ),
		'it'       => __( 'IT company', 'forma-3d-toolkit' ),
		'photo'    => __( 'Photography', 'forma-3d-toolkit' ),
	);
}

/**
 * URL of a bundled kit photo.
 *
 * @param string $name File name without extension, e.g. "photo-hero".
 */
function forma_3d_tk_kit_src( $name ) {
	return FORMA_3D_TK_URI . 'assets/kits/' . $name . '.webp';
}

/**
 * Join blocks.
 *
 * @param string[] $blocks Block markup.
 */
function forma_3d_tk_kit_join( $blocks ) {
	return implode( "\n\n", $blocks );
}

/**
 * Paragraph with a class and an optional preset font size.
 *
 * @param string $text       Text (already escaped).
 * @param string $class_name Class name.
 * @param string $size       Font size slug, or ''.
 */
function forma_3d_tk_kit_p( $text, $class_name = '', $size = '' ) {
	$attrs   = array();
	$classes = array();
	if ( $class_name ) {
		$attrs['className'] = $class_name;
		$classes[]          = $class_name;
	}
	if ( $size ) {
		$attrs['fontSize'] = $size;
		$classes[]         = 'has-' . $size . '-font-size';
	}
	$open = $attrs ? ' ' . serialize_block_attributes( $attrs ) : '';
	$html = $classes ? ' class="' . esc_attr( implode( ' ', $classes ) ) . '"' : '';
	return "<!-- wp:paragraph{$open} -->\n<p{$html}>{$text}</p>\n<!-- /wp:paragraph -->";
}

/**
 * Small label above a heading.
 *
 * @param string $text Text (already escaped).
 */
function forma_3d_tk_kit_eyebrow( $text ) {
	return forma_3d_tk_kit_p( $text, 'forma-kit-eyebrow' );
}

/**
 * Heading with an optional HTML anchor.
 *
 * @param string $text   Text (already escaped).
 * @param int    $level  Heading level.
 * @param string $anchor Anchor, or ''.
 */
function forma_3d_tk_kit_h( $text, $level = 2, $anchor = '' ) {
	$open = 2 === $level ? '' : ' {"level":' . (int) $level . '}';
	$id   = $anchor ? ' id="' . esc_attr( $anchor ) . '"' : '';
	return "<!-- wp:heading{$open} -->\n<h{$level} class=\"wp-block-heading\"{$id}>{$text}</h{$level}>\n<!-- /wp:heading -->";
}

/**
 * Image block, cropped to an aspect ratio.
 *
 * @param string $src     Image URL.
 * @param string $alt     Alt text (already escaped).
 * @param string $ratio   Aspect ratio such as "4/3", or '' for the photo's own shape.
 * @param bool   $rounded Use the theme's "Rounded" image style.
 */
function forma_3d_tk_kit_image( $src, $alt, $ratio = '', $rounded = false ) {
	$attrs = array();
	if ( $ratio ) {
		$attrs['aspectRatio'] = $ratio;
		$attrs['scale']       = 'cover';
	}
	$attrs['sizeSlug']        = 'large';
	$attrs['linkDestination'] = 'none';
	$class_name               = 'wp-block-image size-large';
	if ( $rounded ) {
		$attrs['className'] = 'is-style-forma-3d-rounded';
		$class_name        .= ' is-style-forma-3d-rounded';
	}
	$style = $ratio ? ' style="aspect-ratio:' . esc_attr( $ratio ) . ';object-fit:cover"' : '';
	return '<!-- wp:image ' . serialize_block_attributes( $attrs ) . " -->\n<figure class=\"{$class_name}\"><img src=\"" . esc_url( $src ) . "\" alt=\"{$alt}\"{$style}/></figure>\n<!-- /wp:image -->";
}

/**
 * Wide photo cover with text in the bottom-left corner.
 *
 * @param string $src   Image URL.
 * @param string $inner Inner block markup.
 * @param int    $min   Minimum height in pixels.
 * @param int    $dim   Overlay opacity (0-100, steps of 10).
 */
function forma_3d_tk_kit_cover( $src, $inner, $min = 620, $dim = 30 ) {
	$attrs = array(
		'url'                => $src,
		'dimRatio'           => $dim,
		'overlayColor'       => 'dark',
		'isUserOverlayColor' => true,
		'minHeight'          => $min,
		'minHeightUnit'      => 'px',
		'contentPosition'    => 'bottom left',
		'align'              => 'wide',
		'className'          => 'forma-kit-cover',
		'layout'             => array( 'type' => 'constrained' ),
	);
	return '<!-- wp:cover ' . serialize_block_attributes( $attrs ) . " -->\n"
		. '<div class="wp-block-cover alignwide has-custom-content-position is-position-bottom-left forma-kit-cover" style="min-height:' . (int) $min . 'px">'
		. '<img class="wp-block-cover__image-background" alt="" src="' . esc_url( $src ) . '" data-object-fit="cover"/>'
		. '<span aria-hidden="true" class="wp-block-cover__background has-dark-background-color has-background-dim-' . (int) $dim . ' has-background-dim"></span>'
		. '<div class="wp-block-cover__inner-container">' . $inner . "</div></div>\n<!-- /wp:cover -->";
}

/**
 * List block.
 *
 * @param string[] $items      Items (already escaped).
 * @param string   $class_name "forma-kit-check" (ticks), "forma-kit-tags" (tags) or ''.
 */
function forma_3d_tk_kit_list( $items, $class_name = '' ) {
	$li = array();
	foreach ( $items as $item ) {
		$li[] = "<!-- wp:list-item -->\n<li>{$item}</li>\n<!-- /wp:list-item -->";
	}
	$open = $class_name ? ' ' . serialize_block_attributes( array( 'className' => $class_name ) ) : '';
	return "<!-- wp:list{$open} -->\n<ul class=\"wp-block-list" . ( $class_name ? ' ' . esc_attr( $class_name ) : '' ) . '">' . implode( "\n\n", $li ) . "</ul>\n<!-- /wp:list -->";
}

/**
 * Table block.
 *
 * @param string[]   $head Header cells (already escaped).
 * @param string[][] $rows Body rows (already escaped).
 */
function forma_3d_tk_kit_table( $head, $rows ) {
	$thead = '<tr><th>' . implode( '</th><th>', $head ) . '</th></tr>';
	$tbody = '';
	foreach ( $rows as $row ) {
		$tbody .= '<tr><td>' . implode( '</td><td>', $row ) . '</td></tr>';
	}
	return '<!-- wp:table {"hasFixedLayout":true,"className":"forma-kit-table"} -->' . "\n"
		. "<figure class=\"wp-block-table forma-kit-table\"><table class=\"has-fixed-layout\"><thead>{$thead}</thead><tbody>{$tbody}</tbody></table></figure>\n<!-- /wp:table -->";
}

/**
 * Social icons.
 *
 * @param string[] $services Service slugs, e.g. "instagram".
 */
function forma_3d_tk_kit_social( $services ) {
	$links = array();
	foreach ( $services as $service ) {
		$links[] = '<!-- wp:social-link ' . serialize_block_attributes(
			array(
				'url'     => 'https://example.com/',
				'service' => $service,
			)
		) . ' /-->';
	}
	return '<!-- wp:social-links {"showLabels":true,"className":"is-style-logos-only forma-kit-social"} -->' . "\n"
		. '<ul class="wp-block-social-links has-visible-labels is-style-logos-only forma-kit-social">' . implode( "\n\n", $links ) . "</ul>\n<!-- /wp:social-links -->";
}

/**
 * Group block: a wide dark band, or a plain wrapper with a class.
 *
 * @param string $inner      Inner block markup.
 * @param string $class_name Class name.
 * @param bool   $dark       Dark background with light text.
 * @param bool   $wide       Wide alignment.
 */
function forma_3d_tk_kit_group( $inner, $class_name = '', $dark = false, $wide = true ) {
	$attrs   = array();
	$classes = array( 'wp-block-group' );
	if ( $wide ) {
		$attrs['align'] = 'wide';
		$classes[]      = 'alignwide';
	}
	if ( $dark ) {
		$class_name = trim( $class_name . ' forma-kit-dark' );
	}
	if ( $class_name ) {
		$attrs['className'] = $class_name;
		$classes[]          = $class_name;
	}
	if ( $dark ) {
		$attrs['backgroundColor'] = 'dark';
		$attrs['textColor']       = 'cream';
		$classes[]                = 'has-cream-color has-dark-background-color has-text-color has-background';
	}
	$attrs['layout'] = array(
		'type'           => 'constrained',
		'justifyContent' => 'left',
	);
	return '<!-- wp:group ' . serialize_block_attributes( $attrs ) . " -->\n<div class=\"" . esc_attr( implode( ' ', $classes ) ) . "\">{$inner}</div>\n<!-- /wp:group -->";
}

/**
 * Group block laid out in a row that wraps.
 *
 * @param string $inner      Inner block markup.
 * @param string $class_name Class name.
 */
function forma_3d_tk_kit_row( $inner, $class_name ) {
	$attrs = array(
		'align'     => 'wide',
		'className' => $class_name,
		'layout'    => array(
			'type'           => 'flex',
			'flexWrap'       => 'wrap',
			'justifyContent' => 'space-between',
		),
	);
	return '<!-- wp:group ' . serialize_block_attributes( $attrs ) . " -->\n<div class=\"wp-block-group alignwide " . esc_attr( $class_name ) . "\">{$inner}</div>\n<!-- /wp:group -->";
}

/**
 * Wide columns block with optional classes and widths.
 *
 * @param string[] $columns     Inner markup of each column.
 * @param string   $class_name  Class name of the columns block.
 * @param bool     $center      Vertically centre the columns.
 * @param string[] $widths      Width of each column, e.g. "40%", or ''.
 * @param string[] $col_classes Class name of each column, or ''.
 */
function forma_3d_tk_kit_columns( $columns, $class_name = '', $center = false, $widths = array(), $col_classes = array() ) {
	$out = array();
	foreach ( array_values( $columns ) as $i => $col ) {
		$attrs   = array();
		$classes = array( 'wp-block-column' );
		if ( $center ) {
			$attrs['verticalAlignment'] = 'center';
			$classes[]                  = 'is-vertically-aligned-center';
		}
		if ( ! empty( $widths[ $i ] ) ) {
			$attrs['width'] = $widths[ $i ];
		}
		if ( ! empty( $col_classes[ $i ] ) ) {
			$attrs['className'] = $col_classes[ $i ];
			$classes[]          = $col_classes[ $i ];
		}
		$open  = $attrs ? ' ' . serialize_block_attributes( $attrs ) : '';
		$style = empty( $widths[ $i ] ) ? '' : ' style="flex-basis:' . esc_attr( $widths[ $i ] ) . '"';
		$out[] = "<!-- wp:column{$open} -->\n<div class=\"" . esc_attr( implode( ' ', $classes ) ) . "\"{$style}>{$col}</div>\n<!-- /wp:column -->";
	}
	$attrs   = array();
	$classes = array( 'wp-block-columns', 'alignwide' );
	if ( $center ) {
		$attrs['verticalAlignment'] = 'center';
		$classes[]                  = 'are-vertically-aligned-center';
	}
	$attrs['align'] = 'wide';
	if ( $class_name ) {
		$attrs['className'] = $class_name;
		$classes[]          = $class_name;
	}
	return '<!-- wp:columns ' . serialize_block_attributes( $attrs ) . " -->\n<div class=\"" . esc_attr( implode( ' ', $classes ) ) . '">' . implode( "\n\n", $out ) . "</div>\n<!-- /wp:columns -->";
}

/**
 * Gallery block.
 *
 * @param array  $images  List of [ url, alt ] (alt already escaped).
 * @param int    $columns Columns.
 * @param bool   $masonry Keep each photo's own shape, in a masonry layout.
 */
function forma_3d_tk_kit_gallery( $images, $columns = 3, $masonry = false ) {
	$inner = array();
	foreach ( $images as $img ) {
		$inner[] = '<!-- wp:image {"sizeSlug":"large","linkDestination":"none"} -->' . "\n" . '<figure class="wp-block-image size-large"><img src="' . esc_url( $img[0] ) . '" alt="' . $img[1] . "\"/></figure>\n<!-- /wp:image -->";
	}
	$attrs      = array(
		'columns' => (int) $columns,
		'linkTo'  => 'none',
	);
	$class_name = 'wp-block-gallery alignwide has-nested-images columns-' . (int) $columns;
	if ( $masonry ) {
		$attrs['imageCrop'] = false;
		$attrs['className'] = 'forma-kit-masonry';
		$class_name        .= ' forma-kit-masonry';
	} else {
		$class_name .= ' is-cropped';
	}
	$attrs['align'] = 'wide';
	return '<!-- wp:gallery ' . serialize_block_attributes( $attrs ) . " -->\n<figure class=\"{$class_name}\">" . implode( "\n\n", $inner ) . "</figure>\n<!-- /wp:gallery -->";
}

/**
 * A WooCommerce product shortcode in a wide group.
 *
 * @param string $shortcode Shortcode.
 */
function forma_3d_tk_kit_products( $shortcode ) {
	return forma_3d_tk_kit_group( "<!-- wp:shortcode -->\n{$shortcode}\n<!-- /wp:shortcode -->", 'forma-kit-products' );
}

/**
 * A big number with a label.
 *
 * @param string $num   Number (already escaped).
 * @param string $label Label (already escaped).
 */
function forma_3d_tk_kit_stat( $num, $label ) {
	return forma_3d_tk_kit_p( $num, 'forma-kit-num' ) . "\n\n" . forma_3d_tk_pattern_p( $label );
}

/**
 * A card: heading, text and optional extra blocks, in the theme's "Card" group style.
 *
 * @param string $title Heading (already escaped).
 * @param string $text  Text (already escaped).
 * @param string $extra Extra block markup.
 */
function forma_3d_tk_kit_card( $title, $text, $extra = '' ) {
	return forma_3d_tk_pattern_group( forma_3d_tk_kit_join( array_filter( array( forma_3d_tk_kit_h( $title, 3 ), forma_3d_tk_pattern_p( $text ), $extra ) ) ), '', false, true );
}

/**
 * A numbered step: number, heading and text.
 *
 * @param string $num   Number (already escaped).
 * @param string $title Heading (already escaped).
 * @param string $text  Text (already escaped).
 */
function forma_3d_tk_kit_step( $num, $title, $text ) {
	return forma_3d_tk_kit_join( array( forma_3d_tk_kit_eyebrow( $num ), forma_3d_tk_kit_h( $title, 3 ), forma_3d_tk_pattern_p( $text ) ) );
}

/**
 * A pricing plan column.
 *
 * @param string   $name     Plan name (already escaped).
 * @param string   $price    Price (already escaped).
 * @param string   $per      Price unit, e.g. "per month" (already escaped).
 * @param string[] $features Features (already escaped).
 * @param array    $button   [ text, url, style ] (text already escaped).
 */
function forma_3d_tk_kit_plan( $name, $price, $per, $features, $button ) {
	return forma_3d_tk_kit_join(
		array(
			forma_3d_tk_kit_eyebrow( $name ),
			forma_3d_tk_kit_p( $price, 'forma-kit-price' ),
			forma_3d_tk_pattern_p( $per, 'muted' ),
			forma_3d_tk_kit_list( $features, 'forma-kit-check' ),
			forma_3d_tk_pattern_buttons( array( $button ) ),
		)
	);
}

/**
 * A timeline row: when and where on the left, what on the right.
 *
 * @param string $when  Dates (already escaped).
 * @param string $where Place (already escaped).
 * @param string $title Heading (already escaped).
 * @param string $text  Text (already escaped).
 */
function forma_3d_tk_kit_timeline( $when, $where, $title, $text ) {
	return forma_3d_tk_kit_columns(
		array(
			forma_3d_tk_kit_eyebrow( $when ) . "\n\n" . forma_3d_tk_pattern_p( $where, 'muted' ),
			forma_3d_tk_kit_h( $title, 3 ) . "\n\n" . forma_3d_tk_pattern_p( $text ),
		),
		'forma-kit-row',
		false,
		array( '30%', '70%' )
	);
}

/**
 * A customer review card.
 *
 * @param string $text Review (already escaped).
 * @param string $name Name (already escaped).
 */
function forma_3d_tk_kit_review( $text, $name ) {
	return forma_3d_tk_pattern_group( forma_3d_tk_kit_join( array( forma_3d_tk_kit_p( '★★★★★', 'forma-kit-stars' ), forma_3d_tk_pattern_p( $text ), forma_3d_tk_pattern_p( $name, 'muted' ) ) ), '', false, true );
}

/**
 * A dark call-to-action band.
 *
 * @param string $eyebrow Label (already escaped).
 * @param string $title   Heading (already escaped).
 * @param string $text    Text (already escaped).
 * @param array  $buttons Buttons, see forma_3d_tk_pattern_buttons().
 */
function forma_3d_tk_kit_cta( $eyebrow, $title, $text, $buttons ) {
	return forma_3d_tk_kit_group( forma_3d_tk_kit_join( array( forma_3d_tk_kit_eyebrow( $eyebrow ), forma_3d_tk_kit_h( $title ), forma_3d_tk_pattern_p( $text ), forma_3d_tk_pattern_buttons( $buttons ) ) ), 'forma-kit-cta', true );
}

/**
 * The kit templates: ID => kit, title, page title, description and block markup.
 *
 * @return array<string, array<string, string>>
 */
function forma_3d_tk_kit_templates() {
	static $templates = null;
	if ( null !== $templates ) {
		return $templates;
	}
	$shop      = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
	$contact   = home_url( '/contact/' );
	$img       = 'forma_3d_tk_kit_src';
	$templates = array();

	/*
	 * Personal profile.
	 */
	$templates['personal-home'] = array(
		'kit'         => 'personal',
		'title'       => __( 'Personal homepage', 'forma-3d-toolkit' ),
		'pageTitle'   => __( 'Hello, I’m Alex.', 'forma-3d-toolkit' ),
		'description' => __( 'Your portrait and introduction, what you do, selected projects, a testimonial and a call to action.', 'forma-3d-toolkit' ),
		'content'     => forma_3d_tk_kit_join(
			array(
				forma_3d_tk_kit_columns(
					array(
						forma_3d_tk_kit_join(
							array(
								forma_3d_tk_pattern_p( esc_html__( 'I’m a product designer in Lisbon. I help small teams turn rough ideas into clear, useful products that people enjoy using.', 'forma-3d-toolkit' ), 'lead' ),
								forma_3d_tk_pattern_p( esc_html__( 'For twelve years I have designed apps, websites and design systems for start-ups, studios and public services. I work best close to the people who build the product and the people who use it.', 'forma-3d-toolkit' ) ),
								forma_3d_tk_pattern_buttons(
									array(
										array( esc_html__( 'See my work', 'forma-3d-toolkit' ), '#work', 'arrow' ),
										array( esc_html__( 'Get in touch', 'forma-3d-toolkit' ), $contact, 'outline' ),
									)
								),
							)
						),
						forma_3d_tk_kit_image( $img( 'personal-portrait' ), esc_attr__( 'Portrait of Alex', 'forma-3d-toolkit' ), '4/5', true ),
					),
					'forma-kit-intro',
					true,
					array( '58%', '42%' )
				),
				forma_3d_tk_kit_columns(
					array(
						forma_3d_tk_kit_stat( '12', esc_html__( 'years designing digital products', 'forma-3d-toolkit' ) ),
						forma_3d_tk_kit_stat( '40+', esc_html__( 'projects shipped with small teams', 'forma-3d-toolkit' ) ),
						forma_3d_tk_kit_stat( '9', esc_html__( 'countries, on-site and remote', 'forma-3d-toolkit' ) ),
					),
					'forma-kit-stats'
				),
				forma_3d_tk_kit_eyebrow( esc_html__( '(01) — What I do', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_h( esc_html__( 'Design that starts with people.', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_columns(
					array(
						forma_3d_tk_kit_card( esc_html__( 'Product design', 'forma-3d-toolkit' ), esc_html__( 'From the first sketch to the shipped screen: research, flows, prototypes and the final interface.', 'forma-3d-toolkit' ) ),
						forma_3d_tk_kit_card( esc_html__( 'Design systems', 'forma-3d-toolkit' ), esc_html__( 'Components, tokens and guidelines that keep a growing product consistent and fast to build.', 'forma-3d-toolkit' ) ),
						forma_3d_tk_kit_card( esc_html__( 'Workshops', 'forma-3d-toolkit' ), esc_html__( 'One- and two-day sessions that get a team to agree on what to build first, and why.', 'forma-3d-toolkit' ) ),
					),
					'forma-kit-cards'
				),
				forma_3d_tk_kit_eyebrow( esc_html__( '(02) — Selected work', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_h( esc_html__( 'Recent projects', 'forma-3d-toolkit' ), 2, 'work' ),
				forma_3d_tk_kit_columns(
					array(
						forma_3d_tk_kit_join(
							array(
								forma_3d_tk_kit_image( $img( 'personal-project-1' ), esc_attr__( 'A desk with a large monitor and a laptop', 'forma-3d-toolkit' ), '4/3', true ),
								forma_3d_tk_kit_h( esc_html__( 'Banking app redesign', 'forma-3d-toolkit' ), 3 ),
								forma_3d_tk_pattern_p( esc_html__( 'Product design · 2025', 'forma-3d-toolkit' ), 'muted' ),
							)
						),
						forma_3d_tk_kit_join(
							array(
								forma_3d_tk_kit_image( $img( 'personal-project-2' ), esc_attr__( 'A camera, a notebook and a cup of coffee on a white desk', 'forma-3d-toolkit' ), '4/3', true ),
								forma_3d_tk_kit_h( esc_html__( 'Travel journal', 'forma-3d-toolkit' ), 3 ),
								forma_3d_tk_pattern_p( esc_html__( 'Mobile app · 2024', 'forma-3d-toolkit' ), 'muted' ),
							)
						),
						forma_3d_tk_kit_join(
							array(
								forma_3d_tk_kit_image( $img( 'personal-project-3' ), esc_attr__( 'Books, pencils and a laptop on a desk', 'forma-3d-toolkit' ), '4/3', true ),
								forma_3d_tk_kit_h( esc_html__( 'School design system', 'forma-3d-toolkit' ), 3 ),
								forma_3d_tk_pattern_p( esc_html__( 'Design system · 2023', 'forma-3d-toolkit' ), 'muted' ),
							)
						),
					)
				),
				forma_3d_tk_pattern_spacer( 32 ),
				forma_3d_tk_pattern_quote( esc_html__( 'Alex asked the questions nobody else did, and the product is simpler because of it. Our sign-ups doubled in the first month.', 'forma-3d-toolkit' ), esc_html__( 'Client name, Company', 'forma-3d-toolkit' ) ),
				forma_3d_tk_pattern_spacer( 32 ),
				forma_3d_tk_kit_cta(
					esc_html__( 'Available from March', 'forma-3d-toolkit' ),
					esc_html__( 'Have a project in mind?', 'forma-3d-toolkit' ),
					esc_html__( 'Tell me what you are building. I reply within two working days.', 'forma-3d-toolkit' ),
					array( array( esc_html__( 'Say hello', 'forma-3d-toolkit' ), $contact, 'arrow' ) )
				),
			)
		),
	);

	$templates['personal-cv'] = array(
		'kit'         => 'personal',
		'title'       => __( 'Résumé', 'forma-3d-toolkit' ),
		'pageTitle'   => __( 'Résumé', 'forma-3d-toolkit' ),
		'description' => __( 'Experience and education on a timeline, skills as tags, languages and talks.', 'forma-3d-toolkit' ),
		'content'     => forma_3d_tk_kit_join(
			array(
				forma_3d_tk_pattern_p( esc_html__( 'Product designer with twelve years of experience in banking, travel and education. I lead design on small, fast teams and like to stay close to the code.', 'forma-3d-toolkit' ), 'lead' ),
				forma_3d_tk_pattern_buttons(
					array(
						array( esc_html__( 'Download my CV (PDF)', 'forma-3d-toolkit' ), '#', '' ),
						array( esc_html__( 'Contact me', 'forma-3d-toolkit' ), $contact, 'outline' ),
					)
				),
				forma_3d_tk_kit_eyebrow( esc_html__( '(01) — Experience', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_h( esc_html__( 'Where I have worked', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_timeline( esc_html__( '2021 — Now', 'forma-3d-toolkit' ), esc_html__( 'Lisbon, remote', 'forma-3d-toolkit' ), esc_html__( 'Lead product designer, Fintech company', 'forma-3d-toolkit' ), esc_html__( 'Lead a team of four designers. Redesigned the mobile app, which raised the store rating from 3.9 to 4.7, and set up the design system the whole company now uses.', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_timeline( esc_html__( '2017 — 2021', 'forma-3d-toolkit' ), esc_html__( 'Berlin', 'forma-3d-toolkit' ), esc_html__( 'Senior product designer, Travel start-up', 'forma-3d-toolkit' ), esc_html__( 'Designed the booking flow from search to payment and ran weekly tests with travellers. Bookings on mobile grew by 60%.', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_timeline( esc_html__( '2013 — 2017', 'forma-3d-toolkit' ), esc_html__( 'Porto', 'forma-3d-toolkit' ), esc_html__( 'Designer, Digital studio', 'forma-3d-toolkit' ), esc_html__( 'Websites and apps for museums, schools and local brands. Learned to ship on time and on budget.', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_eyebrow( esc_html__( '(02) — Education', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_h( esc_html__( 'Education', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_timeline( esc_html__( '2011 — 2013', 'forma-3d-toolkit' ), esc_html__( 'University of Lisbon', 'forma-3d-toolkit' ), esc_html__( 'Master’s in Interaction Design', 'forma-3d-toolkit' ), esc_html__( 'Thesis on designing forms for people with low vision.', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_timeline( esc_html__( '2008 — 2011', 'forma-3d-toolkit' ), esc_html__( 'University of Porto', 'forma-3d-toolkit' ), esc_html__( 'Bachelor’s in Communication Design', 'forma-3d-toolkit' ), esc_html__( 'Typography, editorial design and photography.', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_eyebrow( esc_html__( '(03) — Skills', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_h( esc_html__( 'What I bring', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_list(
					array(
						esc_html__( 'Product strategy', 'forma-3d-toolkit' ),
						esc_html__( 'User research', 'forma-3d-toolkit' ),
						esc_html__( 'Interaction design', 'forma-3d-toolkit' ),
						esc_html__( 'Prototyping', 'forma-3d-toolkit' ),
						esc_html__( 'Design systems', 'forma-3d-toolkit' ),
						esc_html__( 'Accessibility', 'forma-3d-toolkit' ),
						esc_html__( 'Workshops', 'forma-3d-toolkit' ),
						esc_html__( 'Figma', 'forma-3d-toolkit' ),
						esc_html__( 'HTML & CSS', 'forma-3d-toolkit' ),
						esc_html__( 'Mentoring', 'forma-3d-toolkit' ),
					),
					'forma-kit-tags'
				),
				forma_3d_tk_kit_eyebrow( esc_html__( '(04) — Languages and talks', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_columns(
					array(
						forma_3d_tk_kit_join(
							array(
								forma_3d_tk_kit_h( esc_html__( 'Languages', 'forma-3d-toolkit' ), 3 ),
								forma_3d_tk_kit_table(
									array( esc_html__( 'Language', 'forma-3d-toolkit' ), esc_html__( 'Level', 'forma-3d-toolkit' ) ),
									array(
										array( esc_html__( 'Portuguese', 'forma-3d-toolkit' ), esc_html__( 'Native', 'forma-3d-toolkit' ) ),
										array( esc_html__( 'English', 'forma-3d-toolkit' ), esc_html__( 'Fluent', 'forma-3d-toolkit' ) ),
										array( esc_html__( 'German', 'forma-3d-toolkit' ), esc_html__( 'Working knowledge', 'forma-3d-toolkit' ) ),
									)
								),
							)
						),
						forma_3d_tk_kit_join(
							array(
								forma_3d_tk_kit_h( esc_html__( 'Talks and awards', 'forma-3d-toolkit' ), 3 ),
								forma_3d_tk_kit_list(
									array(
										esc_html__( '2025 — “Small design systems”, a design conference in Porto', 'forma-3d-toolkit' ),
										esc_html__( '2024 — Mobile app of the year, finalist', 'forma-3d-toolkit' ),
										esc_html__( '2022 — Guest lecturer, University of Lisbon', 'forma-3d-toolkit' ),
									),
									'forma-kit-check'
								),
							)
						),
					)
				),
			)
		),
	);

	$templates['personal-contact'] = array(
		'kit'         => 'personal',
		'title'       => __( 'Contact me', 'forma-3d-toolkit' ),
		'pageTitle'   => __( 'Let’s talk.', 'forma-3d-toolkit' ),
		'description' => __( 'Email, a call booking and your location, social links and a few answers before people write.', 'forma-3d-toolkit' ),
		'content'     => forma_3d_tk_kit_join(
			array(
				forma_3d_tk_pattern_p( esc_html__( 'Tell me about your project, a role or just say hello. I read every message and reply within two working days.', 'forma-3d-toolkit' ), 'lead' ),
				forma_3d_tk_kit_columns(
					array(
						forma_3d_tk_kit_card( esc_html__( 'Email', 'forma-3d-toolkit' ), 'hello@example.com', forma_3d_tk_pattern_p( esc_html__( 'The best way to reach me.', 'forma-3d-toolkit' ), 'muted' ) ),
						forma_3d_tk_kit_card( esc_html__( 'A short call', 'forma-3d-toolkit' ), esc_html__( 'Twenty minutes to see if we are a good fit.', 'forma-3d-toolkit' ), forma_3d_tk_pattern_buttons( array( array( esc_html__( 'Book a call', 'forma-3d-toolkit' ), '#', 'outline' ) ) ) ),
						forma_3d_tk_kit_card( esc_html__( 'Based in', 'forma-3d-toolkit' ), esc_html__( 'Lisbon, Portugal', 'forma-3d-toolkit' ), forma_3d_tk_pattern_p( esc_html__( 'I work with teams from UTC-5 to UTC+3.', 'forma-3d-toolkit' ), 'muted' ) ),
					),
					'forma-kit-cards'
				),
				forma_3d_tk_pattern_p( esc_html__( 'Add a contact form here with the form plugin you prefer.', 'forma-3d-toolkit' ), 'muted' ),
				forma_3d_tk_kit_eyebrow( esc_html__( 'Elsewhere', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_h( esc_html__( 'Find me online', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_social( array( 'linkedin', 'instagram', 'dribbble', 'github' ) ),
				forma_3d_tk_kit_eyebrow( esc_html__( 'Before you write', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_h( esc_html__( 'Good to know', 'forma-3d-toolkit' ) ),
				forma_3d_tk_pattern_details( esc_html__( 'Are you available for new projects?', 'forma-3d-toolkit' ), esc_html__( 'I take on two projects at a time. My next free slot starts in March.', 'forma-3d-toolkit' ) ),
				forma_3d_tk_pattern_details( esc_html__( 'Do you work remotely?', 'forma-3d-toolkit' ), esc_html__( 'Yes, most of the time. For workshops I am happy to travel.', 'forma-3d-toolkit' ) ),
				forma_3d_tk_pattern_details( esc_html__( 'How do you charge?', 'forma-3d-toolkit' ), esc_html__( 'A fixed price for clear projects and a day rate for ongoing work. You get a written quote before we start.', 'forma-3d-toolkit' ) ),
			)
		),
	);

	/*
	 * eCommerce.
	 */
	$templates['shop-home'] = array(
		'kit'         => 'shop',
		'title'       => __( 'Store homepage', 'forma-3d-toolkit' ),
		'pageTitle'   => __( 'Good things, made to last.', 'forma-3d-toolkit' ),
		'description' => __( 'A featured collection, store promises, new arrivals and best sellers from your shop, reviews and a newsletter offer.', 'forma-3d-toolkit' ),
		'content'     => forma_3d_tk_kit_join(
			array(
				forma_3d_tk_pattern_p( esc_html__( 'Furniture and homeware from small workshops, delivered to your door. New pieces every week.', 'forma-3d-toolkit' ), 'lead' ),
				forma_3d_tk_pattern_buttons(
					array(
						array( esc_html__( 'Shop new arrivals', 'forma-3d-toolkit' ), $shop, 'arrow' ),
						array( esc_html__( 'See the sale', 'forma-3d-toolkit' ), $shop, 'outline' ),
					)
				),
				forma_3d_tk_pattern_group(
					forma_3d_tk_kit_columns(
						array(
							forma_3d_tk_kit_join(
								array(
									forma_3d_tk_kit_eyebrow( esc_html__( 'New season', 'forma-3d-toolkit' ) ),
									forma_3d_tk_kit_h( esc_html__( 'The velvet edit', 'forma-3d-toolkit' ) ),
									forma_3d_tk_pattern_p( esc_html__( 'Deep colours and soft textures for the cooler months. Sofas, armchairs and ottomans in six shades.', 'forma-3d-toolkit' ) ),
									forma_3d_tk_pattern_buttons( array( array( esc_html__( 'Shop the collection', 'forma-3d-toolkit' ), $shop, 'arrow' ) ) ),
								)
							),
							forma_3d_tk_kit_image( forma_3d_tk_pattern_img( 'GlamVelvetSofa-1' ), esc_attr__( 'A velvet sofa', 'forma-3d-toolkit' ), '16/9' ),
						),
						'forma-kit-feature',
						true,
						array( '40%', '60%' )
					),
					'bg-2',
					true
				),
				forma_3d_tk_kit_columns(
					array(
						forma_3d_tk_pattern_p( '<strong>' . esc_html__( 'Free delivery', 'forma-3d-toolkit' ) . '</strong>' ) . "\n\n" . forma_3d_tk_pattern_p( esc_html__( 'On orders over $99.', 'forma-3d-toolkit' ), 'muted' ),
						forma_3d_tk_pattern_p( '<strong>' . esc_html__( '30-day returns', 'forma-3d-toolkit' ) . '</strong>' ) . "\n\n" . forma_3d_tk_pattern_p( esc_html__( 'Changed your mind? Send it back.', 'forma-3d-toolkit' ), 'muted' ),
						forma_3d_tk_pattern_p( '<strong>' . esc_html__( 'Secure checkout', 'forma-3d-toolkit' ) . '</strong>' ) . "\n\n" . forma_3d_tk_pattern_p( esc_html__( 'Cards, wallets and pay later.', 'forma-3d-toolkit' ), 'muted' ),
						forma_3d_tk_pattern_p( '<strong>' . esc_html__( 'Real people', 'forma-3d-toolkit' ) . '</strong>' ) . "\n\n" . forma_3d_tk_pattern_p( esc_html__( 'Help seven days a week.', 'forma-3d-toolkit' ), 'muted' ),
					),
					'forma-kit-stats'
				),
				forma_3d_tk_kit_eyebrow( esc_html__( '(01) — Just in', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_h( esc_html__( 'New arrivals', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_products( '[products limit="4" columns="4" orderby="date" order="DESC"]' ),
				forma_3d_tk_kit_columns(
					array(
						forma_3d_tk_pattern_group(
							forma_3d_tk_kit_join(
								array(
									forma_3d_tk_kit_image( forma_3d_tk_pattern_img( 'SheenWoodLeatherSofa-0' ), esc_attr__( 'A leather sofa', 'forma-3d-toolkit' ), '16/9' ),
									forma_3d_tk_kit_eyebrow( esc_html__( 'Living room', 'forma-3d-toolkit' ) ),
									forma_3d_tk_kit_h( esc_html__( 'Sofas to sink into', 'forma-3d-toolkit' ), 3 ),
									forma_3d_tk_pattern_buttons( array( array( esc_html__( 'Shop sofas', 'forma-3d-toolkit' ), $shop, 'outline' ) ) ),
								)
							),
							'bg-2'
						),
						forma_3d_tk_pattern_group(
							forma_3d_tk_kit_join(
								array(
									forma_3d_tk_kit_image( forma_3d_tk_pattern_img( 'coffee_table_round_01-1' ), esc_attr__( 'A round coffee table', 'forma-3d-toolkit' ), '16/9' ),
									forma_3d_tk_kit_eyebrow( esc_html__( 'Tables', 'forma-3d-toolkit' ) ),
									forma_3d_tk_kit_h( esc_html__( 'Marble, oak and brass', 'forma-3d-toolkit' ), 3 ),
									forma_3d_tk_pattern_buttons( array( array( esc_html__( 'Shop tables', 'forma-3d-toolkit' ), $shop, 'outline' ) ) ),
								)
							),
							'bg-2'
						),
					),
					'forma-kit-cards'
				),
				forma_3d_tk_kit_eyebrow( esc_html__( '(02) — Customer favourites', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_h( esc_html__( 'Best sellers', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_products( '[products limit="4" columns="4" orderby="popularity"]' ),
				forma_3d_tk_kit_eyebrow( esc_html__( '(03) — Reviews', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_h( esc_html__( 'Rated 4.9 out of 5', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_columns(
					array(
						forma_3d_tk_kit_review( esc_html__( 'The chair is even better than in the photos, and it arrived two days early.', 'forma-3d-toolkit' ), esc_html__( 'Customer name, City', 'forma-3d-toolkit' ) ),
						forma_3d_tk_kit_review( esc_html__( 'Solid, beautifully finished and easy to put together. We have already ordered the matching table.', 'forma-3d-toolkit' ), esc_html__( 'Customer name, City', 'forma-3d-toolkit' ) ),
						forma_3d_tk_kit_review( esc_html__( 'I changed the colour after delivery and the exchange took one phone call.', 'forma-3d-toolkit' ), esc_html__( 'Customer name, City', 'forma-3d-toolkit' ) ),
					),
					'forma-kit-cards'
				),
				forma_3d_tk_pattern_spacer( 32 ),
				forma_3d_tk_kit_cta(
					esc_html__( 'Newsletter', 'forma-3d-toolkit' ),
					esc_html__( 'Get 10% off your first order', 'forma-3d-toolkit' ),
					esc_html__( 'New arrivals, restocks and early access to the sale, once a week. Add your newsletter form here.', 'forma-3d-toolkit' ),
					array( array( esc_html__( 'Sign me up', 'forma-3d-toolkit' ), '#', 'arrow' ) )
				),
			)
		),
	);

	$templates['shop-sale'] = array(
		'kit'         => 'shop',
		'title'       => __( 'Sale', 'forma-3d-toolkit' ),
		'pageTitle'   => __( 'The sale is on.', 'forma-3d-toolkit' ),
		'description' => __( 'A sale banner, every product on sale from your shop, the sale’s rules and questions.', 'forma-3d-toolkit' ),
		'content'     => forma_3d_tk_kit_join(
			array(
				forma_3d_tk_pattern_p( esc_html__( 'Up to 40% off selected pieces, while stock lasts. No code needed: the prices in the shop are already reduced.', 'forma-3d-toolkit' ), 'lead' ),
				forma_3d_tk_kit_group(
					forma_3d_tk_kit_columns(
						array(
							forma_3d_tk_kit_join(
								array(
									forma_3d_tk_kit_eyebrow( esc_html__( 'Ends Sunday at midnight', 'forma-3d-toolkit' ) ),
									forma_3d_tk_kit_p( esc_html__( 'Up to 40% off', 'forma-3d-toolkit' ), 'forma-kit-num' ),
									forma_3d_tk_pattern_p( esc_html__( 'Sofas, chairs, tables and storage. Extra 10% off with the code WELCOME10 on your first order.', 'forma-3d-toolkit' ) ),
									forma_3d_tk_pattern_buttons( array( array( esc_html__( 'Shop the sale', 'forma-3d-toolkit' ), $shop, 'arrow' ) ) ),
								)
							),
							forma_3d_tk_kit_image( forma_3d_tk_pattern_img( 'SheenChair-1' ), esc_attr__( 'A velvet chair', 'forma-3d-toolkit' ), '4/3' ),
						),
						'',
						true,
						array( '55%', '45%' )
					),
					'forma-kit-banner',
					true
				),
				forma_3d_tk_kit_eyebrow( esc_html__( '(01) — On sale now', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_h( esc_html__( 'Every reduced piece', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_products( '[sale_products limit="8" columns="4"]' ),
				forma_3d_tk_kit_eyebrow( esc_html__( '(02) — The rules', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_h( esc_html__( 'Sale, without the small print', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_columns(
					array(
						forma_3d_tk_kit_card( esc_html__( 'Free delivery', 'forma-3d-toolkit' ), esc_html__( 'On every order over $99, sale items included.', 'forma-3d-toolkit' ) ),
						forma_3d_tk_kit_card( esc_html__( 'Price promise', 'forma-3d-toolkit' ), esc_html__( 'Bought something at full price in the last 14 days? We refund the difference.', 'forma-3d-toolkit' ) ),
						forma_3d_tk_kit_card( esc_html__( 'Easy returns', 'forma-3d-toolkit' ), esc_html__( 'Sale pieces have the same 30 days to return as everything else.', 'forma-3d-toolkit' ) ),
					),
					'forma-kit-cards'
				),
				forma_3d_tk_kit_eyebrow( esc_html__( '(03) — Questions', 'forma-3d-toolkit' ) ),
				forma_3d_tk_pattern_details( esc_html__( 'Can I combine the sale with a discount code?', 'forma-3d-toolkit' ), esc_html__( 'Yes, one code per order, including WELCOME10 on your first order.', 'forma-3d-toolkit' ) ),
				forma_3d_tk_pattern_details( esc_html__( 'Will the sale pieces come back in stock?', 'forma-3d-toolkit' ), esc_html__( 'Some will, at full price. When a piece is gone at the sale price, it is gone.', 'forma-3d-toolkit' ) ),
				forma_3d_tk_pattern_details( esc_html__( 'Can I reserve a piece?', 'forma-3d-toolkit' ), esc_html__( 'Sale items cannot be reserved. A piece is yours once the order is paid.', 'forma-3d-toolkit' ) ),
			)
		),
	);

	$templates['shop-shipping'] = array(
		'kit'         => 'shop',
		'title'       => __( 'Shipping & returns', 'forma-3d-toolkit' ),
		'pageTitle'   => __( 'Shipping & returns', 'forma-3d-toolkit' ),
		'description' => __( 'Delivery times and prices in a table, international shipping, returns in three steps and questions.', 'forma-3d-toolkit' ),
		'content'     => forma_3d_tk_kit_join(
			array(
				forma_3d_tk_pattern_p( esc_html__( 'Everything about getting your order to you, and back to us if it is not right.', 'forma-3d-toolkit' ), 'lead' ),
				forma_3d_tk_kit_columns(
					array(
						forma_3d_tk_kit_stat( esc_html__( '2–5', 'forma-3d-toolkit' ), esc_html__( 'working days for most deliveries', 'forma-3d-toolkit' ) ),
						forma_3d_tk_kit_stat( '$99', esc_html__( 'and over ships for free', 'forma-3d-toolkit' ) ),
						forma_3d_tk_kit_stat( '30', esc_html__( 'days to change your mind', 'forma-3d-toolkit' ) ),
					),
					'forma-kit-stats'
				),
				forma_3d_tk_kit_eyebrow( esc_html__( '(01) — Delivery', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_h( esc_html__( 'Delivery options', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_table(
					array( esc_html__( 'Option', 'forma-3d-toolkit' ), esc_html__( 'Time', 'forma-3d-toolkit' ), esc_html__( 'Price', 'forma-3d-toolkit' ) ),
					array(
						array( esc_html__( 'Standard', 'forma-3d-toolkit' ), esc_html__( '3–5 working days', 'forma-3d-toolkit' ), esc_html__( '$6.95, free over $99', 'forma-3d-toolkit' ) ),
						array( esc_html__( 'Express', 'forma-3d-toolkit' ), esc_html__( '1–2 working days', 'forma-3d-toolkit' ), esc_html__( '$14.95', 'forma-3d-toolkit' ) ),
						array( esc_html__( 'Large items, to the room of your choice', 'forma-3d-toolkit' ), esc_html__( 'A two-hour slot you book', 'forma-3d-toolkit' ), esc_html__( '$49', 'forma-3d-toolkit' ) ),
						array( esc_html__( 'Click & collect', 'forma-3d-toolkit' ), esc_html__( 'Next working day', 'forma-3d-toolkit' ), esc_html__( 'Free', 'forma-3d-toolkit' ) ),
					)
				),
				forma_3d_tk_pattern_p( esc_html__( 'We ship to most countries in Europe and North America. Duties and taxes are shown at checkout, so there is nothing more to pay on delivery.', 'forma-3d-toolkit' ), 'muted' ),
				forma_3d_tk_kit_eyebrow( esc_html__( '(02) — Returns', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_h( esc_html__( 'Returns in three steps', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_columns(
					array(
						forma_3d_tk_kit_step( '01', esc_html__( 'Start a return', 'forma-3d-toolkit' ), esc_html__( 'Open your account, choose the order and tell us what is coming back.', 'forma-3d-toolkit' ) ),
						forma_3d_tk_kit_step( '02', esc_html__( 'Pack it', 'forma-3d-toolkit' ), esc_html__( 'Use the original box if you can. Large pieces are collected from your door.', 'forma-3d-toolkit' ) ),
						forma_3d_tk_kit_step( '03', esc_html__( 'Get your refund', 'forma-3d-toolkit' ), esc_html__( 'We refund to your original payment method within 5 days of receiving it.', 'forma-3d-toolkit' ) ),
					),
					'forma-kit-steps'
				),
				forma_3d_tk_kit_eyebrow( esc_html__( '(03) — Questions', 'forma-3d-toolkit' ) ),
				forma_3d_tk_pattern_details( esc_html__( 'How do I track my order?', 'forma-3d-toolkit' ), esc_html__( 'You get an email with a tracking link when your order leaves us.', 'forma-3d-toolkit' ) ),
				forma_3d_tk_pattern_details( esc_html__( 'Something arrived damaged. What now?', 'forma-3d-toolkit' ), esc_html__( 'Send us a photo within 48 hours and we send a replacement or refund you, whichever you prefer.', 'forma-3d-toolkit' ) ),
				forma_3d_tk_pattern_details( esc_html__( 'Who pays for return shipping?', 'forma-3d-toolkit' ), esc_html__( 'Returns are free for orders over $99. Otherwise the return label costs $6.95.', 'forma-3d-toolkit' ) ),
				forma_3d_tk_pattern_spacer( 32 ),
				forma_3d_tk_pattern_group(
					forma_3d_tk_kit_h( esc_html__( 'Need a hand?', 'forma-3d-toolkit' ), 3 ) . "\n\n" . forma_3d_tk_pattern_p( esc_html__( 'Our team answers every day from 9:00 to 18:00.', 'forma-3d-toolkit' ) ) . "\n\n" . forma_3d_tk_pattern_buttons( array( array( esc_html__( 'Contact us', 'forma-3d-toolkit' ), $contact, 'arrow' ) ) ),
					'',
					false,
					true
				),
			)
		),
	);

	/*
	 * IT company.
	 */
	$templates['it-home'] = array(
		'kit'         => 'it',
		'title'       => __( 'IT company homepage', 'forma-3d-toolkit' ),
		'pageTitle'   => __( 'We build software that works.', 'forma-3d-toolkit' ),
		'description' => __( 'A photo cover, clients, six services, numbers, how you work, a client quote and a call to action.', 'forma-3d-toolkit' ),
		'content'     => forma_3d_tk_kit_join(
			array(
				forma_3d_tk_pattern_p( esc_html__( 'We design, build and run web and mobile products for growing companies, from the first idea to the millionth user.', 'forma-3d-toolkit' ), 'lead' ),
				forma_3d_tk_pattern_buttons(
					array(
						array( esc_html__( 'Start a project', 'forma-3d-toolkit' ), $contact, 'arrow' ),
						array( esc_html__( 'Our services', 'forma-3d-toolkit' ), '#services', 'outline' ),
					)
				),
				forma_3d_tk_kit_cover(
					$img( 'it-team' ),
					forma_3d_tk_kit_eyebrow( esc_html__( 'Since 2012', 'forma-3d-toolkit' ) ) . "\n\n" . forma_3d_tk_kit_p( esc_html__( '60 engineers and designers in three time zones.', 'forma-3d-toolkit' ), '', 'large' ),
					600,
					30
				),
				forma_3d_tk_kit_row(
					forma_3d_tk_kit_join(
						array(
							forma_3d_tk_kit_p( esc_html__( 'Trusted by', 'forma-3d-toolkit' ), 'forma-kit-eyebrow' ),
							forma_3d_tk_pattern_p( 'Northbeam' ),
							forma_3d_tk_pattern_p( 'Fernhill' ),
							forma_3d_tk_pattern_p( 'Quarry &amp; Co' ),
							forma_3d_tk_pattern_p( 'Halden' ),
							forma_3d_tk_pattern_p( 'Mosaic Labs' ),
						)
					),
					'forma-kit-logos'
				),
				forma_3d_tk_kit_eyebrow( esc_html__( '(01) — Services', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_h( esc_html__( 'Everything you need to ship.', 'forma-3d-toolkit' ), 2, 'services' ),
				forma_3d_tk_kit_columns(
					array(
						forma_3d_tk_kit_card( esc_html__( 'Web applications', 'forma-3d-toolkit' ), esc_html__( 'Fast, accessible web apps and customer portals, built to grow with you.', 'forma-3d-toolkit' ) ),
						forma_3d_tk_kit_card( esc_html__( 'Mobile apps', 'forma-3d-toolkit' ), esc_html__( 'iOS and Android apps from one code base, with the polish of native ones.', 'forma-3d-toolkit' ) ),
						forma_3d_tk_kit_card( esc_html__( 'Cloud & DevOps', 'forma-3d-toolkit' ), esc_html__( 'Infrastructure as code, automated releases and monitoring that wakes us, not you.', 'forma-3d-toolkit' ) ),
					),
					'forma-kit-cards'
				),
				forma_3d_tk_kit_columns(
					array(
						forma_3d_tk_kit_card( esc_html__( 'UX & product design', 'forma-3d-toolkit' ), esc_html__( 'Research, prototypes and interfaces that people understand the first time.', 'forma-3d-toolkit' ) ),
						forma_3d_tk_kit_card( esc_html__( 'Data & AI', 'forma-3d-toolkit' ), esc_html__( 'Dashboards, data pipelines and AI features that solve a real problem.', 'forma-3d-toolkit' ) ),
						forma_3d_tk_kit_card( esc_html__( 'Support & maintenance', 'forma-3d-toolkit' ), esc_html__( 'Updates, security patches and a team that knows your product, around the clock.', 'forma-3d-toolkit' ) ),
					),
					'forma-kit-cards'
				),
				forma_3d_tk_pattern_spacer( 32 ),
				forma_3d_tk_kit_group(
					forma_3d_tk_kit_columns(
						array(
							forma_3d_tk_kit_stat( '250+', esc_html__( 'projects delivered', 'forma-3d-toolkit' ) ),
							forma_3d_tk_kit_stat( '98%', esc_html__( 'of clients stay with us', 'forma-3d-toolkit' ) ),
							forma_3d_tk_kit_stat( '60', esc_html__( 'engineers and designers', 'forma-3d-toolkit' ) ),
							forma_3d_tk_kit_stat( '24/7', esc_html__( 'support and monitoring', 'forma-3d-toolkit' ) ),
						)
					),
					'forma-kit-numbers',
					true
				),
				forma_3d_tk_kit_eyebrow( esc_html__( '(02) — How we work', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_h( esc_html__( 'From first call to launch.', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_columns(
					array(
						forma_3d_tk_kit_step( '01', esc_html__( 'Discover', 'forma-3d-toolkit' ), esc_html__( 'A two-week sprint to agree on goals, users and scope.', 'forma-3d-toolkit' ) ),
						forma_3d_tk_kit_step( '02', esc_html__( 'Design', 'forma-3d-toolkit' ), esc_html__( 'Clickable prototypes, tested with real users before we write code.', 'forma-3d-toolkit' ) ),
						forma_3d_tk_kit_step( '03', esc_html__( 'Build', 'forma-3d-toolkit' ), esc_html__( 'Releases every two weeks, so you see progress, not reports.', 'forma-3d-toolkit' ) ),
						forma_3d_tk_kit_step( '04', esc_html__( 'Run', 'forma-3d-toolkit' ), esc_html__( 'We host, monitor and improve the product after launch.', 'forma-3d-toolkit' ) ),
					),
					'forma-kit-steps'
				),
				forma_3d_tk_pattern_spacer( 48 ),
				forma_3d_tk_kit_columns(
					array(
						forma_3d_tk_kit_image( $img( 'it-planning' ), esc_attr__( 'Two people planning a project on paper next to their laptops', 'forma-3d-toolkit' ), '4/3', true ),
						forma_3d_tk_pattern_quote( esc_html__( 'They shipped our new platform in five months and our support tickets fell by half. It feels like one team, not a supplier.', 'forma-3d-toolkit' ), esc_html__( 'Client name, CTO, Company', 'forma-3d-toolkit' ) ),
					),
					'',
					true
				),
				forma_3d_tk_pattern_spacer( 48 ),
				forma_3d_tk_kit_cta(
					esc_html__( 'Free 30-minute call', 'forma-3d-toolkit' ),
					esc_html__( 'Let’s build something together.', 'forma-3d-toolkit' ),
					esc_html__( 'Tell us about your project. You get a plan and an estimate within a week.', 'forma-3d-toolkit' ),
					array(
						array( esc_html__( 'Start a project', 'forma-3d-toolkit' ), $contact, 'arrow' ),
						array( esc_html__( 'See pricing', 'forma-3d-toolkit' ), home_url( '/pricing/' ), 'outline' ),
					)
				),
			)
		),
	);

	$services = array(
		array( 'it-code', esc_attr__( 'Code on a screen', 'forma-3d-toolkit' ), esc_html__( 'Web applications', 'forma-3d-toolkit' ), esc_html__( 'Customer portals, booking systems, internal tools and SaaS products. Built with modern frameworks, tested automatically and fast on any device.', 'forma-3d-toolkit' ), array( esc_html__( 'React, Vue and server-rendered apps', 'forma-3d-toolkit' ), esc_html__( 'APIs and integrations with the tools you use', 'forma-3d-toolkit' ), esc_html__( 'Accessibility to WCAG 2.2 AA', 'forma-3d-toolkit' ) ) ),
		array( 'it-office', esc_attr__( 'A tidy desk with a computer', 'forma-3d-toolkit' ), esc_html__( 'Cloud & DevOps', 'forma-3d-toolkit' ), esc_html__( 'We move your product to the cloud, automate how it is released and watch it day and night, so updates are boring and outages are rare.', 'forma-3d-toolkit' ), array( esc_html__( 'AWS, Google Cloud and Azure', 'forma-3d-toolkit' ), esc_html__( 'Automated tests and releases', 'forma-3d-toolkit' ), esc_html__( 'Monitoring, backups and security updates', 'forma-3d-toolkit' ) ) ),
		array( 'it-planning', esc_attr__( 'Two people planning on paper', 'forma-3d-toolkit' ), esc_html__( 'UX & product design', 'forma-3d-toolkit' ), esc_html__( 'We talk to your users, map what they need and design interfaces they understand the first time. Every design is tested before it is built.', 'forma-3d-toolkit' ), array( esc_html__( 'User research and interviews', 'forma-3d-toolkit' ), esc_html__( 'Clickable prototypes', 'forma-3d-toolkit' ), esc_html__( 'Design systems', 'forma-3d-toolkit' ) ) ),
	);
	$rows     = array();
	foreach ( $services as $i => $service ) {
		$text   = forma_3d_tk_kit_join(
			array(
				forma_3d_tk_kit_eyebrow( sprintf( '%02d', $i + 1 ) ),
				forma_3d_tk_kit_h( $service[2] ),
				forma_3d_tk_pattern_p( $service[3] ),
				forma_3d_tk_kit_list( $service[4], 'forma-kit-check' ),
				forma_3d_tk_pattern_buttons( array( array( esc_html__( 'Talk to us', 'forma-3d-toolkit' ), $contact, 'outline' ) ) ),
			)
		);
		$image  = forma_3d_tk_kit_image( $img( $service[0] ), $service[1], '4/3', true );
		$rows[] = forma_3d_tk_kit_columns( 1 === $i % 2 ? array( $text, $image ) : array( $image, $text ), '', true );
		$rows[] = forma_3d_tk_pattern_spacer( 48 );
	}
	$templates['it-services'] = array(
		'kit'         => 'it',
		'title'       => __( 'Services', 'forma-3d-toolkit' ),
		'pageTitle'   => __( 'Services', 'forma-3d-toolkit' ),
		'description' => __( 'Three services with photos and what is included, the technologies you use and three ways to work together.', 'forma-3d-toolkit' ),
		'content'     => forma_3d_tk_kit_join(
			array_merge(
				array(
					forma_3d_tk_pattern_p( esc_html__( 'One team for the whole product: design, engineering and the cloud it runs on. Pick one service or all of them.', 'forma-3d-toolkit' ), 'lead' ),
					forma_3d_tk_pattern_spacer( 32 ),
				),
				$rows,
				array(
					forma_3d_tk_kit_eyebrow( esc_html__( 'Our stack', 'forma-3d-toolkit' ) ),
					forma_3d_tk_kit_h( esc_html__( 'Technologies we use', 'forma-3d-toolkit' ) ),
					forma_3d_tk_kit_list( array( 'TypeScript', 'React', 'Next.js', 'Vue', 'Node.js', 'PHP', 'Python', 'Swift', 'Kotlin', 'PostgreSQL', 'Docker', 'Kubernetes', 'Terraform', 'AWS' ), 'forma-kit-tags' ),
					forma_3d_tk_kit_eyebrow( esc_html__( 'Working together', 'forma-3d-toolkit' ) ),
					forma_3d_tk_kit_h( esc_html__( 'Three ways to start', 'forma-3d-toolkit' ) ),
					forma_3d_tk_kit_columns(
						array(
							forma_3d_tk_kit_card( esc_html__( 'Fixed project', 'forma-3d-toolkit' ), esc_html__( 'A clear scope, a fixed price and a launch date. Best for a first version or a redesign.', 'forma-3d-toolkit' ) ),
							forma_3d_tk_kit_card( esc_html__( 'Dedicated team', 'forma-3d-toolkit' ), esc_html__( 'Designers and engineers who work only on your product, month after month.', 'forma-3d-toolkit' ) ),
							forma_3d_tk_kit_card( esc_html__( 'Support plan', 'forma-3d-toolkit' ), esc_html__( 'Updates, fixes and monitoring for a product that is already live.', 'forma-3d-toolkit' ) ),
						),
						'forma-kit-cards'
					),
					forma_3d_tk_pattern_spacer( 48 ),
					forma_3d_tk_kit_cta(
						esc_html__( 'Not sure where to start?', 'forma-3d-toolkit' ),
						esc_html__( 'Tell us what you need.', 'forma-3d-toolkit' ),
						esc_html__( 'A 30-minute call with an engineer and a designer. No slides, no sales pitch.', 'forma-3d-toolkit' ),
						array( array( esc_html__( 'Book a call', 'forma-3d-toolkit' ), $contact, 'arrow' ) )
					),
				)
			)
		),
	);

	$templates['it-pricing'] = array(
		'kit'         => 'it',
		'title'       => __( 'Pricing', 'forma-3d-toolkit' ),
		'pageTitle'   => __( 'Simple, honest pricing.', 'forma-3d-toolkit' ),
		'description' => __( 'Three plans with a featured one, a comparison table, questions and a call to action.', 'forma-3d-toolkit' ),
		'content'     => forma_3d_tk_kit_join(
			array(
				forma_3d_tk_pattern_p( esc_html__( 'Monthly plans with no long contracts. Change or cancel with 30 days’ notice.', 'forma-3d-toolkit' ), 'lead' ),
				forma_3d_tk_pattern_spacer( 16 ),
				forma_3d_tk_kit_columns(
					array(
						forma_3d_tk_kit_plan(
							esc_html__( 'Care', 'forma-3d-toolkit' ),
							'$490',
							esc_html__( 'per month, for a live product', 'forma-3d-toolkit' ),
							array( esc_html__( 'Security and software updates', 'forma-3d-toolkit' ), esc_html__( 'Uptime monitoring', 'forma-3d-toolkit' ), esc_html__( 'Daily backups', 'forma-3d-toolkit' ), esc_html__( '4 hours of fixes a month', 'forma-3d-toolkit' ) ),
							array( esc_html__( 'Choose Care', 'forma-3d-toolkit' ), $contact, 'outline' )
						),
						forma_3d_tk_kit_plan(
							esc_html__( 'Growth — most popular', 'forma-3d-toolkit' ),
							'$2,900',
							esc_html__( 'per month, for a product that keeps improving', 'forma-3d-toolkit' ),
							array( esc_html__( 'Everything in Care', 'forma-3d-toolkit' ), esc_html__( 'A designer and an engineer, 40 hours a month', 'forma-3d-toolkit' ), esc_html__( 'A release every two weeks', 'forma-3d-toolkit' ), esc_html__( 'Monthly product review', 'forma-3d-toolkit' ), esc_html__( 'Support within 4 hours', 'forma-3d-toolkit' ) ),
							array( esc_html__( 'Choose Growth', 'forma-3d-toolkit' ), $contact, 'arrow' )
						),
						forma_3d_tk_kit_plan(
							esc_html__( 'Team', 'forma-3d-toolkit' ),
							esc_html__( 'Custom', 'forma-3d-toolkit' ),
							esc_html__( 'for a dedicated product team', 'forma-3d-toolkit' ),
							array( esc_html__( 'Everything in Growth', 'forma-3d-toolkit' ), esc_html__( 'A full team on your product', 'forma-3d-toolkit' ), esc_html__( 'Your tools, your rituals', 'forma-3d-toolkit' ), esc_html__( '24/7 support', 'forma-3d-toolkit' ) ),
							array( esc_html__( 'Talk to us', 'forma-3d-toolkit' ), $contact, 'outline' )
						),
					),
					'forma-kit-plans',
					false,
					array(),
					array( '', 'forma-kit-dark', '' )
				),
				forma_3d_tk_kit_eyebrow( esc_html__( 'Compare', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_h( esc_html__( 'What each plan includes', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_table(
					array( '', esc_html__( 'Care', 'forma-3d-toolkit' ), esc_html__( 'Growth', 'forma-3d-toolkit' ), esc_html__( 'Team', 'forma-3d-toolkit' ) ),
					array(
						array( esc_html__( 'Updates and backups', 'forma-3d-toolkit' ), '✓', '✓', '✓' ),
						array( esc_html__( 'New features', 'forma-3d-toolkit' ), '—', esc_html__( '40 hours a month', 'forma-3d-toolkit' ), esc_html__( 'Full time', 'forma-3d-toolkit' ) ),
						array( esc_html__( 'Response time', 'forma-3d-toolkit' ), esc_html__( '1 working day', 'forma-3d-toolkit' ), esc_html__( '4 hours', 'forma-3d-toolkit' ), esc_html__( '1 hour, 24/7', 'forma-3d-toolkit' ) ),
						array( esc_html__( 'Product reviews', 'forma-3d-toolkit' ), '—', esc_html__( 'Monthly', 'forma-3d-toolkit' ), esc_html__( 'Weekly', 'forma-3d-toolkit' ) ),
					)
				),
				forma_3d_tk_kit_eyebrow( esc_html__( 'Questions', 'forma-3d-toolkit' ) ),
				forma_3d_tk_pattern_details( esc_html__( 'Is there a minimum contract?', 'forma-3d-toolkit' ), esc_html__( 'No. Plans run month to month and you can cancel with 30 days’ notice.', 'forma-3d-toolkit' ) ),
				forma_3d_tk_pattern_details( esc_html__( 'What if I need more hours?', 'forma-3d-toolkit' ), esc_html__( 'Extra hours are billed at the plan’s hourly rate, and we always ask before going over.', 'forma-3d-toolkit' ) ),
				forma_3d_tk_pattern_details( esc_html__( 'Who owns the code?', 'forma-3d-toolkit' ), esc_html__( 'You do, from the first commit. Everything lives in your own repository.', 'forma-3d-toolkit' ) ),
				forma_3d_tk_pattern_details( esc_html__( 'Do you build new products from scratch?', 'forma-3d-toolkit' ), esc_html__( 'Yes, as a fixed-price project. Ask us for a quote.', 'forma-3d-toolkit' ) ),
				forma_3d_tk_pattern_spacer( 32 ),
				forma_3d_tk_pattern_group(
					forma_3d_tk_kit_h( esc_html__( 'Not sure which plan fits?', 'forma-3d-toolkit' ), 3 ) . "\n\n" . forma_3d_tk_pattern_p( esc_html__( 'Tell us about your product and we will suggest one. The call is free.', 'forma-3d-toolkit' ) ) . "\n\n" . forma_3d_tk_pattern_buttons( array( array( esc_html__( 'Book a free call', 'forma-3d-toolkit' ), $contact, 'arrow' ) ) ),
					'',
					false,
					true
				),
			)
		),
	);

	/*
	 * Photography.
	 */
	$templates['photo-home'] = array(
		'kit'         => 'photo',
		'title'       => __( 'Photography homepage', 'forma-3d-toolkit' ),
		'pageTitle'   => __( 'Light, land and people.', 'forma-3d-toolkit' ),
		'description' => __( 'A full-width photo, a masonry gallery, an introduction, services, a client quote and a booking call to action.', 'forma-3d-toolkit' ),
		'content'     => forma_3d_tk_kit_join(
			array(
				forma_3d_tk_pattern_p( esc_html__( 'I’m Sam, a landscape and wedding photographer. I travel for quiet places and honest moments.', 'forma-3d-toolkit' ), 'lead' ),
				forma_3d_tk_pattern_buttons(
					array(
						array( esc_html__( 'View the portfolio', 'forma-3d-toolkit' ), home_url( '/portfolio/' ), 'arrow' ),
						array( esc_html__( 'Book a session', 'forma-3d-toolkit' ), $contact, 'outline' ),
					)
				),
				forma_3d_tk_kit_cover(
					$img( 'photo-hero' ),
					forma_3d_tk_kit_eyebrow( esc_html__( 'Alpine lake at first light', 'forma-3d-toolkit' ) ),
					680,
					10
				),
				forma_3d_tk_kit_eyebrow( esc_html__( '(01) — Selected work', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_h( esc_html__( 'Recent stories', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_gallery(
					array(
						array( $img( 'photo-1' ), esc_attr__( 'A wooden barn below sunlit mountains', 'forma-3d-toolkit' ) ),
						array( $img( 'photo-5' ), esc_attr__( 'A bride among blossoming trees', 'forma-3d-toolkit' ) ),
						array( $img( 'photo-7' ), esc_attr__( 'A river winding through a forested valley', 'forma-3d-toolkit' ) ),
						array( $img( 'photo-3' ), esc_attr__( 'A surfer inside a breaking wave', 'forma-3d-toolkit' ) ),
						array( $img( 'photo-2' ), esc_attr__( 'A couple on a bench by a lake', 'forma-3d-toolkit' ) ),
						array( $img( 'photo-9' ), esc_attr__( 'A misty forest', 'forma-3d-toolkit' ) ),
					),
					3,
					true
				),
				forma_3d_tk_pattern_buttons( array( array( esc_html__( 'See the whole portfolio', 'forma-3d-toolkit' ), home_url( '/portfolio/' ), 'arrow' ) ) ),
				forma_3d_tk_pattern_spacer( 64 ),
				forma_3d_tk_kit_columns(
					array(
						forma_3d_tk_kit_image( $img( 'photo-photographer' ), esc_attr__( 'A photographer taking a picture of the sea', 'forma-3d-toolkit' ), '4/5', true ),
						forma_3d_tk_kit_join(
							array(
								forma_3d_tk_kit_eyebrow( esc_html__( '(02) — About', 'forma-3d-toolkit' ) ),
								forma_3d_tk_kit_h( esc_html__( 'Patient light, honest moments.', 'forma-3d-toolkit' ) ),
								forma_3d_tk_pattern_p( esc_html__( 'I have photographed mountains, coastlines and more than 120 weddings across Europe. I work with natural light and as few poses as possible, so your photos look like you on your best day.', 'forma-3d-toolkit' ) ),
								forma_3d_tk_pattern_p( esc_html__( 'Prints of my landscape work are made on archival paper and signed by hand.', 'forma-3d-toolkit' ) ),
								forma_3d_tk_pattern_buttons( array( array( esc_html__( 'More about me', 'forma-3d-toolkit' ), '#', 'outline' ) ) ),
							)
						),
					),
					'',
					true,
					array( '45%', '55%' )
				),
				forma_3d_tk_kit_eyebrow( esc_html__( '(03) — Services', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_h( esc_html__( 'How I can help', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_columns(
					array(
						forma_3d_tk_kit_card( esc_html__( 'Weddings', 'forma-3d-toolkit' ), esc_html__( 'The whole day or the moments that matter most, from getting ready to the last dance.', 'forma-3d-toolkit' ) ),
						forma_3d_tk_kit_card( esc_html__( 'Portraits', 'forma-3d-toolkit' ), esc_html__( 'Couples, families and personal branding, outdoors in beautiful light.', 'forma-3d-toolkit' ) ),
						forma_3d_tk_kit_card( esc_html__( 'Prints & licensing', 'forma-3d-toolkit' ), esc_html__( 'Signed fine-art prints and photos for books, brands and magazines.', 'forma-3d-toolkit' ) ),
					),
					'forma-kit-cards'
				),
				forma_3d_tk_pattern_spacer( 48 ),
				forma_3d_tk_pattern_quote( esc_html__( 'We cried when we saw the photos. Sam caught moments we did not even know had happened.', 'forma-3d-toolkit' ), esc_html__( 'Client names, wedding in the Alps', 'forma-3d-toolkit' ) ),
				forma_3d_tk_pattern_spacer( 48 ),
				forma_3d_tk_kit_cta(
					esc_html__( 'Now booking next year', 'forma-3d-toolkit' ),
					esc_html__( 'Tell me about your day.', 'forma-3d-toolkit' ),
					esc_html__( 'Send me your date and place, and I will reply with availability and a few ideas.', 'forma-3d-toolkit' ),
					array(
						array( esc_html__( 'Check my availability', 'forma-3d-toolkit' ), $contact, 'arrow' ),
						array( esc_html__( 'See packages', 'forma-3d-toolkit' ), home_url( '/packages/' ), 'outline' ),
					)
				),
			)
		),
	);

	$templates['photo-portfolio'] = array(
		'kit'         => 'photo',
		'title'       => __( 'Portfolio', 'forma-3d-toolkit' ),
		'pageTitle'   => __( 'Portfolio', 'forma-3d-toolkit' ),
		'description' => __( 'Galleries for landscapes, weddings and the sea, and a note about prints.', 'forma-3d-toolkit' ),
		'content'     => forma_3d_tk_kit_join(
			array(
				forma_3d_tk_pattern_p( esc_html__( 'A selection from the last five years. Every landscape here is available as a signed print.', 'forma-3d-toolkit' ), 'lead' ),
				forma_3d_tk_kit_eyebrow( esc_html__( '(01) — Landscapes', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_h( esc_html__( 'Mountains and forests', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_gallery(
					array(
						array( $img( 'photo-hero' ), esc_attr__( 'A mountain lake reflecting the forest', 'forma-3d-toolkit' ) ),
						array( $img( 'photo-9' ), esc_attr__( 'A misty forest', 'forma-3d-toolkit' ) ),
						array( $img( 'photo-1' ), esc_attr__( 'A wooden barn below sunlit mountains', 'forma-3d-toolkit' ) ),
						array( $img( 'photo-7' ), esc_attr__( 'A river winding through a forested valley', 'forma-3d-toolkit' ) ),
					),
					2,
					true
				),
				forma_3d_tk_kit_eyebrow( esc_html__( '(02) — Weddings', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_h( esc_html__( 'Love stories', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_gallery(
					array(
						array( $img( 'photo-5' ), esc_attr__( 'A bride among blossoming trees', 'forma-3d-toolkit' ) ),
						array( $img( 'photo-8' ), esc_attr__( 'Chairs and flowers lining a wedding aisle', 'forma-3d-toolkit' ) ),
						array( $img( 'photo-2' ), esc_attr__( 'A couple on a bench by a lake', 'forma-3d-toolkit' ) ),
					)
				),
				forma_3d_tk_kit_eyebrow( esc_html__( '(03) — Sea & city', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_h( esc_html__( 'Water and light', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_gallery(
					array(
						array( $img( 'photo-3' ), esc_attr__( 'A surfer inside a breaking wave', 'forma-3d-toolkit' ) ),
						array( $img( 'photo-6' ), esc_attr__( 'A sailing ship seen from the top of its mast', 'forma-3d-toolkit' ) ),
						array( $img( 'photo-4' ), esc_attr__( 'A city skyline across the water at dusk', 'forma-3d-toolkit' ) ),
					)
				),
				forma_3d_tk_pattern_spacer( 48 ),
				forma_3d_tk_kit_cta(
					esc_html__( 'Prints', 'forma-3d-toolkit' ),
					esc_html__( 'Bring one home.', 'forma-3d-toolkit' ),
					esc_html__( 'Fine-art prints on archival paper, from 30 × 40 cm, signed and numbered.', 'forma-3d-toolkit' ),
					array( array( esc_html__( 'Ask about a print', 'forma-3d-toolkit' ), $contact, 'arrow' ) )
				),
			)
		),
	);

	$templates['photo-packages'] = array(
		'kit'         => 'photo',
		'title'       => __( 'Packages & booking', 'forma-3d-toolkit' ),
		'pageTitle'   => __( 'Packages & booking', 'forma-3d-toolkit' ),
		'description' => __( 'Three packages with prices, what is included, how booking works and questions.', 'forma-3d-toolkit' ),
		'content'     => forma_3d_tk_kit_join(
			array(
				forma_3d_tk_pattern_p( esc_html__( 'Clear prices and no surprises. Every package includes an online gallery to share with your family and friends.', 'forma-3d-toolkit' ), 'lead' ),
				forma_3d_tk_pattern_spacer( 16 ),
				forma_3d_tk_kit_columns(
					array(
						forma_3d_tk_kit_plan(
							esc_html__( 'Portrait session', 'forma-3d-toolkit' ),
							'$350',
							esc_html__( 'up to 90 minutes, one location', 'forma-3d-toolkit' ),
							array( esc_html__( '40 edited photos', 'forma-3d-toolkit' ), esc_html__( 'Online gallery', 'forma-3d-toolkit' ), esc_html__( 'Photos within 2 weeks', 'forma-3d-toolkit' ) ),
							array( esc_html__( 'Book a session', 'forma-3d-toolkit' ), $contact, 'outline' )
						),
						forma_3d_tk_kit_plan(
							esc_html__( 'Wedding day — most booked', 'forma-3d-toolkit' ),
							'$2,400',
							esc_html__( 'up to 10 hours', 'forma-3d-toolkit' ),
							array( esc_html__( '500+ edited photos', 'forma-3d-toolkit' ), esc_html__( 'Planning call and timeline', 'forma-3d-toolkit' ), esc_html__( 'Second photographer', 'forma-3d-toolkit' ), esc_html__( 'Online gallery and USB box', 'forma-3d-toolkit' ), esc_html__( 'Sneak peek within 48 hours', 'forma-3d-toolkit' ) ),
							array( esc_html__( 'Check my date', 'forma-3d-toolkit' ), $contact, 'arrow' )
						),
						forma_3d_tk_kit_plan(
							esc_html__( 'Elopement', 'forma-3d-toolkit' ),
							'$1,200',
							esc_html__( 'up to 4 hours, anywhere', 'forma-3d-toolkit' ),
							array( esc_html__( '200 edited photos', 'forma-3d-toolkit' ), esc_html__( 'Help with the location', 'forma-3d-toolkit' ), esc_html__( 'Online gallery', 'forma-3d-toolkit' ), esc_html__( 'Travel in Europe included', 'forma-3d-toolkit' ) ),
							array( esc_html__( 'Plan an elopement', 'forma-3d-toolkit' ), $contact, 'outline' )
						),
					),
					'forma-kit-plans',
					false,
					array(),
					array( '', 'forma-kit-dark', '' )
				),
				forma_3d_tk_kit_eyebrow( esc_html__( '(01) — Booking', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_h( esc_html__( 'How booking works', 'forma-3d-toolkit' ) ),
				forma_3d_tk_kit_columns(
					array(
						forma_3d_tk_kit_step( '01', esc_html__( 'Enquire', 'forma-3d-toolkit' ), esc_html__( 'Send your date, place and what you have in mind.', 'forma-3d-toolkit' ) ),
						forma_3d_tk_kit_step( '02', esc_html__( 'Reserve', 'forma-3d-toolkit' ), esc_html__( 'A 30% deposit holds your date. The rest is due a week before.', 'forma-3d-toolkit' ) ),
						forma_3d_tk_kit_step( '03', esc_html__( 'Plan', 'forma-3d-toolkit' ), esc_html__( 'We plan the timeline and the light together on a call.', 'forma-3d-toolkit' ) ),
						forma_3d_tk_kit_step( '04', esc_html__( 'Relive it', 'forma-3d-toolkit' ), esc_html__( 'Your gallery arrives, ready to download and share.', 'forma-3d-toolkit' ) ),
					),
					'forma-kit-steps'
				),
				forma_3d_tk_pattern_spacer( 48 ),
				forma_3d_tk_kit_columns(
					array(
						forma_3d_tk_kit_image( $img( 'photo-8' ), esc_attr__( 'Chairs and flowers lining a wedding aisle', 'forma-3d-toolkit' ), '4/5', true ),
						forma_3d_tk_kit_join(
							array(
								forma_3d_tk_kit_eyebrow( esc_html__( 'Always included', 'forma-3d-toolkit' ) ),
								forma_3d_tk_kit_h( esc_html__( 'In every package', 'forma-3d-toolkit' ) ),
								forma_3d_tk_kit_list(
									array(
										esc_html__( 'Natural, timeless editing', 'forma-3d-toolkit' ),
										esc_html__( 'A private online gallery for two years', 'forma-3d-toolkit' ),
										esc_html__( 'Print rights for personal use', 'forma-3d-toolkit' ),
										esc_html__( 'Backup equipment at every shoot', 'forma-3d-toolkit' ),
										esc_html__( 'Travel within 50 km', 'forma-3d-toolkit' ),
									),
									'forma-kit-check'
								),
							)
						),
					),
					'',
					true,
					array( '45%', '55%' )
				),
				forma_3d_tk_kit_eyebrow( esc_html__( '(02) — Questions', 'forma-3d-toolkit' ) ),
				forma_3d_tk_pattern_details( esc_html__( 'How far ahead should we book?', 'forma-3d-toolkit' ), esc_html__( 'Weddings are usually booked 9 to 14 months ahead. Portraits a few weeks ahead.', 'forma-3d-toolkit' ) ),
				forma_3d_tk_pattern_details( esc_html__( 'Do you travel?', 'forma-3d-toolkit' ), esc_html__( 'Yes. Travel within 50 km is included; beyond that I charge travel at cost.', 'forma-3d-toolkit' ) ),
				forma_3d_tk_pattern_details( esc_html__( 'What if it rains?', 'forma-3d-toolkit' ), esc_html__( 'Rain often makes the best photos. For portraits we can also move the session for free.', 'forma-3d-toolkit' ) ),
				forma_3d_tk_pattern_details( esc_html__( 'Can we have the raw files?', 'forma-3d-toolkit' ), esc_html__( 'I deliver edited photos only; the edit is part of how the photos look.', 'forma-3d-toolkit' ) ),
				forma_3d_tk_pattern_spacer( 48 ),
				forma_3d_tk_kit_cta(
					esc_html__( 'Dates go fast', 'forma-3d-toolkit' ),
					esc_html__( 'Is your date still free?', 'forma-3d-toolkit' ),
					esc_html__( 'Send me your date and I will reply within a day.', 'forma-3d-toolkit' ),
					array( array( esc_html__( 'Check my date', 'forma-3d-toolkit' ), $contact, 'arrow' ) )
				),
			)
		),
	);

	return $templates;
}

add_action( 'init', 'forma_3d_tk_register_kit_patterns' );

/**
 * Register a pattern category per kit, and each kit page as a pattern.
 */
function forma_3d_tk_register_kit_patterns() {
	foreach ( forma_3d_tk_kits() as $kit => $label ) {
		/* translators: %s: kit name, e.g. "Photography". */
		register_block_pattern_category( 'forma-kit-' . $kit, array( 'label' => sprintf( __( 'Forma: %s', 'forma-3d-toolkit' ), $label ) ) );
	}
	foreach ( forma_3d_tk_kit_templates() as $id => $template ) {
		register_block_pattern(
			'forma-3d-toolkit/kit-' . $id,
			array(
				'title'       => $template['title'],
				'description' => $template['description'],
				'content'     => $template['content'],
				'categories'  => array( 'forma-kit-' . $template['kit'] ),
				'postTypes'   => array( 'page' ),
			)
		);
	}
}

// Styles for the kit pages: on pages that use them, and in the block editor.
add_action(
	'wp_enqueue_scripts',
	function () {
		$post = get_post();
		if ( ! is_singular() || ! $post || false === strpos( $post->post_content, 'forma-kit-' ) ) {
			return;
		}
		$deps = wp_style_is( 'forma-3d-main', 'registered' ) ? array( 'forma-3d-main' ) : array();
		wp_enqueue_style( 'forma-3d-tk-kits', FORMA_3D_TK_URI . 'assets/kits.css', $deps, FORMA_3D_TK_VERSION );
	},
	20
);
add_action(
	'enqueue_block_assets',
	function () {
		if ( is_admin() ) {
			wp_enqueue_style( 'forma-3d-tk-kits', FORMA_3D_TK_URI . 'assets/kits.css', array(), FORMA_3D_TK_VERSION );
		}
	}
);
