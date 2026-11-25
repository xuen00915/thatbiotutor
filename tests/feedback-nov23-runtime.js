// Client feedback of 23 November ("Adcendes Website Editing (1)"): browser checks for sizes, crops and spacing.
const assert = require('node:assert/strict');
const { chromium } = require('playwright');
const base = (process.env.TBT_RUNTIME_URL || 'http://localhost:8888').replace(/\/?$/, '/');
const provider = 'https://12-dec-2025-updated-signup-form-02264.getresponsesite.com/';
const programmes = ['classes/', 'sec-3-pure-g3-biology-tuition/', 'sec-4-pure-g3-biology-tuition/'];

(async () => {
  const browser = await chromium.launch();
  try {
    const page = await browser.newPage();
    await page.route(provider, route => route.fulfill({ contentType: 'text/html', body: '<!doctype html><title>Signup provider fixture</title>' }));
    const sizes = selector => page.locator(selector).evaluateAll(els => els.filter(e => e.getBoundingClientRect().width > 0).map(e => {
      const r = e.getBoundingClientRect();
      return { w: Math.round(r.width), h: Math.round(r.height), font: getComputedStyle(e).fontSize, text: e.textContent.trim() };
    }));

    for (const width of [1440, 1024, 768, 430, 390]) {
      await page.setViewportSize({ width, height: 900 });

      // Every button on a programme page is the same size and type; no Book a Trial in the Hall of Fame.
      for (const route of programmes) {
        await page.goto(new URL(route, base).href);
        const buttons = await sizes('#main-content a:is(.tbt-programme-button, .tbt-programme-text-link)');
        assert.ok(buttons.length >= 3, `${route} has its buttons`);
        assert.equal(new Set(buttons.map(b => `${b.w}x${b.h} ${b.font}`)).size, 1, `${route} at ${width}: one button size, got ${JSON.stringify(buttons)}`);
        assert.ok(buttons.every(b => !/book a trial/i.test(b.text)), `${route}: no Book a Trial button`);
        if (route !== 'classes/') assert.equal(await page.locator('.tbt-programme-hall__action').count(), 1, `${route}: one Hall of Fame button`);
      }

      // Notes: four identical DOWNLOAD buttons with breathing room; no icon before the proof line.
      await page.goto(new URL('notes/', base).href);
      const downloads = await sizes('#main-content a.tbt-resource-button');
      assert.equal(downloads.length, 4);
      assert.equal(new Set(downloads.map(b => `${b.w}x${b.h} ${b.font}`)).size, 1, `Notes at ${width}: one DOWNLOAD size, got ${JSON.stringify(downloads)}`);
      assert.equal(await page.locator('.tbt-notes-hero .tbt-resource-proof svg').count(), 0, 'No stray icon before "Join over…"');
      const room = await page.evaluate(() => {
        const r = e => e.getBoundingClientRect();
        const hero = document.querySelector('.tbt-notes-hero__download'), lead = document.querySelector('.tbt-notes-hero__lead');
        const card = document.querySelector('.tbt-notes-download > .tbt-resource-button'), list = document.querySelector('.tbt-notes-download ul');
        const end = document.querySelector('.tbt-notes-end-button'), panel = end.previousElementSibling, footer = document.querySelector('footer');
        const benefits = document.querySelector('.tbt-notes-benefits'), h2 = benefits.querySelector('h2'), grid = benefits.querySelector('.tbt-notes-benefits__grid');
        return {
          heroAbove: r(hero).top - r(lead).bottom, cardAbove: r(card).top - r(list).bottom, cardBelow: r(card.parentElement).bottom - r(card).bottom,
          endAbove: r(end).top - r(panel).bottom, endBelow: r(footer).top - r(end).bottom,
          headingAbove: r(h2).top - r(benefits).top, headingBelow: r(grid).top - r(h2).bottom,
        };
      });
      assert.ok(room.heroAbove >= 20, `Notes hero button keeps its compact 20px+ gap at ${width}: ${room.heroAbove}px`);
      assert.ok(room.cardAbove >= 16 && room.cardAbove <= 24 && room.cardBelow === 24, `Compact download card at ${width}: ${JSON.stringify(room)}`);
      for (const key of ['endAbove', 'endBelow']) assert.ok(room[key] >= 28, `Notes ${key} at ${width}: ${room[key]}px`);
      assert.ok(room.headingAbove >= (width <= 390 ? 32 : 40) && room.headingBelow <= 32 && room.headingAbove > room.headingBelow, `"ThatBioTutor's Notes" belongs to its cards at ${width}: ${JSON.stringify(room)}`);

      // The signup iframe stops before the provider's dark footer strip (measured heights per form width).
      const form = await page.locator('.tbt-notes-form__embed').evaluate(e => { const r = e.getBoundingClientRect(); return { w: r.width, h: r.height }; });
      const expected = form.w > 610 ? 440 : form.w > 426 ? 449 : form.w > 362 ? 468 : form.w > 360 ? 488 : form.w > 340 ? 508 : 528;
      assert.equal(await page.locator('.tbt-notes-form__embed').getAttribute('scrolling'), 'no', 'Signup frame never scrolls (Submit is fully shown); forms remain cropped');
      assert.equal(Math.round(form.h), expected, `Signup iframe height at form width ${form.w}`);

      // Classes: dark green heading accent and four numbers.
      await page.goto(new URL('classes/', base).href);
      assert.equal(await page.locator('.tbt-programme-difference h2 span').evaluate(e => getComputedStyle(e).color), 'rgb(47, 138, 39)');
      await page.locator('.tbt-programme-results').scrollIntoViewIfNeeded();
      await page.waitForFunction(() => [...document.querySelectorAll('.tbt-programme-results [data-count-up]')].every(e => e.dataset.countComplete === 'true'), null, { timeout: 8000 });
      assert.deepEqual(await page.locator('.tbt-programme-results .tbt-programme-stats strong').allTextContents(), ['2.3', '912+', '91%', '1 in 2']);

      // Testimonials year selector: "Class of <year>", single spaces.
      await page.goto(new URL('testimonials/', base).href);
      const label = await page.locator('.tbt-year-nav__toggle').evaluate(e => e.textContent);
      assert.match(label.replace(/\s+/g, ' ').trim(), /^☰ ?Class of \d{4} ?⌄$/);
      assert.ok(!/ {2}/.test(await page.locator('[data-year-current]').textContent()), 'No doubled spaces in the year label');
    }

    // Phones and tablets crop the programme heroes towards the teacher and students, not the whiteboard.
    await page.setViewportSize({ width: 390, height: 844 });
    for (const [route, position] of [['classes/', '5% 50%'], ['sec-3-pure-g3-biology-tuition/', '22% 50%'], ['sec-4-pure-g3-biology-tuition/', '5% 50%']]) {
      await page.goto(new URL(route, base).href);
      assert.equal(await page.locator('.tbt-programme-hero__background img').evaluate(e => getComputedStyle(e).objectPosition), position, `${route} phone hero crop`);
    }
    console.log('PASS 23 November feedback runtime');
  } finally {
    await browser.close();
  }
})().catch(error => { console.error(error); process.exit(1); });
