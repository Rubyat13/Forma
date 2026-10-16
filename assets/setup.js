/**
 * Forma Setup: run the demo import step by step and show progress.
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

	const post = async (data) => {
		const body = new URLSearchParams({ action: 'forma_3d_tk_demo_step', nonce: cfg.nonce, ...data });
		const res = await fetch(cfg.ajax, { method: 'POST', credentials: 'same-origin', body });
		const json = await res.json().catch(() => null);
		if (!json || !json.success) throw new Error((json && json.data) || cfg.i18n.error);
	};

	btn.addEventListener('click', async () => {
		const steps = [{ step: 'setup', label: cfg.i18n.setup }]
			.concat(cfg.products.map((name, i) => ({ step: 'product', index: i, label: name })))
			.concat([{ step: 'finish', label: cfg.i18n.finish }]);
		btn.disabled = true;
		box.hidden = false;
		view.hidden = true;
		try {
			for (let i = 0; i < steps.length; i++) {
				const s = steps[i];
				status.textContent = s.step === 'product'
					? cfg.i18n.product.replace('%1$s', s.label).replace('%2$d', s.index + 1).replace('%3$d', cfg.products.length)
					: s.label;
				await post({ step: s.step, index: s.index ?? '' });
				bar.style.width = `${((i + 1) / steps.length) * 100}%`;
			}
			status.textContent = cfg.i18n.done;
			box.classList.add('is-done');
			view.hidden = false;
			btn.hidden = true;
		} catch (e) {
			status.textContent = e.message;
			box.classList.add('is-error');
			btn.disabled = false;
		}
	});
})();
