const { chromium } = require('playwright');
const assert = require('node:assert/strict');
(async () => {
  const browser = await chromium.launch();
  const base = process.env.TBT_RUNTIME_URL || 'http://localhost:8890';
  try {
    for (const reducedMotion of ['no-preference', 'reduce']) {
      const page = await browser.newPage({ reducedMotion });
      for (const route of ['', 'notes/', 'celldivision/', 'about/', 'sec-3-pure-g3-biology-tuition/', 'sec-4-pure-g3-biology-tuition/', 'intensivefix/']) {
        await page.goto(`${base}/${route}`);
        const carousels = page.locator('[data-carousel]');
        for (let n = 0; n < await carousels.count(); n++) {
          const carousel = carousels.nth(n);
          await carousel.dispatchEvent('mouseenter');
          const duration = await carousel.locator('[data-carousel-slide]').first().evaluate(e => parseFloat(getComputedStyle(e).transitionDuration));
          assert.ok(reducedMotion === 'reduce' ? duration < .01 : duration >= .5, `${route}: appropriate fade duration`);
          await carousel.locator('[data-carousel-next]').evaluate(e => e.click());
          await page.waitForTimeout(reducedMotion === 'reduce' ? 30 : 150);
          const state = await carousel.evaluate(e => [...e.querySelectorAll('[data-carousel-slide]')].map(s => ({ active: s.classList.contains('is-active'), opacity: +getComputedStyle(s).opacity, hidden: s.hidden, inert: s.inert, aria: s.getAttribute('aria-hidden') })));
          assert.equal(state.filter(s => s.active).length, 1);
          assert.ok(state.every(s => !s.hidden && s.inert === !s.active && s.aria === String(!s.active)), `${route}: inactive slides stay rendered but inaccessible`);
          if (reducedMotion !== 'reduce') assert.ok(state.some(s => s.active && s.opacity > 0 && s.opacity < 1), `${route}: transition has intermediate frames`);
          await carousel.locator('[data-carousel-prev]').evaluate(e => { e.click(); e.click(); });
          await page.waitForTimeout(700);
          assert.equal(await carousel.locator('[data-carousel-slide].is-active').count(), 1, 'Rapid controls settle on one slide');
        }
        console.log(`PASS carousel motion ${route || 'home'} ${reducedMotion}`);
      }
      await page.close();
    }
  } finally { await browser.close(); }
})().catch(e => { console.error(e); process.exit(1); });
