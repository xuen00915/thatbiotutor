// About (/about/) keeps the original page look: Montserrat, 65px hero heading,
// a light 78% hero photo, a 321px hero and 22px reading text (width stays the shared content width).
const assert = require('node:assert/strict');
const { chromium } = require('playwright');
const base = (process.env.TBT_RUNTIME_URL || 'http://localhost:8888').replace(/\/?$/, '/');
(async () => {
  const browser = await chromium.launch();
  try {
    const page = await browser.newPage();
    page.setDefaultTimeout(10000);
    for (const width of [1440, 1024, 768, 390, 320]) {
      await page.setViewportSize({ width, height: 900 });
      await page.goto(new URL('./about/', base).href, { waitUntil: 'load' });
      await page.evaluate(() => document.fonts?.ready);
      await page.waitForTimeout(900); // let the image fade-in finish
      const m = await page.evaluate(() => {
        const cs = (selector) => getComputedStyle(document.querySelector(selector));
        const size = (selector) => parseFloat(cs(selector).fontSize);
        return {
          heroHeight: document.querySelector('.tbt-about-hero').getBoundingClientRect().height,
          imageOpacity: parseFloat(cs('.tbt-about-hero__image').opacity),
          heroFont: cs('.tbt-about-hero h1').fontFamily,
          heroSize: size('.tbt-about-hero h1'),
          h3Size: size('.tbt-about-mission h3'),
          pSize: size('.tbt-about-mission .tbt-reading-width > p'),
          pFont: cs('.tbt-about-mission .tbt-reading-width > p').fontFamily,
          overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
        };
      });
      const at = `at ${width}px: ${JSON.stringify(m)}`;
      assert.ok(Math.abs(m.imageOpacity - 0.78) < 0.02, `Hero photo must stay at 78% opacity ${at}`);
      assert.match(m.heroFont, /^"?Montserrat/, `Hero heading must use Montserrat ${at}`);
      assert.ok(m.overflow <= 0, `Horizontal overflow ${at}`);
      if (width >= 1024) {
        assert.ok(Math.abs(m.heroHeight - 321) <= 1, `Hero must be 321px tall ${at}`);
        assert.equal(m.heroSize, 65, `Hero heading must be 65px ${at}`);
        assert.equal(m.h3Size, 40, `Mission headline must be 40px ${at}`);
        assert.equal(m.pSize, 22, `Reading text must be 22px ${at}`);
        assert.match(m.pFont, /^"?Montserrat/, `Reading text must use Montserrat ${at}`);
      } else {
        assert.ok(m.pSize >= 16, `Reading text must stay comfortable on small screens ${at}`);
      }
      console.log(`PASS about ${width}`);
    }
  } finally { await browser.close(); }
})().catch((e) => { console.error(e.message); process.exit(1); });
