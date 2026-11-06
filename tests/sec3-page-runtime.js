const assert = require('node:assert/strict');
const {chromium} = require('playwright');
(async()=>{
 const base=(process.env.TBT_RUNTIME_URL || 'http://localhost:8888/').replace(/\/?$/,'/');
 const browser=await chromium.launch();
 try {
  const page=await browser.newPage();
  for(const width of [1440,768,390]) {
   await page.setViewportSize({width,height:900});
   await page.goto(new URL('sec-3-pure-g3-biology-tuition/',base).href,{waitUntil:'load'});
   await page.evaluate(async()=>{for(let y=0;y<document.body.scrollHeight;y+=500){scrollTo(0,y);await new Promise(r=>setTimeout(r,50))}});
   await page.waitForTimeout(600);
   const r=await page.evaluate(()=>{
    const q=s=>document.querySelector('.tbt-sec3-page '+s);
    const cs=(e,p)=>getComputedStyle(e)[p];
    const font=e=>cs(e,'fontFamily').split(',')[0].replace(/"/g,'');
    const eyebrow=q('.tbt-programme-hero__eyebrow');
    const h1=q('.tbt-programme-hero h1');const accent=h1.querySelector('.tbt-programme-hero__highlight');
    const lead=q('.tbt-programme-hero__lead');const leadSpans=[...lead.querySelectorAll('span')];
    const shade=getComputedStyle(q('.tbt-programme-hero__shade')).backgroundColor.match(/[\d.]+/g).map(Number);
    const opts=[...document.querySelectorAll('.tbt-sec3-page .tbt-programme-options__grid article')].map(a=>({hdr:cs(a.querySelector('h3'),'color'),bg:getComputedStyle(a).backgroundImage.slice(0,60),items:[...a.querySelectorAll('li')].map(li=>({bold:li.querySelectorAll('strong').length,check:getComputedStyle(li,'::before').backgroundImage}))}));
    const both=getComputedStyle(q('.tbt-programme-options__included')).backgroundColor;
    const hall=[...document.querySelectorAll('.tbt-sec3-page .tbt-programme-track')].map(i=>{const b=i.getBoundingClientRect();return {w:Math.round(b.width),h:Math.round(b.height),nat:Math.round(i.naturalHeight/i.naturalWidth*100)/100,box:Math.round(b.height/b.width*100)/100,fit:cs(i,'objectFit')}});
    const frame=q('.tbt-programme-lesson iframe');const fb=frame?frame.getBoundingClientRect():null;
    const lessonGrid=q('.tbt-programme-lesson__grid').getBoundingClientRect();
    const cover=cs(q('.tbt-programme-cover'),'backgroundColor');
    const topics=[...document.querySelectorAll('.tbt-sec3-page .tbt-programme-topics li')].map(li=>({t:li.textContent.trim().slice(0,12).trim(),x:Math.round(li.getBoundingClientRect().left),y:Math.round(li.getBoundingClientRect().top)}));
    const lines=(()=>{const w=document.createTreeWalker(h1,NodeFilter.SHOW_TEXT);const m=new Map();let n;while((n=w.nextNode())){const re=/\S+/g;let x;while((x=re.exec(n.textContent))){const rg=document.createRange();rg.setStart(n,x.index);rg.setEnd(n,x.index+x[0].length);const top=Math.round(rg.getBoundingClientRect().top/20);m.set(top,(m.get(top)||[]).concat(x[0]))}}return [...m.entries()].sort((a,b)=>a[0]-b[0]).map(e=>e[1].join(' '))})();
    const topicsCard=getComputedStyle(q('.tbt-programme-topics'));
    const accents=['.tbt-programme-lesson h2 span','.tbt-programme-inclusions-section h2 span'].map(sel=>cs(q(sel),'color'));
    const centre=e=>{const rg=document.createRange();rg.selectNodeContents(e);const b=rg.getBoundingClientRect();return Math.round(Math.abs((b.left+b.right)/2-innerWidth/2))};
    const heads=['options','cover','faq'].map(n=>{const sec=q('[data-section='+n+']');return {n,k:centre(sec.querySelector('.tbt-programme-kicker')),h:centre(sec.querySelector('h2'))}});
    const tick=(sel)=>{const e=q(sel);return e?getComputedStyle(e,'::before').backgroundImage.includes('svg'):false};
    const ticks={opt:tick('.tbt-programme-options__grid li'),phase:tick('.tbt-programme-phase p'),exam:tick('.tbt-programme-exam li'),quality:tick('.tbt-programme-quality p')};
    const note=q('.tbt-programme-join-note');const noteIn=!!(note&&note.closest('.tbt-programme-phase'));
    const tcard=q('.tbt-programme-topics-card');const topicsOne=!!(tcard&&tcard.querySelector('h2')&&tcard.querySelector('ol'));
    const numImg=q('.tbt-programme-numbers__background img');const nums=q('.tbt-programme-stats strong');
    const numbers={ratio:Math.round(numImg.naturalWidth/numImg.naturalHeight*10)/10,col:cs(nums,'color'),shade:getComputedStyle(q('.tbt-programme-numbers__shade')).backgroundColor.match(/[\d.]+/g).map(Number)[3]};
    const testi=[...document.querySelectorAll('.tbt-sec3-page .tbt-programme-testimonials .tbt-testimonial')].map(a=>{const i=a.querySelector('img');const r=a.querySelector('.tbt-testimonial__result');return {img:!!(i&&i.getBoundingClientRect().width>=40),grade:r?r.textContent:''}});
    const icons=[...document.querySelectorAll('.tbt-sec3-page .tbt-inclusion-card')].map(c=>{const i=c.querySelector('.tbt-inclusion-icon img');return !!(i&&i.getBoundingClientRect().width>0)});
    const tutors={roles:[...document.querySelectorAll('.tbt-sec3-page .tbt-tutor__role')].filter(e=>e.getBoundingClientRect().height>0).length,names:[...document.querySelectorAll('.tbt-sec3-page .tbt-tutor h3')].map(h=>h.textContent),nameAbove:(()=>{const t=q('.tbt-tutor');return t.querySelector('h3').getBoundingClientRect().bottom<=t.querySelector('img').getBoundingClientRect().top+1})(),qItalic:cs(q('.tbt-programme-quality strong'),'fontStyle')};
    const lessonFonts=[q('.tbt-programme-lesson__grid h3'),q('.tbt-practical-feature > h3'),q('.tbt-inclusion-card h3')].map(e=>font(e));
    const faqSlider=!!q('.tbt-programme-faq [data-carousel] img');
    const iframeAttr=q('.tbt-programme-lesson iframe').getAttribute('width');
    return {heads,ticks,noteIn,topicsOne,numbers,testi,icons,tutors,lessonFonts,faqSlider,iframeAttr,topicsRadius:topicsCard.borderTopLeftRadius,accents,lines,eyebrowTransform:cs(eyebrow,'textTransform'),eyebrow:{show:cs(eyebrow,'display')!=='none'&&eyebrow.getBoundingClientRect().height>0,font:font(eyebrow),style:cs(eyebrow,'fontStyle')},
      h1:{font:font(h1)+(accent&&font(accent)!==font(h1)?'/'+font(accent):''),w:cs(h1,'fontWeight'),accent:accent?cs(accent,'color'):null},
      lead:{font:font(lead),spans:leadSpans.map(s=>cs(s,'color'))},shade:shade[3],opts,both,hall,
      video:fb?{src:frame.src,w:Math.round(fb.width),h:Math.round(fb.height),gap:Math.round(lessonGrid.top-fb.bottom)}:null,
      cover,topics,overflow:document.documentElement.scrollWidth-innerWidth};
   });
   assert.ok(r.eyebrow.show&&r.eyebrow.font==='Montserrat'&&r.eyebrow.style==='italic','Hero label must show in italic Montserrat '+JSON.stringify(r.eyebrow));
   assert.equal(r.eyebrowTransform,'none','Hero label is sentence case like the original');
   if(width===1440) assert.deepEqual(r.lines,['The Best Time to Start Pure /','G3 Biology Tuition is at the','Start of Sec 3.','The Second Best Time is Now!'],'Hero heading wraps like the original '+JSON.stringify(r.lines));
   assert.ok(r.h1.font==='Montserrat'&&r.h1.w==='700'&&r.h1.accent==='rgb(141, 225, 94)','Hero heading must be Montserrat bold with the original light green '+JSON.stringify(r.h1));
   assert.ok(r.lead.font==='Montserrat'&&r.lead.spans.length>=2&&r.lead.spans.every(c=>c==='rgb(141, 225, 94)'),'Hero lead must be Montserrat with two light-green phrases '+JSON.stringify(r.lead));
   assert.ok(r.shade<=0.4,'Hero overlay must be light ('+r.shade+')');
   assert.equal(r.opts.length,2);
   assert.ok(r.opts.every(o=>o.items.length===6&&o.items.every(i=>i.bold>=1&&/svg/.test(i.check))),'Class options need check marks and bold keywords '+JSON.stringify(r.opts));
   assert.equal(r.both,'rgb(8, 93, 73)','Both-formats block must use the original teal');
   assert.equal(r.hall.length,2);
   assert.ok(r.hall.every(h=>h.nat>=1.5&&Math.abs(h.box-h.nat)<0.08&&h.fit==='contain'),'Hall of Fame must show the two result tables uncropped '+JSON.stringify(r.hall));
   assert.ok(r.video&&/youtube(-nocookie)?\.com\/embed\/e55iPGQL6ng/.test(r.video.src)&&Math.abs(r.video.w/r.video.h-16/9)<0.05&&r.video.w>=Math.min(width-32,700)&&r.video.gap<=80,'Lesson video must be the original YouTube video, 16:9, with no big gap '+JSON.stringify(r.video));
   assert.equal(r.cover,'rgb(8, 93, 73)','What-we-cover section must use the original teal');
   if(width>=768){const left=r.topics.filter(t=>t.x===Math.min(...r.topics.map(x=>x.x))).map(t=>t.t);assert.deepEqual(left,['Cells','Movement of','Biomolecules','Enzymes','Nutrition in'],'Topics 1-5 must run down the left column '+JSON.stringify(r.topics));}
   assert.ok(r.heads.every(h=>h.k<=24&&h.h<=8),'Eyebrow and title must be centred in Class Options, What We Cover and FAQ at '+width+'px '+JSON.stringify(r.heads));
   assert.ok(Object.values(r.ticks).every(Boolean),'Lists use a tick icon, not a text glyph '+JSON.stringify(r.ticks));
   assert.ok(r.noteIn,'The "students can join any time" note belongs inside the Phase 2 card');
   assert.ok(r.topicsOne,'Sec 3 Topics title and list must sit in one container');
   assert.ok(r.numbers.ratio>=3&&r.numbers.col==='rgb(141, 225, 94)'&&r.numbers.shade<=0.3,'By the Numbers needs the original classroom photo, light overlay and light-green numbers '+JSON.stringify(r.numbers));
   assert.ok(r.testi.length===3&&r.testi.every(t=>t.img&&/→/.test(t.grade)),'Each Sec 3 review needs a profile picture and a grade line '+JSON.stringify(r.testi));
   assert.ok(r.icons.length===8&&r.icons.every(Boolean),'Every "Everything your child gets" card needs its icon');
   assert.ok(r.tutors.roles===0&&r.tutors.nameAbove&&r.tutors.names.some(n=>/\(Founder\)/.test(n))&&r.tutors.names.some(n=>/Ex-MOE/.test(n))&&r.tutors.qItalic==='italic','Tutors show name then photo only, quality box italic '+JSON.stringify(r.tutors));
   assert.ok(r.lessonFonts.every(f=>f==='Montserrat'),'Lesson and card titles use the original Montserrat '+JSON.stringify(r.lessonFonts));
   assert.ok(r.faqSlider,'FAQ needs the image slider');
   assert.equal(r.iframeAttr,'960','Video iframe carries its own size');
   assert.equal(r.topicsRadius,'0px','Topics list must join the title above without a notch');
   assert.ok(r.accents.every(c=>c==='rgb(141, 225, 94)'),'Headings on dark sections use the original light green '+JSON.stringify(r.accents));
   assert.equal(r.overflow,0,'No sideways scroll at '+width+'px');
  }
  console.log('PASS: sec 3 page runtime');
 } finally { await browser.close(); }
})().catch(e=>{console.error(e);process.exit(1)});
