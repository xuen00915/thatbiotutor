// Test our embed container and direct fallback. Live provider fields are checked separately without submitting.
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
   assert.ok(box.width>=width-32 || box.width>=700);
   assert.ok(box.x>=8&&box.x+box.width<=width-8&&box.height>=540);
   const link=page.locator('.tbt-notes-form__alternative a');
   assert.equal(await link.getAttribute('href'),provider);
   assert.equal(await link.getAttribute('target'),'_blank');
   assert.equal(await page.locator('.tbt-notes-form form,.tbt-notes-form [data-tbt-redirect]').count(),0);
   assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth));
   console.log('PASS GetResponse container',width);
  }
 } finally {await browser.close();}
})().catch(error=>{console.error(error);process.exit(1)});
