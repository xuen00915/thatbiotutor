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
  for(const width of [1440,1024,768,427,390,375,320]) {
   await page.setViewportSize({width,height:900});
   await page.goto(new URL('notes/',base).href);
   const frame=page.locator('.tbt-notes-form__embed');
   assert.equal(await frame.getAttribute('src'),provider);
   assert.equal(await frame.getAttribute('title'),'Free Bio Notes signup form');
   const box=await frame.boundingBox();
   const clip=await page.locator('.tbt-notes-form--getresponse').boundingBox();
   // Height table (standard.css): Submit bottom at that form width + 36px, before the provider's dark footer strip.
   const cw=clip.width, want=width<=351?517:cw<=228?597:cw<=251?577:cw<=263?557:cw<=311?537:cw<=323?517:cw<=389?478:cw<=575?458:441;
   assert.equal(Math.round(box.height),want,`Signup frame height leaves room below Submit at ${width}`);
   assert.ok(clip.x>=0&&clip.x+clip.width<=width,'Visible signup box stays inside the page');
   assert.equal(await frame.getAttribute('scrolling'),'no','Signup frame never scrolls; Submit is fully shown');
   if(width<=1180) assert.ok(Math.abs((box.x+box.width/2)-(clip.x+clip.width/2))<=1,'Form stays centred in its box');
   if (width === 320) assert.equal(Math.round(box.width), 360, 'Small-phone provider viewport keeps Submit on one line');
   assert.equal(await page.locator('.tbt-notes-form__alternative').count(),0);
   assert.equal(await page.locator('.tbt-notes-form form,.tbt-notes-form [data-tbt-redirect]').count(),0);
   assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth));
   console.log('PASS GetResponse container',width);
  }
 } finally {await browser.close();}
})().catch(error=>{console.error(error);process.exit(1)});
