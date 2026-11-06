// Free Bio Notes email form (Contact Form 7 ready): a clear, accessible, brand-styled form sits under
// "Simply Enter Your Email Below", it fits every screen, and a successful Contact Form 7 send returns to the homepage.
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
      const m = await page.evaluate(() => {
        const form = document.querySelector('.tbt-notes-form');
        if (!form) return null;
        const input = form.querySelector('input[type="email"]');
        const submit = form.querySelector('.wpcf7-submit');
        const ir = input.getBoundingClientRect();
        const sr = submit.getBoundingClientRect();
        const heading = document.querySelector('[data-section="access-intro"] h2').getBoundingClientRect();
        const instructions = document.querySelector('.tbt-resource-access-instructions').getBoundingClientRect();
        const fr = form.getBoundingClientRect();
        const ss = getComputedStyle(submit);
        input.focus();
        const focus = getComputedStyle(input);
        return {
          inputHeight: ir.height, inputWidth: ir.width, inputRight: ir.right, submitHeight: sr.height, submitBg: ss.backgroundColor, submitFont: ss.fontFamily,
          label: !!input.closest('label') && input.closest('label').textContent.trim().length > 3, required: input.required,
          focusRing: focus.outlineStyle !== 'none' || focus.boxShadow !== 'none',
          formTop: fr.top, formBottom: fr.bottom, headingBottom: heading.bottom, instructionsTop: instructions.top,
          spacer: document.querySelectorAll('.tbt-resource-access-spacer').length,
          viewport: document.documentElement.clientWidth, overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
        };
      });
      assert.ok(m, `The email form must be on the page at ${width}px`);
      const at = `at ${width}px: ${JSON.stringify(m)}`;
      assert.ok(m.inputHeight >= 48 && m.inputWidth >= 240 && m.inputRight <= m.viewport - 8, `Email field must be roomy and on screen ${at}`);
      assert.ok(m.submitHeight >= 44 && m.submitBg === 'rgb(67, 180, 58)' && /^"?Poppins/.test(m.submitFont), `Submit must be a brand-green touch-sized button ${at}`);
      assert.ok(m.label && m.required, `Email field needs a visible label and required flag ${at}`);
      assert.ok(m.focusRing, `Email field needs a visible focus style ${at}`);
      assert.ok(m.formTop >= m.headingBottom && m.formBottom <= m.instructionsTop, `Form sits between the heading and the instructions ${at}`);
      assert.ok(m.instructionsTop - m.headingBottom <= 330, `No big blank gap around the form ${at}`);
      assert.equal(m.spacer, 0, `The blank placeholder must be gone ${at}`);
      assert.ok(m.overflow <= 0, `Horizontal overflow ${at}`);
      console.log(`PASS notes form ${width}`);
    }
    // A successful Contact Form 7 send (its wpcf7mailsent event) returns visitors to the homepage.
    await page.setViewportSize({ width: 1024, height: 800 });
    await page.goto(new URL('./notes/', base).href, { waitUntil: 'load' });
    const target = await page.locator('.tbt-notes-form').getAttribute('data-tbt-redirect');
    await page.evaluate(() => document.querySelector('.tbt-notes-form form').dispatchEvent(new CustomEvent('wpcf7mailsent', { bubbles: true, detail: {} })));
    await page.waitForURL((url) => url.href.replace(/\/?$/, '/') === target.replace(/\/?$/, '/'), { timeout: 8000 });
    console.log('PASS notes form redirects home after a successful send');
  } finally { await browser.close(); }
})().catch((e) => { console.error(e.message); process.exit(1); });
