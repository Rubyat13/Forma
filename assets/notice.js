/**
 * Forma welcome notice: remember when it is dismissed.
 */
document.addEventListener('click', function (e) {
	'use strict';
	const n = e.target.closest('#forma-3d-tk-notice .notice-dismiss');
	if (!n) return;
	const body = new URLSearchParams({ action: 'forma_3d_tk_dismiss_notice', nonce: n.closest('.notice').dataset.nonce });
	fetch(window.ajaxurl, { method: 'POST', credentials: 'same-origin', body });
});
