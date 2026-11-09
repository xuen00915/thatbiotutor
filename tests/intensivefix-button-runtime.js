const assert = require('node:assert/strict');
const { chromium } = require('playwright');
const base = (process.env.TBT_RUNTIME_URL || 'http://localhost:8890').replace(/\/?$/, '/');
(async () => {
  const browser = await chromium.launch();
  try {
    const page = await browser.newPage({ reducedMotion: 'reduce' });
    for (const width of [1440, 1024, 834, 768, 600, 390, 320]) {
      await page.setViewportSize({ width, height: 900 });
      await page.goto(new URL('intensivefix/', base).href);
      await page.evaluate(() => document.fonts.ready);
      const button = page.locator('.tbt-intensivefix-closing__cta .tbt-home-button');
      const r = await button.evaluate(e => {
        const b = e.getBoundingClientRect(), p = e.parentElement.getBoundingClientRect(), s = getComputedStyle(e);
        const range = document.createRange(); range.selectNodeContents(e);
        const text = range.getBoundingClientRect();
        return { width:b.width, height:b.height, inside:b.left>=p.left && b.right<=p.right+1, textInside:text.left>=b.left&&text.right<=b.right, align:s.textAlign, font:s.fontSize, overflow:document.documentElement.scrollWidth-innerWidth };
      });
      assert.equal((await button.textContent()).trim(), 'Fix My Sec 3 Foundation Now');
      assert.match(await button.getAttribute('href'), /^https:\/\/(wa\.me|api\.whatsapp\.com)\//);
      assert.ok(r.inside && r.textInside && r.overflow === 0, `Button fits at ${width}: ${JSON.stringify(r)}`);
      if (width <= 1024) {
        assert.equal(r.height, 52, `Compact touch target at ${width}`);
        assert.equal(r.align, 'center');
        assert.equal(r.font, '16px');
        assert.ok(r.width <= 420);
      } else { assert.equal(r.font, '18px'); assert.equal(r.height, 60); }
      console.log(`PASS IntensiveFix closing button ${width}`);
    }
  } finally { await browser.close(); }
})().catch(e => { console.error(e); process.exit(1); });
