/**
 * Forma template library: a "Forma Templates" button in the editor toolbar
 * that opens a library of pages, homepages and sections. Preview renders the
 * real page on the front end; Import puts the blocks into the page.
 */
(function (wp) {
	'use strict';

	const { createElement: h, Fragment, useState, useMemo, createRoot } = wp.element;
	const { Button, Modal, SearchControl, ButtonGroup } = wp.components;
	const { select, dispatch, subscribe } = wp.data;
	const { __, sprintf } = wp.i18n;
	const LIB = window.FORMA_LIBRARY || { items: [], kits: [], preview: '' };
	const KITS = LIB.kits || [];

	const TYPES = [
		{ id: 'all', label: __('All templates', 'forma-3d-toolkit') },
		{ id: 'page', label: __('Pages', 'forma-3d-toolkit') },
		{ id: 'home', label: __('Homepages', 'forma-3d-toolkit') },
		{ id: 'section', label: __('Sections', 'forma-3d-toolkit') },
	];
	const DEVICES = [
		{ id: 'desktop', label: __('Desktop', 'forma-3d-toolkit'), width: '100%' },
		{ id: 'tablet', label: __('Tablet', 'forma-3d-toolkit'), width: '834px' },
		{ id: 'mobile', label: __('Mobile', 'forma-3d-toolkit'), width: '390px' },
	];

	const Mark = () => h('svg', { width: 20, height: 20, viewBox: '0 0 24 24', 'aria-hidden': true, className: 'forma-library-mark' },
		h('circle', { cx: 12, cy: 12, r: 9, fill: 'none', stroke: 'currentColor', strokeWidth: 1.6 }),
		h('path', { d: 'M12 3a9 9 0 0 1 0 18z', fill: 'currentColor' })
	);

	const inView = (item, view) => {
		if (view.startsWith('kit:')) return item.kit === view.slice(4);
		return view === 'all' || item.type === view;
	};
	const navButton = (id, label, view, setView) => h(Button, {
		key: id, className: 'forma-library__type' + (view === id ? ' is-active' : ''), onClick: () => setView(id),
		'aria-pressed': view === id,
	}, label, h('span', null, LIB.items.filter((it) => inView(it, id)).length));

	const isEmptyPage = () => {
		const blocks = select('core/block-editor').getBlocks();
		if (!blocks.length) return true;
		return blocks.length === 1 && blocks[0].name === 'core/paragraph' && !String(blocks[0].attributes.content || '').trim();
	};

	function doImport(item, mode) {
		const blocks = wp.blocks.parse(item.content);
		const editor = dispatch('core/block-editor');
		if (mode === 'append') {
			editor.insertBlocks(blocks);
		} else {
			editor.resetBlocks(blocks);
		}
		// Name an untitled page after the page template.
		const title = select('core/editor').getEditedPostAttribute('title');
		if (item.type === 'page' && !String(title || '').trim()) {
			dispatch('core/editor').editPost({ title: item.pageTitle || item.title });
		}
		dispatch('core/notices').createSuccessNotice(
			sprintf(/* translators: %s: template name. */ __('“%s” imported.', 'forma-3d-toolkit'), item.title),
			{ type: 'snackbar' }
		);
	}

	// Badge: the kit for kit pages, the type for everything else.
	const badge = (item) => {
		const kit = item.kit && KITS.find((k) => k.id === item.kit);
		return kit ? kit.label : (TYPES.find((t) => t.id === item.type) || {}).label;
	};

	function Card({ item, onPreview, onImport }) {
		return h('div', { className: 'forma-library__card' },
			h('button', { type: 'button', className: 'forma-library__thumb', onClick: () => onPreview(item), 'aria-label': sprintf(/* translators: %s: template name. */ __('Preview %s', 'forma-3d-toolkit'), item.title) },
				h('img', { src: item.thumb, alt: '', loading: 'lazy' }),
				h('span', { className: 'forma-library__badge' }, badge(item))
			),
			h('div', { className: 'forma-library__card-body' },
				h('strong', null, item.title),
				h('p', null, item.desc)
			),
			h('div', { className: 'forma-library__card-actions' },
				h(Button, { variant: 'secondary', onClick: () => onPreview(item) }, __('Preview', 'forma-3d-toolkit')),
				h(Button, { variant: 'primary', onClick: () => onImport(item) }, __('Import', 'forma-3d-toolkit'))
			)
		);
	}

	function Library({ onClose }) {
		// 'all', a type ('page', 'home', 'section') or a kit ('kit:photo').
		const [view, setView] = useState('all');
		const [search, setSearch] = useState('');
		const [preview, setPreview] = useState(null);
		const [device, setDevice] = useState('desktop');
		const [ask, setAsk] = useState(null);

		const items = useMemo(() => {
			const q = search.trim().toLowerCase();
			return LIB.items.filter((it) => inView(it, view) && (!q || (it.title + ' ' + it.desc).toLowerCase().includes(q)));
		}, [view, search]);

		const startImport = (item) => {
			if (isEmptyPage()) {
				doImport(item, 'replace');
				onClose();
			} else {
				setAsk(item);
			}
		};
		const finish = (mode) => {
			doImport(ask, mode);
			setAsk(null);
			onClose();
		};

		const body = preview
			? h('div', { className: 'forma-library__preview' },
				h('div', { className: 'forma-library__bar' },
					h(Button, { variant: 'tertiary', icon: 'arrow-left-alt2', onClick: () => setPreview(null) }, __('All templates', 'forma-3d-toolkit')),
					h('strong', { className: 'forma-library__bar-title' }, preview.title),
					h(ButtonGroup, { className: 'forma-library__devices' }, DEVICES.map((d) => h(Button, {
						key: d.id, variant: device === d.id ? 'primary' : 'secondary', size: 'compact', onClick: () => setDevice(d.id),
					}, d.label))),
					h(Button, { variant: 'primary', onClick: () => startImport(preview) }, __('Import this template', 'forma-3d-toolkit'))
				),
				h('div', { className: 'forma-library__frame-wrap' },
					h('iframe', {
						key: preview.id,
						title: sprintf(/* translators: %s: template name. */ __('Preview of %s', 'forma-3d-toolkit'), preview.title),
						src: LIB.preview.replace('%id%', encodeURIComponent(preview.id)),
						style: { width: (DEVICES.find((d) => d.id === device) || DEVICES[0]).width },
					})
				)
			)
			: h('div', { className: 'forma-library__layout' },
				h('nav', { className: 'forma-library__nav', 'aria-label': __('Template types', 'forma-3d-toolkit') },
					h(SearchControl, { value: search, onChange: setSearch, label: __('Search templates', 'forma-3d-toolkit'), __nextHasNoMarginBottom: true }),
					TYPES.map((t) => navButton(t.id, t.label, view, setView)),
					KITS.length ? h('h3', { className: 'forma-library__nav-title' }, __('Collections', 'forma-3d-toolkit')) : null,
					KITS.map((k) => navButton('kit:' + k.id, k.label, view, setView))
				),
				h('div', { className: 'forma-library__grid' },
					items.length
						? items.map((it) => h(Card, { key: it.id, item: it, onPreview: setPreview, onImport: startImport }))
						: h('p', { className: 'forma-library__empty' }, __('No templates match your search.', 'forma-3d-toolkit'))
				)
			);

		return h(Modal, {
			title: h(Fragment, null, h(Mark), ' ', __('Forma template library', 'forma-3d-toolkit')),
			onRequestClose: onClose,
			className: 'forma-library',
			isFullScreen: true,
		},
		body,
		ask ? h(Modal, {
			title: __('This page already has content', 'forma-3d-toolkit'),
			onRequestClose: () => setAsk(null),
			className: 'forma-library__ask',
			size: 'small',
		},
		h('p', null, sprintf(/* translators: %s: template name. */ __('Replace what is on the page with “%s”, or add the template below it?', 'forma-3d-toolkit'), ask.title)),
		h('div', { className: 'forma-library__ask-actions' },
			h(Button, { variant: 'tertiary', onClick: () => setAsk(null) }, __('Cancel', 'forma-3d-toolkit')),
			h(Button, { variant: 'secondary', onClick: () => finish('append') }, __('Add below', 'forma-3d-toolkit')),
			h(Button, { variant: 'primary', isDestructive: true, onClick: () => finish('replace') }, __('Replace content', 'forma-3d-toolkit'))
		)) : null);
	}

	function App() {
		const [open, setOpen] = useState(false);
		return h(Fragment, null,
			h(Button, { className: 'forma-library-button', onClick: () => setOpen(true) }, h(Mark), h('span', null, __('Forma Templates', 'forma-3d-toolkit'))),
			open ? h(Library, { onClose: () => setOpen(false) }) : null
		);
	}

	// Put the button after the editor's document tools; put it back if the header re-renders.
	let queued = false;
	function mount() {
		queued = false;
		if (document.querySelector('.forma-library-host')) return;
		// Next to the document tools group (inside it, the toolbar keeps re-rendering its own items).
		const anchor = document.querySelector('.editor-header__toolbar .editor-document-tools, .edit-post-header__toolbar .edit-post-header-toolbar');
		if (!anchor || !anchor.parentNode) return;
		const host = document.createElement('div');
		host.className = 'forma-library-host';
		anchor.parentNode.insertBefore(host, anchor.nextSibling);
		createRoot(host).render(h(App));
	}
	const queue = () => {
		if (!queued) {
			queued = true;
			window.requestAnimationFrame(mount);
		}
	};
	wp.domReady(() => {
		mount();
		subscribe(queue);
		new window.MutationObserver(queue).observe(document.body, { childList: true, subtree: true });
	});
})(window.wp);
