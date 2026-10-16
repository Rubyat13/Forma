# Forma 3D

A WooCommerce theme for stores that show products as interactive 3D models.

- Scroll-animated 3D homepage (three.js) that turns your featured products as visitors scroll
- 3D quick view in the shop, cart drawer with live updates
- Product pages built for the free [3D Viewer](https://wordpress.org/plugins/3d-viewer/) plugin: rotate, zoom, dimensions ruler and AR
- Block editor ready: `theme.json` colours, fonts and buttons, editor styles, block styles and patterns
- Works without plugins; WooCommerce turns it into a store

**Requirements:** WordPress 6.5+, PHP 7.4+. Tested with WooCommerce 11.2.

**Author:** Al Rubyat ([rubyat13](https://profiles.wordpress.org/rubyat13/)) · **License:** GPLv2 or later. Third-party resources and their licences are listed in `readme.txt`.

## Development

The theme has no build step. When packaging for WordPress.org, leave out `.git`, `.gitignore` and `README.md`:

```sh
cd .. && zip -r forma-3d.zip forma-3d -x "forma-3d/.git/*" "forma-3d/.gitignore" "forma-3d/README.md" "*.DS_Store"
```
