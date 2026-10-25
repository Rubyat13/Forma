// Usage: node frames.js <page.html> <outdir> <width> <height> <scale> <fps> <seconds>
// Captures one PNG per frame by calling the page's window.__frame(t).
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright-core');

(async () => {
	const [page, outdir, w, h, scale, fps, secs] = process.argv.slice(2);
	fs.rmSync(outdir, { recursive: true, force: true });
	fs.mkdirSync(outdir, { recursive: true });
	const browser = await chromium.launch({ args: ['--allow-file-access-from-files'] });
	const ctx = await browser.newContext({ viewport: { width: +w, height: +h }, deviceScaleFactor: +scale });
	const p = await ctx.newPage();
	p.on('pageerror', (e) => console.error('pageerror:', e.message));
	await p.goto('file://' + path.resolve(page));
	await p.waitForFunction(() => window.__ready === true, null, { timeout: 15000 });
	const n = Math.round(+fps * +secs);
	for (let i = 0; i < n; i++) {
		await p.evaluate((t) => window.__frame(t), i / +fps);
		await p.screenshot({ path: path.join(outdir, String(i).padStart(4, '0') + '.png') });
	}
	await browser.close();
	console.log('frames', n, '->', outdir);
})();
