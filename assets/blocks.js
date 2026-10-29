/**
 * Forma 3D blocks — editor UI.
 *
 * Every block is rendered on the server (inc/sections.php). In the editor each
 * one shows an on-brand preview card whose text is edited in place; product
 * pickers and counts live in the block sidebar.
 */
(function (wp) {
	'use strict';

	const { registerBlockType } = wp.blocks;
	const { createElement: h, Fragment } = wp.element;
	const { RichText, InspectorControls, useBlockProps } = wp.blockEditor;
	const { PanelBody, SelectControl, RangeControl, TextControl, CheckboxControl, Button, Notice } = wp.components;
	const { __ } = wp.i18n;
	const DATA = window.FORMA_BLOCKS || { products: [], categories: [], shopUrl: '' };
	const FORMATS = ['core/italic', 'core/bold'];
	const ICONS = ['cube', 'truck', 'return', 'shield', 'ruler', 'ar', 'bag', 'check'];

	const products3d = () => DATA.products.filter((p) => p.has3d);
	const productBySlug = (slug) => DATA.products.find((p) => p.slug === slug);
	const firstFeatured = (skip) => products3d().find((p) => p.featured && p.slug !== skip) || products3d()[0];

	/* ---------- small building blocks ---------- */

	function Card(props, label, threeD, children, className) {
		const blockProps = useBlockProps({ className: 'forma-ed ' + (className || '') });
		return h('div', blockProps,
			h('div', { className: 'forma-ed__head' },
				h('span', { className: 'forma-ed__tag' }, label),
				threeD ? h('span', { className: 'forma-ed__3d' }, '3D') : null,
				h('span', { className: 'forma-ed__hint' }, __('Click any text to edit it', 'forma-3d-toolkit'))
			),
			children
		);
	}

	function Rich(props, key, tagName, className, placeholder, plain) {
		return h(RichText, {
			tagName,
			className,
			value: props.attributes[key],
			allowedFormats: plain ? [] : FORMATS,
			placeholder: placeholder || '',
			onChange: (v) => props.setAttributes({ [key]: v }),
		});
	}

	// RichText bound to attributes[key][index][field].
	function RichItem(props, key, index, field, tagName, className, placeholder) {
		const list = props.attributes[key] || [];
		return h(RichText, {
			tagName,
			className,
			value: (list[index] || {})[field] || '',
			allowedFormats: [],
			placeholder: placeholder || '',
			onChange: (v) => {
				const next = list.map((item, i) => (i === index ? { ...item, [field]: v } : item));
				props.setAttributes({ [key]: next });
			},
		});
	}

	function ProductPicker(props, key, label) {
		const opts = [{ label: __('Automatic (first featured 3D product)', 'forma-3d-toolkit'), value: '' }]
			.concat(products3d().map((p) => ({ label: p.name, value: p.slug })));
		return h(SelectControl, {
			label,
			value: props.attributes[key],
			options: opts,
			onChange: (v) => props.setAttributes({ [key]: v }),
			help: __('Only products with a 3D model are listed.', 'forma-3d-toolkit'),
		});
	}

	function Thumb(p, className) {
		if (!p) return h('div', { className: 'forma-ed__thumb is-empty ' + (className || '') }, __('No 3D product yet', 'forma-3d-toolkit'));
		return h('div', { className: 'forma-ed__thumb ' + (className || ''), style: { background: p.bg || '#e6e0d6' } },
			p.thumb ? h('img', { src: p.thumb, alt: '' }) : null,
			h('span', null, p.name)
		);
	}

	/* ---------- blocks ---------- */

	const blocks = {
		hero(props) {
			const a = props.attributes;
			const p = productBySlug(a.product) || firstFeatured();
			return h(Fragment, null,
				h(InspectorControls, null,
					h(PanelBody, { title: __('3D product', 'forma-3d-toolkit') }, ProductPicker(props, 'product', __('Product that turns in the hero', 'forma-3d-toolkit'))),
					h(PanelBody, { title: __('Button', 'forma-3d-toolkit') },
						h(TextControl, { label: __('Button link', 'forma-3d-toolkit'), value: a.buttonUrl, placeholder: DATA.shopUrl, help: __('Leave empty to link to the shop.', 'forma-3d-toolkit'), onChange: (v) => props.setAttributes({ buttonUrl: v }) })
					)
				),
				Card(props, __('Hero', 'forma-3d-toolkit'), true, h('div', { className: 'forma-ed__hero' },
					h('div', { className: 'forma-ed__hero-text' },
						Rich(props, 'eyebrow', 'p', 'forma-ed__eyebrow'),
						h('div', { className: 'forma-ed__display forma-ed__display--xl' },
							Rich(props, 'line1', 'div', 'forma-ed__line'),
							Rich(props, 'line2', 'div', 'forma-ed__line forma-ed__line--indent'),
							Rich(props, 'line3', 'div', 'forma-ed__line')
						),
						Rich(props, 'note', 'p', 'forma-ed__note'),
						h('div', { className: 'forma-ed__btn' }, Rich(props, 'buttonText', 'span', '', __('Button text', 'forma-3d-toolkit'), true), ' →')
					),
					Thumb(p, 'forma-ed__thumb--hero')
				), 'forma-ed--light')
			);
		},

		statement(props) {
			const facts = props.attributes.facts || [];
			return Card(props, __('Statement', 'forma-3d-toolkit'), false, h('div', { className: 'forma-ed__statement' },
				Rich(props, 'eyebrow', 'p', 'forma-ed__eyebrow'),
				Rich(props, 'text', 'p', 'forma-ed__serif-text', '', true),
				h('div', { className: 'forma-ed__facts' }, facts.map((f, i) => h('div', { key: i },
					RichItem(props, 'facts', i, 'value', 'strong', 'forma-ed__fact-value'),
					RichItem(props, 'facts', i, 'label', 'span', 'forma-ed__fact-label')
				))),
				h('p', { className: 'forma-ed__small' }, __('Tip: a value that starts with a number (e.g. 360°) counts up on scroll.', 'forma-3d-toolkit'))
			), 'forma-ed--light');
		},

		showcase(props) {
			const a = props.attributes;
			const chosen = (a.products || []).map(productBySlug).filter(Boolean);
			const auto = !chosen.length;
			const list = auto ? products3d().filter((p) => p.featured).slice(0, a.count) : chosen;
			const toggle = (slug, on) => {
				const cur = a.products || [];
				props.setAttributes({ products: on ? cur.concat(slug).slice(0, 6) : cur.filter((s) => s !== slug) });
			};
			return h(Fragment, null,
				h(InspectorControls, null,
					h(PanelBody, { title: __('Products', 'forma-3d-toolkit') },
						h('p', null, auto
							? __('Showing your featured products (star them in Products). Or pick products below.', 'forma-3d-toolkit')
							: __('Showing the products you picked, in the order you ticked them.', 'forma-3d-toolkit')),
						auto ? h(RangeControl, { label: __('How many featured products', 'forma-3d-toolkit'), min: 1, max: 6, value: a.count, onChange: (v) => props.setAttributes({ count: v }) }) : null,
						products3d().map((p) => h(CheckboxControl, {
							key: p.slug, label: p.name,
							checked: (a.products || []).includes(p.slug),
							onChange: (on) => toggle(p.slug, on),
						}))
					)
				),
				Card(props, __('3D Showcase', 'forma-3d-toolkit'), true, h('div', null,
					Rich(props, 'eyebrow', 'p', 'forma-ed__eyebrow'),
					h('p', { className: 'forma-ed__small' }, __('Pinned while visitors scroll: each product rotates in, its text slides in, and its colour swatches work live.', 'forma-3d-toolkit')),
					list.length
						? h('div', { className: 'forma-ed__strip' }, list.map((p, i) => h('div', { key: p.slug, className: 'forma-ed__strip-item' }, h('b', null, String(i + 1).padStart(2, '0')), Thumb(p))))
						: h(Notice, { status: 'warning', isDismissible: false }, __('No featured 3D products yet. Star a product in Products, or pick products in the sidebar.', 'forma-3d-toolkit')),
					h('div', { className: 'forma-ed__btn forma-ed__btn--ghost' }, Rich(props, 'buttonText', 'span', '', __('Button text', 'forma-3d-toolkit'), true), ' →')
				), 'forma-ed--light')
			);
		},

		rooms(props) {
			return Card(props, __('Shop by room', 'forma-3d-toolkit'), false, h('div', { className: 'forma-ed__rooms' },
				h('div', { className: 'forma-ed__rooms-intro' },
					Rich(props, 'eyebrow', 'p', 'forma-ed__eyebrow'),
					Rich(props, 'title', 'h2', 'forma-ed__display'),
					h('div', { className: 'forma-ed__link' }, Rich(props, 'linkText', 'span', '', '', true), ' →')
				),
				h('div', { className: 'forma-ed__rooms-track' },
					DATA.categories.map((c, i) => h('div', { key: i, className: 'forma-ed__room', style: { background: c.bg || '#e3ddd3' } },
						c.thumb ? h('img', { src: c.thumb, alt: '' }) : null, h('span', null, c.name))),
					h('div', { className: 'forma-ed__room-end' }, Rich(props, 'endText', 'p', '', '', true))
				),
				h('p', { className: 'forma-ed__small' }, __('Cards come from your product categories (with their thumbnails).', 'forma-3d-toolkit'))
			), 'forma-ed--dark');
		},

		anatomy(props) {
			const a = props.attributes;
			const p = productBySlug(a.product) || firstFeatured();
			const callouts = a.callouts || [];
			return h(Fragment, null,
				h(InspectorControls, null,
					h(PanelBody, { title: __('3D product', 'forma-3d-toolkit') }, ProductPicker(props, 'product', __('Product to explain', 'forma-3d-toolkit')))
				),
				Card(props, __('Anatomy', 'forma-3d-toolkit'), true, h('div', { className: 'forma-ed__anatomy' },
					h('div', null,
						Rich(props, 'eyebrow', 'p', 'forma-ed__eyebrow'),
						Rich(props, 'title', 'h2', 'forma-ed__display'),
						h('div', { className: 'forma-ed__btn forma-ed__btn--ghost' }, Rich(props, 'buttonText', 'span', '', '', true), ' →')
					),
					Thumb(p, 'forma-ed__thumb--anatomy'),
					h('ol', { className: 'forma-ed__callouts' }, callouts.map((c, i) => h('li', { key: i },
						RichItem(props, 'callouts', i, 'title', 'strong', ''),
						RichItem(props, 'callouts', i, 'text', 'span', '')
					)))
				), 'forma-ed--light')
			);
		},

		marquee(props) {
			const items = props.attributes.items || [];
			const set = (next) => props.setAttributes({ items: next });
			return Card(props, __('Marquee', 'forma-3d-toolkit'), false, h('div', null,
				h('div', { className: 'forma-ed__marquee' }, items.map((t, i) => h('span', { key: i, className: i % 2 ? 'is-em' : '' },
					h(RichText, { tagName: 'span', value: t, allowedFormats: [], onChange: (v) => set(items.map((x, k) => (k === i ? v : x))) }),
					h(Button, { icon: 'no-alt', label: __('Remove', 'forma-3d-toolkit'), isSmall: true, onClick: () => set(items.filter((_, k) => k !== i)) })
				))),
				h(Button, { variant: 'secondary', icon: 'plus', onClick: () => set(items.concat(__('New phrase', 'forma-3d-toolkit'))) }, __('Add phrase', 'forma-3d-toolkit'))
			), 'forma-ed--light');
		},

		arrivals(props) {
			const a = props.attributes;
			const list = DATA.products.slice(0, Math.min(a.count, 8));
			return h(Fragment, null,
				h(InspectorControls, null,
					h(PanelBody, { title: __('Products', 'forma-3d-toolkit') },
						h(SelectControl, {
							label: __('Show', 'forma-3d-toolkit'), value: a.source,
							options: [
								{ label: __('Newest products', 'forma-3d-toolkit'), value: 'latest' },
								{ label: __('Featured products', 'forma-3d-toolkit'), value: 'featured' },
								{ label: __('Products on sale', 'forma-3d-toolkit'), value: 'sale' },
							],
							onChange: (v) => props.setAttributes({ source: v }),
						}),
						h(RangeControl, { label: __('Number of products', 'forma-3d-toolkit'), min: 1, max: 12, value: a.count, onChange: (v) => props.setAttributes({ count: v }) })
					)
				),
				Card(props, __('Product grid', 'forma-3d-toolkit'), false, h('div', null,
					h('div', { className: 'forma-ed__section-head' },
						h('div', null, Rich(props, 'eyebrow', 'p', 'forma-ed__eyebrow'), Rich(props, 'title', 'h2', 'forma-ed__display')),
						h('div', { className: 'forma-ed__link' }, Rich(props, 'linkText', 'span', '', '', true), ' →')
					),
					h('div', { className: 'forma-ed__grid' }, list.map((p) => h('div', { key: p.slug }, Thumb(p))))
				), 'forma-ed--light')
			);
		},

		promise(props) {
			const items = props.attributes.items || [];
			const setIcon = (i, v) => props.setAttributes({ items: items.map((it, k) => (k === i ? { ...it, icon: v } : it)) });
			return h(Fragment, null,
				h(InspectorControls, null,
					h(PanelBody, { title: __('Icons', 'forma-3d-toolkit') }, items.map((it, i) => h(SelectControl, {
						key: i, label: __('Item', 'forma-3d-toolkit') + ' ' + (i + 1), value: it.icon,
						options: ICONS.map((x) => ({ label: x, value: x })),
						onChange: (v) => setIcon(i, v),
					})))
				),
				Card(props, __('Promise', 'forma-3d-toolkit'), false, h('div', null,
					h('div', { className: 'forma-ed__promise' },
						h('div', null, Rich(props, 'eyebrow', 'p', 'forma-ed__eyebrow'), Rich(props, 'title', 'h2', 'forma-ed__display')),
						h('ul', null, items.map((it, i) => h('li', { key: i },
							h('span', { className: 'forma-ed__icon' }, it.icon),
							h('div', null, RichItem(props, 'items', i, 'title', 'strong', ''), RichItem(props, 'items', i, 'text', 'span', ''))
						)))
					),
					h('div', { className: 'forma-ed__quote' }, Rich(props, 'quote', 'p', 'forma-ed__serif-text', __('Customer quote (optional)', 'forma-3d-toolkit'), true), Rich(props, 'cite', 'cite', '', __('Name — City', 'forma-3d-toolkit'), true))
				), 'forma-ed--paper')
			);
		},

		looks(props) {
			const a = props.attributes;
			const looks = a.looks || [];
			const { MediaUpload, MediaUploadCheck } = wp.blockEditor;
			const setLook = (i, patch) => props.setAttributes({ looks: looks.map((l, k) => (k === i ? { ...l, ...patch } : l)) });
			const move = (i, d) => {
				const next = looks.slice();
				const [it] = next.splice(i, 1);
				next.splice(Math.max(0, Math.min(next.length, i + d)), 0, it);
				props.setAttributes({ looks: next });
			};
			const hasProduct = (l, slug) => (l.products || []).map(String).includes(String(slug));
			const toggleProduct = (i, slug, on) => {
				const cur = (looks[i].products || []).map(String);
				setLook(i, { products: on ? cur.concat(String(slug)) : cur.filter((s) => s !== String(slug)) });
			};
			const lookProducts = (l) => (l.products || []).map((ref) => DATA.products.find((p) => p.slug === ref || String(p.id) === String(ref))).filter(Boolean);

			return h(Fragment, null,
				h(InspectorControls, null,
					looks.map((l, i) => h(PanelBody, { key: i, title: (l.name || __('Look', 'forma-3d-toolkit')) + ' (' + (i + 1) + ')', initialOpen: false },
						h(TextControl, { label: __('Look name', 'forma-3d-toolkit'), value: l.name || '', onChange: (v) => setLook(i, { name: v }) }),
						h(MediaUploadCheck, null, h(MediaUpload, {
							allowedTypes: ['image'],
							value: l.imageId,
							onSelect: (m) => setLook(i, { imageId: m.id, image: (m.sizes && m.sizes.large ? m.sizes.large.url : m.url) }),
							render: ({ open }) => h('div', { className: 'forma-ed__look-media' },
								l.image ? h('img', { src: l.image, alt: '' }) : null,
								h(Button, { variant: 'secondary', onClick: open }, l.image ? __('Replace photo', 'forma-3d-toolkit') : __('Choose photo', 'forma-3d-toolkit'))
							),
						})),
						h('p', { className: 'forma-ed__small' }, __('Use a full-body photo with a transparent background (PNG or WebP).', 'forma-3d-toolkit')),
						h('p', { className: 'components-base-control__label' }, __('Pieces in this look', 'forma-3d-toolkit')),
						h('div', { className: 'forma-ed__checklist' }, DATA.products.map((p) => h(CheckboxControl, {
							key: p.slug, label: p.name, checked: hasProduct(l, p.slug),
							onChange: (on) => toggleProduct(i, p.slug, on),
						}))),
						h('div', { className: 'forma-ed__row' },
							h(Button, { variant: 'tertiary', disabled: i === 0, onClick: () => move(i, -1) }, __('Move up', 'forma-3d-toolkit')),
							h(Button, { variant: 'tertiary', disabled: i === looks.length - 1, onClick: () => move(i, 1) }, __('Move down', 'forma-3d-toolkit')),
							h(Button, { variant: 'tertiary', isDestructive: true, onClick: () => props.setAttributes({ looks: looks.filter((x, k) => k !== i) }) }, __('Remove', 'forma-3d-toolkit'))
						)
					)),
					h(PanelBody, { title: __('Looks', 'forma-3d-toolkit') },
						h(Button, { variant: 'primary', onClick: () => props.setAttributes({ looks: looks.concat({ name: __('New look', 'forma-3d-toolkit'), image: '', imageId: 0, products: [] }) }) }, __('Add a look', 'forma-3d-toolkit'))
					),
					h(PanelBody, { title: __('Complete the look', 'forma-3d-toolkit') },
						h(RangeControl, { label: __('Bundle discount (%)', 'forma-3d-toolkit'), min: 0, max: 50, value: a.discount, onChange: (v) => props.setAttributes({ discount: v }), help: __('Applied in the cart when every piece of a look was added with the bundle button. 0 turns the discount off.', 'forma-3d-toolkit') }),
						h(TextControl, { label: __('Button text', 'forma-3d-toolkit'), value: a.buttonText, onChange: (v) => props.setAttributes({ buttonText: v }) })
					)
				),
				Card(props, __('Try the Look', 'forma-3d-toolkit'), false, h('div', null,
					Rich(props, 'eyebrow', 'p', 'forma-ed__eyebrow'),
					Rich(props, 'title', 'h2', 'forma-ed__display'),
					Rich(props, 'intro', 'p', 'forma-ed__note', '', true),
					looks.length
						? h('div', { className: 'forma-ed__looks' }, looks.map((l, i) => h('div', { key: i, className: 'forma-ed__look' },
							l.image ? h('img', { src: l.image, alt: '' }) : h('div', { className: 'forma-ed__look-empty' }, __('No photo', 'forma-3d-toolkit')),
							h('strong', null, l.name || __('Look', 'forma-3d-toolkit') + ' ' + (i + 1)),
							h('span', null, lookProducts(l).map((p) => p.name).join(' · ') || __('No pieces yet', 'forma-3d-toolkit'))
						)))
						: h(Notice, { status: 'info', isDismissible: false }, __('Add looks in the block settings: a photo, a name and the pieces in it.', 'forma-3d-toolkit')),
					h('p', { className: 'forma-ed__small' }, a.discount
						? __('Bundle discount', 'forma-3d-toolkit') + ': ' + a.discount + '%'
						: __('No bundle discount', 'forma-3d-toolkit'))
				), 'forma-ed--light')
			);
		},
	};

	Object.keys(blocks).forEach((slug) => {
		registerBlockType('forma/' + slug, { edit: blocks[slug], save: () => null });
	});
})(window.wp);
