const assert = require('node:assert/strict');
const {chromium} = require('playwright');
(async()=>{
 const browser=await chromium.launch();
 try {
 const page=await browser.newPage();
 for(const width of [1440,768,390,320]) {
  await page.setViewportSize({width,height:900});
  await page.goto(process.env.TBT_RUNTIME_URL || 'http://localhost:8888/',{waitUntil:'domcontentloaded'});
  for(const selector of ['.tbt-home-stats__photos figure','.tbt-home-tutors__grid article']) {
   const boxes=await page.locator(selector).evaluateAll(es=>es.map(e=>{const r=e.getBoundingClientRect();return {y:r.y,h:r.height}}));
   const columns=width>980?4:2;
   for(let i=0;i<boxes.length;i+=columns) assert.ok(boxes.slice(i,i+columns).every(b=>Math.abs(b.y-boxes[i].y)<1),`${selector}: staggered at ${width}`);
  }
  const portraits=await page.locator('.tbt-home-tutors__grid img').evaluateAll(es=>es.map(e=>({w:e.getBoundingClientRect().width,h:e.getBoundingClientRect().height,r:getComputedStyle(e).borderRadius})));
  assert.ok(portraits.every(p=>Math.abs(p.w-p.h)<1 && p.w<=144 && p.r==='50%'),'Tutors must have consistently sized circular portraits');
  assert.equal(await page.locator('.tbt-home-fit__yes').evaluate(e=>getComputedStyle(e).borderTopWidth),'2px');
  assert.equal(await page.locator('.tbt-home-fit__yes h2').evaluate(e=>getComputedStyle(e).color),'rgb(47, 138, 39)');
  const results=await page.locator('.tbt-home-proof__results').boundingBox();
  assert.ok(Math.abs(results.x+results.width/2-width/2)<1,'Results must be centered');
  assert.equal(await page.locator('.tbt-home-practical .tbt-home-eyebrow,.tbt-home-classroom .tbt-home-eyebrow,.tbt-home-tutors .tbt-home-eyebrow').count(),0);
  const rows=await page.locator('.tbt-home-quality li').evaluateAll(es=>es.map(e=>e.getBoundingClientRect().y));
  assert.ok(rows[0]<rows[1]&&rows[1]<rows[2],'Quality checks must stack');
  const faq=page.locator('.tbt-home-faq__item button').first();
  await faq.hover();
  await page.waitForFunction(()=>getComputedStyle(document.querySelector('.tbt-home-faq__item button')).backgroundColor==='rgb(237, 245, 232)');
  assert.equal(await faq.evaluate(e=>getComputedStyle(e).backgroundColor),'rgb(237, 245, 232)');
  const faqList=await page.locator('.tbt-home-faq__list').evaluate(e=>{const s=getComputedStyle(e);return {radius:s.borderTopLeftRadius,border:s.borderTopWidth,overflow:s.overflow}});
  assert.deepEqual(faqList,{radius:'18px',border:'1px',overflow:'hidden'});
  const faqIcon=await faq.evaluate(e=>getComputedStyle(e.querySelector('.tbt-home-faq__icon'),'::before').backgroundColor);
  assert.equal(faqIcon,'rgb(47, 138, 39)');
  await faq.click();assert.equal(await faq.getAttribute('aria-expanded'),'true');
  await page.waitForTimeout(600);
  assert.equal(await faq.evaluate(e=>getComputedStyle(e.querySelector('.tbt-home-faq__icon'),'::after').transform!=='none'||getComputedStyle(e.querySelector('.tbt-home-faq__icon'),'::after').opacity==='0'),true,'Open FAQ icon must turn into a minus');
  assert.ok(await page.locator('.tbt-home-faq__answer:not([hidden])').first().evaluate(e=>e.getBoundingClientRect().height>40),'Open FAQ answer must be visible');
  assert.equal(await page.evaluate(()=>getComputedStyle(document.documentElement).scrollBehavior),'smooth');
  assert.ok(await page.locator('.tbt-reveal').count()>5,'Sections must opt into the smooth reveal effect');
  await page.locator('.site-footer').scrollIntoViewIfNeeded();await page.waitForTimeout(900);
  assert.equal(await page.locator('.tbt-reveal:not(.is-visible)').evaluateAll(es=>es.filter(e=>e.getBoundingClientRect().top<innerHeight*0.5&&e.getBoundingClientRect().bottom>0).length),0,'Sections in view must be revealed');
  assert.ok((await page.locator('.tbt-google-reviews h3').first().textContent()).includes('Google Reviews'));
  await page.locator('.tbt-google-reviews').scrollIntoViewIfNeeded();
  await page.locator('.tbt-google-review__avatar img').first().evaluate(img=>img.decode());
  const reviewTrack=page.locator('[data-review-track]');
  assert.ok(await reviewTrack.locator('article').count()>=3);
  await page.locator('[data-review-next]').click();
  await page.waitForFunction(()=>document.querySelector('[data-review-track]').scrollLeft>10);
  const more=page.locator('[data-review-expand]').first();await more.click();assert.equal(await more.getAttribute('aria-expanded'),'true');
  assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'Page overflow');
 }
 await page.setViewportSize({width:1440,height:900});
 await page.goto(process.env.TBT_RUNTIME_URL || 'http://localhost:8888/',{waitUntil:'domcontentloaded'});
 const crests=page.locator('.tbt-home-proof__crests');await crests.scrollIntoViewIfNeeded();
 await page.mouse.move(0,0);
 const start=await crests.evaluate(e=>e.scrollLeft);
 await page.waitForFunction(start=>document.querySelector('.tbt-home-proof__crests').scrollLeft>start+5,start);
 assert.equal(await page.locator('[data-crest-toggle]').count(),0);
 await crests.hover();
 const paused=await crests.evaluate(e=>e.scrollLeft);await page.waitForTimeout(200);assert.equal(await crests.evaluate(e=>e.scrollLeft),paused);
 await page.emulateMedia({reducedMotion:'reduce'});await page.reload({waitUntil:'domcontentloaded'});
 const reduced=await crests.evaluate(e=>e.scrollLeft);await page.waitForTimeout(200);assert.equal(await crests.evaluate(e=>e.scrollLeft),reduced);
 console.log('PASS: photo/tutor alignment, centered results, reference sections, reviews, FAQ colors and crest movement');
 } finally {await browser.close();}
})().catch(e=>{console.error(e);process.exit(1)});
