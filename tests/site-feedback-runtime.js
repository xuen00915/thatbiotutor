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
    console.log('PASS site feedback contract');
  } finally { await browser.close(); }
})().catch((e) => { console.error(e.message); process.exit(1); });
