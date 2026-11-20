// Free Bio Notes (/notes/): every review sits in a panel that scrolls on its own and opens showing three reviews on phones and four on larger screens.
const assert = require('node:assert/strict');
const { chromium } = require('playwright');
const base = (process.env.TBT_RUNTIME_URL || 'http://localhost:8888').replace(/\/?$/, '/');
(async () => {
  const browser = await chromium.launch();
  try {
    const page = await browser.newPage();
    page.setDefaultTimeout(10000);
    for (const [width, height] of [[1440,900], [1250,900], [768,900], [390,900], [320,900], [390,640], [320,568]]) {
      await page.setViewportSize({ width, height });
      await page.goto(new URL('./notes/', base).href, { waitUntil: 'load' });
      await page.evaluate(() => document.fonts?.ready);
      await page.waitForTimeout(500);
      for (const name of ['reviews-top', 'reviews-bottom']) {
        const m = await page.evaluate((sectionName) => {
          const section = document.querySelector(`.tbt-notes-page [data-section="${sectionName}"]`);
          const panel = section.querySelector('[data-review-panel] [data-testimonial-list]');
          if (!panel) return null;
          const cs = getComputedStyle(panel);
          const items = Array.from(panel.querySelectorAll(':scope > .tbt-testimonial'));
          const pr = panel.getBoundingClientRect();
          const visible = innerWidth <= 640 ? 3 : 4;
          const rect = (i) => items[i].getBoundingClientRect();
          const squashed = items.slice(0, 8).filter((item) => item.scrollHeight > item.clientHeight + 2 || item.getBoundingClientRect().height < 50).length;
          const overlapping = items.slice(0, 8).filter((item, i) => i > 0 && item.getBoundingClientRect().top < items[i - 1].getBoundingClientRect().bottom - 1).length;
          const before = { panelTop: panel.scrollTop, page: window.scrollY };
          panel.scrollTop = 400;
          const after = { panelTop: panel.scrollTop, page: window.scrollY };
          panel.scrollTop = 0;
          return {
            squashed, overlapping, overflowY: cs.overflowY, scrollable: panel.scrollHeight > panel.clientHeight + 20, total: items.length, hidden: items.filter((i) => i.hidden).length,
            lastBottomInside: rect(visible - 1).bottom <= pr.bottom + 1, nextVisible: Math.max(0, pr.bottom - rect(visible).top), panelHeight: pr.height, firstTopInside: rect(0).top >= pr.top - 1,
            controls: section.querySelectorAll('.tbt-reviews-controls').length, tabindex: panel.getAttribute('tabindex'), role: panel.getAttribute('role'), label: panel.getAttribute('aria-label'),
            movedPanel: after.panelTop > before.panelTop, movedPage: after.page !== before.page,
            panelLeft: pr.left, panelRight: pr.right, viewport: document.documentElement.clientWidth, border: cs.borderTopWidth, radius: parseFloat(cs.borderTopLeftRadius),
          };
        }, name);
        assert.ok(m, `${name}: reviews must be inside a panel at ${width}px`);
        const at = `${name} at ${width}px: ${JSON.stringify(m)}`;
        assert.equal(m.squashed, 0, `Reviews must keep their full height (not squashed and overlapping) ${at}`);
        assert.equal(m.overlapping, 0, `Reviews must not overlap ${at}`);
        assert.ok(m.panelHeight >= 240, `Panel must keep readable reviews ${at}`);
        assert.ok(['auto', 'scroll'].includes(m.overflowY) && m.scrollable, `Panel must scroll on its own ${at}`);
        assert.ok(m.total >= 5 && m.hidden === 0, `Every review stays in the panel, none hidden ${at}`);
        assert.equal(m.controls, 0, `No show-more buttons beside the panel ${at}`);
        assert.ok(m.firstTopInside && (width<=640 || m.lastBottomInside) && m.nextVisible <= 12, `The panel opens on at most the requested number of reviews ${at}`);
        if(width<=640) assert.ok(m.panelHeight<=480&&m.panelHeight<=height*0.6+1,'Mobile panels stay within a compact viewport limit');
        assert.ok(m.movedPanel && !m.movedPage, `Scrolling the panel must not scroll the page ${at}`);
        assert.ok(m.tabindex === '0' && m.role === 'region' && m.label, `Panel must be keyboard-scrollable and labelled ${at}`);
        assert.ok(m.panelLeft >= 0 && m.panelRight <= m.viewport && m.border !== '0px' && m.radius >= 12, `Panel looks like a card inside the page ${at}`);
        console.log(`PASS review panel ${name} ${width}`);
      }
    }
  } finally { await browser.close(); }
})().catch((e) => { console.error(e.message); process.exit(1); });
