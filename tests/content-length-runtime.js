const assert = require('node:assert/strict');
const { chromium } = require('playwright');

// Long review pages stay readable: tight spacing, a short first view, and every review still on the page.
const base = (process.env.TBT_RUNTIME_URL || 'http://localhost:8888').replace(/\/?$/, '/');
const pages = [
  { route: 'testimonials/', reviews: 135, maxHeight: 32000 },
  { route: 'notes/', reviews: 227, maxHeight: 14000 },
  { route: 'celldivision/', reviews: 15, maxHeight: 9000 },
];

(async () => {
  const browser = await chromium.launch();
  try {
    const page = await browser.newPage();
    page.setDefaultTimeout(10000);
    for (const width of [1440, 768, 390]) {
      await page.setViewportSize({ width, height: 900 });
      for (const spec of pages) {
        await page.goto(new URL(spec.route, base).href, { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(400);
        const tag = `${spec.route} @${width}`;
        const state = await page.evaluate(() => ({
          total: document.querySelectorAll('.tbt-testimonial').length,
          visible: [...document.querySelectorAll('.tbt-testimonial')].filter((e) => { const panel = e.closest('[data-review-panel] [data-testimonial-list]'); /* a scrolling panel clips what is below its height */ return !e.hidden && e.getBoundingClientRect().height > 0 && (!panel || e.getBoundingClientRect().top < panel.getBoundingClientRect().bottom - 1); }).length,
          height: document.documentElement.scrollHeight,
          buttons: [...document.querySelectorAll('[data-reviews-more]')].map((b) => b.textContent.trim()),
          panels: document.querySelectorAll('[data-review-panel]').length, // review panels scroll on their own instead of using show-more buttons
        }));
        assert.ok(state.total >= spec.reviews, `${tag}: every review must stay on the page (${state.total} of at least ${spec.reviews})`);
        assert.ok(state.visible < state.total || spec.reviews <= 20, `${tag}: long lists must start collapsed`);
        assert.ok(state.height <= spec.maxHeight * (width < 800 ? 1.6 : 1), `${tag}: page is still ${state.height}px tall`);
        if (spec.reviews > 20 && !state.panels) assert.ok(state.buttons.length >= 1 && /more/i.test(state.buttons[0]), `${tag}: a "show more" control is required`);
      }
    }

    // The control reveals more reviews, keeps the count right, and removes itself when everything is shown.
    await page.setViewportSize({ width: 1440, height: 900 });
    await page.goto(new URL('testimonials/', base).href, { waitUntil: 'domcontentloaded' });
    const colours = await page.evaluate(() => ({
      more: getComputedStyle(document.querySelector('[data-reviews-more]')).backgroundColor,
      all: getComputedStyle(document.querySelector('[data-reviews-all]')).borderTopColor,
    }));
    assert.equal(colours.more, 'rgb(14, 55, 29)', 'Show more reviews is forest green, not the bright action green');
    assert.equal(colours.all, 'rgb(14, 55, 29)', 'Show all reviews is outlined in forest green');
    const first = page.locator('[data-reviews-more]').first();
    const before = await page.locator('.tbt-testimonial:not([hidden])').count();
    await first.click();
    const after = await page.locator('.tbt-testimonial:not([hidden])').count();
    assert.ok(after > before, 'Show more must reveal more reviews');
    assert.equal(await first.getAttribute('aria-controls') !== null, true, 'Show more must point at its list for assistive tech');
    const showAll = page.locator('#class-of-2024 [data-reviews-all]');
    await showAll.scrollIntoViewIfNeeded();
    await showAll.click();
    assert.equal(await page.locator('#class-of-2024 .tbt-testimonial[hidden]').count(), 0, 'Show all must reveal every review in that list');
    assert.equal(await page.locator('#class-of-2024 .tbt-reviews-controls').evaluate((e) => e.hidden), true, 'Controls hide when the list is fully shown');
    console.log('PASS: long review pages are compact, collapsible and keep every review');
  } finally {
    await browser.close();
  }
})().catch((error) => { console.error(error); process.exit(1); });
