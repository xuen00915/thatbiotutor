// Results & Testimonials (/testimonials/): original-style hero (Montserrat title right-aligned over the plant photo with five gold stars),
// compact spacing, original "Student Stories!" heading, a growth-chart icon on the Class of 2022 / 2021 headings,
// and every review list stacked in a panel that scrolls on its own and opens on four reviews.
const assert = require('node:assert/strict');
const { chromium } = require('playwright');
const base = (process.env.TBT_RUNTIME_URL || 'http://localhost:8888').replace(/\/?$/, '/');
(async () => {
  const browser = await chromium.launch();
  try {
    const page = await browser.newPage();
    page.setDefaultTimeout(15000);
    for (const width of [1440, 1024, 768, 390, 320]) {
      await page.setViewportSize({ width, height: 900 });
      await page.goto(new URL('./testimonials/', base).href, { waitUntil: 'load' });
      await page.evaluate(() => document.fonts?.ready);
      await page.waitForTimeout(600);
      const m = await page.evaluate(() => {
        const q = (s, r = document) => r.querySelector(s);
        const box = (e) => e.getBoundingClientRect();
        const hero = q('.tbt-testimonials-hero');
        const h1 = q('.tbt-testimonials-hero h1');
        const stars = q('.tbt-testimonials-hero__stars');
        const hcs = getComputedStyle(h1);
        const stories = q('#class-of-2025 .tbt-story-heading');
        const h3 = q('h3', stories);
        const sub = q('p', stories);
        const artwork = q('#class-of-2025 .tbt-testimonials-artwork');
        const featuredButton = q('.tbt-testimonials-featured .tbt-testimonials-button');
        const band2022 = q('#class-of-2022 .tbt-testimonials-artwork-band');
        const band2021 = q('#class-of-2021 .tbt-testimonials-results-band');
        const iconInfo = (band) => { const icon = q('svg.tbt-testimonials-icon', band); const title = q('h2', band); return icon ? { w: box(icon).width, below: box(icon).top >= box(title).bottom - 1, fontFamily: getComputedStyle(title).fontFamily, weight: getComputedStyle(title).fontWeight, color: getComputedStyle(title).color } : null; };
        const lists = Array.from(document.querySelectorAll('.tbt-testimonials-year [data-testimonial-list]')).map((list) => {
          const items = Array.from(list.querySelectorAll(':scope > .tbt-testimonial'));
          const cs = getComputedStyle(list);
          const lb = box(list);
          const rect = (i) => box(items[i]);
          const lefts = items.slice(0, 6).map((i) => Math.round(box(i).left));
          const tops = items.slice(0, 6).map((i) => Math.round(box(i).top));
          const before = { p: list.scrollTop, y: window.scrollY };
          list.scrollTop = 300;
          const moved = list.scrollTop > before.p && window.scrollY === before.y;
          list.scrollTop = 0;
          return {
            id: list.closest('section').id, total: items.length, hidden: items.filter((i) => i.hidden).length, panel: !!list.closest('[data-review-panel]'), overflowY: cs.overflowY,
            scrollable: list.scrollHeight > list.clientHeight + 20, stacked: new Set(lefts).size === 1 && new Set(tops).size === tops.length,
            fourthInside: items.length >= 4 ? rect(3).bottom <= lb.bottom + 1 : true, fifthVisible: items.length >= 5 ? Math.max(0, lb.bottom - rect(4).top) : 0, moved,
            controls: list.closest('section').querySelectorAll('.tbt-reviews-controls').length, tabindex: list.getAttribute('tabindex'),
            squashed: items.slice(0, 6).filter((i) => i.scrollHeight > i.clientHeight + 3).length,
          };
        });
        return {
          viewport: document.documentElement.clientWidth, overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
          heroHeight: box(hero).height, h1Font: hcs.fontFamily, h1Weight: hcs.fontWeight, h1Align: hcs.textAlign, h1Right: box(h1).right, h1Left: box(h1).left,
          starCount: stars ? stars.querySelectorAll('svg:not([width="0"])').length : 0, starLabel: stars ? stars.getAttribute('aria-label') : null, starsRight: stars ? box(stars).right : 0,
          h3Font: getComputedStyle(h3).fontFamily, h3Weight: getComputedStyle(h3).fontWeight, h3Color: getComputedStyle(h3).color, h3SpanColor: getComputedStyle(q('span', h3)).color,
          subFont: getComputedStyle(sub).fontFamily, subStyle: getComputedStyle(sub).fontStyle,
          featuredToArtwork: box(artwork).top - box(featuredButton).bottom, artworkToStories: box(h3).top - box(artwork).bottom,
          band2022Pad: [parseFloat(getComputedStyle(band2022).paddingTop), parseFloat(getComputedStyle(band2022).paddingBottom)], band2021H: box(band2021).height,
          icon2022: iconInfo(band2022), icon2021: iconInfo(band2021), lists,
        };
      });
      const at = `at ${width}px`;
      const detail = (extra) => `${at}: ${JSON.stringify(extra)}`;
      assert.match(m.h1Font, /^"?Montserrat/, `Hero title must use Montserrat ${at}: ${m.h1Font}`);
      assert.ok(m.starCount === 5 && /five/i.test(m.starLabel || ''), `Hero needs five gold stars ${at}`);
      assert.ok(m.overflow <= 0, `Horizontal overflow ${at}`);
      if (width >= 769) {
        assert.equal(m.h1Align, 'right', `Hero title is right-aligned like the original ${at}`);
        assert.ok(m.h1Right >= m.viewport * 0.6 && Math.abs(m.starsRight - m.h1Right) <= 4, `Title and stars sit on the right with a shared right edge ${detail({ h1Right: m.h1Right, starsRight: m.starsRight })}`);
        assert.ok(m.heroHeight <= 460, `Hero should be about 449px tall ${detail({ h: m.heroHeight })}`);
        assert.ok(m.featuredToArtwork <= 64 && m.artworkToStories <= 64, `Spacing around the 2025 result cards must be compact ${detail({ a: m.featuredToArtwork, b: m.artworkToStories })}`);
        assert.ok(m.band2022Pad[0] <= 56 && m.band2022Pad[1] <= 56, `Class of 2022 band padding must be compact ${detail(m.band2022Pad)}`);
      } else {
        assert.ok(m.featuredToArtwork <= 48 && m.artworkToStories <= 48, `Spacing around the 2025 result cards must be compact on phones ${detail({ a: m.featuredToArtwork, b: m.artworkToStories })}`);
      }
      assert.match(m.h3Font, /Helvetica|Arial/, `Student Stories heading uses the original Helvetica-style bold ${at}: ${m.h3Font}`);
      assert.ok(Number(m.h3Weight) >= 700 && m.h3Color !== m.h3SpanColor, `"Student" dark and "Stories!" green ${at}`);
      assert.match(m.subFont, /^"?Montserrat/, `Student Stories subtitle uses Montserrat ${at}`);
      assert.equal(m.subStyle, 'italic', `Student Stories subtitle is italic ${at}`);
      for (const [name, icon] of [['2022', m.icon2022], ['2021', m.icon2021]]) {
        assert.ok(icon && icon.w >= 56 && icon.below, `Class of ${name} needs the growth-chart icon under its title ${at}`);
        assert.match(icon.fontFamily, /^"?Montserrat/, `Class of ${name} title uses Montserrat ${at}`);
        assert.ok(Number(icon.weight) >= 700 && icon.color === 'rgb(255, 255, 255)', `Class of ${name} title is white and bold ${at}`);
      }
      assert.equal(m.lists.length, 5, `All five yearly review panels must be present ${at}`);
      for (const l of m.lists) {
        const t = `${l.id} ${at}: ${JSON.stringify(l)}`;
        assert.ok(l.panel && l.hidden === 0 && l.controls === 0, `Reviews live in a panel with none hidden and no show-more buttons ${t}`);
        assert.ok(l.stacked, `Reviews are stacked in one column, not a card grid ${t}`);
        assert.equal(l.squashed, 0, `Reviews keep their full height ${t}`);
        if (l.total > 4) {
          assert.ok(['auto', 'scroll'].includes(l.overflowY) && l.scrollable && l.moved && l.tabindex === '0', `Panel scrolls on its own and is keyboard reachable ${t}`);
          assert.ok(l.fourthInside && l.fifthVisible <= 40, `Panel opens on exactly four reviews ${t}`);
        }
      }
      console.log(`PASS testimonials ${width}`);
    }
  } finally { await browser.close(); }
})().catch((e) => { console.error(e.message); process.exit(1); });
