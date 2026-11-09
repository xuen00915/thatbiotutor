const assert = require('node:assert/strict');
const { chromium } = require('playwright');
const base = (process.env.TBT_RUNTIME_URL || 'http://localhost:8890').replace(/\/?$/, '/');
(async () => {
 const browser = await chromium.launch();
 try {
  const page = await browser.newPage();
  for (const width of [1440, 1024, 768, 390, 320]) {
   await page.setViewportSize({width,height:900});
   for (const route of ['classes/', 'about/', '', 'sec-4-pure-g3-biology-tuition/', 'testimonials/']) {
    await page.goto(new URL(route, base).href, {waitUntil:'domcontentloaded'});
    await page.evaluate(()=>document.fonts.ready);
    const r = await page.evaluate(()=>{
     const b=e=>e.getBoundingClientRect(), inner=b(document.querySelector('.site-header__inner')),logo=b(document.querySelector('.site-brand__logo')),nav=b(document.querySelector('.site-nav'));
     return {w:inner.width,left:inner.left,logoLeft:logo.left,navCentre:(nav.left+nav.right)/2,overflow:document.documentElement.scrollWidth-innerWidth};
    });
    assert.ok(Math.abs(r.w-Math.min(1120,width-32))<2, 'Header matches shared content width');
    assert.ok(Math.abs(r.logoLeft-r.left)<2, `Logo aligns left on ${route} at ${width}`);
    if(width>980)assert.ok(Math.abs(r.navCentre-width/2)<2, `Desktop menu centres on ${route}`);
    assert.equal(r.overflow,0);
    assert.ok(await page.locator('.site-nav__item').evaluateAll(items=>items.every(e=>parseFloat(getComputedStyle(e).borderLeftWidth)===0)), 'Header menu has no vertical dividers');
   }
   for (const route of ['', 'classes/', 'testimonials/', 'intensivefix/']) {
    await page.goto(new URL(route,base).href);
    assert.equal(await page.locator('.site-header').evaluate(e=>e.getBoundingClientRect().height),80,`Shared 80px header on ${route} at ${width}`);
    await page.evaluate(()=>window.scrollTo(0,600));
    assert.equal(await page.locator('.site-header').evaluate(e=>e.getBoundingClientRect().height),80,`Scrolled header stays 80px on ${route} at ${width}`);
   }
   await page.goto(new URL('classes/',base).href);
   const r=await page.locator('[data-section="definition"]').evaluate(e=>{
    const b=e=>e.getBoundingClientRect(),image=e.querySelector('.tbt-programme-artwork'),copy=e.querySelector('.tbt-programme-copy'),topics=e.querySelector('.tbt-topic-columns');
    const rect=e=>({x:b(e).left,y:b(e).top,right:b(e).right,bottom:b(e).bottom,w:b(e).width});
    return {image:rect(image),copy:rect(copy),para:rect(copy.querySelector('p')),topics:rect(topics),cols:[...topics.children].map(rect),loaded:image.complete&&image.naturalWidth>0};
   });
   assert.ok(r.loaded,'Infographic loads');
   if(width>980){assert.ok(r.image.right<r.copy.x&&Math.abs(r.image.y-r.copy.y)<3,'Image left, copy right with aligned tops');}
   else assert.ok(r.image.bottom<=r.copy.y,'Stack image above copy on narrower screens');
   assert.ok(r.topics.y>=r.para.bottom,'Topics below paragraph');
   assert.ok(Math.abs(r.topics.w-r.copy.w)<2,'Topics fill copy column');
   if(width>=560)assert.ok(Math.abs(r.cols[0].y-r.cols[1].y)<2&&r.cols[0].right<r.cols[1].x,'Topic lists side by side');
  }
  await page.goto(new URL('intensivefix/',base).href);
  assert.equal(await page.locator('.tbt-landing-header__cta').count(),1,'IntensiveFix keeps its separate header');
  console.log('PASS shared header alignment and Classes definition layout');
 } finally {await browser.close();}
})().catch(e=>{console.error(e);process.exit(1)});
