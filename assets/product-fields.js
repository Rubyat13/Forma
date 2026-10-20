/**
 * Forma Display tab: pick or remove the product's .glb model from the media library.
 */
jQuery(function ($) {
	'use strict';
	const cfg = window.FORMA_PRODUCT_FIELDS;
	let frame;
	$('#forma_model_pick').on('click', function (e) {
		e.preventDefault();
		if (frame) { frame.open(); return; }
		frame = wp.media({ title: cfg.i18n.title, button: { text: cfg.i18n.button }, multiple: false });
		frame.on('select', function () {
			const a = frame.state().get('selection').first().toJSON();
			$('#forma_model_id').val(a.id);
			$('#forma_model_name').text(a.filename);
		});
		frame.open();
	});
	$('#forma_model_clear').on('click', function () {
		$('#forma_model_id').val('');
		$('#forma_model_name').text('');
	});
});
