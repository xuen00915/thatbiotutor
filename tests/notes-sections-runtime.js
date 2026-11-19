// Free Bio Notes (/notes/): the "Hey G3/Pure Bio Student, Is This You?" title is a contained, centred panel (not edge to edge),
// and the sections around it plus the "Your Questions, Answered" cards (why free / catch / download) keep a compact rhythm.
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
        const h2Top = (name) => (sec(name).querySelector('h2') || sec(name).querySelector('h3')).getBoundingClientRect().top;
        const bar = sec('fit').querySelector('h2');
        const br = bar.getBoundingClientRect();
        const shell = sec('reviews-top').querySelector('h2').getBoundingClientRect();
        const cs = getComputedStyle(bar);
        return {
          viewport: document.documentElement.clientWidth,
          barLeft: br.left, barWidth: br.width, shellWidth: shell.width, radius: parseFloat(cs.borderTopLeftRadius), textAlign: cs.textAlign,
          fitToBenefits: h2Top('benefits') - bottomOf(sec('fit')),
          cardRects: ['why-free', 'catch', 'download'].map((name) => { const r = sec(name).getBoundingClientRect(); return { left: Math.round(r.left), right: Math.round(r.right), top: Math.round(r.top), bottom: Math.round(r.bottom) }; }),
          cards: Array.from(document.querySelectorAll('.tbt-notes-benefit')).map((c) => { const r = c.getBoundingClientRect(); return { left: Math.round(r.left), right: Math.round(r.right), top: Math.round(r.top), bottom: Math.round(r.bottom) }; }),
          downloadButton: (() => { const b = sec('download').querySelector('.tbt-resource-button').getBoundingClientRect(); const h = sec('download').querySelector('.tbt-notes-card__body').getBoundingClientRect(); return { center: (b.left + b.right) / 2, shellCenter: h.left + h.width / 2 }; })(),
          endButton: (() => { const sectionEl = sec('reviews-bottom'); const b = sectionEl.querySelector('.tbt-resource-button'); if (!b) return null; const r = b.getBoundingClientRect(); const panel = sectionEl.querySelector('[data-testimonial-list]').getBoundingClientRect(); return { text: b.textContent.trim(), href: b.getAttribute('href'), center: (r.left + r.right) / 2, height: r.height, below: r.top >= panel.bottom - 1, sectionBottomGap: sectionEl.getBoundingClientRect().bottom - r.bottom, band: !!document.querySelector('.tbt-notes-end-cta') }; })(),
          darkHeads: Array.from(document.querySelectorAll('.tbt-notes-details h2')).map((h) => { const c = getComputedStyle(h); return { size: parseFloat(c.fontSize), weight: parseInt(c.fontWeight, 10) }; }),
          cardHeads: Array.from(document.querySelectorAll('.tbt-notes-details h3')).map((h) => parseInt(getComputedStyle(h).fontWeight, 10)),
          answerBold: !!sec('fit').querySelector('.tbt-notes-fit__answer strong'),
          answerStyle: getComputedStyle(sec('fit').querySelector('.tbt-notes-fit__answer')).fontStyle,
          answerSynthesis: getComputedStyle(sec('fit').querySelector('.tbt-notes-fit__answer')).fontSynthesis,
          overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
        };
      });
      const at = `at ${width}px: ${JSON.stringify(m)}`;
      assert.ok(m.barLeft >= 8 && m.barWidth <= m.viewport - 16, `Title panel must not run edge to edge ${at}`);
      assert.ok(m.barWidth <= m.shellWidth + 1 && Math.abs(m.barLeft + m.barWidth / 2 - m.viewport / 2) <= 2, `Title panel is centred and no wider than the page content ${at}`);
      assert.ok(m.radius >= 16, `Title panel must have rounded corners like the other title bars ${at}`);
      assert.equal(m.textAlign, 'center', `Title must be centred in its panel ${at}`);
      assert.ok(m.fitToBenefits <= (width >= 769 ? 90 : 56), `Space between "is this you?" and "ThatBioTutor's Notes" is too long ${at}`);
      const [why, catchCard, dl] = m.cardRects;
      if (width >= 861) assert.ok(Math.abs(why.top - catchCard.top) <= 2 && catchCard.left >= why.right && dl.top - Math.max(why.bottom, catchCard.bottom) <= 40, `Why-free and catch cards sit side by side with the download card below ${at}`);
      else assert.ok(catchCard.top >= why.bottom && dl.top >= catchCard.bottom && catchCard.top - why.bottom <= 40 && dl.top - catchCard.bottom <= 40, `Cards stack with a tight gap on narrow screens ${at}`);
      assert.ok(Math.abs(m.downloadButton.center - m.downloadButton.shellCenter) <= 2, `The DOWNLOAD button in the dark block must be centred ${at}`);
      assert.ok(m.endButton, `A DOWNLOAD button must close the last review section ${at}`);
      assert.equal(m.endButton.text, 'DOWNLOAD', `Closing button text ${at}`);
      assert.ok(m.endButton.href.endsWith('#notes-form'), `Closing button must lead to the email form ${at}`); // the static export may prefix the page name
      assert.ok(Math.abs(m.endButton.center - m.viewport / 2) <= 2 && m.endButton.below && m.endButton.height >= 44 && m.endButton.sectionBottomGap <= 90, `Closing button is centred, under the review panel and close to the footer ${at}`);
      assert.equal(m.endButton.band, false, `No extra phrase band around the closing button ${at}`);
      assert.equal(m.darkHeads.length, 1, `One "Your Questions, Answered" heading above the cards ${at}`);
      m.darkHeads.forEach((h) => assert.ok(h.weight === 800 && h.size === (width <= 520 ? 30 : 36), `The heading uses the shared section-heading style (Poppins 800, 36px / 30px on phones) ${at}`));
      assert.equal(m.cardHeads.length, 3, `Three card headings ${at}`);
      m.cardHeads.forEach((w) => assert.ok(w >= 700, `Card headings are bold ${at}`));
      assert.equal(m.answerStyle, 'normal', `The "If you answered yes" block is no longer italic ${at}`);
      assert.ok(m.answerBold, `The first sentence of the closing message is bold ${at}`);
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
