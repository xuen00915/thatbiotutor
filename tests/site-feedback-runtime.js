// Client feedback contract: dropdown design, menu highlight colour, one light green on every dark green section, uncropped teacher in the home slider.
const assert = require('node:assert/strict');
const { chromium } = require('playwright');
const base = (process.env.TBT_RUNTIME_URL || 'http://localhost:8890').replace(/\/?$/, '/');
const LIGHT_GREEN = 'rgb(143, 225, 126)';
const MENU_GREEN = 'rgb(47, 138, 39)';
(async () => {
  const browser = await chromium.launch();
  try {
    const page = await browser.newPage();
    page.setDefaultTimeout(10000);

    // 1. Selected menu item uses the same green as the page's green text, not the old lime.
    await page.setViewportSize({ width: 1440, height: 900 });
    for (const [route, label] of [['', 'Home'], ['classes/', 'Classes'], ['testimonials/', 'Results & Testimonials'], ['notes/', 'Free Bio Notes'], ['about/', 'About Us'], ['trees/', 'Our Causes']]) {
      await page.goto(new URL(route, base).href);
      const active = await page.locator('.site-nav__link.is-active').evaluateAll((els) => els.map((e) => ({ text: e.textContent.trim(), color: getComputedStyle(e).color })));
      assert.deepEqual(active.map((a) => a.text), [label], `Only ${label} is selected on /${route}`);
      assert.equal(active[0].color, MENU_GREEN, `Selected menu item must use the site green on /${route}: ${active[0].color}`);
    }

    // 2. Classes dropdown matches the menu: same font, ink text, soft border, rounded, green hover.
    await page.goto(new URL('', base).href);
    await page.hover('.site-nav__item--has-children');
    await page.waitForTimeout(350);
    const dd = await page.evaluate(() => {
      const cs = (e) => getComputedStyle(e);
      const menuLink = [...document.querySelectorAll('.site-nav__link')].find((e) => e.textContent.trim() === 'Results & Testimonials');
      const sub = document.querySelector('.site-nav__submenu'); const link = document.querySelector('.site-nav__submenu-link');
      const r = sub.getBoundingClientRect();
      return { visible: r.height > 0 && cs(sub).visibility !== 'hidden', menu: { font: cs(menuLink).fontFamily, size: cs(menuLink).fontSize, color: cs(menuLink).color }, link: { font: cs(link).fontFamily, size: cs(link).fontSize, color: cs(link).color }, border: cs(sub).borderTopColor, borderW: cs(sub).borderTopWidth, radius: parseFloat(cs(sub).borderTopLeftRadius), shadow: cs(sub).boxShadow, bg: cs(sub).backgroundColor };
    });
    assert.ok(dd.visible, 'Dropdown opens on hover');
    assert.equal(dd.link.font, dd.menu.font, `Dropdown uses the menu font ${JSON.stringify(dd)}`);
    assert.equal(dd.link.size, dd.menu.size, 'Dropdown text is the same size as the menu');
    assert.equal(dd.link.color, dd.menu.color, 'Dropdown text is the same ink colour as the menu');
    assert.equal(dd.border, 'rgb(219, 230, 214)', `Dropdown border is the soft site tint, not navy: ${dd.border}`);
    assert.ok(dd.radius >= 12, `Dropdown corners are rounded like the site cards: ${dd.radius}`);
    assert.notEqual(dd.shadow, 'none', 'Dropdown has the soft site shadow');
    await page.hover('.site-nav__submenu-link');
    await page.waitForTimeout(250);
    const hov = await page.locator('.site-nav__submenu-link').first().evaluate((e) => ({ color: getComputedStyle(e).color, bg: getComputedStyle(e).backgroundColor }));
    assert.equal(hov.color, MENU_GREEN, 'Hovered dropdown item turns site green');
    assert.equal(hov.bg, 'rgb(238, 246, 234)', 'Hovered dropdown item gets a light green tint');

    // 2b. The dropdown highlights the page you are on.
    for (const [route, current, other] of [['sec-3-pure-g3-biology-tuition/', 'Sec 3', 'Sec 4'], ['sec-4-pure-g3-biology-tuition/', 'Sec 4', 'Sec 3']]) {
      await page.goto(new URL(route, base).href);
      await page.hover('.site-nav__item--has-children');
      await page.mouse.move(5, 400);
      await page.evaluate(() => document.querySelector('.site-nav__submenu').removeAttribute('hidden'));
      const st = await page.evaluate(() => [...document.querySelectorAll('.site-nav__submenu-link')].map((e) => ({ text: e.textContent.trim(), aria: e.getAttribute('aria-current'), color: getComputedStyle(e).color, bg: getComputedStyle(e).backgroundColor, weight: getComputedStyle(e).fontWeight })));
      const cur = st.find((x) => x.text === current); const oth = st.find((x) => x.text === other);
      assert.equal(cur.aria, 'page', `${current} is marked as the current page in the dropdown`);
      assert.equal(cur.color, MENU_GREEN, `${current} shows in site green when current`);
      assert.equal(cur.bg, 'rgb(238, 246, 234)', `${current} keeps the light green tint when current`);
      assert.equal(oth.aria, null, `${other} is not marked current`);
      assert.notEqual(oth.bg, 'rgb(238, 246, 234)', `${other} is not highlighted`);
    }
    await page.goto(new URL('classes/', base).href);
    assert.equal(await page.locator('.site-nav__submenu-link[aria-current]').count(), 0, 'No dropdown item is current on the Classes overview');

    // 3. Green text on dark green is one light green everywhere.
    for (const width of [1440, 390]) {
      await page.setViewportSize({ width, height: 900 });
      for (const route of ['', 'intensivefix/', 'classes/', 'sec-3-pure-g3-biology-tuition/', 'sec-4-pure-g3-biology-tuition/', 'testimonials/', 'notes/', 'about/', 'trees/', 'contact/', 'celldivision/', 'tuitionrecs/']) {
        await page.goto(new URL(route, base).href, { waitUntil: 'load' });
        const bad = await page.evaluate((LIGHT) => {
          const parse = (c) => { const m = c.match(/[\d.]+/g).map(Number); return { r: m[0], g: m[1], b: m[2], a: m[3] ?? 1 }; };
          const bgOf = (e) => { for (; e; e = e.parentElement) { const c = parse(getComputedStyle(e).backgroundColor); if (c.a > 0.5) return c; } return { r: 255, g: 255, b: 255 }; };
          const out = []; let seen = 0;
          for (const e of document.querySelectorAll('body *')) {
            if (![...e.childNodes].some((n) => n.nodeType === 3 && n.textContent.trim())) continue;
            const col = parse(getComputedStyle(e).color);
            if (!(col.g > col.r + 25 && col.g > col.b + 25)) continue;
            const bg = bgOf(e);
            if ((0.2126 * bg.r + 0.7152 * bg.g + 0.0722 * bg.b) / 255 > 0.4) continue;
            seen++;
            if (getComputedStyle(e).color !== LIGHT) out.push(`${getComputedStyle(e).color} <${e.tagName.toLowerCase()}> "${e.textContent.trim().slice(0, 24)}"`);
          }
          return { out, seen };
        }, LIGHT_GREEN);
        assert.deepEqual(bad.out, [], `Green text on dark green must be ${LIGHT_GREEN} on /${route} at ${width}`);
        assert.ok(bad.seen > 0 || /^(celldivision|tuitionrecs)\//.test(route), `Expected green text on a dark section on /${route} at ${width}`);
      }
    }

    // 4. Home slider keeps the teacher's whole head in view on desktop.
    for (const width of [1440, 1280, 1024]) {
      await page.setViewportSize({ width, height: 900 });
      await page.goto(new URL('', base).href, { waitUntil: 'load' });
      await page.evaluate(() => document.fonts?.ready);
      const m = await page.evaluate(() => {
        const img = document.querySelector('.tbt-home-hero__slide:nth-child(2) img');
        const box = img.parentElement.getBoundingClientRect();
        const scale = Math.max(box.width / img.naturalWidth, box.height / img.naturalHeight);
        const renderedH = img.naturalHeight * scale; const pos = parseFloat(getComputedStyle(img).objectPosition.split(' ')[1]) / 100;
        const top = (renderedH - box.height) * pos;
        return { windowTopFrac: top / renderedH, windowBottomFrac: (top + box.height) / renderedH, headroomPx: 0.24 * renderedH - top };
      });
      assert.ok(m.windowTopFrac <= 0.24 - 0.04, `Teacher's hair line (24% down the photo) needs headroom at ${width}: ${JSON.stringify(m)}`);
      assert.ok(m.windowBottomFrac >= 0.34, `Teacher's chin (34% down the photo) stays in view at ${width}: ${JSON.stringify(m)}`);
    }
    // 5. Slider dots never turn the parent theme's red-pink on hover, focus or click.
    await page.setViewportSize({ width: 1440, height: 900 });
    for (const route of ['', 'intensivefix/', 'notes/', 'about/', 'celldivision/']) {
      await page.goto(new URL(route, base).href, { waitUntil: 'load' });
      await page.evaluate(() => document.querySelectorAll('.tbt-resource-carousel__dots').forEach((e) => { e.style.opacity = '1'; }));
      const dots = page.locator('[data-carousel-dot]');
      const n = await dots.count();
      for (let i = 0; i < n; i++) {
        const dot = dots.nth(i);
        if (!(await dot.isVisible())) continue;
        await dot.scrollIntoViewIfNeeded();
        for (const action of ['hover', 'focus', 'press']) {
          if (action === 'press') { await dot.hover(); await page.mouse.down(); } else await dot[action]();
          await page.waitForTimeout(450);
          const bg = await dot.evaluate((e) => getComputedStyle(e).backgroundColor);
          const [r, g, b] = bg.match(/[\d.]+/g).map(Number);
          assert.ok(!(r > g + 60 && b > g + 20), `Dot ${i + 1} on /${route} must not turn pink on ${action}: ${bg}`);
          if (action === 'press') await page.mouse.up();
        }
      }
    }
    // 6. Every image slider has rounded corners (18px) on the shape that actually clips it.
    for (const width of [1440, 390]) {
      await page.setViewportSize({ width, height: 900 });
      for (const route of ['', 'intensivefix/', 'classes/', 'sec-3-pure-g3-biology-tuition/', 'sec-4-pure-g3-biology-tuition/', 'testimonials/', 'notes/', 'about/', 'trees/', 'celldivision/', 'tuitionrecs/']) {
        await page.goto(new URL(route, base).href, { waitUntil: 'load' });
        const sliders = await page.evaluate(() => [...document.querySelectorAll('[data-carousel]')].map((c) => {
          const cs = (e) => getComputedStyle(e); const clips = (e) => cs(e).overflow !== 'visible';
          const vp = c.querySelector('[class*="viewport"]'); const img = c.querySelector('img');
          const radius = clips(c) ? cs(c).borderTopLeftRadius : vp && clips(vp) ? cs(vp).borderTopLeftRadius : img ? cs(img).borderTopLeftRadius : '0px';
          return { name: String(c.className).split(' ')[0], radius: parseFloat(radius) };
        }));
        for (const sl of sliders) assert.ok(sl.radius >= 18, `Slider ${sl.name} on /${route} at ${width} needs 18px rounded corners, has ${sl.radius}px`);
      }
    }
    // 7. Home "Don't just take our word for it": the second video (was Alicia's Story) is Gwen's Story; first video unchanged.
    await page.goto(new URL('', base).href, { waitUntil: 'load' });
    const vids = await page.locator('[data-section="proof"] .tbt-home-video iframe').evaluateAll((els) => els.map((e) => ({ src: e.getAttribute('src'), title: e.getAttribute('title') })));
    assert.equal(vids.length, 2, 'Proof section keeps two videos');
    assert.ok(vids[0].src.includes('/embed/Vkzlr16AGvo'), 'First proof video is unchanged');
    assert.ok(/^https:\/\/www\.youtube-nocookie\.com\/embed\/V4dj5XYEcow(\?|$)/.test(vids[1].src), `Second proof video is the new YouTube video: ${vids[1].src}`);
    assert.ok(!vids.some((v) => v.src.includes('RJxR3ONbylQ')), "Alicia's video is no longer in the proof section");
    assert.ok(/Gwen/.test(vids[1].title), 'Second video has a matching title');
    // 8. Home "Trusted by students from 98 schools": large crests, every crest in an identical square box.
    for (const [vw, minBox] of [[1440, 130], [1024, 130], [768, 115], [390, 100], [320, 80]]) {
      await page.setViewportSize({ width: vw, height: 900 });
      await page.goto(new URL('', base).href, { waitUntil: 'load' });
      const crests = await page.locator('.tbt-home-proof__crests img').evaluateAll((els) => els.map((e) => { const r = e.getBoundingClientRect(); return { w: Math.round(r.width * 10) / 10, h: Math.round(r.height * 10) / 10, fit: getComputedStyle(e).objectFit }; }));
      assert.ok(crests.length >= 12, `School crests present at ${vw}`);
      assert.ok(crests.every((c) => c.w === crests[0].w && c.h === crests[0].h), `All school crests are the same size at ${vw}: ${JSON.stringify(crests.slice(0, 3))}`);
      assert.ok(Math.abs(crests[0].w - crests[0].h) <= 1, `School crests sit in square boxes at ${vw}`);
      assert.ok(crests[0].w >= minBox && crests[0].w <= minBox + 30, `School crests are a comfortable size at ${vw}: ${crests[0].w}px (needs ${minBox}-${minBox + 30}px)`);
      assert.equal(crests[0].fit, 'contain', 'Crests are never cropped or stretched');
    }
    await page.setViewportSize({ width: 1440, height: 900 });
    // 9. Sec 3 and Sec 4 hero: the green phrases are not underlined (no text-decoration, border or marker stroke).
    for (const [vw, route] of [[1440, 'sec-3-pure-g3-biology-tuition/'], [1440, 'sec-4-pure-g3-biology-tuition/'], [390, 'sec-3-pure-g3-biology-tuition/'], [390, 'sec-4-pure-g3-biology-tuition/']]) {
      await page.setViewportSize({ width: vw, height: 900 });
      await page.goto(new URL(route, base).href, { waitUntil: 'load' });
      await page.waitForTimeout(900);
      const marks = await page.locator('.tbt-programme-hero :is(h1, p) *').evaluateAll((els) => els.map((e) => { const s = getComputedStyle(e); return { text: e.textContent.trim().slice(0, 24), line: s.textDecorationLine, border: s.borderBottomWidth, image: s.backgroundImage, shadow: s.boxShadow }; }));
      assert.ok(marks.length >= 2, `Hero has green phrases on ${route}`);
      for (const m of marks) assert.ok(m.line === 'none' && m.border === '0px' && m.image === 'none' && m.shadow === 'none', `Hero phrase "${m.text}" is not underlined on ${route} at ${vw}: ${JSON.stringify(m)}`);
    }
    await page.setViewportSize({ width: 1440, height: 900 });
    // 10. Free Bio Notes: every DOWNLOAD button leads to the email form section (never to the Contact page).
    await page.setViewportSize({ width: 1440, height: 900 });
    await page.goto(new URL('notes/', base).href, { waitUntil: 'load' });
    const dl = await page.locator('#main-content a.tbt-resource-button').evaluateAll((els) => els.filter((e) => /download/i.test(e.textContent)).map((e) => e.getAttribute('href')));
    assert.ok(dl.length >= 4, `Free Bio Notes has its DOWNLOAD buttons: ${dl.length}`);
    for (const href of dl) assert.ok(/#notes-form$/.test(href), `Every DOWNLOAD button leads to the form section, not ${href}`);
    assert.equal(await page.locator('#notes-form form').count(), 1, 'The #notes-form section contains the email form');
    for (const i of [0, dl.length - 1]) {
      await page.goto(new URL('notes/', base).href, { waitUntil: 'load' });
      const btn = page.locator('#main-content a.tbt-resource-button', { hasText: /download/i }).nth(i);
      await btn.scrollIntoViewIfNeeded(); await btn.click(); await page.waitForTimeout(1600);
      const box = await page.locator('#notes-form form').boundingBox();
      assert.ok(box && box.y >= 0 && box.y < 900, `Clicking DOWNLOAD #${i} brings the form into view (form top ${box && box.y})`);
      assert.ok(/#notes-form$/.test(page.url()) && !/contact/.test(page.url()), `Stays on the notes page: ${page.url()}`);
    }
    // 11. "Real Practical Sessions. Real Practice." uses the full content width and has room above it (Classes, Sec 3, Sec 4).
    for (const vw of [1440, 390]) {
      await page.setViewportSize({ width: vw, height: 900 });
      for (const route of ['classes/', 'sec-3-pure-g3-biology-tuition/', 'sec-4-pure-g3-biology-tuition/']) {
        await page.goto(new URL(route, base).href, { waitUntil: 'load' });
        const f = await page.locator('.tbt-practical-feature').evaluate((e) => { const r = e.getBoundingClientRect(), p = e.querySelector('p').getBoundingClientRect(), prev = e.previousElementSibling.getBoundingClientRect(); return { fw: r.width, pw: p.width, gap: r.top - prev.bottom }; });
        assert.ok(Math.abs(f.pw - f.fw) <= 1, `Practical-sessions text uses the full content width on ${route} at ${vw}: ${f.pw} of ${f.fw}`);
        assert.ok(f.gap >= (vw > 520 ? 72 : 56) - 1, `Practical-sessions block has padding above it on ${route} at ${vw}: ${f.gap}px`);
      }
    }
    await page.setViewportSize({ width: 1440, height: 900 });
    // 12. One green for every green button: same colour at rest and the same darker green on hover; the floating WhatsApp button too.
    await page.setViewportSize({ width: 1440, height: 900 });
    const BRAND = 'rgb(67, 180, 58)', HOVER = 'rgb(58, 159, 50)';
    const buttonSets = [['', ['.site-header__cta', '.tbt-home-button']], ['intensivefix/', ['.tbt-home-button']], ['classes/', ['.tbt-programme-button']], ['sec-3-pure-g3-biology-tuition/', ['.tbt-programme-hall__action', '.tbt-programme-text-link']], ['testimonials/', ['.tbt-testimonials-button']], ['notes/', ['.tbt-resource-button', '.tbt-notes-form .wpcf7-submit']], ['contact/', ['.tbt-institutional-button']]];
    for (const [route, sels] of buttonSets) {
      await page.goto(new URL(route, base).href, { waitUntil: 'load' });
      for (const sel of sels) {
        const el = page.locator(sel).first();
        await el.scrollIntoViewIfNeeded();
        assert.equal(await el.evaluate((e) => getComputedStyle(e).backgroundColor), BRAND, `${sel} on /${route} is the brand green at rest`);
        await el.hover(); await page.waitForTimeout(550);
        assert.equal(await el.evaluate((e) => getComputedStyle(e).backgroundColor), HOVER, `${sel} on /${route} uses the shared hover green`);
        await page.mouse.move(0, 0);
      }
    }
    for (const [route, state] of [['', 'rest'], ['', 'hover']]) {
      await page.goto(new URL(route, base).href, { waitUntil: 'load' });
      const wa = page.locator('.whatsapp-float');
      await page.addStyleTag({ content: '.whatsapp-float{animation:none!important;box-shadow:none!important}' });
      if (state === 'hover') { await wa.hover(); await page.waitForTimeout(500); }
      const shot = (await wa.screenshot()).toString('base64');
      const got = await page.evaluate(async (b64) => {
        const bmp = await createImageBitmap(await (await fetch('data:image/png;base64,' + b64)).blob());
        const c = document.createElement('canvas'); c.width = bmp.width; c.height = bmp.height; const g = c.getContext('2d'); g.drawImage(bmp, 0, 0);
        return [...g.getImageData(Math.round(bmp.width * 0.12), Math.round(bmp.height * 0.5), 1, 1).data].slice(0, 3);
      }, shot);
      const want = state === 'rest' ? [67, 180, 58] : [58, 159, 50];
      assert.ok(got.every((v, k) => Math.abs(v - want[k]) <= 8), `WhatsApp button ${state} colour is ${want} (got ${got})`);
    }
    // 13. The footer is shorter (was 268px on desktop, 414px on phones) with nothing clipped.
    for (const [vw, max] of [[1440, 190], [1024, 190], [768, 190], [390, 345]]) {
      await page.setViewportSize({ width: vw, height: 900 });
      await page.goto(new URL('contact/', base).href, { waitUntil: 'load' });
      const f = await page.locator('.site-footer').evaluate((e) => { const r = e.getBoundingClientRect(); const kids = [...e.querySelectorAll('.site-footer__brand, .site-footer__column, .site-footer__copyright')].map((k) => k.getBoundingClientRect()); return { h: r.height, inside: kids.every((k) => k.top >= r.top && k.bottom <= r.bottom + 0.5), overflow: document.documentElement.scrollWidth - innerWidth }; });
      assert.ok(f.h <= max, `Footer is compact at ${vw}: ${f.h}px (max ${max})`);
      assert.ok(f.inside && f.overflow === 0, `Footer content fits inside the footer at ${vw}`);
    }
    await page.setViewportSize({ width: 1440, height: 900 });
    // 14. Results & Testimonials, Class of 2021: "100% of our Class of 2021 improved their grade in 6 months or less" is one row on tablet and desktop and uses the full content width.
    for (const vw of [1440, 1024, 768, 600, 390]) {
      await page.setViewportSize({ width: vw, height: 900 });
      await page.goto(new URL('testimonials/', base).href, { waitUntil: 'load' });
      await page.evaluate(() => document.fonts.ready);
      const r = await page.locator('#class-of-2021 .tbt-testimonials-results-band p').evaluate((e) => { const b = e.getBoundingClientRect(), s = getComputedStyle(e), c = e.closest('.tbt-testimonials-container').getBoundingClientRect(); const range = document.createRange(); range.selectNodeContents(e); return { rows: new Set([...range.getClientRects()].map((q) => Math.round(q.top))).size, w: b.width, cw: c.width, hasBr: !!e.querySelector('br'), text: e.textContent.replace(/\s+/g, ' ').trim(), size: parseFloat(s.fontSize) }; });
      assert.equal(r.text, '100% of our Class of 2021 improved their grade in 6 months or less', 'The phrase is unchanged');
      assert.ok(!r.hasBr, 'No forced line break');
      assert.ok(Math.abs(r.w - r.cw) <= 1, `The phrase uses the full content width at ${vw}: ${r.w} of ${r.cw}`);
      if (vw >= 600) assert.equal(r.rows, 1, `The phrase is one row at ${vw} (${r.rows} rows, ${r.size}px)`);
    }
    await page.setViewportSize({ width: 1440, height: 900 });
    // 15. Results & Testimonials year menu: choosing a year scrolls to it AND the menu label shows that year once the smooth scroll ends.
    for (const vw of [1440, 390]) {
      await page.setViewportSize({ width: vw, height: 900 });
      await page.goto(new URL('testimonials/', base).href, { waitUntil: 'load' });
      for (const year of ['2023', '2021', '2022', '2024', '2023']) {
        await page.locator('[data-year-nav] button').click();
        await page.locator(`[data-year-nav] a[href$="#class-of-${year}"]`).click();
        // Lazy images above the target load while it scrolls and push the section down a little; the label must still be the chosen year.
        await page.waitForTimeout(250);
        await page.evaluate(() => { const sp = document.createElement('div'); sp.className = 'tbt-test-shift'; sp.style.height = '36px'; document.querySelector('#class-of-2025, .tbt-testimonials-hero').insertAdjacentElement('afterend', sp); });
        let last = -1, still = 0;
        for (let i = 0; i < 60 && still < 8; i++) { await page.waitForTimeout(100); const y = await page.evaluate(() => Math.round(scrollY)); still = y === last ? still + 1 : 0; last = y; }
        await page.waitForTimeout(300);
        const r = await page.evaluate((y) => { document.querySelectorAll('.tbt-test-shift').forEach((e) => e.remove()); return ({ label: document.querySelector('[data-year-current]').textContent, current: document.querySelector('[data-year-nav] [aria-current="location"] .tbt-year-nav__title')?.textContent, top: document.querySelector('#class-of-' + y).getBoundingClientRect().top, header: document.querySelector('.site-header').getBoundingClientRect().bottom }); }, year);
        assert.equal(r.label, year, `Year menu label shows ${year} after choosing it at ${vw} (shows ${r.label})`);
        assert.equal(r.current, `Class of ${year}`, `Year menu highlights ${year} at ${vw}`);
        assert.ok(r.top >= r.header - 2 && r.top <= r.header + 40, `The ${year} section sits just under the header at ${vw}: ${r.top} vs header ${r.header}`);
      }
    }
    // ...and once the reader scrolls on with the wheel, the label follows the page again.
    await page.setViewportSize({ width: 1440, height: 900 });
    await page.goto(new URL('testimonials/', base).href, { waitUntil: 'load' });
    await page.locator('[data-year-nav] button').click();
    await page.locator('[data-year-nav] a[href$="#class-of-2023"]').click();
    await page.waitForTimeout(2500);
    assert.equal(await page.locator('[data-year-current]').textContent(), '2023');
    const toNext = await page.locator('#class-of-2022').evaluate((e) => e.getBoundingClientRect().top + 200);
    await page.mouse.move(700, 450); await page.mouse.wheel(0, toNext); await page.waitForTimeout(1200);
    assert.equal(await page.locator('[data-year-current]').textContent(), '2022', 'Scrolling on with the wheel updates the year label');
    await page.setViewportSize({ width: 1440, height: 900 });
    // 16. Results & Testimonials: no dark-green block behind the result pictures; instead every year starts with a green "Class of YYYY" panel
    //     (content width, rounded, centred) above its pictures. 2021 keeps its green panel with the growth chart and summary.
    for (const vw of [1440, 1024, 768, 390, 320]) {
      await page.setViewportSize({ width: vw, height: 900 });
      await page.goto(new URL('testimonials/', base).href, { waitUntil: 'load' });
      for (const year of ['2025', '2024', '2023', '2022', '2021']) {
        const sel = year === '2021' ? `#class-of-${year} .tbt-testimonials-results-band` : `#class-of-${year} .tbt-testimonials-year-panel`;
        const r = await page.locator(sel).evaluate((e, y) => { const b = e.getBoundingClientRect(), st = getComputedStyle(e), h = e.querySelector('h2'); const img = document.querySelector(`#class-of-${y} .tbt-testimonials-artwork img`); const ib = img && img.getBoundingClientRect(); return { w: Math.round(b.width), centre: Math.round(b.left + b.width / 2), bg: st.backgroundColor, radius: parseFloat(st.borderTopLeftRadius), text: h && h.textContent.trim(), hv: h && h.getBoundingClientRect().height > 10, colour: h && getComputedStyle(h).color, above: ib ? b.bottom <= ib.top + 1 : true }; }, year);
        assert.equal(r.text, `Class of ${year}`, `${year} has a visible green panel title at ${vw}`);
        assert.ok(r.hv && r.colour === 'rgb(255, 255, 255)', `${year} panel title is visible white text at ${vw}`);
        assert.equal(r.bg, 'rgb(14, 55, 29)', `${year} panel is the dark green at ${vw}`);
        assert.ok(Math.abs(r.w - Math.min(1120, vw - 32)) <= 2 && Math.abs(r.centre - vw / 2) <= 2, `${year} panel uses the content width at ${vw}: ${r.w}px`);
        assert.ok(r.radius >= 18, `${year} panel is rounded at ${vw}`);
        assert.ok(r.above, `${year} panel sits above its result pictures at ${vw}`);
      }
      const bands = await page.locator('.tbt-testimonials-artwork-band').evaluateAll((els) => els.map((e) => getComputedStyle(e).backgroundColor));
      assert.ok(bands.length >= 3 && bands.every((c) => c === 'rgba(0, 0, 0, 0)'), `No dark-green block behind the result pictures at ${vw}: ${bands.join(' | ')}`);
      assert.equal(await page.evaluate(() => document.documentElement.scrollWidth - innerWidth), 0, `No sideways scroll at ${vw}`);
    }
    await page.setViewportSize({ width: 1440, height: 900 });
    // 17. The year menu has more detail: each year shows its headline result under the title (taken from that year's own result pictures).
    const wantSummary = { '2025': /86%.*B3.*46%.*A1\/A2/, '2024': /94%.*As and Bs.*62%.*A1\/A2/, '2023': /92%.*As and Bs.*69%.*A1\/A2/, '2022': /64%.*A1\/A2/, '2021': /100%.*improved.*6 months/ };
    for (const vw of [1440, 768, 390, 320]) {
      await page.setViewportSize({ width: vw, height: 900 });
      await page.goto(new URL('testimonials/', base).href, { waitUntil: 'load' });
      await page.locator('[data-year-nav] button').click();
      const rows = await page.locator('[data-year-nav] nav a.tbt-year-nav__year').evaluateAll((els) => els.map((e) => ({ year: e.hash.replace('#class-of-', ''), title: e.querySelector('.tbt-year-nav__title')?.textContent.trim(), summary: e.querySelector('.tbt-year-nav__summary')?.textContent.replace(/\s+/g, ' ').trim(), h: e.getBoundingClientRect().height, sumSize: parseFloat(getComputedStyle(e.querySelector('.tbt-year-nav__summary') || e).fontSize) })));
      assert.equal(rows.length, 5, 'Five years in the menu');
      for (const r of rows) {
        assert.equal(r.title, `Class of ${r.year}`, `Menu title for ${r.year} at ${vw}`);
        assert.ok(r.summary && wantSummary[r.year].test(r.summary), `Menu shows the ${r.year} headline result at ${vw}: "${r.summary}"`);
        assert.ok(r.h >= 44 && r.sumSize >= 12, `Menu row for ${r.year} is touch-sized and readable at ${vw}: ${r.h}px / ${r.sumSize}px`);
      }
      const g = await page.evaluate(() => { const p = document.querySelector('[data-year-nav] nav').getBoundingClientRect(), w = document.querySelector('.whatsapp-float').getBoundingClientRect(); return { l: p.left, r: p.right, t: p.top, b: p.bottom, waL: w.left, waT: w.top, waB: w.bottom, vw: innerWidth, vh: innerHeight, overflow: document.documentElement.scrollWidth - innerWidth }; });
      assert.ok(g.l >= 0 && g.r <= g.vw && g.t >= 0 && g.b <= g.vh, `Menu panel fits the screen at ${vw}: ${JSON.stringify(g)}`);
      assert.ok(g.r <= g.waL || g.b <= g.waT || g.t >= g.waB, `Menu panel does not cover the WhatsApp button at ${vw}`);
      assert.equal(g.overflow, 0, `No sideways scroll at ${vw}`);
    }
    await page.setViewportSize({ width: 1440, height: 900 });
    // 18. Year menu sections: under the current year the menu lists what that class has on the page - result poster, results table,
    //     student stories, Google reviews - and each link scrolls to that part (the year label stays the year).
    const expectSections = { '2025': ['Result poster', 'Student stories', 'Google reviews'], '2024': ['Result poster', 'Results table', 'Student stories', 'Google reviews'], '2023': ['Result poster', 'Results table', 'Student stories', 'Google reviews'], '2022': ['Results table', 'Student stories', 'Google reviews'], '2021': ['Student stories', 'Google reviews'] };
    const idFor = { 'Result poster': 'poster', 'Results table': 'table', 'Student stories': 'stories', 'Google reviews': 'google-reviews' };
    for (const vw of [1440, 390]) {
      await page.setViewportSize({ width: vw, height: 900 });
      await page.goto(new URL('testimonials/', base).href, { waitUntil: 'load' });
      for (const [year, labels] of Object.entries(expectSections)) {
        await page.locator('[data-year-nav] button').click();
        await page.locator(`[data-year-nav] a.tbt-year-nav__year[href$="#class-of-${year}"]`).click();
        await page.waitForTimeout(1800);
        await page.locator('[data-year-nav] button').click();
        const group = page.locator(`[data-year-nav] .tbt-year-nav__group:has(a[href$="#class-of-${year}"])`);
        const shown = await group.locator('a.tbt-year-nav__section').evaluateAll((els) => els.filter((e) => e.getClientRects().length).map((e) => ({ text: e.textContent.trim(), h: e.getBoundingClientRect().height })));
        assert.deepEqual(shown.map((x) => x.text), labels, `Menu lists the ${year} sections at ${vw}: ${JSON.stringify(shown)}`);
        assert.ok(shown.every((x) => x.h >= 44), `Section links are touch-sized at ${vw}`);
        const others = await page.locator(`[data-year-nav] .tbt-year-nav__group:not(:has(a[href$="#class-of-${year}"])) a.tbt-year-nav__section`).evaluateAll((els) => els.filter((e) => e.getClientRects().length).length);
        assert.equal(others, 0, `Only the current year shows its sections at ${vw}`);
        await page.keyboard.press('Escape');
      }
      for (const [year, label] of [['2024', 'Results table'], ['2023', 'Google reviews'], ['2025', 'Student stories'], ['2022', 'Results table'], ['2021', 'Student stories']]) {
        await page.locator('[data-year-nav] button').click();
        await page.locator(`[data-year-nav] a.tbt-year-nav__year[href$="#class-of-${year}"]`).click();
        await page.waitForTimeout(1800);
        await page.locator('[data-year-nav] button').click();
        await page.locator(`[data-year-nav] .tbt-year-nav__group:has(a[href$="#class-of-${year}"]) a.tbt-year-nav__section`, { hasText: label }).click();
        let last = -1, still = 0;
        for (let i = 0; i < 60 && still < 8; i++) { await page.waitForTimeout(100); const y = await page.evaluate(() => Math.round(scrollY)); still = y === last ? still + 1 : 0; last = y; }
        await page.waitForTimeout(300);
        const r = await page.evaluate(([y, id]) => { const t = document.getElementById(`class-of-${y}-${id}`); const b = t.getBoundingClientRect(); return { label: document.querySelector('[data-year-current]').textContent, top: b.top, header: document.querySelector('.site-header').getBoundingClientRect().bottom, open: document.querySelector('[data-year-nav] button').getAttribute('aria-expanded') }; }, [year, idFor[label]]);
        assert.equal(r.label, year, `Jumping to ${year} ${label} keeps the ${year} label at ${vw}`);
        assert.ok(r.top >= r.header - 2 && r.top <= r.header + 60, `${year} ${label} lands just under the header at ${vw}: ${r.top} vs ${r.header}`);
        assert.equal(r.open, 'false', 'The menu closes after jumping');
      }
    }
    await page.setViewportSize({ width: 1440, height: 900 });
    console.log('PASS site feedback contract');
  } finally { await browser.close(); }
})().catch((e) => { console.error(e.message); process.exit(1); });
