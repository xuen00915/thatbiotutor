const assert = require('node:assert/strict');
const {chromium} = require('playwright');
(async()=>{
 const browser=await chromium.launch();
 try {
  const page=await browser.newPage({reducedMotion:'reduce'});
  const base=(process.env.TBT_RUNTIME_URL||'http://localhost:8892').replace(/\/?$/,'/');
  for(const width of [1440,1024,768,390,320]){
   await page.setViewportSize({width,height:900});
   await page.goto(new URL('sec-4-pure-g3-biology-tuition/',base).href);
   await page.evaluate(async()=>{await document.fonts.ready;for(let y=0;y<document.body.scrollHeight;y+=600){scrollTo(0,y);await new Promise(r=>setTimeout(r,35));}});
   await page.waitForFunction(()=>[...document.querySelectorAll('.tbt-sec4-page img')].every(i=>i.complete&&i.naturalWidth>0));
   const r=await page.evaluate(()=>{
    const q=s=>document.querySelector('.tbt-sec4-page '+s),qa=s=>[...document.querySelectorAll('.tbt-sec4-page '+s)],cs=e=>getComputedStyle(e),b=e=>e.getBoundingClientRect();
    const phases=qa('.tbt-programme-phase'),note=q('.tbt-programme-join-note');
    const video=q('.tbt-programme-lesson iframe');
    return {
     hero:{image:q('.tbt-programme-hero__background img').src,shade:cs(q('.tbt-programme-hero__shade')).backgroundColor,text:q('h1').textContent,font:cs(q('h1')).fontFamily,weight:cs(q('h1')).fontWeight,accents:qa('.tbt-programme-hero__lead span').length,label:cs(q('.tbt-programme-hero__eyebrow')).textTransform},
     options:qa('.tbt-programme-options__grid li').map(e=>({bold:e.querySelectorAll('strong').length,tick:getComputedStyle(e,'::before').backgroundImage.includes('svg')})),
     centered:cs(q('.tbt-programme-options h2')).textAlign,
     phases:phases.length,joinInLast:phases[2]?.contains(note),phase3:phases[2]?.textContent,
     topics:!!q('.tbt-programme-topics-card ol'),topicNames:qa('.tbt-programme-topics li').map(e=>e.textContent),
     results:qa('.tbt-programme-track').map(e=>({src:e.src,ratio:e.naturalHeight/e.naturalWidth,rendered:b(e).height/b(e).width})),
     stats:{photo:q('.tbt-programme-numbers__background img').naturalWidth/q('.tbt-programme-numbers__background img').naturalHeight,color:cs(q('.tbt-programme-stats strong')).color,values:qa('.tbt-programme-stats strong').map(e=>e.textContent)},
     reviews:qa('.tbt-programme-testimonials .tbt-testimonial').map(e=>({name:e.querySelector('.tbt-testimonial__name').textContent,photo:!!e.querySelector('img'),top:e.querySelector('img')?b(e.querySelector('img')).top-b(e).top:999,border:cs(e).borderTopColor,align:cs(e.querySelector('blockquote p')).textAlign})),
     video:video?{src:video.src,ratio:b(video).width/b(video).height,gap:b(q('.tbt-programme-lesson__grid')).top-b(video).bottom}:null,
     lessonTitles:qa('.tbt-programme-lesson__grid h3').map(e=>e.textContent),photos:qa('.tbt-practical-photo').filter(e=>b(e).height>=200&&cs(e.parentElement).visibility!=='hidden').length,
     icons:qa('.tbt-inclusion-icon img').length,practical:parseFloat(cs(q('.tbt-practical-feature h3')).fontSize),small:parseFloat(cs(q('.tbt-programme-lesson__grid h3')).fontSize),
     tutors:qa('.tbt-programme-tutors .tbt-tutor').map(e=>Math.round(b(e).left)),
     faqCount:qa('.tbt-faq').length,faq:cs(q('.tbt-programme-faq')).backgroundColor,
     widths:qa('.tbt-programme-container, .tbt-programme-hall__grid, .tbt-programme-lesson__video, .tbt-programme-inclusions, .tbt-programme-faq .tbt-faqs').map(e=>b(e).width),
     overflow:document.documentElement.scrollWidth-innerWidth
    };
   });
   assert.match(r.hero.image,/cb930409307ab6ba74e509232acff7c5/,'Use the uncropped original Sec 4 hero');assert.equal(r.hero.shade,'rgba(0, 0, 0, 0)','Original hero is already darkened');
   assert.match(r.hero.font,/^"?Montserrat/);assert.equal(r.hero.weight,'700');assert.equal(r.hero.label,'none');assert.ok(r.hero.accents>=7);assert.match(r.hero.text,/From B4 or worse to A in O Levels/);
   assert.equal(r.options.length,12);assert.ok(r.options.every(e=>e.bold&&e.tick));assert.equal(r.centered,'center');
   assert.equal(r.phases,3);assert.ok(r.joinInLast,'Joining note belongs inside Phase 3');assert.match(r.phase3,/O Level and prelim questions/);assert.ok(r.topics);
   assert.deepEqual(r.topicNames,['Infectious Diseases','Nutrition in Plants','Transport in Plants','Organisms and Their Environment','Molecular Genetics','Reproduction in Plants','Reproduction in Humans','Inheritance']);
   assert.ok(r.results[0].src.includes('73bc1256792c12554d50dc9a124a1b4a')&&r.results[1].src.includes('d94e6ef92d16a8621101f7bd961b5da3'),'Use Sec 4 original results');
   assert.ok(r.results.length===2&&r.results.every(e=>e.ratio>1.5&&Math.abs(e.ratio-e.rendered)<.05),'Original result tables, uncropped');
   assert.ok(r.stats.photo>3);assert.equal(r.stats.color,'rgb(141, 225, 94)');assert.deepEqual(r.stats.values,['98','912+','69.21%','2.3','91%','1 in 2']);
   assert.deepEqual(r.reviews.map(e=>e.name),['Yong Zheng, Sec 4 in 2024','Gwen, Sec 4 in 2024','Aryan, Sec 4 in 2024']);assert.ok(r.reviews.every(e=>e.photo&&e.top<=25&&e.border==='rgb(0, 0, 0)'&&e.align==='center'));
   assert.ok(r.video&&r.video.src.includes('/embed/e8F2G0P4dEA')&&Math.abs(r.video.ratio-16/9)<.05&&r.video.gap<80,'Original ADH lesson video');
   assert.deepEqual(r.lessonTitles,['Concept first','Memory shortcuts','OEQ practice and exam skills','Kahoot review']);assert.equal(r.photos,3);assert.equal(r.icons,8);assert.ok(r.practical>=28&&r.small<=20);
   assert.equal(new Set(r.tutors).size,width>=1024?4:width>=641?2:1);assert.equal(r.faqCount,12);assert.equal(r.faq,'rgb(250, 249, 245)');
   assert.ok(r.widths.every(w=>Math.abs(w-Math.min(1120,width-32))<2),'Shared content width');assert.equal(r.overflow,0);
  }
  console.log('PASS: Sec 4 original content, media, shared styling and responsive layout');
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exit(1)});
