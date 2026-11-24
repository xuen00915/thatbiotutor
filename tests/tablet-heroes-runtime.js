const assert = require('node:assert/strict');
const { chromium } = require('playwright');
const base = (process.env.TBT_RUNTIME_URL || 'http://localhost:8888/').replace(/\/?$/, '/');
(async () => {
 const browser = await chromium.launch();
 try {
  const page = await browser.newPage();
  for (const width of [768, 820, 1024, 1180]) {
   await page.setViewportSize({width, height:900});
   const heights = [];
   for (const route of ['testimonials', 'classes', 'sec-3-pure-g3-biology-tuition', 'sec-4-pure-g3-biology-tuition']) {
    await page.goto(new URL(route+'/',base).href, {waitUntil:'load'});
    await page.evaluate(() => document.fonts.ready);
    const m = await page.evaluate(() => {
     const rect = s => document.querySelector(s)?.getBoundingClientRect().toJSON();
     return {title:rect('h1'),photo:rect('.tbt-testimonials-hero__photo'),hero:rect('.tbt-programme-hero'),content:rect('.tbt-programme-hero__content'),buttons:[...document.querySelectorAll('.tbt-programme-hero__actions a')].map(e=>e.getBoundingClientRect().toJSON()),overflow:document.documentElement.scrollWidth-innerWidth};
    });
    assert.ok(m.overflow <= 0, `${route} overflow at ${width}`);
    if (route === 'testimonials') assert.ok(m.photo.left >= m.title.right && m.photo.top < m.title.bottom, `Tablet testimonials uses two columns at ${width}`);
    else if (route === 'classes') assert.ok(m.buttons.length===2 && Math.abs(m.buttons[0].top-m.buttons[1].top)<1, `Tablet class buttons share a row at ${width}`);
    else {
     heights.push(m.hero.height);
     assert.ok(m.hero.height <= 480 && m.content.top >= m.hero.top && m.content.bottom <= m.hero.bottom, `Compact programme hero contains its copy at ${width}: ${JSON.stringify(m)}`);
    }
   }
   assert.equal(heights[0],heights[1],`Programme hero heights match at ${width}`);
   console.log(`PASS tablet heroes ${width}`);
  }
 } finally { await browser.close(); }
})().catch(e=>{console.error(e);process.exit(1);});
