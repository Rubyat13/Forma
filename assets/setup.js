/**
 * Forma Setup: pick a starter site, then run its import step by step and show progress.
 */
(function () {
	'use strict';
	const cfg = window.FORMA_SETUP;
	const btn = document.querySelector('[data-import-start]');
	if (!btn || !cfg) return;
	const box = document.querySelector('[data-progress]');
	const bar = document.querySelector('[data-bar]');
	const status = document.querySelector('[data-status]');
	const view = document.querySelector('[data-view]');
	const replace = document.querySelector('[data-replace]');
	const replaceInput = document.querySelector('[data-replace-input]');
	const radios = Array.from(document.querySelectorAll('input[name="forma_site"]'));

	const selected = () => radios.find((r) => r.checked) || radios[0];

	// Button label and the "replace the previous starter site" option follow the selected card.
	const sync = () => {
		const r = selected();
		radios.forEach((x) => x.closest('.frm-site').classList.toggle('is-selected', x === r));
		const again = r.value === cfg.current;
		btn.textContent = (again ? cfg.i18n.again : cfg.i18n.import).replace('%s', r.dataset.name);
		replace.hidden = !cfg.current || again;
	};
	radios.forEach((r) => r.addEventListener('change', sync));
	sync();

	const post = async (data) => {
		const body = new URLSearchParams({ action: 'forma_3d_tk_demo_step', nonce: cfg.nonce, ...data });
		const res = await fetch(cfg.ajax, { method: 'POST', credentials: 'same-origin', body });
		const json = await res.json().catch(() => null);
		if (!json || !json.success) throw new Error((json && json.data) || cfg.i18n.error);
		return json.data || {};
	};

	btn.addEventListener('click', async () => {
		const site = selected().value;
		const remote = selected().dataset.remote === '1';
		btn.disabled = true;
		radios.forEach((r) => { r.disabled = true; });
		box.hidden = false;
		box.classList.remove('is-done', 'is-error');
		bar.style.width = '2%';
		view.hidden = true;
		try {
			status.textContent = remote ? cfg.i18n.fetch : cfg.i18n.setup;
			const setup = await post({ step: 'setup', site, replace: !replace.hidden && replaceInput.checked ? '1' : '' });
			const products = setup.products || [];
			const total = products.length + 2;
			bar.style.width = `${(1 / total) * 100}%`;
			for (let i = 0; i < products.length; i++) {
				status.textContent = cfg.i18n.product.replace('%1$s', products[i]).replace('%2$d', i + 1).replace('%3$d', products.length);
				await post({ step: 'product', site, index: i });
				bar.style.width = `${((i + 2) / total) * 100}%`;
			}
			status.textContent = cfg.i18n.finish;
			await post({ step: 'finish', site });
			bar.style.width = '100%';
			status.textContent = cfg.i18n.done;
			box.classList.add('is-done');
			view.hidden = false;
			cfg.current = site;
			document.querySelectorAll('.frm-site__badge').forEach((b) => b.remove());
			const badge = document.createElement('em');
			badge.className = 'frm-site__badge';
			badge.textContent = cfg.i18n.imported;
			selected().closest('.frm-site').querySelector('strong').appendChild(badge);
		} catch (e) {
			status.textContent = e.message;
			box.classList.add('is-error');
		}
		btn.disabled = false;
		radios.forEach((r) => { r.disabled = false; });
		sync();
	});
})();
