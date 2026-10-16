/**
 * Store-kit edition: install and activate the demo store plugins one by one,
 * then open Forma Setup.
 */
(function () {
	'use strict';
	const cfg = window.forma3dInstaller;
	const dialog = document.getElementById('forma-3d-installer');
	if (!cfg || !dialog) return;

	const run = dialog.querySelector('[data-install]');
	const message = dialog.querySelector('[data-message]');

	const post = async (data) => {
		const body = new URLSearchParams({ nonce: cfg.nonce, ...data });
		const res = await fetch(cfg.ajax, { method: 'POST', credentials: 'same-origin', body });
		const json = await res.json().catch(() => null);
		if (!json || !json.success) throw new Error((json && json.data && json.data.message) || cfg.i18n.error);
		return json.data;
	};

	const open = () => {
		if (dialog.open) return;
		if (typeof dialog.showModal === 'function') dialog.showModal();
		else dialog.setAttribute('open', '');
	};

	document.addEventListener('click', (e) => {
		if (e.target.closest('[data-forma-installer-open]')) {
			e.preventDefault();
			open();
		}
		if (e.target.closest('#forma-3d-installer-notice .notice-dismiss')) {
			post({ action: 'forma_3d_installer_dismiss' }).catch(() => {});
		}
	});
	dialog.querySelector('[data-close]').addEventListener('click', () => dialog.close());

	run.addEventListener('click', async () => {
		run.disabled = true;
		message.textContent = '';
		message.classList.remove('is-error');
		for (const item of dialog.querySelectorAll('[data-plugin]')) {
			if (item.dataset.state === 'active') continue;
			const label = item.querySelector('[data-label]');
			item.dataset.state = 'working';
			label.textContent = cfg.i18n.working;
			try {
				await post({ action: 'forma_3d_install_plugin', plugin: item.dataset.plugin });
				item.dataset.state = 'active';
				label.textContent = cfg.i18n.active;
			} catch (err) {
				item.dataset.state = 'error';
				label.textContent = '';
				message.textContent = err.message;
				message.classList.add('is-error');
				run.textContent = cfg.i18n.retry;
				run.disabled = false;
				return;
			}
		}
		message.textContent = cfg.i18n.done;
		window.location.href = cfg.setupUrl;
	});

	if (cfg.autoOpen) open();
})();
