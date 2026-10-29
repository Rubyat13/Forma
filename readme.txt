=== Forma 3D Toolkit ===
Contributors: rubyat13
Tags: 3d, woocommerce, blocks, demo import, product viewer
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Companion plugin for the Forma 3D theme: homepage blocks, page templates, product finishes for 3D models and one-click 3D starter sites.

== Description ==

Forma 3D Toolkit adds the store features of the [Forma 3D](https://wordpress.org/themes/forma-3d/) theme:

* Appearance > Forma Setup: three one-click starter sites, each with products, true-to-scale 3D models, photos, categories and a homepage (see below).
* Six page templates, offered when you create a new page: About us, Contact, Lookbook, FAQ, Shop the room and a 3D campaign landing page.
* Eight homepage blocks for the block editor (Hero, Statement, 3D Showcase, Shop by Room, Anatomy, Marquee, Product Grid, Promise) and the "Forma 3D homepage" pattern.
* A "Forma Display" product tab: finishes (colour swatches that recolour the 3D model), stage colour, materials and designer.
* The chosen finish is saved on the cart line and the order.
* .glb uploads in the media library.

= Starter sites =

* Furniture: 16 sofas, chairs, tables and storage pieces. Included in the plugin.
* Lighting: 14 chandeliers, pendants, table lamps, wall lights and lanterns. Downloaded when you import it.
* Home Decor: 14 vases, faux plants, clocks, mirrors and decorative objects. Downloaded when you import it.

The blocks are drawn by the Forma 3D theme, so they show nothing with another theme. The 3D models on product pages are shown by the free [3D Viewer](https://wordpress.org/plugins/3d-viewer/) plugin, and the store runs on [WooCommerce](https://wordpress.org/plugins/woocommerce/).

== Installation ==

1. Install and activate the Forma 3D theme, WooCommerce and 3D Viewer.
2. Install and activate Forma 3D Toolkit.
3. Go to Appearance > Forma Setup, choose a starter site and click Import.

== Frequently Asked Questions ==

= What does a starter site import change on my site? =

It adds the starter site's products with their 3D models and photos, its product categories and a "Home" page set as the front page. It also sets centimetres and kilograms as units, prices without cents, product reviews off, uncropped product thumbnails and pretty permalinks, and turns on the 3D Viewer WooCommerce integration. On the first import, an existing "Home" page that has content is kept.

= Can I switch to another starter site later? =

Yes. Choose another starter site in Forma Setup. By default the products of the previous starter site move to the Trash (you can restore them from Products > Trash), and the Home page gets the new design; its previous version stays in the page's revisions.

= Can I run the import again? =

Yes. Products are matched by SKU and updated, and media files that were already imported are reused.

= How do I use the page templates? =

Go to Pages > Add New Page. The "Choose a pattern" window lists the Forma 3D page templates; pick one and edit the text and photos. They are also in the block inserter under Patterns > Forma 3D pages.

= Does it work without the Forma 3D theme? =

The product fields and .glb uploads do. The homepage blocks, the campaign page template and the starter site homepages need the Forma 3D theme.

== External services ==

The Lighting and Home Decor starter sites are not included in the plugin. When you choose one of them in Appearance > Forma Setup and click Import, the plugin downloads that starter site's files from GitHub (raw.githubusercontent.com), from the public repository https://github.com/Rubyat13/Forma/tree/starter-sites: a demo.json file with the product and page data, the 3D models (.glb) and the product photos (.webp).

Nothing is downloaded until you click Import, and no information about your site, your store or your users is sent. As with any web request, GitHub receives your server's IP address and the standard request headers. GitHub's terms of service: https://docs.github.com/site-policy/github-terms/github-terms-of-service. GitHub's privacy statement: https://docs.github.com/site-policy/privacy-policies/github-general-privacy-statement.

The Furniture starter site is included in the plugin and needs no download.

== Changelog ==

= 1.1.0 =
* New: Lighting and Home Decor starter sites in Forma Setup, next to Furniture. Pick one and import it in one click.
* New: switching starter sites moves the previous starter site's products to the Trash and gives the Home page the new design (the old version stays in its revisions).
* New: six page templates for new pages: About us, Contact, Lookbook, FAQ, Shop the room and a 3D campaign landing page.
* Imported product data is sanitised, and starter site homepages keep only Forma blocks.

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

Furniture starter site 3D models: Poly Haven (CC0, https://polyhaven.com) and Khronos glTF Sample Assets (https://github.com/KhronosGroup/glTF-Sample-Assets): SheenChair (CC0, Wayfair), GlamVelvetSofa (CC BY 4.0, Wayfair / Eric Chadwick), SheenWoodLeatherSofa (CC BY 4.0, Eric Chadwick / Darmstadt Graphics Group).

Lighting and Home Decor starter site 3D models: Poly Haven (CC0, https://polyhaven.com).

Demo product photos and the starter site previews are renders of these models and use the same licences.
