// Free Bio Notes (/notes/): split hero. Desktop: the title, audience line, introduction, DOWNLOAD button and downloads line are on the left,
// the tablet preview slider on the right, and both fit in the first screen. Narrow screens stack them and centre the text.
// Every DOWNLOAD button carries the download icon.
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
        const h1Span = document.querySelector('.tbt-notes-hero h1 span');
        return {
          viewportWidth: document.documentElement.clientWidth,
          h1: box('.tbt-notes-hero h1'), h1Font: cs('.tbt-notes-hero h1').fontFamily, h1Size: parseFloat(cs('.tbt-notes-hero h1').fontSize), h1Align: cs('.tbt-notes-hero h1').textAlign,
          h1SpanText: h1Span ? h1Span.textContent : null, h1SpanColor: h1Span ? getComputedStyle(h1Span).color : null,
          audFont: cs('.tbt-notes-hero__audience').fontFamily, audStyle: cs('.tbt-notes-hero__audience').fontStyle,
          lead: box('.tbt-notes-hero__lead'), leadText: document.querySelector('.tbt-notes-hero__lead').textContent,
          button: box('.tbt-notes-hero .tbt-resource-button'), buttonIcon: !!document.querySelector('.tbt-notes-hero .tbt-resource-button .tbt-resource-button__icon'),
          proof: box('.tbt-notes-hero .tbt-resource-proof'), proofText: document.querySelector('.tbt-notes-hero .tbt-resource-proof').textContent.trim(),
          slider: box('.tbt-resource-carousel--notes'), sliderBg: cs('.tbt-resource-carousel--notes .tbt-resource-carousel__viewport').backgroundColor,
          arrow: box('.tbt-resource-carousel--notes .tbt-resource-carousel__control').width,
          dotBox: box('.tbt-resource-carousel--notes [data-carousel-dot]').width,
          buttons: [...document.querySelectorAll('.tbt-notes-page .tbt-resource-button')].map((b) => ({ text: b.textContent.trim(), icon: !!b.querySelector('.tbt-resource-button__icon') })),
          overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
        };
      });
      const at = `at ${width}x${height}: ${JSON.stringify(m)}`;
      assert.match(m.h1Font, /^"?Poppins/, `Title must use Poppins ${at}`);
      assert.match(m.audFont, /^"?Poppins/, `Audience line must use Poppins ${at}`);
      assert.equal(m.h1SpanText, 'Biology Notes', `"Biology Notes" is the green part of the title ${at}`);
      assert.ok(m.h1SpanColor === 'rgb(143, 225, 126)', `"Biology Notes" is light green ${at}`);
      assert.ok(/Why struggle with heavy textbooks\?/.test(m.leadText) && /60\+ memory shortcuts\./.test(m.leadText), `The introduction paragraph is shown ${at}`);
      assert.equal(m.proofText, 'Join over 10,000+ students who have downloaded.', `Downloads line ${at}`);
      assert.ok(m.buttonIcon, `The hero DOWNLOAD button has the download icon ${at}`);
      assert.ok(m.buttons.length >= 4 && m.buttons.every((b) => b.icon && /^DOWNLOAD/.test(b.text)), `Every DOWNLOAD button has the icon ${at}`);
      assert.equal(m.sliderBg, 'rgba(0, 0, 0, 0)', `The slider must not sit on a white card ${at}`);
      assert.ok(m.arrow <= 44 && m.dotBox <= 32, `Slider controls stay small ${at}`);
      assert.ok(m.h1.right <= m.viewportWidth, `Title must not be cut off at the right edge ${at}`);
      assert.ok(m.overflow <= 0, `Horizontal overflow ${at}`);
      if (width >= 768) {
        assert.equal(m.h1Align, 'left', `Title is left-aligned beside the slider ${at}`);
        assert.ok(m.slider.left >= m.h1.right - 4 && m.slider.left > m.viewportWidth / 2 - 40, `The slider sits on the right of the text ${at}`);
        assert.ok(m.h1.bottom <= height && m.slider.top < height - 100, `Title and the top of the tablet picture share the first screen ${at}`);
        if (height >= 700) assert.ok(m.slider.bottom <= height + 2 && m.proof.bottom <= height + 2, `Text, button and tablet picture share the first screen ${at}`);
        assert.ok(m.slider.height >= (height < 560 ? 150 : 240), `The preview slider must stay readable ${at}`);
      } else {
        assert.equal(m.h1Align, 'center', `Title is centred when stacked ${at}`);
        assert.ok(m.slider.top >= m.proof.bottom - 1, `The slider stacks under the text ${at}`);
        assert.ok(Math.abs((m.button.left + m.button.right) / 2 - m.viewportWidth / 2) <= 2, `The button is centred when stacked ${at}`);
      }
      console.log(`PASS notes hero ${width}x${height}`);
    }
  } finally { await browser.close(); }
})().catch((e) => { console.error(e.message); process.exit(1); });
