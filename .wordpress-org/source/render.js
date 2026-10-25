// Usage: node render.js <page.html> <out.png> <width> <height> [scale]
const path = require('path');
const { chromium } = require('playwright-core');

(async () => {
	const [page, out, w, h, scale = '1'] = process.argv.slice(2);
	const browser = await chromium.launch({ args: ['--allow-file-access-from-files'] });
	const ctx = await browser.newContext({ viewport: { width: +w, height: +h }, deviceScaleFactor: +scale });
	const p = await ctx.newPage();
	p.on('pageerror', (e) => console.error('pageerror:', e.message));
	await p.goto('file://' + path.resolve(page));
	await p.waitForFunction(() => window.__ready === true, null, { timeout: 15000 });
	await p.screenshot({ path: out, omitBackground: true });
	await browser.close();
	console.log('wrote', out);
})();
