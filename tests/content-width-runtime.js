const assert = require('node:assert/strict');
const { chromium } = require('playwright');
const routes = {
  '/': '.tbt-home-classroom .tbt-home-container',
  '/classes/': '.tbt-programme-container',
  '/sec-3-pure-g3-biology-tuition/': '.tbt-programme-container',
  '/sec-4-pure-g3-biology-tuition/': '.tbt-programme-container',
  '/testimonials/': '.tbt-testimonials-container',
  '/notes/': '.tbt-resource-shell',
  '/celldivision/': '.tbt-resource-shell',
  '/about/': '.tbt-reading-width',
  '/contact/': '.tbt-institutional-shell',
  '/trees/': '.tbt-institutional-shell',
  '/tuitionrecs/': '.tbt-institutional-shell',
  '/intensivefix/': '.tbt-section',
};
(async () => {
  const browser = await chromium.launch();
  try {
    const page = await browser.newPage();
    page.setDefaultTimeout(10000);
    for (const width of [1440, 1024, 768, 390, 320]) {
      await page.setViewportSize({ width, height: 900 });
      for (const [route, selector] of Object.entries(routes)) {
        await page.goto(new URL(route, process.env.TBT_RUNTIME_URL || 'http://localhost:8888').href, { waitUntil: 'domcontentloaded' });
        console.log(`Checking ${route} at ${width}`);
        for (const target of [selector, '.site-header__inner', '.site-footer__inner']) {
          const box = await page.locator(target).first().evaluate(el => {
            const r = el.getBoundingClientRect(), s = getComputedStyle(el);
            return { width: r.width - parseFloat(s.paddingLeft) - parseFloat(s.paddingRight), center: r.x + r.width / 2 };
          });
          assert.ok(Math.abs(box.width - Math.min(1120, width - 32)) < 2, `${route} ${target} at ${width}: ${box.width}`);
          assert.ok(Math.abs(box.center - width / 2) < 2, `${route} ${target} is not centered`);
        }
        assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), `${route} overflows at ${width}`);
      }
    }
    console.log('PASS: all 12 routes share centered content, header and footer widths at five viewport sizes');
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exit(1); });
