// Crops each img[data-trim] to its visible pixels, then flags the page as ready to capture.
(async () => {
	const imgs = [...document.querySelectorAll('img[data-trim]')];
	await Promise.all(imgs.map(async (img) => {
		await img.decode();
		const c = document.createElement('canvas');
		c.width = img.naturalWidth; c.height = img.naturalHeight;
		const ctx = c.getContext('2d');
		ctx.drawImage(img, 0, 0);
		const { data, width, height } = ctx.getImageData(0, 0, c.width, c.height);
		let x0 = width, y0 = height, x1 = 0, y1 = 0;
		for (let y = 0; y < height; y++) {
			for (let x = 0; x < width; x++) {
				if (data[(y * width + x) * 4 + 3] > 24) {
					if (x < x0) x0 = x; if (x > x1) x1 = x;
					if (y < y0) y0 = y; if (y > y1) y1 = y;
				}
			}
		}
		const w = x1 - x0 + 1, h = y1 - y0 + 1;
		const out = document.createElement('canvas');
		out.width = w; out.height = h;
		out.getContext('2d').drawImage(c, x0, y0, w, h, 0, 0, w, h);
		img.src = out.toDataURL('image/png');
		await img.decode();
	}));
	if (window.__prep) await window.__prep();
	await document.fonts.ready;
	window.__ready = true;
})();
