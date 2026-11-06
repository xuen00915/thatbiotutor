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
  if (width===1440) {
    const lines=await page.evaluate(()=>{const h=document.querySelector('.tbt-home-hero h1');const w=document.createTreeWalker(h,NodeFilter.SHOW_TEXT);const lines=new Map();let n;while((n=w.nextNode())){const re=/\S+/g;let m;while((m=re.exec(n.textContent))){const r=document.createRange();r.setStart(n,m.index);r.setEnd(n,m.index+m[0].length);const top=Math.round(r.getBoundingClientRect().top);lines.set(top,(lines.get(top)||[]).concat(m[0]))}}return [...lines.entries()].sort((a,b)=>a[0]-b[0]).map(e=>e[1].join(' '))});
    assert.equal(lines[0],'The One Missing Key to go','Homepage headline wraps like the original ('+JSON.stringify(lines)+')');
  }
  assert.deepEqual(await page.evaluate(()=>{const f=document.querySelector('[data-section=fasttrack]');const c=e=>getComputedStyle(e).color;return {eyebrow:c(f.querySelector('.tbt-home-eyebrow')),accent:c(f.querySelector('h2 span'))}}),{eyebrow:'rgb(67, 180, 58)',accent:'rgb(67, 180, 58)'},'Homepage FastTrack heading colours');
  // FastTrack images share one large size.
  const icons=await page.locator('.tbt-home-fasttrack__tbt-content img').evaluateAll(es=>es.map(e=>[Math.round(e.getBoundingClientRect().width),Math.round(e.getBoundingClientRect().height)]));
  assert.ok(icons.length===5&&icons.every(([w,h])=>w===icons[0][0]&&h===icons[0][1]),'FastTrack images must be one size ('+JSON.stringify(icons)+')');
  assert.ok(icons[0][0]>=(width>520?120:96),'FastTrack images must be large ('+icons[0][0]+'px)');
  // Google reviews: equal card heights and arrows clear of the cards.
  await page.locator('.tbt-google-reviews').scrollIntoViewIfNeeded();await page.waitForTimeout(400);
  const reviews=await page.evaluate(()=>{const track=document.querySelector('.tbt-google-reviews__track').getBoundingClientRect();const cards=[...document.querySelectorAll('.tbt-google-review')].map(e=>e.getBoundingClientRect()).filter(r=>r.left>=track.left-2&&r.right<=track.right+2);const arrows=[...document.querySelectorAll('.tbt-google-reviews__controls button')].map(e=>e.getBoundingClientRect()).filter(r=>r.width>0);const hit=(a,b)=>a.left<b.right&&a.right>b.left&&a.top<b.bottom&&a.bottom>b.top;return {heights:[...new Set(cards.map(r=>Math.round(r.height)))],overlap:arrows.some(a=>cards.some(c=>hit(a,c))),n:cards.length}});
  assert.equal(reviews.heights.length,1,'All review cards must share one height ('+JSON.stringify(reviews.heights)+')');
  assert.equal(reviews.overlap,false,'Review arrows must not overlap the cards');
  // Closing photo is as tall as the text beside it.
  const homeClosing=await page.evaluate(()=>{const f=document.querySelector('.tbt-home-closing figure').getBoundingClientRect();const d=document.querySelector('.tbt-home-closing__inner > div').getBoundingClientRect();return {f:f.height,d:d.height}});
  if(width>768) assert.ok(Math.abs(homeClosing.f-homeClosing.d)<3,'Homepage closing photo must match its text height ('+JSON.stringify(homeClosing)+')');
  const faq=page.locator('.tbt-home-faq__item button').first();
  await faq.hover();
  await page.waitForFunction(()=>getComputedStyle(document.querySelector('.tbt-home-faq__item button')).backgroundColor==='rgb(237, 245, 232)');
  assert.equal(await faq.evaluate(e=>getComputedStyle(e).backgroundColor),'rgb(237, 245, 232)');
  assert.equal(await faq.evaluate(e=>getComputedStyle(e).color),'rgb(25, 34, 26)','FAQ text must stay dark and readable on hover');
  const faqList=await page.locator('.tbt-home-faq__list').evaluate(e=>{const s=getComputedStyle(e);return {radius:s.borderTopLeftRadius,border:s.borderTopWidth,overflow:s.overflow}});
  assert.deepEqual(faqList,{radius:'18px',border:'1px',overflow:'hidden'});
  const faqIcon=await faq.evaluate(e=>getComputedStyle(e.querySelector('.tbt-home-faq__icon'),'::before').backgroundColor);
  assert.equal(faqIcon,'rgb(47, 138, 39)');
  await faq.click();assert.equal(await faq.getAttribute('aria-expanded'),'true');
  await page.waitForTimeout(600);
  await page.mouse.move(0,0);await page.waitForTimeout(400);
  assert.deepEqual(await faq.evaluate(e=>{const s=getComputedStyle(e);return {bg:s.backgroundColor,color:s.color,focused:document.activeElement===e}}),{bg:'rgba(0, 0, 0, 0)',color:'rgb(25, 34, 26)',focused:true},'Clicked FAQ button must never turn pink');
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
  await page.setViewportSize({width:1440,height:900});
  const base=(process.env.TBT_RUNTIME_URL || 'http://localhost:8888/').replace(/\/?$/,'/');
  await page.goto(new URL('intensivefix/',base).href,{waitUntil:'domcontentloaded'});
  assert.equal(await page.locator('.site-header.tbt-landing-header .site-brand__wordmark').count(),1,'IntensiveFix keeps the Inquire header with the wordmark');
  assert.equal((await page.locator('.tbt-landing-header__cta').textContent()).trim(),'Contact Us','IntensiveFix header button must read Contact Us');
  assert.equal(await page.locator('.site-header .site-nav').count(),0,'IntensiveFix header has no menu');
  assert.equal(await page.locator('.tbt-intensivefix-carousel__viewport').evaluate(e=>getComputedStyle(e).borderTopWidth),'0px','Programme graphics must have no border line');
  await page.locator('.tbt-intensivefix-carousel .tbt-home-carousel__control--next').click();
  await page.mouse.move(0,0);await page.waitForTimeout(500);
  assert.deepEqual(await page.locator('.tbt-intensivefix-carousel .tbt-home-carousel__control--next').evaluate(e=>{const s=getComputedStyle(e);return {bg:s.backgroundColor,focused:document.activeElement===e}}),{bg:'rgba(0, 0, 0, 0)',focused:true},'Clicked carousel arrows must never turn pink');
  await page.evaluate(()=>window.scrollTo(0,0));await page.waitForTimeout(700);
  // Smooth slider: crossfade (no hidden slides, so no blank flash) and automatic sliding.
  const fade=await page.evaluate(()=>{const c=document.querySelector('.tbt-intensivefix-carousel');const slides=[...c.querySelectorAll('[data-carousel-slide]')];return {fade:c.hasAttribute('data-carousel-fade'),autoplay:c.getAttribute('data-carousel-autoplay'),hidden:slides.filter(s=>s.hidden).length,active:slides.findIndex(s=>s.classList.contains('is-active')),transition:getComputedStyle(slides[1]).transitionProperty,lazy:slides.filter(s=>s.querySelector('img[loading=lazy]')).length}});
  assert.equal(fade.fade,true,'Programme slider must crossfade');
  assert.equal(Number(fade.autoplay),3000,'Programme slider changes every 3 seconds');
  assert.equal(fade.hidden,0,'No slide may be display:none (that causes the blank flash)');
  assert.equal(fade.lazy,0,'Slider images must preload so a switch never shows a blank frame');
  assert.match(fade.transition,/opacity/,'Slides must fade with an opacity transition');
  const shape=await page.evaluate(()=>{const i=document.querySelector('.tbt-intensivefix-carousel figure.is-active img');const r=i.getBoundingClientRect();return {radius:parseFloat(getComputedStyle(i).borderTopLeftRadius),box:r.width/r.height,natural:i.naturalWidth/i.naturalHeight}});
  const fit=await page.evaluate(()=>{const i=document.querySelector('.tbt-intensivefix-carousel figure.is-active img').getBoundingClientRect();const v=document.querySelector('.tbt-intensivefix-carousel__viewport').getBoundingClientRect();return {inside:i.left>=v.left-1&&i.right<=v.right+1&&i.top>=v.top-1&&i.bottom<=v.bottom+1,i:[Math.round(i.width),Math.round(i.height)],v:[Math.round(v.width),Math.round(v.height)]}});
  assert.equal(fit.inside,true,'Programme graphic must sit fully inside its frame '+JSON.stringify(fit));
  assert.ok(shape.radius>=16,'Programme graphics must have rounded corners on the picture itself ('+shape.radius+'px)');
  assert.ok(Math.abs(shape.box-shape.natural)/shape.natural<0.03,'Rounded corners must follow the picture, not a letterboxed frame ('+JSON.stringify(shape)+')');
  await page.evaluate(()=>document.querySelector('.tbt-intensivefix-carousel').scrollIntoView({block:'center'}));
  await page.mouse.move(5,5);await page.waitForTimeout(3700);
  assert.ok(await page.evaluate(()=>[...document.querySelectorAll('.tbt-intensivefix-carousel [data-carousel-slide]')].findIndex(s=>s.classList.contains('is-active'))>0),'Programme slider must advance by itself');
  // FastTrack heading colours: green eyebrow, white headline, brand-green A1/A2.
  assert.deepEqual(await page.evaluate(()=>{const f=document.querySelector('[data-section=fasttrack]');const c=e=>getComputedStyle(e).color;return {eyebrow:c(f.querySelector('.tbt-home-eyebrow')),strong:c(f.querySelector('h2 strong')),accent:c(f.querySelector('h2 span'))}}),{eyebrow:'rgb(67, 180, 58)',strong:'rgb(255, 255, 255)',accent:'rgb(67, 180, 58)'},'FastTrack heading must follow the reference colours');
  // Closing image is as tall as the text beside it.
  const closing=await page.evaluate(()=>{const f=document.querySelector('.tbt-home-closing figure').getBoundingClientRect();const d=document.querySelector('.tbt-home-closing__inner > div').getBoundingClientRect();return {f:f.height,d:d.height,fw:f.width}});
  assert.ok(Math.abs(closing.f-closing.d)<3,'Closing image and text must have the same height ('+JSON.stringify(closing)+')');
  await page.evaluate(()=>window.scrollTo(0,0));await page.waitForTimeout(800);
  const heroGap=await page.evaluate(()=>document.querySelector('.tbt-home-hero .tbt-home-kicker').getBoundingClientRect().top-document.querySelector('.site-header').getBoundingClientRect().bottom);
  assert.ok(heroGap<=56,'Space between the IntensiveFix header and hero must be tight ('+heroGap+'px)');
  const heroLines=()=>page.evaluate(()=>{const h=document.querySelector('.tbt-home-hero h1');const w=document.createTreeWalker(h,NodeFilter.SHOW_TEXT);const lines=new Map();let n;while((n=w.nextNode())){const re=/\S+/g;let m;while((m=re.exec(n.textContent))){const r=document.createRange();r.setStart(n,m.index);r.setEnd(n,m.index+m[0].length);const top=Math.round(r.getBoundingClientRect().top);lines.set(top,(lines.get(top)||[]).concat(m[0]))}}return [...lines.entries()].sort((a,b)=>a[0]-b[0]).map(e=>e[1].join(' '))});
  const ifLines=await heroLines();
  assert.deepEqual(ifLines,['Fix Your Entire Sec 3 Bio','Foundation Before the','Chaotic Sec 4 Year Begins','— In Just 4 DAYS'],'IntensiveFix headline wraps like the original');
  // Contact Us goes straight to the WhatsApp number.
  const contact=await page.locator('.tbt-landing-header__cta').evaluate(e=>({href:e.getAttribute('href'),target:e.getAttribute('target')}));
  assert.ok(contact.href.startsWith('https://wa.me/6587597182'),'Contact Us must open the WhatsApp number');
  assert.equal(contact.target,'_blank');
  // Floating WhatsApp button: light-green disc, white glyph, red dot, two translucent rings.
  const wa=await page.locator('[data-whatsapp-float]').evaluate(e=>{const s=getComputedStyle(e);const n=getComputedStyle(e.querySelector('.whatsapp-float__notification'));return {bg:s.backgroundColor,border:s.borderTopWidth,rings:(s.boxShadow.match(/rgba?\([^)]*\)\s+0px 0px 0px (\d+)px/g)||[]).length,w:e.getBoundingClientRect().width,dot:n.backgroundColor,dotW:parseFloat(n.width),icon:getComputedStyle(e.querySelector('.whatsapp-float__icon')).color}});
  assert.equal(wa.bg,'rgb(88, 185, 71)');
  assert.equal(wa.border,'0px','Floating button has no solid border ring');
  await page.emulateMedia({reducedMotion:'no-preference'});
  const glow=await page.locator('[data-whatsapp-float]').evaluate(e=>{const s=getComputedStyle(e);const sizes=[...s.boxShadow.matchAll(/0px 0px 0px (\d+(?:\.\d+)?)px/g)].map(m=>Number(m[1]));return {maxSpread:sizes.length?Math.max(...sizes):0,animation:s.animationName,duration:s.animationDuration,iteration:s.animationIterationCount}});
  assert.notEqual(glow.animation,'none','Glow must animate');
  assert.equal(glow.iteration,'infinite','Glow keeps pulsing');
  assert.ok(parseFloat(glow.duration)>=1.6,'Glow pulse should be slow and calm');
  assert.ok(glow.maxSpread<=14,'Glow must stay small ('+glow.maxSpread+'px)');
  assert.equal(wa.icon,'rgb(255, 255, 255)');
  assert.ok(wa.dotW>=10&&wa.dotW<=14,'Red notification dot is clearly visible');
  const headerBg=()=>page.locator('.site-header').evaluate(e=>getComputedStyle(e).backgroundColor);
  assert.equal(await headerBg(),'rgb(255, 255, 255)','Landing header is solid white at the top');
  await page.evaluate(()=>window.scrollTo(0,700));await page.waitForTimeout(700);
  assert.match(await headerBg(),/^rgba\(255, 255, 255, 0\.\d+\)$/,'Landing header turns translucent white after scrolling');
  await page.goto(new URL('',base).href,{waitUntil:'domcontentloaded'});
  assert.equal(await page.locator('.site-header.tbt-landing-header').count(),0,'Homepage uses the shared menu header');
  assert.ok(await page.locator('.site-header .site-nav .site-nav__link').count()>=6,'Homepage header shows the full menu');
  await page.goto(new URL('classes/',(process.env.TBT_RUNTIME_URL || 'http://localhost:8888/').replace(/\/?$/,'/')).href,{waitUntil:'domcontentloaded'});
  const navItem=page.locator('.site-nav__item').filter({has:page.locator('[data-submenu-toggle]')}).first();
  const submenu=navItem.locator('.site-nav__submenu');
  assert.equal(await submenu.isVisible(),false,'Dropdown starts closed');
  await navItem.hover();
  await page.waitForTimeout(450);
  assert.equal(await submenu.isVisible(),true,'Hovering a menu item opens its dropdown');
  await navItem.locator('[data-submenu-toggle]').click();
  await page.mouse.move(700,500);await page.waitForTimeout(600);
  assert.equal(await submenu.isVisible(),false,'Dropdown closes when the pointer leaves');
  assert.equal(await navItem.locator('[data-submenu-toggle]').evaluate(e=>getComputedStyle(e).backgroundColor),'rgba(0, 0, 0, 0)','Dropdown arrow must never turn pink');
  await navItem.hover();await page.waitForTimeout(450);
  assert.equal(await navItem.locator('[data-submenu-toggle]').evaluate(e=>getComputedStyle(e).backgroundColor),'rgba(0, 0, 0, 0)','Dropdown arrow stays transparent while hovered');
  await page.mouse.move(700,500);await page.waitForTimeout(500);
  const navVisible=await page.evaluate(()=>{const n=document.querySelector('.site-nav');n.hidden=true;const d=getComputedStyle(n).display;n.hidden=false;return d});
  assert.notEqual(navVisible,'none','Desktop navigation must stay visible even if its hidden attribute is set');
  const labels=await page.locator('.site-header .site-nav .site-nav__link').allTextContents();
  for (const label of ['Home','Classes','Results & Testimonials','Free Bio Notes','About Us','Our Causes']) assert.ok(labels.some(x=>x.trim()===label),'Header must show '+label);
 } finally {await browser.close();}
})().catch(e=>{console.error(e);process.exit(1)});
