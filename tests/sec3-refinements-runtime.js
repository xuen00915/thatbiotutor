const assert = require('node:assert/strict');
const { chromium } = require('playwright');
(async () => {
 const browser = await chromium.launch();
 try {
  const page = await browser.newPage({ reducedMotion: 'reduce' });
  const base = (process.env.TBT_RUNTIME_URL || 'http://localhost:8892').replace(/\/?$/, '/');
  for (const width of [1440, 1024, 768, 427, 390, 320]) {
   await page.setViewportSize({width, height:900});
   await page.goto(new URL('about/', base).href);
   const aboutWidth = await page.locator('.tbt-reading-width').first().evaluate(e=>e.getBoundingClientRect().width);
   await page.goto(new URL('sec-3-pure-g3-biology-tuition/', base).href);
   await page.evaluate(()=>document.fonts.ready);
   const spacing=await page.evaluate(()=>{
    const q=s=>document.querySelector(s),b=e=>e.getBoundingClientRect();
    const cards=[...document.querySelectorAll('.tbt-programme-options__grid article')];
    const included=q('.tbt-programme-options__included');
    return {cards:b(cards[1]).top-b(cards[0]).bottom,included:b(included).top-b(q('.tbt-programme-options__grid')).bottom,optionsBottom:b(q('.tbt-programme-options')).bottom-b(included).bottom,faq:b(q('.tbt-programme-faq .tbt-programme-kicker')).top-b(q('.tbt-programme-tutors .tbt-programme-button')).bottom};
   });
   assert.ok(Math.abs(spacing.cards-24)<=1&&Math.abs(spacing.included-24)<=1&&Math.abs(spacing.optionsBottom-24)<=1&&Math.abs(spacing.faq-(width<=640?48:64))<=1,'Compact option cards and FAQ transition at '+width+': '+JSON.stringify(spacing));
   if(width>=641&&width<=1180) {
    const crop=await page.locator('img.tbt-practical-photo[src*="35ad6135826a6626379208fe8e6eefe5"]').evaluate(e=>{const frame=e.parentElement,b=frame.getBoundingClientRect(),i=e.getBoundingClientRect();return {wrapped:frame.classList.contains('tbt-practical-crop'),overflow:getComputedStyle(frame).overflow,coverage:i.left<=b.left&&i.right>=b.right&&i.top<=b.top&&i.bottom>=b.bottom};});
    assert.ok(crop.wrapped&&crop.overflow==='hidden'&&crop.coverage,'Tablet practical photo fills a cropped frame: '+JSON.stringify(crop));
   }
   const coverSpacing=await page.evaluate(()=>{
    const root=document.querySelector('.tbt-programme-cover'),cs=e=>getComputedStyle(e),box=e=>e.getBoundingClientRect();
    const cards=[...root.querySelectorAll('.tbt-programme-phase,.tbt-programme-topics-card,.tbt-programme-exam')];
    return {gaps:cards.slice(1).map((e,i)=>box(e).top-box(cards[i]).bottom),pads:cards.map(e=>[parseFloat(cs(e).paddingTop),parseFloat(cs(e).paddingBottom)]),section:parseFloat(cs(root).paddingTop)};
   });
   assert.ok(coverSpacing.gaps.every(g=>Math.abs(g-24)<=1)&&coverSpacing.pads.flat().every(p=>p===(width<=640?20:24))&&coverSpacing.section===(width<=640?24:32),'Compact What We Cover layout at '+width+': '+JSON.stringify(coverSpacing));
   if(width<=640) {
    const hallButton=await page.locator('.tbt-programme-hall__actions .tbt-programme-button').evaluate(e=>{const b=e.getBoundingClientRect();return {center:(b.left+b.right)/2,width:b.width};});
    assert.ok(Math.abs(hallButton.center-width/2)<=1&&hallButton.width===240,'Mobile hall WhatsApp button keeps shared size and is centered: '+JSON.stringify(hallButton));
   }
   const r = await page.evaluate(()=>{
    const q=s=>document.querySelector('.tbt-sec3-page '+s), qa=s=>[...document.querySelectorAll('.tbt-sec3-page '+s)];
    const box=e=>e.getBoundingClientRect(), cs=e=>getComputedStyle(e);
    const heads=qa('.tbt-programme-option-heading');
    const cards=qa('.tbt-programme-testimonials .tbt-testimonial');
    return {
     headers:heads.map(e=>box(e).bottom-box(e.querySelector('b')).bottom),
     included:qa('.tbt-programme-options__included .tbt-tick').map(e=>({height:box(e).height,line:parseFloat(cs(e).lineHeight),y:box(e).top})),
     cards:cards.map(e=>({radius:cs(e).borderTopLeftRadius,border:cs(e).borderTopColor,portraitTop:box(e.querySelector('img')).top-box(e).top,portraitWidth:parseFloat(cs(e.querySelector('img')).width),nameBottom:box(e).bottom-box(e.querySelector('.tbt-testimonial__name')).bottom,quote:getComputedStyle(e,'::before').content,quoteSize:parseFloat(cs(e.querySelector('blockquote p')).fontSize),textAlign:cs(e.querySelector('blockquote p')).textAlign,shadow:cs(e).boxShadow})),
     faqBackground:cs(q('.tbt-programme-faq')).backgroundColor,trialText:q('.tbt-programme-hall__actions a:last-child').textContent.trim(),
     button:{text:q('.tbt-programme-text-link').textContent.trim(),radius:parseFloat(cs(q('.tbt-programme-text-link')).borderTopLeftRadius)},practicalGap:box(q('.tbt-practical-feature')).top-box(q('.tbt-programme-lesson__grid')).bottom,
     practical:parseFloat(cs(q('.tbt-practical-feature > h3')).fontSize),
     headings:qa('.tbt-programme-lesson__grid h3').map(e=>parseFloat(cs(e).fontSize)),
     widths:qa('.tbt-programme-container, .tbt-programme-hall__grid, .tbt-programme-proofs, .tbt-programme-lesson__video, .tbt-programme-inclusions, .tbt-programme-quality, .tbt-programme-faq .tbt-home-faq__list').map(e=>box(e).width),
     tutors:qa('.tbt-programme-tutors .tbt-tutor').map(e=>({x:Math.round(box(e).left),y:Math.round(box(e).top)})),
     slides:qa('.tbt-faq-slider [data-carousel-slide]').length,
     overflow:document.documentElement.scrollWidth-innerWidth
    };
   });
   assert.equal(r.faqBackground,'rgb(255, 255, 255)','FAQ section background is white'); assert.equal(r.trialText,'WhatsApp Us');
   assert.equal(r.button.text,'More Testimonials'); assert.ok(r.button.radius>=999,'More Testimonials uses shared pill style'); assert.ok(Math.abs(r.practicalGap-(width<=520?32:40))<=1,'Practical heading uses the approved compact spacing at '+width+'px: '+r.practicalGap);
   assert.equal(r.headers.length,2,'Both option headers need a content-sized header');
   assert.ok(r.headers.every(n=>n>=20),'Space below option subtitle');
   assert.ok(r.included.every(e=>e.height<=e.line+1),'Each benefit stays on one line');
   if(width>=1024) assert.ok(r.included.every(e=>Math.abs(e.y-r.included[0].y)<1),'All four benefits share one desktop row');
   assert.ok(r.cards.every(c=>c.radius==='24px'&&c.border!=='rgb(0, 0, 0)'&&c.portraitTop<=25&&c.portraitWidth===60&&c.nameBottom<=40&&c.quoteSize<=16&&c.textAlign==='center'&&c.shadow!=='none'),'Review cards match reference '+JSON.stringify(r.cards.map(({quote,...c})=>c)));
   assert.ok(r.practical>=28 && r.headings.every(n=>n<=20&&n<r.practical),'Practical title larger than lesson card headings');
   assert.ok(r.widths.every(w=>Math.abs(w-aboutWidth)<2),'Every section uses About Us content width '+JSON.stringify({aboutWidth,widths:r.widths}));
   assert.equal(new Set(r.tutors.map(t=>t.x)).size,width>=1024?4:2,'Responsive tutor columns');
   assert.equal(r.slides,0,'FAQ graphics are removed'); assert.equal(r.overflow,0,'No horizontal overflow');
   if(width<=640) assert.ok(await page.locator('.tbt-programme-tutors .tbt-tutor img').evaluateAll(es=>es.every(e=>e.getBoundingClientRect().width<=144)), 'Mobile tutor portraits are compact');
   const tutor=page.locator('.tbt-programme-tutors .tbt-tutor').first(); await tutor.hover(); await page.waitForTimeout(400);
   assert.deepEqual(await tutor.evaluate(e=>({transform:getComputedStyle(e).transform,shadow:getComputedStyle(e).boxShadow})),{transform:'none',shadow:'none'},'Tutor profiles remain still without a hover shadow');
   assert.equal(await page.locator('.tbt-home-faq__list [data-faq-button]').count(),13,'Text FAQs remain available');
  }
  console.log('PASS: Sec 3 requested refinements, width parity and text-only FAQs');
 } finally { await browser.close(); }
})().catch(e=>{console.error(e);process.exit(1)});
