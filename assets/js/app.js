/**
 * Forma 3D — global interactions.
 *
 * A small animation loop (no animation library) drives smooth scrolling
 * (Lenis), scroll-linked effects and tweens. Also: header, reveals, cart
 * drawer, AJAX add to cart and the 3D quick view.
 */
(function () {
	'use strict';

	const CFG = window.forma3dSettings || {};
	const I18N = CFG.i18n || {};
	const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	const $ = (s, c = document) => c.querySelector(s);
	const $$ = (s, c = document) => Array.from(c.querySelectorAll(s));

	document.documentElement.classList.add('js');

	/* ------------------------------------------------------------------
	 * Animation engine: frame loop, easing, tweens.
	 * ---------------------------------------------------------------- */
	const clamp = (v, a = 0, b = 1) => Math.min(b, Math.max(a, v));
	const lerp = (a, b, t) => a + (b - a) * t;
	const ease = {
		linear: (t) => t,
		power1In: (t) => t * t,
		power1Out: (t) => 1 - (1 - t) * (1 - t),
		power2In: (t) => t * t * t,
		power2Out: (t) => 1 - Math.pow(1 - t, 3),
		power3Out: (t) => 1 - Math.pow(1 - t, 4),
		expoOut: (t) => (t >= 1 ? 1 : 1 - Math.pow(2, -10 * t)),
		expoInOut: (t) => (t <= 0 ? 0 : t >= 1 ? 1 : t < 0.5 ? Math.pow(2, 20 * t - 10) / 2 : (2 - Math.pow(2, -20 * t + 10)) / 2),
		elasticOut: (t) => (t <= 0 ? 0 : t >= 1 ? 1 : Math.pow(2, -10 * t) * Math.sin((t * 10 - 0.75) * ((2 * Math.PI) / 3)) + 1),
	};

	let lenis = null;
	if (!reduced && window.Lenis) {
		lenis = new window.Lenis({ lerp: 0.1, wheelMultiplier: 1, smoothWheel: true });
	}

	const frameFns = new Set();
	const tweens = [];
	let lastTime = performance.now();

	function loop(now) {
		const dt = Math.min((now - lastTime) / 1000, 0.1);
		lastTime = now;
		if (lenis) lenis.raf(now);
		for (let i = tweens.length - 1; i >= 0; i--) {
			const tw = tweens[i];
			if (now < tw.start) continue;
			if (!tw.from) {
				tw.from = {};
				Object.keys(tw.to).forEach((k) => { tw.from[k] = tw.obj[k]; });
			}
			const p = tw.dur > 0 ? clamp((now - tw.start) / tw.dur) : 1;
			const k = tw.ease(p);
			Object.keys(tw.to).forEach((key) => { tw.obj[key] = lerp(tw.from[key], tw.to[key], k); });
			if (tw.onUpdate) tw.onUpdate(tw.obj);
			if (p >= 1) {
				tweens.splice(i, 1);
				if (tw.onComplete) tw.onComplete();
			}
		}
		frameFns.forEach((fn) => fn(now / 1000, dt));
		requestAnimationFrame(loop);
	}
	requestAnimationFrame(loop);

	/** Run fn(time, dt) every frame; returns an unsubscribe function. */
	function onFrame(fn) {
		frameFns.add(fn);
		return () => frameFns.delete(fn);
	}

	/** Animate numeric properties of obj to the values in `to`. */
	function tween(obj, to, opts = {}) {
		for (let i = tweens.length - 1; i >= 0; i--) {
			if (tweens[i].obj === obj && Object.keys(to).some((k) => k in tweens[i].to)) tweens.splice(i, 1);
		}
		const tw = {
			obj,
			to,
			from: null,
			dur: (opts.duration ?? 1) * 1000,
			ease: typeof opts.ease === 'function' ? opts.ease : ease[opts.ease || 'power2Out'],
			start: performance.now() + (opts.delay || 0) * 1000,
			onUpdate: opts.onUpdate,
			onComplete: opts.onComplete,
		};
		tweens.push(tw);
		return tw;
	}

	/** 0..1 progress of the scroll position between two page offsets. */
	const progress = (start, end) => (end === start ? (window.scrollY >= end ? 1 : 0) : clamp((window.scrollY - start) / (end - start)));
	const pageTop = (el) => el.getBoundingClientRect().top + window.scrollY;

	function scrollTo(y, duration = 1.2) {
		if (lenis) lenis.scrollTo(y, { duration, easing: ease.expoInOut });
		else window.scrollTo({ top: y, behavior: reduced ? 'auto' : 'smooth' });
	}

	const lockScroll = (on) => {
		if (lenis) (on ? lenis.stop() : lenis.start());
		document.documentElement.style.overflow = on ? 'hidden' : '';
	};

	/* ------------------------------------------------------------------
	 * Header: solid after scrolling, hides on the way down.
	 * ---------------------------------------------------------------- */
	const header = $('[data-header]');
	if (header) {
		let last = 0;
		const onScroll = () => {
			const y = window.scrollY;
			header.classList.toggle('is-scrolled', y > 30);
			const hide = y > 400 && y > last && !document.body.classList.contains('menu-open');
			header.classList.toggle('is-hidden', hide);
			document.body.classList.toggle('header-hidden', hide);
			last = y;
		};
		window.addEventListener('scroll', onScroll, { passive: true });
		onScroll();
	}

	/* Mobile menu */
	const menuBtn = $('[data-menu-toggle]');
	const menu = $('[data-mobile-menu]');
	if (menuBtn && menu) {
		$$('li', menu).forEach((li, i) => li.style.setProperty('--i', i));
		menuBtn.addEventListener('click', () => {
			const open = menuBtn.getAttribute('aria-expanded') !== 'true';
			menuBtn.setAttribute('aria-expanded', String(open));
			menu.hidden = !open;
			document.body.classList.toggle('menu-open', open);
			lockScroll(open);
			if (open) requestAnimationFrame(() => menu.classList.add('is-open'));
			else menu.classList.remove('is-open');
		});
	}

	/* ------------------------------------------------------------------
	 * Split helpers.
	 * ---------------------------------------------------------------- */
	// Wrap each visual line in a mask so it can slide up from below.
	function splitLines(el) {
		if (el.dataset.splitDone) return $$('.split-line > span', el);
		const chunks = el.innerHTML.split(/<br\s*\/?>/i);
		el.innerHTML = chunks.map((c) => `<span class="split-chunk">${c.trim().split(/\s+/).map((w) => `<span class="split-w">${w}</span>`).join(' ')}</span>`).join('<br>');
		const lines = [];
		$$('.split-chunk', el).forEach((chunk) => {
			let top = null;
			let cur = [];
			$$('.split-w', chunk).forEach((w) => {
				const t = w.offsetTop;
				if (top !== null && Math.abs(t - top) > 4) { lines.push(cur); cur = []; }
				top = t;
				cur.push(w.innerHTML);
			});
			if (cur.length) lines.push(cur);
		});
		el.innerHTML = lines.map((l, i) => `<span class="split-line"><span style="--i:${i}">${l.join(' ')}</span></span>`).join('');
		el.dataset.splitDone = '1';
		return $$('.split-line > span', el);
	}

	function splitWords(el) {
		if (el.dataset.wordsDone) return $$('.w', el);
		el.innerHTML = el.textContent.trim().split(/\s+/).map((w) => `<span class="w">${w}</span>`).join(' ');
		el.dataset.wordsDone = '1';
		return $$('.w', el);
	}

	/** Add .is-in once an element enters the viewport. */
	function whenVisible(els, margin, cb) {
		if (!('IntersectionObserver' in window)) { els.forEach(cb); return; }
		const io = new IntersectionObserver((entries) => {
			entries.forEach((entry) => {
				if (!entry.isIntersecting) return;
				io.unobserve(entry.target);
				cb(entry.target);
			});
		}, { rootMargin: margin });
		els.forEach((el) => io.observe(el));
	}

	/* ------------------------------------------------------------------
	 * Scroll reveals. Run after fonts so line measurement is correct.
	 * ---------------------------------------------------------------- */
	function initReveals() {
		const splits = $$('[data-split]').filter((el) => !el.closest('[data-hero]'));
		splits.forEach((el) => { splitLines(el); el.classList.add('split-ready'); });
		whenVisible(splits, '0px 0px -12% 0px', (el) => el.classList.add('is-in'));

		let batch = 0;
		whenVisible($$('[data-reveal]'), '0px 0px -8% 0px', (el) => {
			const i = batch++;
			setTimeout(() => { el.classList.add('is-in'); batch = Math.max(0, batch - 1); }, (i % 4) * 90);
		});

		// Statement-style text: words light up as you scroll through (start "top 80%", end "bottom 45%").
		const wordBlocks = $$('[data-words]').map((el) => ({ el, words: splitWords(el), last: -1 }));
		if (wordBlocks.length) {
			onFrame(() => {
				const vh = window.innerHeight;
				wordBlocks.forEach((b) => {
					const r = b.el.getBoundingClientRect();
					if (r.bottom < -vh || r.top > vh * 2) return;
					const p = clamp((vh * 0.8 - r.top) / (vh * 0.35 + r.height));
					if (Math.abs(p - b.last) < 0.002) return;
					b.last = p;
					const n = b.words.length;
					const total = (n - 1) * 0.1 + 0.5;
					const t = p * total;
					b.words.forEach((w, i) => { w.style.opacity = (0.14 + 0.86 * clamp((t - i * 0.1) / 0.5)).toFixed(3); });
				});
			});
		}

		whenVisible($$('[data-count]'), '0px 0px -10% 0px', (el) => {
			const to = parseFloat(el.dataset.count);
			const dec = String(el.dataset.count).includes('.') ? 1 : 0;
			const o = { v: 0 };
			tween(o, { v: to }, { duration: reduced ? 0 : 1.8, ease: 'expoOut', onUpdate: () => { el.textContent = o.v.toFixed(dec); } });
		});

		const parallax = $$('[data-parallax]');
		if (parallax.length && !reduced) {
			onFrame(() => {
				const vh = window.innerHeight;
				parallax.forEach((img) => {
					const r = img.parentElement.getBoundingClientRect();
					if (r.bottom < 0 || r.top > vh) return;
					const p = clamp((vh - r.top) / (vh + r.height));
					img.style.transform = `translate3d(0, ${lerp(-6, 6, p).toFixed(2)}%, 0)`;
				});
			});
		}

		initMarquee();
	}

	/* Infinite marquee whose speed and skew react to scroll velocity. */
	function initMarquee() {
		$$('[data-marquee]').forEach((track) => {
			const groups = $$('.marquee__group', track);
			if (!groups.length) return;
			let x = 0;
			let dir = -1;
			let boost = 0;
			let lastY = window.scrollY;
			onFrame((t, dt) => {
				const y = window.scrollY;
				const v = dt > 0 ? (y - lastY) / dt : 0; // px per second
				lastY = y;
				if (Math.abs(v) > 1) {
					dir = v > 0 ? -1 : 1;
					boost = Math.max(boost, Math.min(Math.abs(v) / 200, 14));
				}
				const r = track.getBoundingClientRect();
				if (r.bottom < 0 || r.top > window.innerHeight) return;
				const w = groups[0].offsetWidth;
				x += (0.6 + boost) * dir * (dt * 60);
				boost *= Math.pow(0.92, dt * 60);
				if (x <= -w) x += w;
				if (x > 0) x -= w;
				track.style.transform = `translate3d(${x.toFixed(2)}px,0,0) skewX(${(reduced ? 0 : boost * dir * -0.6).toFixed(2)}deg)`;
			});
		});
	}

	/* Magnetic buttons */
	if (!reduced && window.matchMedia('(pointer:fine)').matches) {
		$$('[data-magnetic]').forEach((el) => {
			const pos = { x: 0, y: 0, tx: 0, ty: 0 };
			el.addEventListener('pointermove', (e) => {
				const r = el.getBoundingClientRect();
				pos.tx = (e.clientX - r.left - r.width / 2) * 0.3;
				pos.ty = (e.clientY - r.top - r.height / 2) * 0.4;
			});
			el.addEventListener('pointerleave', () => { pos.tx = 0; pos.ty = 0; });
			onFrame((t, dt) => {
				const k = 1 - Math.exp(-dt / 0.12);
				pos.x += (pos.tx - pos.x) * k;
				pos.y += (pos.ty - pos.y) * k;
				if (Math.abs(pos.x) + Math.abs(pos.y) > 0.05 || pos.tx || pos.ty) el.style.transform = `translate(${pos.x.toFixed(2)}px, ${pos.y.toFixed(2)}px)`;
			});
		});
	}

	/* ------------------------------------------------------------------
	 * Toast
	 * ---------------------------------------------------------------- */
	const toastEl = $('[data-toast]');
	let toastTimer;
	function toast(msg) {
		if (!toastEl) return;
		toastEl.textContent = msg;
		toastEl.classList.add('is-on');
		clearTimeout(toastTimer);
		toastTimer = setTimeout(() => toastEl.classList.remove('is-on'), 3200);
	}

	/* ------------------------------------------------------------------
	 * Cart drawer + AJAX
	 * ---------------------------------------------------------------- */
	const drawer = $('[data-drawer]');
	function openDrawer() {
		if (!drawer) return;
		const openQv = $('[data-qv].is-open');
		if (openQv) {
			openQv.classList.remove('is-open');
			openQv.setAttribute('aria-hidden', 'true');
		}
		drawer.classList.add('is-open');
		drawer.setAttribute('aria-hidden', 'false');
		lockScroll(true);
		setTimeout(() => $('.drawer__panel .icon-btn', drawer)?.focus(), 300);
	}
	function closeDrawer() {
		if (!drawer) return;
		drawer.classList.remove('is-open');
		drawer.setAttribute('aria-hidden', 'true');
		lockScroll(false);
	}
	$$('[data-drawer-open]').forEach((b) => b.addEventListener('click', openDrawer));
	$$('[data-drawer-close]').forEach((b) => b.addEventListener('click', closeDrawer));

	function applyFragments(fragments) {
		if (!fragments) return;
		Object.entries(fragments).forEach(([sel, html]) => {
			$$(sel).forEach((node) => {
				const tpl = document.createElement('template');
				tpl.innerHTML = html.trim();
				const fresh = tpl.content.firstElementChild;
				if (fresh) node.replaceWith(fresh.cloneNode(true));
			});
		});
		$$('.cart-count').forEach((c) => { c.classList.remove('is-bump'); void c.offsetWidth; c.classList.add('is-bump'); });
	}

	async function wcAjax(endpoint, data) {
		const res = await fetch(CFG.ajaxUrl.replace('%%endpoint%%', endpoint), {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: new URLSearchParams(data),
		});
		return res.json();
	}

	async function addToCart(productId, qty = 1, extra = {}, btn = null) {
		if (btn) btn.classList.add('is-loading');
		try {
			const json = await wcAjax('add_to_cart', { product_id: productId, quantity: qty, ...extra });
			if (!json || json.error) {
				if (json && json.product_url) { window.location = json.product_url; return false; }
				throw new Error('add_to_cart failed');
			}
			applyFragments(json.fragments);
			if (btn) {
				btn.classList.remove('is-loading');
				btn.classList.add('is-done');
				setTimeout(() => btn.classList.remove('is-done'), 1600);
			}
			openDrawer();
			return true;
		} catch (e) {
			if (btn) btn.classList.remove('is-loading');
			toast(I18N.error || 'Error');
			return false;
		}
	}

	document.addEventListener('click', (e) => {
		const btn = e.target.closest('.js-add-to-cart');
		if (btn && btn.dataset.productId && CFG.ajaxUrl) {
			e.preventDefault();
			const extra = btn.dataset.finish ? { forma_3d_finish: btn.dataset.finish } : {};
			addToCart(btn.dataset.productId, 1, extra, btn);
			return;
		}
		const rm = e.target.closest('[data-cart-remove]');
		if (rm && CFG.ajaxUrl) {
			e.preventDefault();
			rm.closest('.mini-cart__item')?.style.setProperty('opacity', '0.4');
			wcAjax('remove_from_cart', { cart_item_key: rm.dataset.cartRemove })
				.then((json) => { if (!json || !json.fragments) throw new Error('remove failed'); applyFragments(json.fragments); })
				.catch(() => { window.location = rm.href; });
		}
	});

	/* Catalog ordering auto-submit. */
	$$('.woocommerce-ordering select').forEach((s) => s.addEventListener('change', () => s.form.submit()));

	/* ------------------------------------------------------------------
	 * model-viewer loader (shared with product.js)
	 * ---------------------------------------------------------------- */
	let mvPromise = null;
	function loadModelViewer() {
		if (customElements.get('model-viewer')) return Promise.resolve();
		if (!mvPromise) {
			mvPromise = new Promise((resolve, reject) => {
				const s = document.createElement('script');
				s.type = 'module';
				s.src = CFG.modelViewer;
				s.onload = () => customElements.whenDefined('model-viewer').then(resolve);
				s.onerror = reject;
				document.head.appendChild(s);
			});
		}
		return mvPromise;
	}

	/**
	 * Apply a finish (glTF variant or material tint) to a model-viewer element.
	 * @param {HTMLElement} mv
	 * @param {{variant?:string,tint?:number[],material?:string}} f
	 */
	function applyFinish(mv, f) {
		if (!mv || !mv.model || !f) return;
		if (f.variant) {
			mv.variantName = f.variant;
			return;
		}
		const mats = mv.model.materials.filter((m) => !f.material || m.name === f.material);
		const tint = f.tint || [1, 1, 1];
		mats.forEach((m) => m.pbrMetallicRoughness.setBaseColorFactor([tint[0], tint[1], tint[2], 1]));
	}

	/* Build a finish picker into a container. */
	function renderFinishes(wrap, finishes, onPick) {
		const list = $('[data-finish-list]', wrap);
		const name = $('[data-finish-name]', wrap);
		list.innerHTML = '';
		finishes.forEach((f, i) => {
			const b = document.createElement('button');
			b.type = 'button';
			b.className = 'swatch' + (i === 0 ? ' is-active' : '');
			b.style.setProperty('--c', f.color);
			b.title = f.name;
			b.setAttribute('aria-label', f.name);
			b.addEventListener('click', () => {
				$$('.swatch', list).forEach((s) => s.classList.toggle('is-active', s === b));
				name.textContent = f.name;
				onPick(f);
			});
			list.appendChild(b);
		});
		name.textContent = finishes[0]?.name || '';
	}

	const modelAlt = (name) => (I18N.modelAlt || '3D model of %s').replace('%s', name);

	/* ------------------------------------------------------------------
	 * Quick view (3D) modal
	 * ---------------------------------------------------------------- */
	const qv = $('[data-qv]');
	if (qv) {
		const stage = $('[data-qv-stage]', qv);
		const bar = $('[data-qv-progress]', qv);
		const hint = $('.qv__hint', qv);
		let mv = null;
		let current = null;

		const close = () => {
			qv.classList.remove('is-open');
			qv.setAttribute('aria-hidden', 'true');
			lockScroll(false);
			if (mv) mv.autoRotate = false;
		};
		$$('[data-qv-close]', qv).forEach((b) => b.addEventListener('click', close));
		document.addEventListener('keydown', (e) => {
			if (e.key !== 'Escape') return;
			if (qv.classList.contains('is-open')) close();
			if (drawer?.classList.contains('is-open')) closeDrawer();
		});

		document.addEventListener('click', async (e) => {
			const trigger = e.target.closest('[data-quick-view]');
			if (!trigger) return;
			e.preventDefault();
			const d = JSON.parse(trigger.dataset.quickView);
			current = d;

			$('[data-qv-title]', qv).textContent = d.name;
			$('[data-qv-cat]', qv).textContent = d.category || '';
			$('[data-qv-price]', qv).innerHTML = d.priceHtml || '';
			$('[data-qv-desc]', qv).textContent = d.excerpt || '';
			$('[data-qv-dims]', qv).textContent = d.dims ? (I18N.dims || 'W × D × H — %s').replace('%s', d.dims) : '';
			$('[data-qv-link]', qv).href = d.url;
			const add = $('[data-qv-add]', qv);
			add.dataset.productId = d.id;
			add.hidden = !d.canBuy;
			delete add.dataset.finish;
			stage.style.setProperty('--stage-bg', d.bg);

			const fw = $('[data-qv-finishes]', qv);
			fw.hidden = !(d.finishes && d.finishes.length > 1);

			qv.classList.add('is-open');
			qv.setAttribute('aria-hidden', 'false');
			lockScroll(true);
			hint.classList.remove('is-hidden');
			bar.parentElement.classList.remove('is-done');
			bar.style.transform = 'scaleX(0)';

			await loadModelViewer();
			if (!mv) {
				mv = document.createElement('model-viewer');
				Object.entries({
					'camera-controls': '', 'touch-action': 'pan-y', 'auto-rotate': '', 'auto-rotate-delay': '1200',
					'rotation-per-second': '20deg', 'interaction-prompt': 'none', 'shadow-intensity': '1', 'shadow-softness': '0.9',
					exposure: '1.05', 'environment-image': 'neutral', 'camera-orbit': '30deg 75deg auto', 'interpolation-decay': '120',
				}).forEach(([k, v]) => mv.setAttribute(k, v));
				mv.addEventListener('progress', (ev) => {
					const p = ev.detail.totalProgress;
					bar.style.transform = `scaleX(${p})`;
					if (p >= 1) bar.parentElement.classList.add('is-done');
				});
				mv.addEventListener('camera-change', (ev) => { if (ev.detail.source === 'user-interaction') hint.classList.add('is-hidden'); });
				mv.addEventListener('load', () => {
					if (current?.finishes?.length) applyFinish(mv, current.finishes[0]);
				});
				stage.appendChild(mv);
			}
			mv.setAttribute('alt', modelAlt(d.name));
			mv.poster = d.poster;
			mv.src = d.model;
			mv.autoRotate = true;

			if (d.finishes && d.finishes.length > 1) {
				renderFinishes(fw, d.finishes, (f) => {
					applyFinish(mv, f);
					add.dataset.finish = f.name;
				});
				add.dataset.finish = d.finishes[0].name;
			}
		});
	}

	/* ------------------------------------------------------------------
	 * Shop feature cards: swap the still for a live, slowly turning model
	 * once the card scrolls into view (desktop only).
	 * ---------------------------------------------------------------- */
	const liveCards = $$('.shop .products > li:nth-child(6n + 1) .p-card').filter((c) => $('[data-quick-view]', c));
	if (liveCards.length && window.matchMedia('(min-width: 961px)').matches) {
		whenVisible(liveCards, '200px', (card) => {
			const d = JSON.parse($('[data-quick-view]', card).dataset.quickView);
			loadModelViewer().then(() => {
				const mv = document.createElement('model-viewer');
				Object.entries({
					src: d.model, alt: modelAlt(d.name), 'auto-rotate': '', 'auto-rotate-delay': '0', 'rotation-per-second': '16deg',
					'interaction-prompt': 'none', 'disable-zoom': '', 'shadow-intensity': '1', 'shadow-softness': '0.9', exposure: '1.05',
					'environment-image': 'neutral', 'camera-orbit': '25deg 76deg auto', 'field-of-view': '24deg', loading: 'eager',
				}).forEach(([k, v]) => mv.setAttribute(k, v));
				mv.addEventListener('load', () => {
					if (d.finishes && d.finishes[0]) applyFinish(mv, d.finishes[0]);
					card.classList.add('has-live');
				});
				$('.p-card__media', card).appendChild(mv);
			});
		});
	}

	/* ------------------------------------------------------------------
	 * Boot
	 * ---------------------------------------------------------------- */
	if (document.fonts && document.fonts.ready) document.fonts.ready.then(initReveals);
	else window.addEventListener('load', initReveals);

	// Safety net: never leave the loading screen up if the 3D stage fails to start.
	setTimeout(() => {
		const loader = $('[data-loader]');
		if (loader) {
			loader.remove();
			document.documentElement.classList.add('is-intro');
		}
	}, 15000);

	window.forma3d = {
		lenis, clamp, lerp, ease, tween, onFrame, progress, pageTop, scrollTo,
		splitLines, splitWords, toast, openDrawer, lockScroll, addToCart, loadModelViewer, applyFinish, reduced,
	};
})();
