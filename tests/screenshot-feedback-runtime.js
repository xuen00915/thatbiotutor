const { chromium } = require('playwright');
const assert = require('node:assert/strict');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();
  const base = process.env.TBT_RUNTIME_URL || 'http://localhost:8890';
  try {
    for (const width of [1440, 1024, 768, 390, 320]) {
      await page.setViewportSize({ width, height: 1000 });
      await page.goto(`${base}/classes/`);
      assert.equal(await page.locator('.tbt-programme-levels').evaluate(e => getComputedStyle(e).backgroundColor), 'rgb(255, 255, 255)');
      assert.equal(await page.locator('.tbt-programme-levels__grid a').first().evaluate(e => getComputedStyle(e).backgroundColor), 'rgb(237, 245, 232)');
      await page.goto(`${base}/sec-3-pure-g3-biology-tuition/`);
      const size = await page.locator('.tbt-programme-hero h1').evaluate(e => parseFloat(getComputedStyle(e).fontSize));
      assert.ok(size >= (width === 1440 ? 58 : 32), `Hero title too small: ${size}`);
      assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), 'Hero must fit viewport');
      await page.goto(`${base}/sec-4-pure-g3-biology-tuition/`);
      assert.equal(await page.locator('.tbt-programme-hero h1').evaluate(e => parseFloat(getComputedStyle(e).fontSize)), size, 'Sec 4 hero matches Sec 3');
      assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), 'Sec 4 hero fits viewport');
      for (const level of [3, 4]) {
        await page.goto(`${base}/sec-${level}-pure-g3-biology-tuition/`);
        await page.evaluate(() => document.fonts.ready);
        const included = await page.locator('.tbt-programme-options__included .tbt-tick').evaluateAll(items => items.map(e => {
          const s = getComputedStyle(e), icon = getComputedStyle(e, '::before'), r = e.getBoundingClientRect();
          return { display: s.display, align: s.alignItems, gap: parseFloat(s.columnGap), iconPosition: icon.position, top: r.top, height: r.height, line: parseFloat(s.lineHeight) };
        }));
        assert.equal(included.length, 4);
        assert.ok(included.every(e => e.display === 'flex' && e.align === 'center' && e.iconPosition === 'static' && e.gap <= 10), 'Each tick stays close to and vertically centred with its phrase');
        assert.ok(included.every(e => e.height <= e.line + 1), 'Each phrase stays on one line');
        if (width >= 1024) assert.ok(included.every(e => Math.abs(e.top - included[0].top) < 1), 'All four benefits share one desktop row');
        assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), 'Benefits fit viewport');
      }
      await page.goto(`${base}/about/`);
      const founder = await page.locator('.tbt-founder-card').evaluateAll(cards => cards.map(c => ({
        quote: getComputedStyle(c, '::before').content,
        highlights: [...c.querySelectorAll('u')].map(e => ({ color: getComputedStyle(e).color, underline: getComputedStyle(e).textDecorationLine }))
      })));
      assert.equal(founder.length, 3);
      assert.ok(founder.every(c => ['none', 'normal'].includes(c.quote)), 'Founder cards have no quote icons');
      const highlights = founder.flatMap(c => c.highlights);
      assert.ok(highlights.length >= 5);
      assert.ok(highlights.every(h => h.color === 'rgb(47, 138, 39)' && h.underline === 'none'), 'Founder emphasis uses green without underlines');
      await page.goto(`${base}/notes/`);
      const cards = await page.locator('.tbt-notes-benefit').evaluateAll(cards => cards.map(c => {
        const icon = c.querySelector('img').getBoundingClientRect();
        const text = c.querySelector('p').getBoundingClientRect();
        return icon.bottom <= text.top && Math.abs(icon.x + icon.width / 2 - (c.getBoundingClientRect().x + c.getBoundingClientRect().width / 2)) < 2;
      }));
      assert.equal(cards.length, 4);
      assert.ok(cards.every(Boolean), 'Each icon must sit above the wording and be centred');
      assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), 'Notes must fit viewport');
      console.log(`PASS screenshot feedback ${width}px`);
    }
  } finally { await browser.close(); }
})().catch(e => { console.error(e); process.exit(1); });
