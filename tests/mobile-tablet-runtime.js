const { chromium } = require('playwright');
const assert = require('node:assert/strict');
(async () => {
 const browser=await chromium.launch(); const page=await browser.newPage({reducedMotion:'reduce'});
 const base=process.env.TBT_RUNTIME_URL||'http://localhost:8890';
 const errors=[];page.on('pageerror',e=>errors.push(e.message));
 try {
  for(const [width,height] of [[1440,900],[1180,820],[1024,768],[820,1180],[600,900],[430,932],[412,915],[390,844],[320,740]]) {
   await page.setViewportSize({width,height});
   await page.goto(`${base}/notes/`);await page.evaluate(()=>document.fonts.ready);
   const hero=await page.locator('.tbt-notes-hero').evaluate(e=>{const box=s=>e.querySelector(s).getBoundingClientRect();return{heading:box('h1').toJSON(),proof:box('.tbt-resource-proof').toJSON(),slider:box('[data-carousel]').toJSON(),controls:[...e.querySelectorAll('[data-carousel-prev],[data-carousel-next]')].map(a=>a.getBoundingClientRect().toJSON())}});
   assert.ok(hero.controls.every(r=>r.left>=8&&r.right<=width-8),`Notes arrows fit at ${width}`);
   if(width<=1180)assert.ok(hero.slider.top>=hero.proof.bottom,`Tablet hero stacks cleanly at ${width}`);
   await page.locator('.tbt-notes-hero [data-carousel-next]').click();
   assert.match(await page.locator('.tbt-notes-hero [data-carousel-status]').textContent(),/2 of 3/);
   await page.goto(`${base}/trees/`);
   const image=await page.locator('.tbt-causes-section__media--square img').boundingBox();
   assert.ok(image.x>=16&&image.x+image.width<=width-16+1,`Entire Causes image fits at ${width}`);
   await page.goto(`${base}/testimonials/`);
   const toggle=page.locator('.tbt-year-nav__toggle');
   await toggle.click();
   const geo=await page.locator('[data-year-nav]').evaluate(e=>{const b=e.getBoundingClientRect(),p=e.querySelector('nav').getBoundingClientRect(),h=document.querySelector('.site-header').getBoundingClientRect();return{top:b.top,bottom:b.bottom,panelTop:p.top,panelBottom:p.bottom,header:h.bottom}});
   if(width<=1180)assert.ok(geo.top>=geo.header&&geo.top<=geo.header+12&&geo.panelTop>=geo.bottom,`Year menu sits below header and opens down at ${width}`);
   assert.ok(geo.panelBottom<=height,`Year menu fits height at ${width}`);
   await page.locator('.tbt-year-nav__year[href$="#class-of-2022"]').click();
   await page.waitForFunction(()=>document.querySelector('[data-year-current]').textContent==='2022');
   const target=await page.locator('#class-of-2022').boundingBox();
   if(width<=1180)assert.ok(target.y>=geo.bottom,`Selected heading clears year selector at ${width}`);
   console.log(`PASS mobile/tablet fixes ${width}x${height}`);
  }
  const routes=['','classes','sec-3-pure-g3-biology-tuition','sec-4-pure-g3-biology-tuition','testimonials','notes','celldivision','about','trees','contact','tuitionrecs','intensivefix'];
  for(const width of [412,600,820,1024]) {
   await page.setViewportSize({width,height:900});
   for(const route of routes) {
    await page.goto(`${base}/${route}/`);await page.evaluate(()=>document.fonts.ready);
    assert.ok((await page.locator('main').innerText()).trim().length>100, `${route}: page has content`);
    const clipped=await page.evaluate(()=>[...document.querySelectorAll('main img,main button,main h1,main h2,main p,main table')].flatMap(e=>{
     const r=e.getBoundingClientRect();if(!r.width||!r.height||e.closest('[inert],[hidden],.screen-reader-text,.tbt-inclusion-icon,.tbt-home-proof__crests'))return[];
     if(r.left>=-1&&r.right<=innerWidth+1)return[];
     let parent=e.parentElement;while(parent&&parent!==document.body){if(['auto','scroll'].includes(getComputedStyle(parent).overflowX))return[];parent=parent.parentElement;}
     return [e.tagName+' '+e.className];
    }));
    assert.deepEqual(clipped,[],`${route} at ${width}: content and controls fit the viewport`);
    if(width===412) {
     const menu=page.locator('[data-menu-toggle]');await menu.click();assert.equal(await menu.getAttribute('aria-expanded'),'true');
     await page.keyboard.press('Escape');assert.equal(await menu.getAttribute('aria-expanded'),'false');
    }
   }
   console.log(`PASS all 12 pages fit ${width}px`);
  }
  assert.deepEqual(errors,[]);
 } finally {await browser.close()}
})().catch(e=>{console.error(e);process.exit(1)});
