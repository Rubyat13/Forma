=== Forma 3D Toolkit ===
Contributors: rubyat13
Tags: 3d, woocommerce, blocks, demo import, product viewer
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Companion plugin for the Forma 3D theme: homepage blocks, product finishes for 3D models and a one-click 3D demo store.

== Description ==

Forma 3D Toolkit adds the store features of the [Forma 3D](https://wordpress.org/themes/forma-3d/) theme:

* Eight homepage blocks for the block editor (Hero, Statement, 3D Showcase, Shop by Room, Anatomy, Marquee, Product Grid, Promise) and the "Forma 3D homepage" pattern.
* A "Forma Display" product tab: finishes (colour swatches that recolour the 3D model), stage colour, materials and designer.
* The chosen finish is saved on the cart line and the order.
* .glb uploads in the media library.
* Appearance > Forma Setup: one-click import of a 16-product 3D furniture store, with 3D models, photos, categories and the homepage.

The blocks are drawn by the Forma 3D theme, so they show nothing with another theme. The 3D models on product pages are shown by the free [3D Viewer](https://wordpress.org/plugins/3d-viewer/) plugin, and the store runs on [WooCommerce](https://wordpress.org/plugins/woocommerce/).

The plugin does not connect to any external service.

== Installation ==

1. Install and activate the Forma 3D theme, WooCommerce and 3D Viewer.
2. Install and activate Forma 3D Toolkit.
3. Go to Appearance > Forma Setup and click "Import demo store".

== Frequently Asked Questions ==

= What does the demo import change on my site? =

It adds 16 products with their 3D models and photos, 4 product categories and a "Home" page set as the front page. It also sets centimetres and kilograms as units, prices without cents, product reviews off, uncropped product thumbnails and pretty permalinks, and turns on the 3D Viewer WooCommerce integration. An existing "Home" page that has content is kept.

= Can I run the import again? =

Yes. Products are matched by SKU and updated, and media files that were already imported are reused.

= Does it work without the Forma 3D theme? =

The product fields and .glb uploads do. The homepage blocks and the demo homepage need the Forma 3D theme.

== Changelog ==

= 1.0.2 =
* The model picker in the "Forma Display" tab and the welcome notice now load their scripts with wp_enqueue_script().
* The model picker's labels can be translated.

= 1.0.1 =
* The demo import now brings in the demo's own homepage (hero product and headings), its tagline when the site has none, and its store settings: prices without cents, product reviews off and uncropped product thumbnails.
* On the first import, the widgets a new WordPress site puts in the footer move to Inactive widgets, so the footer matches the demo.
* The welcome notice can be dismissed and only shows with the Forma 3D theme.
* Finish data is sanitised when a product is saved.

= 1.0.0 =
* Initial release.

== Credits ==

Demo 3D models: Poly Haven (CC0, https://polyhaven.com) and Khronos glTF Sample Assets (https://github.com/KhronosGroup/glTF-Sample-Assets): SheenChair (CC0, Wayfair), GlamVelvetSofa (CC BY 4.0, Wayfair / Eric Chadwick), SheenWoodLeatherSofa (CC BY 4.0, Eric Chadwick / Darmstadt Graphics Group).

Demo product photos are renders of these models and use the same licences.
