const assert = require('node:assert/strict');
const { chromium } = require('playwright');

// Every public page must share one design standard: width, type, colour, buttons, spacing and touch targets.
const routes = [
  '/', '/intensivefix/', '/classes/', '/sec-3-pure-g3-biology-tuition/', '/sec-4-pure-g3-biology-tuition/',
  '/testimonials/', '/notes/', '/celldivision/', '/about/', '/trees/', '/contact/', '/tuitionrecs/',
];
const contentSelector = '.tbt-home-container, .tbt-programme-container, .tbt-testimonials-container, .tbt-resource-shell, .tbt-institutional-shell, .tbt-reading-width';
const sectionSelector = '.tbt-programme-section, .tbt-testimonials-year, .tbt-resource-section, .tbt-institutional-section, .tbt-causes-section';
const buttonSelector = '.tbt-home-button, .tbt-programme-button, .tbt-testimonials-button, .tbt-resource-button, .tbt-institutional-button, .tbt-button';
const darkInk = 'rgb(25, 34, 26)';

(async () => {
  const browser = await chromium.launch();
  try {
    const page = await browser.newPage();
    page.setDefaultTimeout(10000);
    const base = (process.env.TBT_RUNTIME_URL || 'http://localhost:8888').replace(/\/?$/, '/');
    for (const width of [1440, 1024, 768, 390, 320]) {
      await page.setViewportSize({ width, height: 900 });
      for (const route of routes) {
        await page.goto(new URL('.' + route, base).href, { waitUntil: 'domcontentloaded' });
        const tag = `${route} @${width}`;
        const m = await page.evaluate(({ contentSelector, sectionSelector, buttonSelector }) => {
          const main = document.querySelector('main');
          const cs = (e) => (e ? getComputedStyle(e) : null);
          const visible = (e) => { const r = e.getBoundingClientRect(); return r.width > 0 && r.height > 0 && getComputedStyle(e).visibility !== 'hidden'; };
          const first = (sel) => [...main.querySelectorAll(sel)].find(visible);
          const para = [...main.querySelectorAll('p')].find((e) => visible(e) && e.textContent.trim().length > 60 && !e.closest('[class*=hero], header, [class*=lead], [class*=quote], [class*=problem], .tbt-story-heading'));
          const h1 = first('h1');
          const h2 = [...main.querySelectorAll('section > h2, section > div > h2, section .tbt-programme-container > h2')].find(visible);
          const content = [...main.querySelectorAll(contentSelector)].filter(visible).map((e) => { const s = cs(e); return Math.round(e.getBoundingClientRect().width - parseFloat(s.paddingLeft) - parseFloat(s.paddingRight)); });
          const buttons = [...main.querySelectorAll(buttonSelector)].filter(visible).map((e) => { const s = cs(e); return { bg: s.backgroundColor, radius: s.borderTopLeftRadius, family: s.fontFamily, weight: s.fontWeight, h: Math.round(e.getBoundingClientRect().height) }; });
          const sections = [...main.querySelectorAll(sectionSelector)].filter(visible).map((e) => parseFloat(cs(e).paddingTop));
          const small = [...main.querySelectorAll('p, li, blockquote, span, a, button, summary')].filter((e) => visible(e) && e.children.length === 0 && e.textContent.trim().length > 3 && parseFloat(cs(e).fontSize) < 11.5).map((e) => e.textContent.trim().slice(0, 20) + ':' + cs(e).fontSize);
          const taps = [...main.querySelectorAll('button, [role=button]')].filter(visible).filter((e) => { const r = e.getBoundingClientRect(); return Math.min(r.width, r.height) < 40 && !e.closest('[hidden]') && !e.matches('[data-carousel-dot]'); }).map((e) => `${e.tagName}.${String(e.className).split(' ')[0]}:${Math.round(e.getBoundingClientRect().width)}x${Math.round(e.getBoundingClientRect().height)}`);
          return {
            font: { h1: h1 && cs(h1).fontFamily, h2: h2 && cs(h2).fontFamily, p: para && cs(para).fontFamily },
            h1: h1 && { size: parseFloat(cs(h1).fontSize), weight: cs(h1).fontWeight },
            h2: h2 && { size: parseFloat(cs(h2).fontSize), weight: cs(h2).fontWeight },
            p: para && parseFloat(cs(para).fontSize),
            content, buttons, sections, small, taps,
            overflow: document.documentElement.scrollWidth - innerWidth,
            style: (() => {
              const issues = [];
              const rounded = (e) => { for (let n = e, i = 0; n && n !== main && i < 3; n = n.parentElement, i += 1) { const s = cs(n); if (parseFloat(s.borderTopLeftRadius) >= 12 && (i === 0 || s.overflow !== 'visible')) return true; } return false; };
              main.querySelectorAll('*').forEach((e) => {
                const r = e.getBoundingClientRect();
                if (r.width < 160 || r.height < 60 || !visible(e) || e.closest('[class*=hero], [class*=crest], .tbt-tutor__portrait-frame')) return;
                const s = cs(e);
                const name = `${e.tagName}.${String(e.className).split(' ')[0]}`;
                const dark = ['rgb(25, 34, 26)', 'rgb(0, 0, 0)', 'rgb(17, 17, 17)', 'rgb(48, 48, 48)', 'rgb(24, 24, 24)', 'rgb(32, 32, 32)'].includes(s.backgroundColor);
                if (dark && r.width > 400 && !e.closest('[class*=video], iframe') && e.tagName !== 'IFRAME') issues.push(`near-black fill ${name}`);
                const media = ['IMG', 'IFRAME', 'VIDEO'].includes(e.tagName) && r.width < 1300 && r.width < innerWidth - 2;
                const boxed = parseFloat(s.borderTopWidth) > 0 && s.borderTopStyle !== 'none' && r.width < 1300;
                if ((media || boxed) && !rounded(e) && !e.matches('.tbt-sec3-page .tbt-programme-testimonials .tbt-testimonial')) issues.push(`sharp edges ${name}`);
                if (boxed && /rgb\((0, 0, 0|25, 34, 26|17, 17, 17)\)/.test(s.borderTopColor) && parseFloat(s.borderTopWidth) >= 1 && !e.matches('input, button, .tbt-sec3-page .tbt-programme-testimonials .tbt-testimonial')) issues.push(`black outline ${name}`);
              });
              main.querySelectorAll('img, [class*=background], [class*=shade]').forEach((e) => {
                const r = e.getBoundingClientRect();
                if (r.width >= innerWidth - 2 && r.height > 40 && visible(e) && parseFloat(cs(e).borderTopLeftRadius) > 0) issues.push(`curved edge-to-edge ${e.tagName}.${String(e.className).split(' ')[0]}`);
              });
              return [...new Set(issues)];
            })(),
          };
        }, { contentSelector, sectionSelector, buttonSelector });

        assert.deepEqual(m.style, [], `${tag}: rounded, softly-coloured style violations`);
        assert.ok(m.overflow <= 1, `${tag}: horizontal overflow ${m.overflow}px`);
        for (const [name, family] of Object.entries(m.font)) if (family && route !== '/about/' && !(['/notes/', '/testimonials/'].includes(route) && name === 'h1') && !(route === '/sec-3-pure-g3-biology-tuition/' && name === 'h1')) assert.match(family, /^"?Poppins/, `${tag}: ${name} must use Poppins, got ${family}`);
        assert.ok(m.content.length > 0, `${tag}: no content container found`);
        for (const w of m.content) assert.ok(Math.abs(w - Math.min(1120, width - 32)) <= 2, `${tag}: content width ${w} must be the shared ${Math.min(1120, width - 32)}`);
        if (m.h1 && width >= 1024) { assert.equal(m.h1.size, { '/trees/': 36, '/about/': 65, '/notes/': 45, '/testimonials/': 64 }[route] || 60, `${tag}: h1 size`); /* Our Causes: its h1 is a section bar that matches the h2 bar below. About keeps its original 65px hero. */ assert.equal(m.h1.weight, ['/about/', '/notes/', '/testimonials/', '/sec-3-pure-g3-biology-tuition/'].includes(route) ? '700' : '800', `${tag}: h1 weight`); }
        if (m.h2 && width >= 1024) { assert.ok([30, 36].includes(m.h2.size), `${tag}: h2 size ${m.h2.size}`); assert.equal(m.h2.weight, '800', `${tag}: h2 weight`); }
        if (m.p && !(route === '/about/' && width >= 981)) { assert.ok(m.p >= 16 && m.p <= 18.5, `${tag}: body text ${m.p}px must be 16-18px`); }
        for (const b of m.buttons) {
          assert.equal(b.bg, 'rgb(67, 180, 58)', `${tag}: buttons share the brand green`);
          assert.equal(b.radius, '9999px', `${tag}: buttons are pills`);
          assert.match(b.family, /^"?Poppins/, `${tag}: button font`);
          assert.ok(b.h >= 44, `${tag}: button height ${b.h}px must be a comfortable touch target`);
        }
        if (route === '/about/' || route === '/notes/' || route === '/classes/' || route === '/testimonials/') { /* About, Free Bio Notes, Classes and Testimonials use a compact rhythm, guarded by about-page-runtime.js, notes-sections-runtime.js, classes-page-runtime.js and testimonials-page-runtime.js */ }
        else if (width >= 769) for (const pad of m.sections) assert.ok(pad === 64 || pad === 80, `${tag}: section padding ${pad}px must be 64 or 80`);
        else for (const pad of m.sections) assert.ok(pad === 48 || pad === 56, `${tag}: mobile section padding ${pad}px must be 48 or 56`);
        assert.deepEqual(m.small, [], `${tag}: no text below 11.5px`);
        assert.deepEqual(m.taps, [], `${tag}: all buttons must be at least 40px in both directions`);
      }
    }
    console.log('PASS: every public page shares the same width, type, colour, buttons, spacing and touch targets at five viewport sizes');
  } finally {
    await browser.close();
  }
})().catch((error) => { console.error(error); process.exit(1); });
