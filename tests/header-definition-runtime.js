const assert = require('node:assert/strict');
const { chromium } = require('playwright');
const base = (process.env.TBT_RUNTIME_URL || 'http://localhost:8890').replace(/\/?$/, '/');
const ctaSizes = {};
(async () => {
 const browser = await chromium.launch();
 try {
  const page = await browser.newPage();
  for (const width of [1440, 1024, 768, 390, 320]) {
   await page.setViewportSize({width,height:900});
   for (const route of ['classes/', 'about/', '', 'sec-3-pure-g3-biology-tuition/', 'sec-4-pure-g3-biology-tuition/', 'testimonials/', 'notes/', 'trees/', 'contact/']) {
    await page.goto(new URL(route, base).href, {waitUntil:'domcontentloaded'});
    await page.evaluate(()=>document.fonts.ready);
    const r = await page.evaluate(()=>{
     const b=e=>e.getBoundingClientRect(), inner=b(document.querySelector('.site-header__inner')),logo=b(document.querySelector('.site-brand__logo')),nav=b(document.querySelector('.site-nav'));
     const header=b(document.querySelector('.site-header')),cta=document.querySelector('.site-header .site-header__cta'),toggle=b(document.querySelector('.site-header__menu-toggle'));
     const c=cta?b(cta):null,cs=cta?getComputedStyle(cta):null;
     return {w:inner.width,left:inner.left,right:inner.right,logoLeft:logo.left,logoRight:logo.right,navRight:nav.right,navLeft:nav.left,overflow:document.documentElement.scrollWidth-innerWidth,
      cta:c&&{count:document.querySelectorAll('.site-header .site-header__cta').length,text:cta.textContent.trim(),href:cta.getAttribute('href'),target:cta.getAttribute('target'),left:c.left,right:c.right,width:c.width,height:c.height,midY:(c.top+c.bottom)/2,bg:cs.backgroundColor,radius:cs.borderTopLeftRadius,color:cs.color},
      headerMidY:(header.top+header.bottom)/2,toggle:{left:toggle.left,visible:toggle.width>0}};
    });
    assert.ok(Math.abs(r.w-Math.min(1120,width-32))<2, 'Header matches shared content width');
    assert.ok(Math.abs(r.logoLeft-r.left)<2, `Logo aligns left on ${route} at ${width}`);
    // Contact Us is the call to action: the same green pill, top right, on every page.
    assert.ok(r.cta,`Contact Us button exists on ${route} at ${width}`);
    assert.equal(r.cta.count,1,`Exactly one header Contact Us button on ${route} at ${width}`);
    assert.equal(r.cta.text,'Contact Us');
    assert.ok(/^https:\/\/wa\.me\//.test(r.cta.href)&&r.cta.target==='_blank',`Contact Us opens WhatsApp in a new tab on ${route}`);
    assert.ok(r.cta.left>=r.logoRight&&r.cta.right<=r.right+1,`Contact Us sits inside the header on ${route} at ${width}: ${JSON.stringify(r.cta)}`);
    assert.ok(Math.abs(r.cta.midY-r.headerMidY)<=3&&r.cta.height>=36&&r.cta.height<=48,`Contact Us is vertically centred in the header on ${route} at ${width}`);
    assert.equal(r.cta.bg,'rgb(67, 180, 58)','Contact Us uses the site green'); assert.equal(r.cta.color,'rgb(255, 255, 255)');
    assert.ok(parseFloat(r.cta.radius)>=18,'Contact Us is a pill');
    if(width>980){assert.ok(Math.abs(r.cta.right-r.right)<2,`Contact Us is the right-most header item on ${route} at ${width}`);assert.ok(r.navRight<=r.cta.left,`Menu sits left of Contact Us on ${route} at ${width}`);assert.ok(r.navLeft>=r.logoRight,`Menu does not collide with the logo on ${route} at ${width}`);}
    else{assert.ok(r.toggle.visible&&r.cta.right<=r.toggle.left,`Contact Us sits left of the menu button on ${route} at ${width}`);}
    ctaSizes[width]??=r.cta; assert.ok(Math.abs(ctaSizes[width].width-r.cta.width)<1&&Math.abs(ctaSizes[width].left-r.cta.left)<1,`Contact Us is identical across pages (${route} at ${width})`);
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
  // Free Bio Notes wears a hand-drawn red circle on desktop; the menu spacing and header height do not change.
  for (const width of [1440, 1024]) {
   await page.setViewportSize({width,height:900});
   for (const route of ['', 'classes/', 'notes/']) {
    await page.goto(new URL(route,base).href);
    const c = await page.locator('.site-nav__link--circled').evaluate(e=>{const a=getComputedStyle(e,'::after'),r=e.getBoundingClientRect();return {text:e.textContent.trim(),bg:a.backgroundImage,content:a.content,pos:a.position,pe:a.pointerEvents,w:parseFloat(a.width),h:parseFloat(a.height),linkW:r.width,linkH:r.height,overflow:document.documentElement.scrollWidth-innerWidth}});
    assert.equal(c.text,'Free Bio Notes');
    assert.ok(/url\(.*svg/.test(c.bg),`Circle is a vector drawing on ${route} at ${width}`);
    assert.ok(/(%23|#)(c|b|d)[0-9a-f]{2,5}/i.test(decodeURIComponent(c.bg)) && /red|c8|c0|b8|d[0-9a-f]/i.test(decodeURIComponent(c.bg)),'Circle is red');
    assert.equal(c.pos,'absolute'); assert.equal(c.pe,'none','Circle never blocks the click');
    assert.ok(c.w>=c.linkW && c.h>=28 && c.h<=c.linkH,`Circle surrounds the label on ${route} at ${width}`);
    assert.equal(c.overflow,0);
   }
  }
  await page.setViewportSize({width:1440,height:900});
  await page.goto(new URL('intensivefix/',base).href);
  assert.equal(await page.locator('.site-header.tbt-landing-header').count(),0,'IntensiveFix uses the shared header');
  assert.equal(await page.locator('.site-header .site-nav .site-nav__link').count()>=5,true,'IntensiveFix shows the same website menu as every page');
  assert.equal(await page.locator('.site-header__cta').count(),1,'IntensiveFix shows one Contact Us button');
  console.log('PASS shared header alignment and Classes definition layout');
 } finally {await browser.close();}
})().catch(e=>{console.error(e);process.exit(1)});
