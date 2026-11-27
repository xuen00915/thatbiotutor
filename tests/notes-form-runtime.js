// Check the embed container. TBT_LIVE_EMBEDS=1 also measures the live provider fields without submitting.
const assert = require('node:assert/strict');
const { chromium } = require('playwright');
const base = (process.env.TBT_RUNTIME_URL || 'http://localhost:8888').replace(/\/?$/, '/');
const live = process.env.TBT_LIVE_EMBEDS === '1';
const provider = 'https://12-dec-2025-updated-signup-form-02264.getresponsesite.com/';
(async () => {
 const browser = await chromium.launch();
 try {
  const page = await browser.newPage();
  if (!live) await page.route(provider, route=>route.fulfill({contentType:'text/html',body:'<!doctype html><title>Signup provider fixture</title>'}));
  await page.goto(new URL('notes/',base).href);
  for(const width of [1440,1024,768,635,634,445,446,427,390,375,353,352,320]) {
   await page.setViewportSize({width,height:900});
   const frame=page.locator('.tbt-notes-form__embed');
   assert.equal(await frame.getAttribute('src'),provider);
   assert.equal(await frame.getAttribute('title'),'Free Bio Notes signup form');
   const box=await frame.boundingBox();
   const wrapper = page.locator('.tbt-notes-form--getresponse');
   const clip = await wrapper.boundingBox();
   const heading = await page.locator('#notes-form h2').boundingBox();
   assert.equal(Math.round(clip.y - heading.y - heading.height), 12, 'The embed sits closer to its heading');
   assert.equal(Math.round(clip.y - box.y), 28, 'Only the provider’s top whitespace is cropped');
   const frameWidth = box.width;
   const expected = frameWidth < 361 ? 537 : frameWidth < 362 ? 517 : frameWidth < 414 ? 497 : frameWidth < 603 ? 478 : 461;
   assert.equal(Math.round(box.height), expected, `Signup frame leaves room below Submit at ${width}px`);
   if (live) {
    await frame.scrollIntoViewIfNeeded();
    const form = frame.contentFrame();
    const submit = form.locator('button[type="submit"]');
    await submit.waitFor({ state: 'attached' });
    await submit.evaluate(async (button) => {
     await Promise.race([button.ownerDocument.fonts.ready, new Promise((resolve) => setTimeout(resolve, 1500))]);
    });
    const [submitBox, firstNameBox, frameBox, headingBox, wrapperBox] = await Promise.all([
     submit.boundingBox(),
     form.getByPlaceholder('First Name *', { exact: true }).boundingBox(),
     frame.boundingBox(),
     page.locator('#notes-form h2').boundingBox(),
     wrapper.boundingBox(),
    ]);
    const gap = firstNameBox.y - headingBox.y - headingBox.height;
    const room = frameBox.y + frameBox.height - submitBox.y - submitBox.height;
    assert.ok(gap >= 12 && gap <= 32, `First Name sits close to its heading at ${width}px (gap ${gap}px)`);
    assert.ok(room >= 18, `Submit stays clear of the bottom edge at ${width}px (room ${room}px)`);
    assert.ok(submitBox.x >= wrapperBox.x && submitBox.x + submitBox.width <= wrapperBox.x + wrapperBox.width, `Submit fits horizontally at ${width}px`);
   }
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
