// Free Bio Notes (/notes/): the hero (title, audience line, preview slider, DOWNLOAD button and downloads count)
// fits the first screen, and its text uses the original Montserrat look with an italic audience line.
const assert = require('node:assert/strict');
const { chromium } = require('playwright');
const base = (process.env.TBT_RUNTIME_URL || 'http://localhost:8888').replace(/\/?$/, '/');
(async () => {
  const browser = await chromium.launch();
  try {
    const page = await browser.newPage();
    page.setDefaultTimeout(10000);
    for (const [width, height] of [[1792, 1056], [1440, 900], [1366, 768], [1280, 600], [1024, 768], [864, 510], [768, 1024], [390, 844], [360, 740]]) {
      await page.setViewportSize({ width, height });
      await page.goto(new URL('./notes/', base).href, { waitUntil: 'load' });
      await page.evaluate(() => document.fonts?.ready);
      await page.waitForTimeout(500);
      const m = await page.evaluate(() => {
        const cs = (selector) => getComputedStyle(document.querySelector(selector));
        const box = (selector) => document.querySelector(selector).getBoundingClientRect();
        const aud = document.querySelector('.tbt-notes-hero__audience');
        const span = aud.querySelector('span');
        return {
          proofBottom: box('.tbt-notes-hero .tbt-resource-proof').bottom,
          buttonBottom: box('.tbt-notes-hero .tbt-resource-button').bottom,
          sliderHeight: box('.tbt-resource-carousel--notes').height,
          sliderBottom: box('.tbt-resource-carousel--notes').bottom,
          h1Bottom: box('.tbt-notes-hero h1').bottom,
          h1Right: box('.tbt-notes-hero h1').right,
          sliderBg: cs('.tbt-resource-carousel--notes .tbt-resource-carousel__viewport').backgroundColor,
          arrow: box('.tbt-resource-carousel--notes .tbt-resource-carousel__control').width,
          dotBox: box('.tbt-resource-carousel--notes [data-carousel-dot]').width,
          dotDraw: parseFloat(getComputedStyle(document.querySelector('.tbt-resource-carousel--notes [data-carousel-dot]'), '::before').width),
          viewportWidth: document.documentElement.clientWidth,
          h1Font: cs('.tbt-notes-hero h1').fontFamily,
          audFont: cs('.tbt-notes-hero__audience').fontFamily,
          audStyle: cs('.tbt-notes-hero__audience').fontStyle,
          audColor: getComputedStyle(aud).color,
          spanFont: span ? getComputedStyle(span).fontFamily : null,
          synthesis: getComputedStyle(aud).fontSynthesis,
          spanColor: span ? getComputedStyle(span).color : null,
          overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
        };
      });
      const at = `at ${width}x${height}: ${JSON.stringify(m)}`;
      assert.ok(m.proofBottom <= height, `The whole hero (down to the downloads count) must fit one screen ${at}`);
      if (width >= 769 && height >= 600) assert.ok(m.sliderHeight >= Math.min(760, height - 300) - 2, `The tablet picture must use the free height of the screen ${at}`);
      assert.ok(m.sliderBottom <= height && m.proofBottom <= height, `Title, picture and button must share one screen ${at}`);
      assert.ok(m.h1Right <= m.viewportWidth, `Title must not be cut off at the right edge ${at}`);
      assert.equal(m.sliderBg, 'rgba(0, 0, 0, 0)', `The slider must not sit on a white card ${at}`);
      assert.ok(m.arrow <= 44, `Slider arrows must not be oversized ${at}`);
      assert.ok(m.dotBox <= 32 && m.dotDraw <= 12, `Slider dots must stay small ${at}`);
      assert.ok(m.sliderHeight >= (height < 560 ? 150 : 240), `The preview slider must stay readable ${at}`);
      assert.match(m.h1Font, /^"?Montserrat/, `Title must use Montserrat ${at}`);
      assert.match(m.audFont, /^"?Montserrat/, `Audience line must use Montserrat ${at}`);
      assert.match(m.spanFont, /^"?Montserrat/, `Green part of the audience line must use Montserrat ${at}`);
      assert.ok(m.synthesis.includes('style'), `Italic must actually be drawn (font synthesis for style) ${at}`);
      assert.equal(m.audStyle, 'italic', `Audience line must be italic ${at}`);
      assert.ok(m.spanColor && m.spanColor !== m.audColor, `Only "Pure / G3 Biology" is green ${at}`);
      assert.ok(m.overflow <= 0, `Horizontal overflow ${at}`);
      console.log(`PASS notes hero ${width}x${height}`);
    }
  } finally { await browser.close(); }
})().catch((e) => { console.error(e.message); process.exit(1); });
