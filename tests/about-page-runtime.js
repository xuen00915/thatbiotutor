// About (/about/) keeps the original page look: Montserrat, 65px hero heading,
// a light 78% hero photo, a 321px hero and 22px reading text (width stays the shared content width).
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
      await page.goto(new URL('./about/', base).href, { waitUntil: 'load' });
      await page.evaluate(() => document.fonts?.ready);
      await page.waitForTimeout(900); // let the image fade-in finish
      const m = await page.evaluate(() => {
        const cs = (selector) => getComputedStyle(document.querySelector(selector));
        const size = (selector) => parseFloat(cs(selector).fontSize);
        return {
          heroHeight: document.querySelector('.tbt-about-hero').getBoundingClientRect().height,
          imageOpacity: parseFloat(cs('.tbt-about-hero__image').opacity),
          heroFont: cs('.tbt-about-hero h1').fontFamily,
          heroSize: size('.tbt-about-hero h1'),
          h3Size: size('.tbt-about-mission h3'),
          pSize: size('.tbt-about-mission .tbt-reading-width > p'),
          pFont: cs('.tbt-about-mission .tbt-reading-width > p').fontFamily,
          overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
        };
      });
      const at = `at ${width}px: ${JSON.stringify(m)}`;
      assert.ok(Math.abs(m.imageOpacity - 0.78) < 0.02, `Hero photo must stay at 78% opacity ${at}`);
      assert.match(m.heroFont, /^"?Montserrat/, `Hero heading must use Montserrat ${at}`);
      assert.ok(m.overflow <= 0, `Horizontal overflow ${at}`);
      if (width >= 1024) {
        assert.ok(Math.abs(m.heroHeight - 321) <= 1, `Hero must be 321px tall ${at}`);
        assert.equal(m.heroSize, 65, `Hero heading must be 65px ${at}`);
        assert.equal(m.h3Size, 40, `Mission headline must be 40px ${at}`);
        assert.equal(m.pSize, 22, `Reading text must be 22px ${at}`);
        assert.match(m.pFont, /^"?Montserrat/, `Reading text must use Montserrat ${at}`);
      } else {
        assert.ok(m.pSize >= 16, `Reading text must stay comfortable on small screens ${at}`);
      }
      // Certificate viewer: no frame lines, and the arrows sit beside the picture, not on it.
      const cert = await page.evaluate(() => {
        const viewer = document.querySelector('.tbt-about-tutors .tbt-tutor__documents');
        if (!viewer) return null;
        const px = (el, prop) => parseFloat(getComputedStyle(el)[prop]) || 0;
        const border = (el) => ['Top', 'Right', 'Bottom', 'Left'].reduce((n, side) => n + px(el, `border${side}Width`), 0);
        const img = Array.from(viewer.querySelectorAll('.tbt-tutor__document')).find((i) => i.getBoundingClientRect().width > 0);
        const ir = img.getBoundingClientRect();
        const pad = (side) => px(img, `padding${side}`);
        const picture = { left: ir.left + pad('Left'), right: ir.right - pad('Right') };
        const gaps = Array.from(viewer.querySelectorAll('.tbt-tutor__document-control')).map((c) => {
          const r = c.getBoundingClientRect();
          return { border: border(c), gap: c.matches('.tbt-tutor__document-control--previous') ? picture.left - r.right : r.left - picture.right };
        });
        return { viewerBorder: border(viewer), enlargeBorder: border(viewer.querySelector('.tbt-tutor__document-enlarge')), gaps };
      });
      if (cert) {
        const where = `${at} cert=${JSON.stringify(cert)}`;
        assert.equal(cert.viewerBorder, 0, `Certificate viewer must have no border line ${where}`);
        assert.equal(cert.enlargeBorder, 0, `Enlarge button must have no border line ${where}`);
        for (const g of cert.gaps) {
          assert.equal(g.border, 0, `Arrow buttons must have no border line ${where}`);
          assert.ok(g.gap >= 8, `Arrow buttons must sit at least 8px away from the certificate ${where}`);
        }
      }
      // Founder photo: as wide as its title bar, with the same gap under the bar as the Our Tutors cards.
      const founder = await page.evaluate(() => {
        const box = (selector) => document.querySelector(selector).getBoundingClientRect();
        const bar = box('.tbt-founder-message .tbt-institutional-title-bar');
        const img = box('.tbt-founder-message__image');
        const tutorBar = box('.tbt-about-tutors .tbt-institutional-title-bar');
        const card = box('.tbt-about-tutors .tbt-tutor');
        return { barWidth: bar.width, imgWidth: img.width, barLeft: bar.left, imgLeft: img.left, gap: img.top - bar.bottom, tutorGap: card.top - tutorBar.bottom };
      });
      const fw = `${at} founder=${JSON.stringify(founder)}`;
      assert.ok(Math.abs(founder.imgWidth - founder.barWidth) <= 1 && Math.abs(founder.imgLeft - founder.barLeft) <= 1, `Founder photo must be as wide as the title bar ${fw}`);
      assert.ok(Math.abs(founder.gap - founder.tutorGap) <= 1, `Founder photo gap under the title bar must match the Our Tutors gap ${fw}`);
      // Quality and Vision/Mission/Values: words left, photo right, photo as tall as the words. Founder Q&A in cards.
      const layout = await page.evaluate(() => {
        const box = (el) => el.getBoundingClientRect();
        const union = (els) => {
          const rects = els.map(box);
          return { left: Math.min(...rects.map((r) => r.left)), right: Math.max(...rects.map((r) => r.right)), top: Math.min(...rects.map((r) => r.top)), bottom: Math.max(...rects.map((r) => r.bottom)) };
        };
        const split = (section, textSelector, imageSelector) => {
          const text = union(Array.from(document.querySelectorAll(`${section} ${textSelector}`)));
          const img = box(document.querySelector(`${section} ${imageSelector}`));
          return { textRight: text.right, textTop: text.top, textHeight: text.bottom - text.top, imgLeft: img.left, imgTop: img.top, imgHeight: img.height };
        };
        const cards = Array.from(document.querySelectorAll('.tbt-founder-message .tbt-founder-card')).map((c) => { const r = box(c); return { left: r.left, right: r.right, top: r.top, bottom: r.bottom }; });
        return {
          quality: split('.tbt-about-quality', '.tbt-reading-width > p, .tbt-reading-width > ul', '.tbt-about-quality__image'),
          beliefs: split('.tbt-about-beliefs', '.tbt-beliefs-grid', '.tbt-about-beliefs__image'),
          cards,
        };
      });
      const lw = `${at} layout=${JSON.stringify(layout)}`;
      assert.equal(layout.cards.length, 3, `Founder answers must be three cards ${lw}`);
      const [c1, c2, c3] = layout.cards;
      if (width >= 1024) {
        for (const key of ['quality', 'beliefs']) {
          const l = layout[key];
          assert.ok(l.imgLeft >= l.textRight, `${key}: photo must sit to the right of the words ${lw}`);
          assert.ok(Math.abs(l.imgTop - l.textTop) <= 2, `${key}: photo must start level with the words ${lw}`);
          assert.ok(Math.abs(l.imgHeight - l.textHeight) <= 2, `${key}: photo must be as tall as the words ${lw}`);
        }
        assert.ok(Math.abs(c1.top - c2.top) <= 1 && c2.left >= c1.right, `Founder questions 1 and 2 must sit side by side ${lw}`);
        assert.ok(c3.top >= Math.max(c1.bottom, c2.bottom), `Founder question 3 must sit below them ${lw}`);
        assert.ok(Math.abs(c3.left - c1.left) <= 1 && Math.abs(c3.right - c2.right) <= 1, `Founder question 3 card must span both columns ${lw}`);
      } else {
        assert.ok(layout.quality.imgTop >= layout.quality.textTop + layout.quality.textHeight - 1, `quality: photo stacks under the words on small screens ${lw}`);
        assert.ok(c2.top >= c1.bottom && c3.top >= c2.bottom, `Founder cards stack on small screens ${lw}`);
      }
      // Tutor certificates: the viewer column is as tall as that tutor's words (desktop), and the picture is big.
      const tutors = await page.evaluate(() => Array.from(document.querySelectorAll('.tbt-about-tutors .tbt-tutor--has-documents')).map((card) => {
        const box = (selector) => card.querySelector(selector).getBoundingClientRect();
        const details = box('.tbt-tutor__details');
        const column = box('.tbt-tutor__certificates');
        const viewer = box('.tbt-tutor__documents');
        const img = Array.from(card.querySelectorAll('.tbt-tutor__document')).find((i) => i.getBoundingClientRect().width > 0).getBoundingClientRect();
        return { detailsTop: details.top, detailsHeight: details.height, columnTop: column.top, columnHeight: column.height, viewerHeight: viewer.height, imgHeight: img.height };
      }));
      assert.ok(tutors.length >= 2, `Tutors with certificates must be on the page at ${width}px`);
      for (const t of tutors) {
        const tw = `${at} tutor=${JSON.stringify(t)}`;
        if (width >= 1024) {
          assert.ok(Math.abs(t.columnTop - t.detailsTop) <= 2 && Math.abs(t.columnHeight - t.detailsHeight) <= 2, `Certificates must be as tall as the tutor's words ${tw}`);
          assert.ok(t.imgHeight >= 290, `Certificate picture must be large ${tw}`);
        } else {
          assert.ok(t.viewerHeight >= 320, `Certificate viewer must stay large on small screens ${tw}`);
        }
      }
      console.log(`PASS about ${width}`);
    }
  } finally { await browser.close(); }
})().catch((e) => { console.error(e.message); process.exit(1); });
