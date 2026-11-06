// Free Bio Notes (/notes/): the "Hey Pure Bio Student, is this you?" title is a contained, centred panel (not edge to edge),
// and the sections around it plus the dark "Why free / catch / download" blocks keep a compact rhythm.
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
      await page.goto(new URL('./notes/', base).href, { waitUntil: 'load' });
      await page.evaluate(() => document.fonts?.ready);
      await page.waitForTimeout(500);
      const m = await page.evaluate(() => {
        const sec = (name) => document.querySelector(`.tbt-notes-page [data-section="${name}"]`);
        const bottomOf = (section) => Math.max(...Array.from(section.querySelectorAll('p, li, img, a.tbt-resource-button')).filter((e) => e.getBoundingClientRect().height > 0).map((e) => e.getBoundingClientRect().bottom));
        const h2Top = (name) => sec(name).querySelector('h2').getBoundingClientRect().top;
        const bar = sec('fit').querySelector('h2');
        const br = bar.getBoundingClientRect();
        const shell = sec('reviews-top').querySelector('h2').getBoundingClientRect();
        const cs = getComputedStyle(bar);
        return {
          viewport: document.documentElement.clientWidth,
          barLeft: br.left, barWidth: br.width, shellWidth: shell.width, radius: parseFloat(cs.borderTopLeftRadius), textAlign: cs.textAlign,
          fitToBenefits: h2Top('benefits') - bottomOf(sec('fit')),
          darkGaps: [['why-free', 'catch'], ['catch', 'download']].map(([a, b]) => h2Top(b) - bottomOf(sec(a))),
          overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
        };
      });
      const at = `at ${width}px: ${JSON.stringify(m)}`;
      assert.ok(m.barLeft >= 8 && m.barWidth <= m.viewport - 16, `Title panel must not run edge to edge ${at}`);
      assert.ok(Math.abs(m.barWidth - m.shellWidth) <= 1, `Title panel must match the page content width ${at}`);
      assert.ok(m.radius >= 16, `Title panel must have rounded corners like the other title bars ${at}`);
      assert.equal(m.textAlign, 'center', `Title must be centred in its panel ${at}`);
      assert.ok(m.fitToBenefits <= (width >= 769 ? 90 : 56), `Space between "is this you?" and "ThatBioTutor's Notes" is too long ${at}`);
      m.darkGaps.forEach((g) => assert.ok(g <= (width >= 769 ? 64 : 40), `Space between dark blocks is too long ${at}`));
      assert.ok(m.overflow <= 0, `Horizontal overflow ${at}`);
      console.log(`PASS notes sections ${width}`);
    }
  } finally { await browser.close(); }
})().catch((e) => { console.error(e.message); process.exit(1); });
