// Our Causes (/trees/) layout contract: no dead space, no cramped title bar, matching title bars, no overflow.
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
      await page.goto(new URL('./trees/', base).href, { waitUntil: 'load' });
      await page.evaluate(() => document.fonts?.ready);
      const m = await page.evaluate(() => {
        const box = (selector) => { const r = document.querySelector(selector).getBoundingClientRect(); return { top: r.top + scrollY, bottom: r.bottom + scrollY, height: r.height }; };
        const fontSize = (selector) => parseFloat(getComputedStyle(document.querySelector(selector)).fontSize);
        return {
          h1: fontSize('[data-section="planting"] .tbt-institutional-title-bar h1'),
          h2: fontSize('[data-section="sg-causes"] .tbt-institutional-title-bar h2'),
          bar: box('[data-section="planting"] .tbt-institutional-title-bar'),
          square: box('.tbt-causes-section__media--square'),
          giving: box('.tbt-causes-section__media--giving'),
          squareX: (() => { const r = document.querySelector('.tbt-causes-section__media--square').getBoundingClientRect(); return { left: r.left, right: r.right }; })(),
          copyX: (() => { const r = document.querySelector('[data-section="planting"] .tbt-causes-copy').getBoundingClientRect(); return { left: r.left, right: r.right, top: r.top + scrollY, bottom: r.bottom + scrollY }; })(),
          align: ['h2', 'p'].map((tag) => getComputedStyle(document.querySelector(`[data-section="planting"] .tbt-causes-copy ${tag}`)).textAlign),
          givingX: (() => { const r = document.querySelector('.tbt-causes-section__media--giving').getBoundingClientRect(); return { left: r.left, right: r.right, top: r.top + scrollY, bottom: r.bottom + scrollY }; })(),
          sgCopy: (() => { const r = document.querySelector('[data-section="sg-causes"] .tbt-causes-copy').getBoundingClientRect(); return { left: r.left, top: r.top + scrollY, bottom: r.bottom + scrollY }; })(),
          sgAlign: [...document.querySelectorAll('[data-section="sg-causes"] .tbt-causes-copy p')].map((e) => getComputedStyle(e).textAlign),
          record: box('.tbt-causes-section__media--record'),
          footer: box('.site-footer'),
          barH1: box('[data-section="planting"] .tbt-institutional-title-bar h1').height,
          overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
        };
      });
      const at = `at ${width}px: ${JSON.stringify(m)}`;
      assert.ok(m.square.top - m.bar.bottom >= 24, `Title bar must have breathing room above the first picture ${at}`);
      assert.ok(m.align.every((a) => a === 'left' || a === 'start'), `Planting heading and paragraphs must share one left alignment ${at}`);
      if (width > 980) {
        assert.ok(m.squareX.right <= m.copyX.left, `Planting picture must sit beside the text on desktop ${at}`);
        assert.ok(m.copyX.top >= m.square.top && m.copyX.bottom <= m.square.bottom, `Text must sit within the picture's height ${at}`);
        assert.ok(m.record.top >= Math.max(m.square.bottom, m.copyX.bottom) - 1, `Donor-search picture sits below the side-by-side block ${at}`);
      } else {
        assert.ok(m.copyX.top >= m.square.bottom, `Planting picture stacks above the text on narrower screens ${at}`);
      }
      assert.ok(m.sgAlign.every((a) => a === 'left' || a === 'start'), `SG causes paragraphs stay left-aligned ${at}`);
      if (width > 980) {
        assert.ok(m.givingX.right <= m.sgCopy.left, `Giving.sg picture must sit beside the text on desktop ${at}`);
        assert.ok(m.sgCopy.top >= m.givingX.top - 1 && m.sgCopy.bottom <= m.givingX.bottom + 1, `SG text must sit within the picture's height ${at}`);
      } else {
        assert.ok(m.givingX.top >= m.sgCopy.bottom - 1, `Giving.sg picture stacks below the text on narrower screens ${at}`);
      }
      assert.ok(m.footer.top - m.giving.bottom <= 180, `Dead space between the last picture and the footer ${at}`);
      assert.equal(m.h1, m.h2, `Both title bars must use the same text size ${at}`);
      assert.ok(m.barH1 <= m.bar.height + 1, `Title text must fit its bar ${at}`);
      assert.ok(m.overflow <= 0, `Horizontal overflow ${at}`);
      console.log(`PASS causes ${width}`);
    }
  } finally { await browser.close(); }
})().catch((e) => { console.error(e.message); process.exit(1); });
