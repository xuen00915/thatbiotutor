const { chromium } = require('playwright');
const assert = require('node:assert/strict');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();
  const base = process.env.TBT_RUNTIME_URL || 'http://localhost:8890';
  try {
    for (const width of [1440, 768, 390]) {
      await page.setViewportSize({ width, height: 1000 });
      await page.goto(`${base}/classes/`);
      assert.equal(await page.locator('.tbt-programme-levels').evaluate(e => getComputedStyle(e).backgroundColor), 'rgb(255, 255, 255)');
      assert.equal(await page.locator('.tbt-programme-levels__grid a').first().evaluate(e => getComputedStyle(e).backgroundColor), 'rgb(238, 247, 234)');
      await page.goto(`${base}/sec-3-pure-g3-biology-tuition/`);
      const size = await page.locator('.tbt-programme-hero h1').evaluate(e => parseFloat(getComputedStyle(e).fontSize));
      assert.ok(size >= (width === 1440 ? 58 : 32), `Hero title too small: ${size}`);
      assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), 'Hero must fit viewport');
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
