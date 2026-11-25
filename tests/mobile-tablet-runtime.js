const { chromium, webkit } = require('playwright');
const assert = require('node:assert/strict');
(async () => {
 const browser=await (process.env.TBT_BROWSER === 'webkit' ? webkit : chromium).launch(); const page=await browser.newPage({reducedMotion:'reduce',hasTouch:true});
 const base=(process.env.TBT_RUNTIME_URL||'http://localhost:8890').replace(/\/+$/, '');
 const errors=[];page.on('pageerror',e=>errors.push(e.message));
 try {
  for(const [width,height] of [[1440,900],[1180,820],[1024,768],[820,1180],[600,900],[430,932],[412,915],[390,844],[320,740]]) {
   await page.setViewportSize({width,height});
   await page.goto(`${base}/notes/`);await page.evaluate(()=>document.fonts.ready);
   const hero=await page.locator('.tbt-notes-hero').evaluate(e=>{const box=s=>e.querySelector(s).getBoundingClientRect();return{heading:box('h1').toJSON(),proof:box('.tbt-resource-proof').toJSON(),slider:box('[data-carousel]').toJSON(),controls:[...e.querySelectorAll('[data-carousel-prev],[data-carousel-next]')].map(a=>a.getBoundingClientRect().toJSON())}});
   assert.ok(hero.controls.every(r=>r.left>=8&&r.right<=width-8),`Notes arrows fit at ${width}`);
   if(width<=1180) {
    if(width<768) assert.ok(hero.slider.top>=hero.proof.bottom,`Phone hero stacks cleanly at ${width}`);
    else assert.ok(hero.slider.left>=hero.heading.right-1,`Tablet hero stays side by side at ${width}`);
    assert.ok(await page.locator('.tbt-notes-hero [data-carousel-next]').evaluate(e=>+getComputedStyle(e).opacity===1), `Touch controls are visible without hover at ${width}`);
   }
   await page.locator('.tbt-notes-hero [data-carousel-next]').click();
   assert.match(await page.locator('.tbt-notes-hero [data-carousel-status]').textContent(),/2 of 3/);
   const footer=await page.locator('.site-footer').evaluate(f=>({titles:[...f.querySelectorAll('.site-footer__column h2')].map(h=>({t:h.textContent.trim(),lines:Math.round(h.getBoundingClientRect().height/parseFloat(getComputedStyle(h).lineHeight))})),split:[...f.querySelectorAll('.site-footer__column :is(address,p)')].some(e=>e.scrollWidth>e.clientWidth+1)}));
   assert.ok(footer.titles.every(h=>h.lines===1)&&!footer.split,`Footer titles stay on one line and nothing overflows at ${width}: ${JSON.stringify(footer)}`);
   const form=await page.locator('.tbt-notes-form__embed').boundingBox();
   assert.ok(form&&form.height>=(form.width<=351?550:form.width<=363?530:form.width<=429?490:form.width<=615?470:450),`Signup frame leaves room below Submit at ${width}: ${JSON.stringify(form)}`);
   await page.goto(`${base}/intensivefix/`);
   const ifx=await page.evaluate(()=>{const pad=s=>parseInt(getComputedStyle(document.querySelector(s)).paddingBottom);const b=document.querySelector('.tbt-home-closing a.tbt-home-button').getBoundingClientRect(),w=document.querySelector('.tbt-home-closing__cta-wrap').getBoundingClientRect();return{white:pad('.tbt-home-fit'),dark:pad('.tbt-home-closing'),centred:Math.abs((b.left-w.left)-(w.right-b.right))<=1}});
   const want=width>768?[40,56]:width>390?[32,40]:[24,32];
   assert.ok(ifx.white===want[0]&&ifx.dark===want[1],`IntensiveFix section spacing at ${width}: ${JSON.stringify(ifx)}`);
   if(width<=1180) assert.ok(ifx.centred,`IntensiveFix closing button is centred at ${width}`);
   const ifxBtn=await page.evaluate(()=>{const h=document.querySelector('.tbt-intensivefix-hero__cta').getBoundingClientRect();return{above:Math.round(h.top-document.querySelector('.tbt-intensivefix-hero__limited').getBoundingClientRect().bottom),below:Math.round(document.querySelector('.tbt-intensivefix-hero__graphics').getBoundingClientRect().top-h.bottom),oneLine:[...document.querySelectorAll('.tbt-intensivefix-page a.tbt-home-button')].every(b=>b.getBoundingClientRect().height<=60&&b.scrollWidth<=b.clientWidth+1&&getComputedStyle(b).whiteSpace==='nowrap')}});
   assert.ok(ifxBtn.oneLine&&ifxBtn.above===ifxBtn.below,`IntensiveFix buttons fit one line and the hero button has equal space above/below at ${width}: ${JSON.stringify(ifxBtn)}`);
   await page.goto(`${base}/trees/`);
   const image=await page.locator('.tbt-causes-section__media--square img').boundingBox();
   assert.ok(image.x>=16&&image.x+image.width<=width-16+1,`Entire Causes image fits at ${width}`);
   await page.goto(`${base}/testimonials/`);
   if(width<768) {
    const aligned=await page.locator('.tbt-testimonials-hero').evaluate(e=>['h1','.tbt-testimonials-hero__lead'].every(s=>getComputedStyle(e.querySelector(s)).textAlign==='center')&&getComputedStyle(e.querySelector('.tbt-testimonials-hero__rating')).justifyContent==='center');
    assert.ok(aligned,`Mobile testimonial heading, copy and rating are centered at ${width}`);
   }
   const toggle=page.locator('.tbt-year-nav__toggle');
   if(width<=1180){assert.equal(await page.locator('[data-year-nav]').evaluate(e=>getComputedStyle(e).visibility),'hidden',`Year bar is hidden above the years at ${width}`);await page.evaluate(()=>document.querySelector('#class-of-2025').scrollIntoView({block:'start',behavior:'instant'}));await page.waitForFunction(()=>!document.querySelector('[data-year-nav]').hasAttribute('data-before-years')&&getComputedStyle(document.querySelector('[data-year-nav]')).visibility==='visible');}
   await toggle.click();
   const geo=await page.locator('[data-year-nav]').evaluate(e=>{const b=e.getBoundingClientRect(),p=e.querySelector('nav').getBoundingClientRect(),h=document.querySelector('.site-header').getBoundingClientRect();return{top:b.top,bottom:b.bottom,panelTop:p.top,panelBottom:p.bottom,header:h.bottom}});
   if(width<=1180)assert.ok(geo.top>=geo.header&&geo.top<=geo.header+12&&geo.panelTop>=geo.bottom,`Year menu sits below header and opens down at ${width}`);
   assert.ok(geo.panelBottom<=height,`Year menu fits height at ${width}`);
   await page.locator('.tbt-year-nav__year[href$="#class-of-2022"]').click();
   await page.waitForFunction(()=>document.querySelector('[data-year-current]').textContent==='Class of 2022');
   const target=await page.locator('#class-of-2022').boundingBox();
   if(width<=1180)assert.ok(target.y>=geo.bottom,`Selected heading clears year selector at ${width}`);
   console.log(`PASS mobile/tablet fixes ${width}x${height}`);
  }
  const routes=['','classes','sec-3-pure-g3-biology-tuition','sec-4-pure-g3-biology-tuition','testimonials','notes','celldivision','about','trees','contact','tuitionrecs','intensivefix'];
  for(const width of [412,600,820,1024]) {
   await page.setViewportSize({width,height:900});
   for(const route of routes) {
    await page.goto(`${base}/${route ? route+'/' : ''}`);await page.evaluate(()=>document.fonts.ready);
    const floating=await page.locator('.whatsapp-float').boundingBox();
    assert.ok(floating&&floating.x>=12&&floating.x+floating.width<=width-12&&floating.y>=12&&floating.y+floating.height<=900-24,`${route}: whole contact button is visible on first screen`);
    assert.ok((await page.locator('main').innerText()).trim().length>100, `${route}: page has content`);
    const clipped=await page.evaluate(()=>[...document.querySelectorAll('main img,main button,main h1,main h2,main p,main table')].flatMap(e=>{
     const r=e.getBoundingClientRect();if(!r.width||!r.height||e.closest('[inert],[hidden],.screen-reader-text,.tbt-inclusion-icon,.tbt-home-proof__crests'))return[];
     if(r.left>=-1&&r.right<=innerWidth+1)return[];
     let parent=e.parentElement;while(parent&&parent!==document.body){if(['auto','scroll'].includes(getComputedStyle(parent).overflowX))return[];parent=parent.parentElement;}
     return [e.tagName+' '+e.className];
    }));
    assert.deepEqual(clipped,[],`${route} at ${width}: content and controls fit the viewport`);
    const teamOrder=await page.locator('.tbt-programme-tutors .tbt-tutor').evaluateAll(es=>es.every(e=>e.querySelector('h3').getBoundingClientRect().top>=e.querySelector('img').getBoundingClientRect().bottom-1));
    assert.ok(teamOrder,`${route}: team names sit below profile pictures at ${width}`);
    if(width===412 && route==='') {
     const track=page.locator('[data-review-track]');await track.scrollIntoViewIfNeeded();
     await page.locator('[data-review-prev]').click();
     await page.waitForFunction(()=>{const e=document.querySelector('[data-review-track]');return Math.abs(e.scrollLeft-(e.scrollWidth-e.clientWidth))<2;});
     await page.locator('[data-review-next]').click();
     await page.waitForFunction(()=>document.querySelector('[data-review-track]').scrollLeft<2);
    }
    if(width===412) {
     const gallery=await page.locator('.tbt-practical-grid,.tbt-home-practical__grid').evaluateAll(es=>es.map(e=>[...e.children].map(c=>c.getBoundingClientRect().toJSON())));
     const classroom=await page.locator('.tbt-home-classroom__grid').evaluateAll(es=>es.map(e=>({width:e.getBoundingClientRect().width,photos:[...e.children].map(c=>c.getBoundingClientRect().toJSON())})));
     classroom.forEach(({width,photos})=>photos.forEach((p,i)=>{assert.ok(Math.abs(p.width-width)<1,'Classroom photos fill mobile content width');if(i)assert.ok(p.top>=photos[i-1].bottom,'Classroom photos stack vertically');}));
     gallery.forEach(([a,b,c])=>{assert.ok(b.top>=a.bottom&&c.top>=b.bottom,`${route}: practical photos stack one per row`);assert.ok(Math.abs(a.width-b.width)<1&&Math.abs(b.width-c.width)<1,`${route}: practical photos share full content width`);});
     if(route==='about') {const widths=await page.locator('.tbt-about-beliefs__image').evaluate(e=>[e.getBoundingClientRect().width,e.parentElement.getBoundingClientRect().width]);assert.ok(Math.abs(widths[0]-widths[1])<1,'About image fills the mobile content width');}
     const practical=await page.locator('main img[src*="35ad6135826a6626379208fe8e6eefe5"]').evaluateAll(es=>es.map(e=>{const r=e.getBoundingClientRect(),s=getComputedStyle(e);return {ratio:r.width/r.height,position:s.objectPosition,fit:s.objectFit};}));
     practical.forEach(s=>{assert.ok(s.ratio<=4/3+.01,`${route}: mobile practical frame crops the baked-in black strip`);assert.equal(s.position,'100% 50%');assert.equal(s.fit,'cover');});
     const oversized=await page.locator('.tbt-notes-benefit__media img,.tbt-cell-feature img,.tbt-programme-tutors .tbt-tutor img,.tbt-about-page .tbt-tutor__frame,.tbt-home-founder img').evaluateAll(es=>es.filter(e=>{const r=e.getBoundingClientRect();return r.height>330||((e.closest('.tbt-notes-benefit__media,.tbt-cell-feature'))&&r.width>100)||(e.matches('.tbt-tutor__frame')&&r.width>132)||(e.closest('.tbt-programme-tutors')&&r.width>144)}).map(e=>e.className||e.alt));
     assert.deepEqual(oversized,[],`${route}: decorative mobile images stay compact`);
     const menu=page.locator('[data-menu-toggle]');await menu.click();assert.equal(await menu.getAttribute('aria-expanded'),'true');
     await page.keyboard.press('Escape');assert.equal(await menu.getAttribute('aria-expanded'),'false');
    }
   }
   console.log(`PASS all 12 pages fit ${width}px`);
  }
  await page.emulateMedia({reducedMotion:'no-preference'});
  await page.setViewportSize({width:412,height:844});await page.goto(`${base}/`);
  const next=page.locator('[data-review-next]'), prev=page.locator('[data-review-prev]');
  await next.scrollIntoViewIfNeeded();
  await next.tap();await next.tap();await next.tap();
  await page.waitForFunction(()=>{const t=document.querySelector('[data-review-track]'),a=t.children;return Math.abs(t.scrollLeft-(a[3].getBoundingClientRect().left-a[0].getBoundingClientRect().left))<2;});
  await prev.tap();await prev.tap();
  await page.waitForFunction(()=>{const t=document.querySelector('[data-review-track]'),a=t.children;return Math.abs(t.scrollLeft-(a[1].getBoundingClientRect().left-a[0].getBoundingClientRect().left))<2;});
  await page.evaluate(()=>{Object.defineProperty(visualViewport,'height',{configurable:true,value:innerHeight-120});visualViewport.dispatchEvent(new Event('resize'));});
  const floatBox=await page.locator('.whatsapp-float').boundingBox();assert.ok(floatBox.y+floatBox.height<=844-120-32,'Contact control clears reduced visible viewport');
  await page.evaluate(()=>{Object.defineProperty(visualViewport,'width',{configurable:true,value:innerWidth-100});visualViewport.dispatchEvent(new Event('resize'));});
  const zoomBox=await page.locator('.whatsapp-float').boundingBox();assert.ok(zoomBox.x+zoomBox.width<=412-100-24,'Contact control clears the right edge of a narrowed visual viewport');
  assert.deepEqual(errors,[]);
 } finally {await browser.close()}
})().catch(e=>{console.error(e);process.exit(1)});
