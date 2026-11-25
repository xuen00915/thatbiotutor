const assert = require('node:assert/strict');
const { chromium } = require('playwright');
const base = (process.env.TBT_RUNTIME_URL || 'http://localhost:8890').replace(/\/?$/, '/');
(async () => {
  const browser = await chromium.launch();
  try {
    const page = await browser.newPage({ reducedMotion: 'reduce' });
    const errors = [];
    page.on('pageerror', (error) => errors.push(error.message));
    for (const width of [1920, 1440, 1280, 1024, 768, 390, 320]) {
      await page.setViewportSize({ width, height: 900 });
      await page.goto(new URL('testimonials/', base).href, { waitUntil: 'load' });
      const nav = page.locator('[data-year-nav]');
      assert.equal(await nav.count(), 1, 'One floating year navigator');
      const toggle = nav.locator('button.tbt-year-nav__toggle');
      const panel = nav.locator('nav');
      assert.equal(await toggle.getAttribute('aria-expanded'), 'false');
      assert.equal(await panel.isVisible(), false);
      assert.equal(await nav.locator('a.tbt-year-nav__year').count(), 5);
      assert.ok(await nav.locator('a.tbt-year-nav__section').count() >= 14, 'Each year lists the parts of its section');
      const obscuresHero = await toggle.evaluate(button => {
        const a = button.getBoundingClientRect();
        return [...document.querySelectorAll('.tbt-testimonials-hero h1, .tbt-testimonials-hero p')].some(e => {
          const b = e.getBoundingClientRect();
          return a.left < b.right && a.right > b.left && a.top < b.bottom && a.bottom > b.top;
        });
      });
      assert.equal(obscuresHero, false, `Year menu must not cover hero copy at ${width}px`);
      await toggle.click();
      assert.equal(await panel.isVisible(), true);
      const geometry = await nav.evaluate(e => {
        const panel = e.querySelector('nav').getBoundingClientRect();
        const button = e.querySelector('button').getBoundingClientRect();
        const wa = document.querySelector('.whatsapp-float').getBoundingClientRect();
        return { left: panel.left, right: panel.right, top: panel.top, bottom: panel.bottom, buttonTop: button.top, buttonBottom: button.bottom, headerBottom: document.querySelector('.site-header').getBoundingClientRect().bottom, width: panel.width, waLeft: wa.left, z: +getComputedStyle(e).zIndex, headerZ: +getComputedStyle(document.querySelector('.site-header')).zIndex, overflow: document.documentElement.scrollWidth - innerWidth };
      });
      assert.ok(geometry.width >= 232 && geometry.width <= 272, `Year panel is wide enough for the detail lines: ${geometry.width}`);
      assert.ok(geometry.left >= 0 && geometry.right <= width && geometry.top >= 0 && geometry.bottom <= 900);
      assert.equal(geometry.overflow, 0);
      assert.ok(geometry.z < geometry.headerZ, 'Keep primary navigation above year panel');
      if (width <= 1180) assert.ok(geometry.buttonTop >= geometry.headerBottom && geometry.top >= geometry.buttonBottom, 'Mobile year panel opens below the header');
      if (width > 1180 && width < 1440) assert.ok(geometry.bottom <= geometry.buttonTop && geometry.right < geometry.waLeft, 'Menu opens upwards and stays separate from WhatsApp');
      await page.keyboard.press('Escape');
      assert.equal(await toggle.getAttribute('aria-expanded'), 'false');
      assert.equal(await toggle.evaluate(e => e === document.activeElement), true);
      await toggle.press('Enter');
      await page.keyboard.press('Tab');
      assert.equal(await nav.locator('a').first().evaluate(e => e === document.activeElement), true, 'Year links are keyboard reachable');
      await nav.locator('a.tbt-year-nav__year[href$="#class-of-2022"]').click();
      await page.waitForFunction(() => document.querySelector('[data-year-current]').textContent === 'Class of 2022');
      assert.equal(await toggle.getAttribute('aria-expanded'), 'false');
      assert.equal(await nav.locator('[aria-current="location"] .tbt-year-nav__title').textContent(), 'Class of 2022');
      assert.equal(await nav.locator('.tbt-year-nav__group[data-open="true"]').count(),1,'Changing active year collapses the previous year');
      assert.equal(await nav.locator('[data-open="true"] .tbt-year-nav__title').textContent(),'Class of 2022');
      const bar = () => innerWidth <= 1180 ? document.querySelector('[data-year-nav]').getBoundingClientRect().bottom : document.querySelector('.site-header').getBoundingClientRect().bottom;
      const top = await page.locator('#class-of-2022 h2').evaluate((e, b) => ({ top: e.getBoundingClientRect().top, header: new Function('return (' + b + ')()')() }), bar.toString());
      assert.ok(Math.abs(top.top - (top.header + 12)) <= 2, `Selected year's title sits at the top, 12px under the sticky bars at ${width}: ${JSON.stringify(top)}`);
      await toggle.click();
      const reviewsGroup = nav.locator('.tbt-year-nav__group[data-open="true"]');
      await reviewsGroup.locator('a.tbt-year-nav__section[href$="#class-of-2022-google-reviews"]').click();
      await page.waitForTimeout(1200);
      const part = await page.locator('#class-of-2022-google-reviews').evaluate((e, b) => ({ top: e.getBoundingClientRect().top, header: new Function('return (' + b + ')()')() }), bar.toString());
      assert.ok(Math.abs(part.top - (part.header + 12)) <= 2, `Google reviews heading sits at the top, 12px under the sticky bars at ${width}: ${JSON.stringify(part)}`);
      const spacing = await page.locator('.tbt-testimonials-year').evaluateAll(es=>es.map(e=>({id:e.id,gap:parseFloat(getComputedStyle(e).paddingTop)+parseFloat(getComputedStyle(e.querySelector('.tbt-testimonials-year-panel')).marginTop)})));
      assert.ok(spacing.every(s=>s.gap === (s.id==='class-of-2021'?(width>768?32:24):(width>768?56:40))),'All year titles have consistent separation from the preceding section');
      await page.evaluate(()=>window.scrollBy(0,400));
      const sticky = await page.locator('#class-of-2022 .tbt-testimonials-year-panel').evaluate(e=>({top:e.getBoundingClientRect().top,position:getComputedStyle(e).position,clearance:(innerWidth<=1180?document.querySelector('[data-year-nav]'):document.querySelector('.site-header')).getBoundingClientRect().bottom}));
      assert.equal(sticky.position,'static','Year titles scroll naturally with their content');
      assert.ok(sticky.top<sticky.clearance-200,'Year title leaves the upper screen when scrolling down');
      // Manual scrolling updates the current year, including while inside a long section.
      const manualDelta = await page.locator('#class-of-2024').evaluate(e => e.getBoundingClientRect().top + 200);
      await page.mouse.move(width / 2, 450);
      await page.mouse.wheel(0, manualDelta); // a reader scrolling with the wheel
      await page.waitForFunction(() => document.querySelector('[data-year-current]').textContent === 'Class of 2024');
      await toggle.click();
      await page.locator('.site-brand').click();
      await page.waitForURL(url => !url.pathname.includes('testimonials'));
      assert.equal(await page.locator('[data-year-nav]').count(), 0, 'Navigator exists only on Testimonials');
      await page.goto(new URL('testimonials/', base).href);
      await page.locator('[data-year-nav] button.tbt-year-nav__toggle').click();
      await page.mouse.click(4, 180); // outside the downward panel, without clicking covered content
      assert.equal(await page.locator('[data-year-nav] button.tbt-year-nav__toggle').getAttribute('aria-expanded'), 'false', 'Outside click closes panel');
      console.log(`PASS year navigation ${width}`);
    }
    assert.deepEqual(errors, []);
    // Motion preference is respected by both the disclosure and scroll action.
    await page.goto(new URL('testimonials/', base).href);
    assert.equal(await page.locator('[data-year-nav] nav').evaluate(e => getComputedStyle(e).animationName), 'none');
    await browser.close();
  } finally { if (browser.isConnected()) await browser.close(); }
})().catch(e => { console.error(e); process.exit(1); });
