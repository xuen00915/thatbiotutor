const { chromium } = require('playwright');
const assert = require('node:assert/strict');
(async () => {
 const browser = await chromium.launch();
 const page = await browser.newPage({ reducedMotion: 'reduce' });
 const base = process.env.TBT_RUNTIME_URL || 'http://localhost:8890';
 const routes = ['', 'classes/', 'sec-3-pure-g3-biology-tuition/', 'sec-4-pure-g3-biology-tuition/', 'testimonials/', 'notes/', 'celldivision/', 'about/', 'trees/', 'contact/', 'tuitionrecs/', 'intensivefix/'];
 try {
  for (const width of [1440, 768, 390, 320]) {
   await page.setViewportSize({width, height: 1000});
   let checked = 0;
   for (const route of routes) {
    await page.goto(`${base}/${route}`);
    await page.evaluate(() => document.fonts.ready);
    const rows = await page.locator('.tbt-tick:not(.tbt-programme-options__included .tbt-tick), .tbt-programme-options__grid li, .tbt-programme-exam li, .tbt-home-fit li, .tbt-notes-pain-list li').evaluateAll(items => items.map(e => {
     const s=getComputedStyle(e), icon=getComputedStyle(e,'::before');
     return {text:e.textContent.slice(0,40), top:parseFloat(icon.top), line:parseFloat(s.lineHeight), height:parseFloat(icon.height), gap:parseFloat(s.paddingLeft)-parseFloat(icon.left)-parseFloat(icon.width)};
    }));
    rows.forEach(r => {
     assert.ok(Math.abs(r.top + r.height / 2 - r.line / 2) < 1, `${route} ${width}: tick must align with first text line ${JSON.stringify(r)}`);
     assert.ok(r.gap >= 6 && r.gap <= 10, `${route} ${width}: tick stays close to phrase ${JSON.stringify(r)}`);
    });
    const quality = await page.locator('.tbt-home-quality li').evaluateAll(items => items.map(e => {
     const icon=e.querySelector('span').getBoundingClientRect(), text=e.querySelector('p'), r=text.getBoundingClientRect();
     return {delta: icon.top+icon.height/2-r.top-parseFloat(getComputedStyle(text).lineHeight)/2, gap:r.left-icon.right};
    }));
    quality.forEach(r => assert.ok(Math.abs(r.delta) < 1 && r.gap === 8, `${route} ${width}: circular tick aligns with text`));
    checked += rows.length + quality.length;
    assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), `${route} ${width}: no overflow`);
   }
   assert.ok(checked > 40);
   console.log(`PASS tick alignment ${width}px: ${checked} rows across all 12 pages`);
  }
 } finally { await browser.close(); }
})().catch(e=>{console.error(e);process.exit(1)});
