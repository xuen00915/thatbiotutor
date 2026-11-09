const assert = require('node:assert/strict');
const { chromium } = require('playwright');
(async () => {
 const browser = await chromium.launch();
 try {
  for (const reducedMotion of ['reduce', 'no-preference']) {
   const page = await browser.newPage({ reducedMotion, viewport: {width:390,height:844} });
   for (const level of [3,4]) {
    await page.goto(`${(process.env.TBT_RUNTIME_URL || 'http://localhost:8892').replace(/\/$/,'')}/sec-${level}-pure-g3-biology-tuition/`);
    const button = page.locator('[data-faq-button]').first();
    await button.scrollIntoViewIfNeeded();
    await button.focus();
    assert.equal(await button.evaluate(e=>getComputedStyle(e).outlineStyle),'solid','Keyboard focus is explicit');
    await page.keyboard.press('Enter');
    await page.waitForTimeout(400);
    assert.equal(await button.getAttribute('aria-expanded'),'true');
    assert.equal(await button.evaluate(e=>getComputedStyle(e.closest('.tbt-faq')).backgroundColor),'rgb(238, 246, 234)','Open answer has a quiet green reading surface');
    await page.keyboard.press('Enter');
    await page.waitForFunction(() => document.querySelector('[data-faq-panel]').hidden);
    assert.equal(await button.getAttribute('aria-expanded'),'false');
    assert.ok(await page.locator('[data-faq-panel]').first().evaluate(e=>e.hidden));
    if(reducedMotion==='reduce') assert.equal(await page.locator('.tbt-programme-hero__highlight').first().evaluate(e=>getComputedStyle(e).animationName),'none');
    assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth),false);
   }
   await page.close();
  }
  console.log('Programme polish: keyboard FAQ, mobile layout and reduced motion passed');
 } finally {await browser.close();}
})().catch(e=>{console.error(e);process.exit(1);});
