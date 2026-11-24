// Fast publication gate. Full behavioural contracts run in the background job.
const assert = require('node:assert/strict');
const { chromium } = require('playwright');
const routes = ['', 'classes/', 'sec-3-pure-g3-biology-tuition/', 'sec-4-pure-g3-biology-tuition/',
  'testimonials/', 'notes/', 'celldivision/', 'about/', 'contact/', 'trees/', 'tuitionrecs/', 'intensivefix/'];

async function runSmoke({ base = process.env.TBT_RUNTIME_URL || 'http://localhost:4173/', routes: pages = routes, widths = [1440, 390] } = {}) {
  const origin = new URL(base).origin;
  const browser = await chromium.launch();
  try {
    await Promise.all(widths.map(async width => {
      const page = await browser.newPage({ viewport: { width, height: 900 }, reducedMotion: 'reduce' });
      page.setDefaultTimeout(10000);
      const failures = [];
      page.on('response', response => {
        if (new URL(response.url()).origin === origin && response.status() >= 400) failures.push(`${response.status()} ${response.url()}`);
      });
      page.on('requestfailed', request => {
        if (new URL(request.url()).origin === origin && request.failure()?.errorText !== 'net::ERR_ABORTED') failures.push(`Failed ${request.url()}`);
      });
      page.on('pageerror', error => failures.push(error.message));
      for (const route of pages) {
        const label = `${route || '/'} @${width}`;
        const response = await page.goto(new URL(route, base.replace(/\/?$/, '/')).href, { waitUntil: 'load' });
        assert.equal(response?.status(), 200, `${label}: page loads`);
        await page.evaluate(() => document.fonts.ready);
        assert.ok((await page.locator('main').innerText()).trim().length >= 20, `${label}: visible content`);
        for (const selector of ['.site-header', 'main', '.site-footer']) {
          assert.equal(await page.locator(selector).count(), 1, `${label}: ${selector} exists`);
          const box = await page.locator(selector).boundingBox();
          assert.ok(box && box.width > 0 && box.height > 0, `${label}: ${selector} is visible`);
        }
        const metrics = await page.evaluate(() => ({
          overflow: document.documentElement.scrollWidth - innerWidth,
          brokenImages: [...document.images].filter(img => img.complete && !img.naturalWidth && img.currentSrc && new URL(img.currentSrc).origin === location.origin).map(img => img.currentSrc),
        }));
        assert.ok(metrics.overflow <= 1, `${label}: horizontal overflow ${metrics.overflow}px`);
        assert.deepEqual(metrics.brokenImages, [], `${label}: images load`);
        assert.deepEqual(failures, [], `${label}: scripts and local assets load`);
      }
      await page.close();
    }));
    console.log(`PASS preview smoke: ${pages.length} pages at ${widths.join('/')}px`);
  } finally { await browser.close(); }
}
module.exports = { runSmoke };
if (require.main === module) runSmoke().catch(error => { console.error(error); process.exitCode = 1; });
