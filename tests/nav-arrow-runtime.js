// The Classes menu item shows a clear drop-down arrow (an SVG chevron, not a tiny text glyph) on desktop and in the phone menu.
const assert = require('node:assert/strict');
const { chromium } = require('playwright');
const base = (process.env.TBT_RUNTIME_URL || 'http://localhost:8888').replace(/\/?$/, '/');
(async () => {
  const browser = await chromium.launch();
  try {
    const page = await browser.newPage();
    page.setDefaultTimeout(10000);
    for (const [route, width] of [['', 1440], ['about/', 1024], ['classes/', 390], ['about/', 320]]) {
      await page.setViewportSize({ width, height: 900 });
      await page.goto(new URL(route, base).href, { waitUntil: 'load' });
      if (width < 981) await page.locator('[data-menu-toggle]').click();
      const chevron = page.locator('.site-nav [data-submenu-toggle] svg.site-nav__chevron');
      assert.equal(await chevron.count(), 1, `Classes needs an SVG chevron at ${width}px`);
      const box = await chevron.boundingBox();
      assert.ok(box && box.width >= 12 && box.height >= 7, `Chevron must be clearly visible at ${width}px: ${JSON.stringify(box)}`);
      const stroke = await chevron.locator('path').evaluate((p) => getComputedStyle(p).stroke);
      assert.notEqual(stroke, 'none', `Chevron must be drawn with a stroke at ${width}px`);
      console.log(`PASS nav arrow ${width}`);
    }
  } finally { await browser.close(); }
})().catch((e) => { console.error(e.message); process.exit(1); });
