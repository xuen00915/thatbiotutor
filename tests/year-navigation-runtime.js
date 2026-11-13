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
      const toggle = nav.locator('button');
      const panel = nav.locator('nav');
      assert.equal(await toggle.getAttribute('aria-expanded'), 'false');
      assert.equal(await panel.isVisible(), false);
      assert.equal(await nav.locator('a').count(), 5);
      await toggle.click();
      assert.equal(await panel.isVisible(), true);
      const geometry = await nav.evaluate(e => {
        const panel = e.querySelector('nav').getBoundingClientRect();
        const button = e.querySelector('button').getBoundingClientRect();
        const wa = document.querySelector('.whatsapp-float').getBoundingClientRect();
        return { left: panel.left, right: panel.right, top: panel.top, bottom: panel.bottom, buttonTop: button.top, width: panel.width, waLeft: wa.left, z: +getComputedStyle(e).zIndex, headerZ: +getComputedStyle(document.querySelector('.site-header')).zIndex, overflow: document.documentElement.scrollWidth - innerWidth };
      });
      assert.ok(geometry.width >= 180 && geometry.width <= 200);
      assert.ok(geometry.left >= 0 && geometry.right <= width && geometry.top >= 0 && geometry.bottom <= 900);
      assert.equal(geometry.overflow, 0);
      assert.ok(geometry.z < geometry.headerZ, 'Keep primary navigation above year panel');
      if (width < 1440) assert.ok(geometry.bottom <= geometry.buttonTop && geometry.right < geometry.waLeft, 'Menu opens upwards and stays separate from WhatsApp');
      await page.keyboard.press('Escape');
      assert.equal(await toggle.getAttribute('aria-expanded'), 'false');
      assert.equal(await toggle.evaluate(e => e === document.activeElement), true);
      await toggle.press('Enter');
      await page.keyboard.press('Tab');
      assert.equal(await nav.locator('a').first().evaluate(e => e === document.activeElement), true, 'Year links are keyboard reachable');
      await nav.locator('a[href$="#class-of-2022"]').click();
      await page.waitForFunction(() => document.querySelector('[data-year-current]').textContent === '2022');
      assert.equal(await toggle.getAttribute('aria-expanded'), 'false');
      assert.equal(await nav.locator('[aria-current="location"]').textContent(), 'Class of 2022');
      const top = await page.locator('#class-of-2022').evaluate(e => ({ top: e.getBoundingClientRect().top, header: document.querySelector('.site-header').getBoundingClientRect().bottom }));
      assert.ok(top.top >= top.header && top.top <= top.header + 24, 'Selected year clears sticky header');
      // Manual scrolling updates the current year, including while inside a long section.
      const manualDelta = await page.locator('#class-of-2024').evaluate(e => e.getBoundingClientRect().top + 200);
      await page.mouse.move(width / 2, 450);
      await page.mouse.wheel(0, manualDelta); // a reader scrolling with the wheel
      await page.waitForFunction(() => document.querySelector('[data-year-current]').textContent === '2024');
      await toggle.click();
      await page.locator('.site-brand').click();
      await page.waitForURL(url => !url.pathname.includes('testimonials'));
      assert.equal(await page.locator('[data-year-nav]').count(), 0, 'Navigator exists only on Testimonials');
      await page.goto(new URL('testimonials/', base).href);
      await page.locator('[data-year-nav] button').click();
      await page.locator('.tbt-testimonials-hero h1').click();
      assert.equal(await page.locator('[data-year-nav] button').getAttribute('aria-expanded'), 'false', 'Outside click closes panel');
      console.log(`PASS year navigation ${width}`);
    }
    assert.deepEqual(errors, []);
    // Motion preference is respected by both the disclosure and scroll action.
    await page.goto(new URL('testimonials/', base).href);
    assert.equal(await page.locator('[data-year-nav] nav').evaluate(e => getComputedStyle(e).animationName), 'none');
    await browser.close();
  } finally { if (browser.isConnected()) await browser.close(); }
})().catch(e => { console.error(e); process.exit(1); });
