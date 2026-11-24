// Test our embed container without a separate signup link. Live provider fields are checked separately without submitting.
const assert = require('node:assert/strict');
const { chromium } = require('playwright');
const base = (process.env.TBT_RUNTIME_URL || 'http://localhost:8888').replace(/\/?$/, '/');
const provider = 'https://12-dec-2025-updated-signup-form-02264.getresponsesite.com/';
(async () => {
 const browser = await chromium.launch();
 try {
  const page = await browser.newPage();
  await page.route(provider, route=>route.fulfill({contentType:'text/html',body:'<!doctype html><title>Signup provider fixture</title>'}));
  for(const width of [1440,1024,768,390,320]) {
   await page.setViewportSize({width,height:900});
   await page.goto(new URL('notes/',base).href);
   const frame=page.locator('.tbt-notes-form__embed');
   assert.equal(await frame.getAttribute('src'),provider);
   assert.equal(await frame.getAttribute('title'),'Free Bio Notes signup form');
   const box=await frame.boundingBox();
   if (width >= 768) assert.ok(box.height === 440, 'Wide signup frame contains Submit without exposing the provider footer');
   assert.ok(box.width>=width-32 || box.width>=700);
   assert.ok(box.x>=8&&box.x+box.width<=width-8&&box.height>=420);
   assert.equal(await page.locator('.tbt-notes-form__alternative').count(),0);
   assert.equal(await page.locator('.tbt-notes-form form,.tbt-notes-form [data-tbt-redirect]').count(),0);
   assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth));
   console.log('PASS GetResponse container',width);
  }
 } finally {await browser.close();}
})().catch(error=>{console.error(error);process.exit(1)});
