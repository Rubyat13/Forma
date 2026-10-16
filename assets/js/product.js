/**
 * Forma — product page 3D viewer (model-viewer) and purchase interactions.
 */
(function () {
	'use strict';

	const $ = (s, c = document) => c.querySelector(s);
	const $$ = (s, c = document) => Array.from(c.querySelectorAll(s));
	const CFG = window.forma3dSettings || {};
	const viewer = $('[data-viewer]');
	const mv = $('#pdp-viewer');
	const pluginWrap = $('[data-plugin-viewer]');
	const form = $('[data-pdp-form]');
	const finishSrc = mv || pluginWrap;
	const finishes = finishSrc ? JSON.parse(finishSrc.dataset.finishes || '[]') : [];
	let currentFinish = finishes[0] || null;
	// The <model-viewer> finishes apply to: ours, or the one the 3D Viewer plugin renders.
	let activeMv = mv;

	/* ------------------------------------------------------------------
	 * Viewer rendered by the 3D Viewer plugin
	 * ---------------------------------------------------------------- */
	if (pluginWrap) {
		const hint = $('[data-viewer-hint]');
		const imgWrap = $('[data-viewer-image]', viewer);
		const img = imgWrap ? $('img', imgWrap) : null;

		const bind = (el) => {
			activeMv = el;
			const apply = () => { if (currentFinish) window.forma3d.applyFinish(el, currentFinish); };
			el.addEventListener('load', apply);
			customElements.whenDefined('model-viewer').then(() => { if (el.loaded) apply(); });
			el.addEventListener('camera-change', (e) => {
				if (e.detail.source === 'user-interaction' && hint) hint.classList.add('is-hidden');
			});
		};
		// The plugin mounts its viewer client-side, so wait for the element to appear.
		const found = pluginWrap.querySelector('model-viewer');
		if (found) {
			bind(found);
		} else {
			const mo = new MutationObserver(() => {
				const el = pluginWrap.querySelector('model-viewer');
				if (el) {
					mo.disconnect();
					bind(el);
				}
			});
			mo.observe(pluginWrap, { childList: true, subtree: true });
		}
		pluginWrap.addEventListener('pointerdown', () => hint && hint.classList.add('is-hidden'), { once: true });

		$$('[data-thumb]').forEach((t) => t.addEventListener('click', () => {
			$$('[data-thumb]').forEach((x) => x.classList.toggle('is-active', x === t));
			if (t.dataset.thumb === '3d') {
				if (imgWrap) imgWrap.hidden = true;
				return;
			}
			if (img) {
				img.removeAttribute('srcset');
				img.src = t.dataset.full;
			}
			if (imgWrap) imgWrap.hidden = false;
		}));
	}

	/* ------------------------------------------------------------------
	 * Viewer
	 * ---------------------------------------------------------------- */
	if (mv) {
		const bar = $('[data-viewer-progress]');
		const hint = $('[data-viewer-hint]');
		const initialOrbit = mv.getAttribute('camera-orbit');

		mv.addEventListener('progress', (e) => {
			const p = e.detail.totalProgress;
			if (bar) {
				bar.style.transform = `scaleX(${p})`;
				if (p >= 1) bar.parentElement.classList.add('is-done');
			}
		});

		mv.addEventListener('camera-change', (e) => {
			if (e.detail.source === 'user-interaction' && hint) hint.classList.add('is-hidden');
			if (mv.classList.contains('show-dims')) drawDims();
		});

		const onLoaded = () => {
			setupDims();
			if (currentFinish) window.forma3d.applyFinish(mv, currentFinish);
		};
		mv.addEventListener('load', onLoaded);
		customElements.whenDefined('model-viewer').then(() => { if (mv.loaded) onLoaded(); });

		/* Tools */
		const tool = (name) => $(`[data-tool="${name}"]`, viewer);

		tool('rotate')?.addEventListener('click', (e) => {
			const on = !mv.autoRotate;
			mv.autoRotate = on;
			e.currentTarget.setAttribute('aria-pressed', String(on));
		});

		tool('dims')?.addEventListener('click', (e) => {
			const on = !mv.classList.contains('show-dims');
			mv.classList.toggle('show-dims', on);
			e.currentTarget.setAttribute('aria-pressed', String(on));
			if (on) {
				showModel();
				drawDims();
			}
		});

		tool('reset')?.addEventListener('click', () => {
			mv.cameraOrbit = initialOrbit;
			mv.cameraTarget = 'auto auto auto';
			mv.fieldOfView = 'auto';
		});

		tool('full')?.addEventListener('click', () => {
			if (document.fullscreenElement) document.exitFullscreen();
			else viewer.requestFullscreen?.();
		});

		tool('ar')?.addEventListener('click', () => {
			if (mv.canActivateAR) mv.activateAR();
			else window.forma3d.toast(CFG.i18n?.noAr || 'Open on a phone to use AR.');
		});

		/* Dimensions overlay — hotspots placed on the bounding box, lines drawn in SVG. */
		const dimLines = $$('.dim-line', mv);
		const unitFmt = (m) => `${Math.round(m * 100)} cm`;

		function setupDims() {
			const c = mv.getBoundingBoxCenter();
			const s = mv.getDimensions();
			const x2 = s.x / 2;
			const y2 = s.y / 2;
			const z2 = s.z / 2;
			const pos = (x, y, z) => `${x} ${y} ${z}`;
			const set = (name, p) => mv.updateHotspot({ name, position: p });

			set('hotspot-dot+X-Y+Z', pos(c.x + x2, c.y - y2, c.z + z2));
			set('hotspot-dim+X-Y', pos(c.x + x2 * 1.2, c.y - y2 * 1.1, c.z));
			set('hotspot-dot+X-Y-Z', pos(c.x + x2, c.y - y2, c.z - z2));
			set('hotspot-dim+X-Z', pos(c.x + x2 * 1.2, c.y, c.z - z2 * 1.2));
			set('hotspot-dot+X+Y-Z', pos(c.x + x2, c.y + y2, c.z - z2));
			set('hotspot-dim+Y-Z', pos(c.x, c.y + y2 * 1.1, c.z - z2 * 1.2));
			set('hotspot-dot-X+Y-Z', pos(c.x - x2, c.y + y2, c.z - z2));
			set('hotspot-dim-X-Z', pos(c.x - x2 * 1.2, c.y, c.z - z2 * 1.2));
			set('hotspot-dot-X-Y-Z', pos(c.x - x2, c.y - y2, c.z - z2));
			set('hotspot-dim-X-Y', pos(c.x - x2 * 1.2, c.y - y2 * 1.1, c.z));
			set('hotspot-dot-X-Y+Z', pos(c.x - x2, c.y - y2, c.z + z2));

			const label = (slot, text) => { const el = mv.querySelector(`[slot="${slot}"]`); if (el) el.textContent = text; };
			label('hotspot-dim+X-Y', unitFmt(s.z));
			label('hotspot-dim-X-Y', unitFmt(s.z));
			label('hotspot-dim+X-Z', unitFmt(s.y));
			label('hotspot-dim-X-Z', unitFmt(s.y));
			label('hotspot-dim+Y-Z', unitFmt(s.x));
		}

		function drawLine(line, a, b, dim) {
			const ha = mv.queryHotspot(a);
			const hb = mv.queryHotspot(b);
			if (!ha || !hb) return;
			line.setAttribute('x1', ha.canvasPosition.x);
			line.setAttribute('y1', ha.canvasPosition.y);
			line.setAttribute('x2', hb.canvasPosition.x);
			line.setAttribute('y2', hb.canvasPosition.y);
			// Hide a line whose label faces away from the camera.
			const hd = dim ? mv.queryHotspot(dim) : null;
			line.classList.toggle('is-hidden', !!hd && !hd.facingCamera);
		}

		function drawDims() {
			if (dimLines.length < 5) return;
			drawLine(dimLines[0], 'hotspot-dot+X-Y-Z', 'hotspot-dot+X-Y+Z', 'hotspot-dim+X-Y');
			drawLine(dimLines[1], 'hotspot-dot+X-Y-Z', 'hotspot-dot+X+Y-Z', 'hotspot-dim+X-Z');
			drawLine(dimLines[2], 'hotspot-dot+X+Y-Z', 'hotspot-dot-X+Y-Z');
			drawLine(dimLines[3], 'hotspot-dot-X+Y-Z', 'hotspot-dot-X-Y-Z', 'hotspot-dim-X-Z');
			drawLine(dimLines[4], 'hotspot-dot-X-Y-Z', 'hotspot-dot-X-Y+Z', 'hotspot-dim-X-Y');
		}

		/* Thumbnails: 3D vs still images */
		const imgWrap = $('[data-viewer-image]', viewer);
		const img = imgWrap ? $('img', imgWrap) : null;
		function showModel() {
			if (imgWrap) imgWrap.hidden = true;
			$$('[data-thumb]').forEach((t) => t.classList.toggle('is-active', t.dataset.thumb === '3d'));
		}
		$$('[data-thumb]').forEach((t) => t.addEventListener('click', () => {
			$$('[data-thumb]').forEach((x) => x.classList.toggle('is-active', x === t));
			if (t.dataset.thumb === '3d') {
				showModel();
				return;
			}
			if (img) {
				img.removeAttribute('srcset');
				img.src = t.dataset.full;
			}
			if (imgWrap) imgWrap.hidden = false;
		}));
	} else if (!pluginWrap) {
		// No model: thumbnails just swap the still image.
		const imgWrap = $('[data-viewer-image]');
		const img = imgWrap ? $('img', imgWrap) : null;
		$$('[data-thumb="img"]').forEach((t) => t.addEventListener('click', () => {
			$$('[data-thumb]').forEach((x) => x.classList.toggle('is-active', x === t));
			if (img) { img.removeAttribute('srcset'); img.src = t.dataset.full; }
		}));
	}

	/* ------------------------------------------------------------------
	 * Finishes
	 * ---------------------------------------------------------------- */
	const finishWrap = $('[data-pdp-finishes]');
	if (finishWrap) {
		const nameEl = $('[data-finish-name]', finishWrap);
		const input = $('[data-finish-input]');
		$$('[data-finish]', finishWrap).forEach((b) => b.addEventListener('click', () => {
			const f = finishes[Number(b.dataset.finish)];
			if (!f) return;
			currentFinish = f;
			$$('[data-finish]', finishWrap).forEach((x) => {
				x.classList.toggle('is-active', x === b);
				x.setAttribute('aria-pressed', String(x === b));
			});
			if (nameEl) nameEl.textContent = f.name;
			if (input) input.value = f.name;
			if (activeMv) {
				const imgWrap = $('[data-viewer-image]');
				if (imgWrap && !imgWrap.hidden) $('[data-thumb="3d"]')?.click();
				window.forma3d.applyFinish(activeMv, f);
			}
		}));
	}

	/* ------------------------------------------------------------------
	 * Quantity + AJAX add to cart
	 * ---------------------------------------------------------------- */
	if (form) {
		const qty = $('input[name="quantity"]', form);
		$$('[data-qty]', form).forEach((b) => b.addEventListener('click', () => {
			const min = Number(qty.min || 1);
			const max = qty.max ? Number(qty.max) : Infinity;
			qty.value = Math.min(max, Math.max(min, Number(qty.value || 1) + Number(b.dataset.qty)));
		}));

		form.addEventListener('submit', (e) => {
			if (!window.forma3d || !CFG.ajaxUrl) return; // fall back to a normal POST
			e.preventDefault();
			const btn = $('.pdp__add', form);
			const extra = {};
			const fin = $('[data-finish-input]', form);
			if (fin && fin.value) extra.forma_3d_finish = fin.value;
			window.forma3d.addToCart(btn.dataset.productId, Number(qty.value || 1), extra, btn);
		});

		/* Sticky buy bar once the main button scrolls away. */
		const buybar = $('[data-buybar]');
		if (buybar && 'IntersectionObserver' in window) {
			const io = new IntersectionObserver(([entry]) => {
				const past = !entry.isIntersecting && entry.boundingClientRect.top < 0;
				buybar.classList.toggle('is-on', past);
				buybar.setAttribute('aria-hidden', String(!past));
			});
			io.observe(form);
			$('[data-buybar-add]', buybar)?.addEventListener('click', () => form.requestSubmit());
		}
	}
})();
