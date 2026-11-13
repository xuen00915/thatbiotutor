// One design across the site: the homepage and IntensiveFix are the template, every other page follows it.
// Same font family, heading style, text colours, dark-green bands, page-title treatment and content width.
const assert = require('node:assert/strict');
// Collect every violation, then fail once with the full list (one run shows everything that is off).
const fails = [];
const t = {
  ok: (c, m) => { if (!c) fails.push(m); },
  equal: (a, b, m) => { if (a !== b) fails.push(m); },
  notEqual: (a, b, m) => { if (a === b) fails.push(m); },
  deepEqual: (a, b, m) => { if (JSON.stringify(a) !== JSON.stringify(b)) fails.push(m); },
};
const { chromium } = require('playwright');
const base = (process.env.TBT_RUNTIME_URL || 'http://localhost:8895').replace(/\/?$/, '/');
const routes = ['', 'intensivefix/', 'classes/', 'sec-3-pure-g3-biology-tuition/', 'sec-4-pure-g3-biology-tuition/', 'testimonials/', 'notes/', 'about/', 'trees/', 'contact/', 'celldivision/', 'tuitionrecs/'];
const followers = routes.filter((r) => r !== '' && r !== 'intensivefix/'); // pages that must follow the template
const INK = 'rgb(25, 34, 26)', GREEN = 'rgb(47, 138, 39)', LIGHT_GREEN = 'rgb(143, 225, 126)', FOREST = 'rgb(14, 55, 29)';
(async () => {
  const browser = await chromium.launch();
  try {
    const page = await browser.newPage();
    page.setDefaultTimeout(10000);
    for (const width of [1440, 390]) {
      await page.setViewportSize({ width, height: 900 });
      await page.goto(new URL('', base).href, { waitUntil: 'load' });
      await page.evaluate(() => document.fonts.ready);
      const homeH1 = await page.evaluate(() => parseFloat(getComputedStyle(document.querySelector('#main-content h1')).fontSize));
      const stdH2 = width <= 520 ? 30 : 36; // the homepage section-heading size
      for (const route of followers) {
        await page.goto(new URL(route, base).href, { waitUntil: 'load' });
        await page.evaluate(() => document.fonts.ready);
        const r = await page.evaluate(() => {
          const main = document.querySelector('#main-content');
          const bgOf = (e) => { while (e && e !== document.body) { const c = getComputedStyle(e).backgroundColor; const n = c.match(/[\d.]+/g); if (n && (n[3] === undefined || +n[3] > 0.5)) return c; e = e.parentElement; } return 'rgb(255, 255, 255)'; };
          const dark = (c) => { const n = c.match(/\d+/g).map(Number); return 0.2126 * n[0] + 0.7152 * n[1] + 0.0722 * n[2] < 110; };
          const shown = (e) => e.getClientRects().length > 0 && getComputedStyle(e).visibility !== 'hidden';
          const out = { fonts: {}, h: [], p: [], bands: [], greens: [] };
          main.querySelectorAll('*').forEach((e) => {
            if (!shown(e) || e.closest('svg, iframe, script, style')) return;
            const own = [...e.childNodes].some((n) => n.nodeType === 3 && n.textContent.trim());
            if (own) { const f = getComputedStyle(e).fontFamily.split(',')[0].replace(/["']/g, '').trim(); if (f !== 'Poppins') out.fonts[f + ' <' + e.tagName.toLowerCase() + '.' + String(e.className).split(' ')[0] + '>'] = 1; }
            if (own) { const c = getComputedStyle(e).color; if (!dark(bgOf(e))) out.greens.push({ c, text: e.textContent.trim().slice(0, 28), cls: String(e.className).split(' ')[0] || e.tagName.toLowerCase() }); }
            const tag = e.tagName;
            if (/^H[123]$/.test(tag)) { const s = getComputedStyle(e); out.h.push({ tag, size: parseFloat(s.fontSize), weight: s.fontWeight, color: s.color, dark: dark(bgOf(e)), text: e.textContent.trim().slice(0, 30), hasAccent: !!e.querySelector('span, strong, em, mark') }); }
            if ((tag === 'P' || tag === 'LI') && own) { const s = getComputedStyle(e); out.p.push({ color: s.color, dark: dark(bgOf(e)), text: e.textContent.trim().slice(0, 30), cls: String(e.className).split(' ')[0] }); }
          });
          document.querySelectorAll('body *').forEach((e) => {
            const c = getComputedStyle(e).backgroundColor; const n = c.match(/[\d.]+/g); if (!n || (n[3] !== undefined && +n[3] < 0.9)) return;
            if (e.closest('.site-footer, .tbt-home-video, .tbt-video, .tbt-programme-lesson__video, iframe, button, a, .tbt-testimonial, .tbt-reviews-controls, .tbt-faq-slider__control, .tbt-testimonial-dot')) return;
            if (0.2126 * n[0] + 0.7152 * n[1] + 0.0722 * n[2] > 95) return;
            const b = e.getBoundingClientRect(); if (b.width < 200 || b.height < 30) return;
            out.bands.push({ c, cls: String(e.className).split(' ')[0] });
          });
          return out;
        });
        const at = `${route || 'home'} at ${width}`;
        // 1. one font family everywhere
        t.deepEqual(Object.keys(r.fonts), [], `Only Poppins is used on ${at}: ${Object.keys(r.fonts).join(', ')}`);
        // 2. headings: one style
        const h1s = r.h.filter((h) => h.tag === 'H1');
        t.ok(h1s.length >= 1, `Page title on ${at}`);
        for (const h of h1s) { t.equal(h.size, homeH1, `H1 matches the homepage size on ${at}: ${h.size}px (${h.text})`); t.equal(h.weight, '800', `H1 weight on ${at} (${h.text})`); }
        for (const h of r.h.filter((x) => x.tag === 'H2')) {
          t.equal(h.weight, '800', `H2 weight on ${at}: ${h.weight} (${h.text})`);
          t.ok(Math.abs(h.size - stdH2) < 0.6 || h.size <= 26, `H2 size on ${at}: ${h.size}px (${h.text})`);
        }
        for (const h of r.h.filter((x) => x.tag === 'H3')) t.ok(Number(h.weight) >= 700, `H3 weight on ${at}: ${h.weight} (${h.text})`);
        // 3. text colours: ink on light, white / light green on dark, brand green accents - never pure black or the lime/bright green
        for (const h of r.h) {
          const ok = h.dark ? [ 'rgb(255, 255, 255)', LIGHT_GREEN, 'rgb(245, 250, 244)' ] : [ INK, GREEN, 'rgb(230, 44, 44)', 'rgb(255, 255, 255)' ];
          t.ok(ok.includes(h.color), `Heading colour on ${at}: ${h.color} (${h.text})`);
        }
        for (const p of r.p) t.notEqual(p.color, 'rgb(0, 0, 0)', `Body text is the site ink, not pure black, on ${at}: "${p.text}" (${p.cls})`);
        for (const g of r.greens) t.ok(![ 'rgb(32, 175, 35)', 'rgb(46, 175, 43)', 'rgb(67, 180, 58)', 'rgb(141, 225, 94)', 'rgb(119, 207, 100)' ].includes(g.c), `Green text on light backgrounds is the one site green (#2f8a27) on ${at}: ${g.c} "${g.text}" (${g.cls})`);
        // 4. every dark band is the same forest green
        for (const b of r.bands) t.equal(b.c, FOREST, `Dark band colour on ${at}: ${b.c} (${b.cls})`);
        
      }
    }
    // 5. Page titles on Contact, Tuition Recommendations and Our Causes follow the homepage title treatment.
    for (const width of [1440, 390]) {
      await page.setViewportSize({ width, height: 900 });
      for (const route of ['contact/', 'tuitionrecs/', 'trees/']) {
        await page.goto(new URL(route, base).href, { waitUntil: 'load' });
        await page.evaluate(() => document.fonts.ready);
        const tt = await page.evaluate(() => {
          const h1 = document.querySelector('#main-content h1'); const s = getComputedStyle(h1); const eb = document.querySelector('#main-content .tbt-page-eyebrow');
          const hb = document.querySelector('.site-header').getBoundingClientRect().bottom; const accent = h1.querySelector('span');
          const bar = h1.closest('.tbt-institutional-title-bar');
          return { align: s.textAlign, bg: bar ? getComputedStyle(bar).backgroundColor : 'none', ebText: eb && eb.textContent.trim(), ebColor: eb && getComputedStyle(eb).color, ebCase: eb && getComputedStyle(eb).textTransform, ebBeforeH1: !!eb && !!(eb.compareDocumentPosition(h1) & Node.DOCUMENT_POSITION_FOLLOWING), accent: accent && getComputedStyle(accent).color, color: s.color, top: h1.getBoundingClientRect().top - hb, ebTop: eb ? eb.getBoundingClientRect().top - hb : null };
        });
        const at = `${route} at ${width}`;
        t.equal(tt.align, 'center', `Title is centred on ${at}`);
        t.ok(tt.bg === 'none' || tt.bg === 'rgba(0, 0, 0, 0)', `Title is not on a dark bar on ${at}: ${tt.bg}`);
        t.ok(tt.ebText && tt.ebBeforeH1 && tt.ebCase === 'uppercase' && tt.ebColor === GREEN, `Green all-caps eyebrow above the title on ${at}: ${JSON.stringify({ t: tt.ebText, c: tt.ebColor, u: tt.ebCase })}`);
        t.equal(tt.accent, GREEN, `Title has a green accent word on ${at}`);
        t.equal(tt.color, INK, `Title is the site ink on ${at}`);
        t.ok(tt.ebTop >= 24, `Eyebrow has breathing room under the header on ${at}: ${tt.ebTop}px`);
      }
    }
    // 6. About: section headings are centred titles like the homepage, not dark bars.
    for (const width of [1440, 390]) {
      await page.setViewportSize({ width, height: 900 });
      await page.goto(new URL('about/', base).href, { waitUntil: 'load' });
      const bars = await page.locator('.tbt-institutional-title-bar').evaluateAll((els) => els.map((e) => ({ bg: getComputedStyle(e).backgroundColor, align: getComputedStyle(e.querySelector('h2')).textAlign })));
      t.ok(bars.length >= 4);
      for (const b of bars) { t.ok(b.bg === 'rgba(0, 0, 0, 0)', `About section headings are not dark bars at ${width}: ${b.bg}`); t.equal(b.align, 'center'); }
    }
    // 7. Short pages: the footer sits at the bottom of the screen, no white strip beneath it.
    await page.setViewportSize({ width: 1440, height: 900 });
    for (const route of ['tuitionrecs/', 'contact/']) {
      await page.goto(new URL(route, base).href, { waitUntil: 'load' });
      const gap = await page.evaluate(() => innerHeight - document.querySelector('.site-footer').getBoundingClientRect().bottom);
      t.ok(gap <= 1, `No white strip below the footer on ${route}: ${gap}px`);
    }
    if (fails.length) { const u = [...new Set(fails)]; console.error(u.length + ' differences from the template design:\n' + u.join('\n')); process.exit(1); }
    console.log('PASS unified design');
  } finally { await browser.close(); }
})().catch((e) => { console.error(e.message); process.exit(1); });
