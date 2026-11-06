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
      console.log(`PASS about ${width}`);
    }
  } finally { await browser.close(); }
})().catch((e) => { console.error(e.message); process.exit(1); });
