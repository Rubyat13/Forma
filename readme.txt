=== Forma 3D Toolkit ===
Contributors: rubyat13
Tags: 3d, woocommerce, blocks, demo import, product viewer
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Companion plugin for the Forma 3D theme: one-click 3D starter sites with their own designs, page templates and homepage blocks.

== Description ==

Forma 3D Toolkit adds the store features of the [Forma 3D](https://wordpress.org/themes/forma-3d/) theme:

* Appearance > Forma Setup: four one-click starter sites, each with products, photos, categories, a homepage and its own design (see below).
* Four store designs: Original, Noir (dark, gold, high-contrast serif), Soft (oat and sage, terracotta, rounded serif, arched cards) and Studio (pale grey and black, light sans-serif). Each starter site comes with its own design; change it any time in Appearance > Customize > Forma 3D options.
* "Try the Look" block: a carousel of styled outfits. Shoppers add single pieces or the whole look with a bundle discount that is applied in the cart.
* Six page templates, offered when you create a new page: About us, Contact, Lookbook, FAQ, Shop the room and a 3D campaign landing page.
* Eight homepage blocks for the block editor (Hero, Statement, 3D Showcase, Shop by Room, Anatomy, Marquee, Product Grid, Promise) and the "Forma 3D homepage" pattern.
* A "Forma Display" product tab: finishes (colour swatches that recolour the 3D model), stage colour, materials and designer.
* The chosen finish is saved on the cart line and the order.
* .glb uploads in the media library.

= Starter sites =

* Furniture (Original design): 16 sofas, chairs, tables and storage pieces. Included in the plugin.
* Lighting (Noir design): 14 chandeliers, pendants, table lamps, wall lights and lanterns. Downloaded when you import it.
* Home Decor (Soft design): 14 vases, faux plants, clocks, mirrors and decorative objects. Downloaded when you import it.
* Fashion (Studio design): six styled looks with 19 pieces (jackets, denim, knitwear, shoes and accessories) and a "Try the Look" homepage. Photos only, no 3D models. Downloaded when you import it.

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

= Can I use a design without its starter site? =

Yes. Go to Appearance > Customize > Forma 3D options and choose Original, Noir or Soft under "Store design". Importing a starter site switches to that starter site's design.

= How does the "Complete the look" discount work? =

In the Try the Look block settings, set the bundle discount (10% by default, 0 turns it off). When a shopper clicks "Add full look to bag", every piece of the look is added and tagged with the look. While all pieces of that look are in the cart, the cart shows a "Complete the look" discount line. Removing a piece removes the discount.

= How do I use the page templates? =

Go to Pages > Add New Page. The "Choose a pattern" window lists the Forma 3D page templates; pick one and edit the text and photos. They are also in the block inserter under Patterns > Forma 3D pages.

= Does it work without the Forma 3D theme? =

The product fields and .glb uploads do. The homepage blocks, the campaign page template and the starter site homepages need the Forma 3D theme.

== External services ==

The Lighting, Home Decor and Fashion starter sites are not included in the plugin. When you choose one of them in Appearance > Forma Setup and click Import, the plugin downloads that starter site's files from GitHub (raw.githubusercontent.com), from the public repository https://github.com/Rubyat13/Forma/tree/starter-sites: a demo.json file with the product and page data, the 3D models (.glb) and the product photos (.webp).

Nothing is downloaded until you click Import, and no information about your site, your store or your users is sent. As with any web request, GitHub receives your server's IP address and the standard request headers. GitHub's terms of service: https://docs.github.com/site-policy/github-terms/github-terms-of-service. GitHub's privacy statement: https://docs.github.com/site-policy/privacy-policies/github-general-privacy-statement.

The Furniture starter site is included in the plugin and needs no download.

== Changelog ==

= 1.1.0 =
* New: Lighting and Home Decor starter sites in Forma Setup, next to Furniture. Pick one and import it in one click.
* New: switching starter sites moves the previous starter site's products to the Trash and gives the Home page the new design (the old version stays in its revisions).
* New: Fashion starter site with six styled looks and the new "Try the Look" block (carousel, single pieces and a "Complete the look" bundle discount in the cart).
* New: four store designs (Original, Noir, Soft and Studio) with their own colours, fonts and shapes. Lighting uses Noir, Home Decor uses Soft and Fashion uses Studio; switch in Appearance > Customize.
* Starter sites without 3D models turn off the shop's "Try it in your room" section.
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

Fashion starter site photos: CC0 photos from Unsplash (published under CC0 before 2017) via Wikimedia Commons, by Brooke Cagle, Elijah Henderson, Jakob Owens, freestocks.org, Mohammad Faruque and Pete Bellis. Backgrounds removed and garments cropped for the demo.

Demo product photos and the starter site previews are renders of these models and use the same licences.

Fonts in the Noir and Soft designs, all under the SIL Open Font License 1.1 (licence files in assets/designs/fonts): Bodoni Moda (The Bodoni Moda Project Authors, https://github.com/indestructible-type/Bodoni), Jost (The Jost Project Authors, https://github.com/indestructible-type/Jost), Fraunces (The Fraunces Project Authors, https://github.com/undercasetype/Fraunces) and DM Sans (The DM Sans Project Authors, https://github.com/googlefonts/dm-fonts).
