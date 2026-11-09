const assert=require('node:assert/strict');
const {chromium}=require('playwright');
(async()=>{
 const browser=await chromium.launch();
 try {
  const page=await browser.newPage({reducedMotion:'reduce'});
  const base=(process.env.TBT_RUNTIME_URL||'http://localhost:8892').replace(/\/$/,'');
  for(const width of [1440,390]) {
   await page.setViewportSize({width,height:900});
   for(const route of ['','classes','sec-3-pure-g3-biology-tuition','sec-4-pure-g3-biology-tuition','testimonials','notes','celldivision','about','trees','contact','tuitionrecs','intensivefix']) {
    await page.goto(`${base}/${route}${route?'/':''}`);
    const link=page.locator('.site-footer a').first(); await link.focus();
    assert.equal(await link.evaluate(e=>getComputedStyle(e).outlineOffset),'5px',`${route}: consistent keyboard focus`);
    assert.equal(await page.locator('body').evaluate(e=>getComputedStyle(e,'::selection').backgroundColor),'rgb(200, 233, 182)',`${route}: branded selection`);
    assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth),false,`${route}: no overflow`);
    const field=page.locator('input[type="email"]').first();
    if(await field.count()) {await field.focus();assert.equal(await field.evaluate(e=>getComputedStyle(e).caretColor),'rgb(47, 138, 39)');}
   }
  }
  console.log('PASS: site-wide polish on 12 routes, desktop/mobile keyboard focus and form states');
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exit(1);});
