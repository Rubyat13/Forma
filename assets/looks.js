/**
 * "Try the Look" carousel: the active look sits in the middle, the others step
 * back, shrink and blur. The panel shows the active look's pieces; "+" adds one
 * piece and "Add full look to bag" adds them all with the look's bundle token.
 */
(function () {
	'use strict';
	const CFG = window.FORMA_LOOKS || { ajaxUrl: '', cartUrl: '', i18n: {} };

	// Add one product. Uses the Forma 3D theme's cart (drawer + fragments) when it is there.
	async function add(id, extra) {
		if (window.forma3d && typeof window.forma3d.addToCart === 'function') {
			return window.forma3d.addToCart(id, 1, extra || {}, null);
		}
		if (!CFG.ajaxUrl) return false;
		const res = await fetch(CFG.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: new URLSearchParams({ product_id: id, quantity: 1, ...(extra || {}) }),
		});
		const json = await res.json().catch(() => null);
		return !!(json && !json.error);
	}

	function init(root) {
		const figs = Array.from(root.querySelectorAll('[data-look-fig]'));
		const sets = Array.from(root.querySelectorAll('[data-look-set]'));
		const steps = Array.from(root.querySelectorAll('[data-look-step]'));
		const status = root.querySelector('[data-look-status]');
		const n = figs.length;
		let index = 0;

		function layout() {
			figs.forEach((fig, k) => {
				let o = k - index;
				if (o > n / 2) o -= n;
				if (o < -n / 2) o += n;
				const a = Math.abs(o);
				fig.style.setProperty('--o', o);
				fig.style.setProperty('--a', a);
				fig.classList.toggle('is-active', o === 0);
				fig.classList.toggle('is-far', a > 2);
				const btn = fig.querySelector('button');
				if (btn) btn.tabIndex = o === 0 || a > 2 ? -1 : 0;
			});
			sets.forEach((set, k) => { set.hidden = k !== index; });
			steps.forEach((step, k) => step.classList.toggle('is-active', k === index));
			root.style.setProperty('--looks-progress', String((index + 1) / n));
			if (status && sets[index]) {
				status.textContent = (CFG.i18n.status || '%1$d / %2$d: %3$s')
					.replace('%1$d', index + 1).replace('%2$d', n).replace('%3$s', sets[index].dataset.lookName || '');
			}
		}
		function go(k) {
			index = (k + n) % n;
			layout();
		}

		root.addEventListener('click', (e) => {
			const pick = e.target.closest('[data-look-go]');
			if (pick) { go(+pick.dataset.lookGo); return; }
			if (e.target.closest('[data-look-prev]')) { go(index - 1); return; }
			if (e.target.closest('[data-look-next]')) { go(index + 1); return; }

			// "+" without the Forma 3D theme (the theme handles .js-add-to-cart itself).
			const one = e.target.closest('.looks__add.js-add-to-cart');
			if (one && !window.forma3d) {
				e.preventDefault();
				one.classList.add('is-loading');
				add(one.dataset.productId).then((ok) => {
					one.classList.remove('is-loading');
					one.classList.toggle('is-done', ok);
				});
				return;
			}

			const buy = e.target.closest('[data-look-buy]');
			if (buy && !buy.disabled) {
				e.preventDefault();
				buyLook(buy);
			}
		});

		async function buyLook(btn) {
			const ids = (btn.dataset.lookProducts || '').split(',').filter(Boolean);
			const label = btn.querySelector('span');
			const text = label.textContent;
			btn.disabled = true;
			btn.classList.add('is-loading');
			label.textContent = CFG.i18n.adding || '…';
			let ok = true;
			for (const id of ids) {
				// eslint-disable-next-line no-await-in-loop -- one at a time keeps the cart fragments in order.
				ok = (await add(id, { forma_3d_look: btn.dataset.lookToken }).catch(() => false)) && ok;
			}
			btn.classList.remove('is-loading');
			btn.disabled = false;
			label.textContent = ok ? (CFG.i18n.added || text) : (CFG.i18n.error || text);
			setTimeout(() => { label.textContent = text; }, 2400);
			if (ok && !window.forma3d && CFG.cartUrl) window.location = CFG.cartUrl;
		}

		// Keyboard and swipe.
		root.addEventListener('keydown', (e) => {
			if (e.target.closest('input, textarea, select')) return;
			if (e.key === 'ArrowLeft') { go(index - 1); }
			if (e.key === 'ArrowRight') { go(index + 1); }
		});
		const stage = root.querySelector('.looks__stage');
		let x0 = null;
		stage.addEventListener('pointerdown', (e) => { x0 = e.clientX; });
		stage.addEventListener('pointerup', (e) => {
			if (x0 === null) return;
			const dx = e.clientX - x0;
			x0 = null;
			if (Math.abs(dx) > 40) go(index + (dx < 0 ? 1 : -1));
		});

		layout();
		root.classList.add('is-ready');
	}

	const start = () => document.querySelectorAll('[data-looks]').forEach(init);
	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
	else start();
})();
