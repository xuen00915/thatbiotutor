const assert = require('node:assert/strict');
const { chromium } = require('playwright');
(async () => {
 const browser = await chromium.launch();
 try {
  const page = await browser.newPage({ reducedMotion: 'reduce' });
  const base = (process.env.TBT_RUNTIME_URL || 'http://localhost:8892').replace(/\/?$/, '/');
  for (const width of [1440, 1024, 768, 390, 320]) {
   await page.setViewportSize({width, height:900});
   await page.goto(new URL('about/', base).href);
   const aboutWidth = await page.locator('.tbt-reading-width').first().evaluate(e=>e.getBoundingClientRect().width);
   await page.goto(new URL('sec-3-pure-g3-biology-tuition/', base).href);
   await page.evaluate(()=>document.fonts.ready);
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
     sliderButton:{border:getComputedStyle(q('.tbt-faq-slider__control')).borderTopWidth,radius:getComputedStyle(q('.tbt-faq-slider__control')).borderTopLeftRadius},
     button:{text:q('.tbt-programme-text-link').textContent.trim(),radius:parseFloat(cs(q('.tbt-programme-text-link')).borderTopLeftRadius)},practicalGap:box(q('.tbt-practical-feature')).top-box(q('.tbt-programme-lesson__grid')).bottom,
     practical:parseFloat(cs(q('.tbt-practical-feature > h3')).fontSize),
     headings:qa('.tbt-programme-lesson__grid h3').map(e=>parseFloat(cs(e).fontSize)),
     widths:qa('.tbt-programme-container, .tbt-programme-hall__grid, .tbt-programme-proofs, .tbt-programme-lesson__video, .tbt-programme-inclusions, .tbt-programme-quality, .tbt-programme-faq .tbt-faqs').map(e=>box(e).width),
     tutors:qa('.tbt-programme-tutors .tbt-tutor').map(e=>({x:Math.round(box(e).left),y:Math.round(box(e).top)})),
     slides:qa('.tbt-faq-slider [data-carousel-slide]').length,
     after:!!(q('.tbt-faqs').compareDocumentPosition(q('.tbt-faq-slider')) & Node.DOCUMENT_POSITION_FOLLOWING),
     overflow:document.documentElement.scrollWidth-innerWidth
    };
   });
   assert.equal(r.faqBackground,'rgb(250, 249, 245)','FAQ background matches supplied screenshot'); assert.equal(r.trialText,'Book a Trial');
   assert.equal(r.sliderButton.border,'1px','Carousel arrows use the thin outlined reference style'); assert.equal(r.sliderButton.radius,'50%');
   assert.equal(r.button.text,'More Testimonials'); assert.ok(r.button.radius>=999,'More Testimonials uses shared pill style'); assert.ok(r.practicalGap>=48,'Practical heading has breathing room above it');
   assert.equal(r.headers.length,2,'Both option headers need a content-sized header');
   assert.ok(r.headers.every(n=>n>=20),'Space below option subtitle');
   assert.ok(r.included.every(e=>e.height<=e.line+1),'Each benefit stays on one line');
   if(width>=1024) assert.ok(r.included.every(e=>Math.abs(e.y-r.included[0].y)<1),'All four benefits share one desktop row');
   assert.ok(r.cards.every(c=>c.radius==='4px'&&c.border==='rgb(0, 0, 0)'&&c.portraitTop<=25&&c.portraitWidth===60&&c.nameBottom<=40&&c.quoteSize<=16&&c.textAlign==='center'&&c.shadow==='none'),'Review cards match reference '+JSON.stringify(r.cards.map(({quote,...c})=>c)));
   assert.ok(r.practical>=28 && r.headings.every(n=>n<=20&&n<r.practical),'Practical title larger than lesson card headings');
   assert.ok(r.widths.every(w=>Math.abs(w-aboutWidth)<2),'Every section uses About Us content width '+JSON.stringify({aboutWidth,widths:r.widths}));
   assert.equal(new Set(r.tutors.map(t=>t.x)).size,width>=1024?4:width>=641?2:1,'Responsive tutor columns');
   assert.equal(r.slides,12,'All supplied FAQ graphics'); assert.ok(r.after,'Slider follows all FAQ answers'); assert.equal(r.overflow,0,'No horizontal overflow');
   const tutor=page.locator('.tbt-programme-tutors .tbt-tutor').first(); await tutor.hover(); await page.waitForTimeout(400);
   assert.deepEqual(await tutor.evaluate(e=>({transform:getComputedStyle(e).transform,shadow:getComputedStyle(e).boxShadow})),{transform:'none',shadow:'none'},'Tutor profiles remain still without a hover shadow');
   const slider=page.locator('.tbt-faq-slider'); await slider.scrollIntoViewIfNeeded();
   assert.ok(await slider.evaluate(e=>{const image=e.querySelector('.tbt-faq-slider__viewport').getBoundingClientRect();return [...e.querySelectorAll('[data-carousel-prev],[data-carousel-next]')].every(b=>{const r=b.getBoundingClientRect();return r.right<=image.left||r.left>=image.right||r.top>=image.bottom;});}),'Slider arrows must not cover the image');
   await slider.locator('[data-carousel-next]').click();
   assert.equal(await slider.locator('[data-carousel-dot]').nth(1).getAttribute('aria-current'),'true');
   await slider.locator('[data-carousel-prev]').click();
   assert.equal(await slider.locator('[data-carousel-dot]').first().getAttribute('aria-current'),'true');
   if(width<=640) {
    assert.equal(await slider.locator('[data-carousel-status]').count(),1,'Mobile slider has a page counter');
    assert.equal(await slider.locator('[data-carousel-dot]').first().isVisible(),false,'Mobile dots are hidden');
    assert.equal(await slider.locator('[data-carousel-status]').innerText(),'1 / 12');
    await slider.locator('[data-carousel-prev]').click();
    assert.equal(await slider.locator('[data-carousel-status]').innerText(),'12 / 12');
   } else { await slider.locator('[data-carousel-dot]').last().click(); }
   assert.equal(await slider.locator('[data-carousel-dot]').last().getAttribute('aria-current'),'true');
   await page.waitForFunction(()=>[...document.querySelectorAll('.tbt-faq-slider img')].every(i=>i.complete&&i.naturalWidth>0),null,{timeout:30000});
   assert.ok(await slider.locator('img').evaluateAll(es=>es.every(e=>e.complete&&e.naturalWidth>0)),'Slider images load');
  }
  console.log('PASS: Sec 3 requested refinements, width parity and 12-slide navigation');
 } finally { await browser.close(); }
})().catch(e=>{console.error(e);process.exit(1)});
