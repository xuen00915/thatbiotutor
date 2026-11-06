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
          cards: Array.from(document.querySelectorAll('.tbt-notes-benefit')).map((c) => { const r = c.getBoundingClientRect(); return { left: Math.round(r.left), right: Math.round(r.right), top: Math.round(r.top), bottom: Math.round(r.bottom) }; }),
          downloadButton: (() => { const b = sec('download').querySelector('.tbt-resource-button').getBoundingClientRect(); const h = sec('download').querySelector('h2').getBoundingClientRect(); return { center: (b.left + b.right) / 2, shellCenter: h.left + h.width / 2 }; })(),
          endButton: (() => { const sectionEl = sec('reviews-bottom'); const b = sectionEl.querySelector('.tbt-resource-button'); if (!b) return null; const r = b.getBoundingClientRect(); const last = Array.from(sectionEl.querySelectorAll('.tbt-testimonial')).filter((e) => e.getBoundingClientRect().height > 0).pop(); return { text: b.textContent.trim(), href: b.getAttribute('href'), center: (r.left + r.right) / 2, height: r.height, below: !last || r.top >= last.getBoundingClientRect().bottom - 1, sectionBottomGap: sectionEl.getBoundingClientRect().bottom - r.bottom }; })(),
          darkHeads: Array.from(document.querySelectorAll('.tbt-notes-details h2')).map((h) => { const c = getComputedStyle(h); return { size: parseFloat(c.fontSize), weight: parseInt(c.fontWeight, 10) }; }),
          answerStyle: getComputedStyle(sec('fit').querySelector('.tbt-notes-fit__answer')).fontStyle,
          answerSynthesis: getComputedStyle(sec('fit').querySelector('.tbt-notes-fit__answer')).fontSynthesis,
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
      assert.ok(Math.abs(m.downloadButton.center - m.downloadButton.shellCenter) <= 2, `The DOWNLOAD button in the dark block must be centred ${at}`);
      assert.ok(m.endButton, `A DOWNLOAD button must close the last review section ${at}`);
      assert.equal(m.endButton.text, 'DOWNLOAD', `Closing button text ${at}`);
      assert.equal(m.endButton.href, '#notes-download', `Closing button must lead to the download block ${at}`);
      assert.ok(Math.abs(m.endButton.center - m.viewport / 2) <= 2 && m.endButton.below && m.endButton.height >= 44 && m.endButton.sectionBottomGap <= 90, `Closing button is centred, under the reviews and not floating far from the footer ${at}`);
      assert.equal(m.darkHeads.length, 3, `Three dark-block headings ${at}`);
      m.darkHeads.forEach((h) => assert.ok(h.weight <= 700 && h.size <= (width >= 769 ? 30 : 24), `Dark-block headings must be light, not extra bold at 36px ${at}`));
      assert.equal(m.answerStyle, 'italic', `The "If you answered yes" line must be italic ${at}`);
      assert.ok(m.answerSynthesis.includes('style'), `Italic must really be drawn (font synthesis for style) ${at}`);
      assert.equal(m.cards.length, 4, `Four benefit cards ${at}`);
      const [c1, c2, c3, c4] = m.cards;
      if (width >= 641) {
        assert.ok(Math.abs(c1.top - c2.top) <= 2 && c2.left >= c1.right && Math.abs(c3.top - c4.top) <= 2 && c4.left >= c3.right, `Benefit cards must sit two to a row ${at}`);
        assert.ok(c3.top >= Math.max(c1.bottom, c2.bottom) - 1, `Second row of benefit cards sits below the first ${at}`);
      } else {
        assert.ok(c2.top >= c1.bottom - 1 && c3.top >= c2.bottom - 1 && c4.top >= c3.bottom - 1, `Benefit cards stack on small phones ${at}`);
      }
      assert.ok(m.overflow <= 0, `Horizontal overflow ${at}`);
      console.log(`PASS notes sections ${width}`);
    }
  } finally { await browser.close(); }
})().catch((e) => { console.error(e.message); process.exit(1); });
