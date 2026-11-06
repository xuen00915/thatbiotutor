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
    const opts=[...document.querySelectorAll('.tbt-sec3-page .tbt-programme-options__grid article')].map(a=>({hdr:cs(a.querySelector('h3'),'color'),bg:getComputedStyle(a).backgroundImage.slice(0,60),items:[...a.querySelectorAll('li')].map(li=>({bold:li.querySelectorAll('strong').length,check:getComputedStyle(li,'::before').content}))}));
    const both=getComputedStyle(q('.tbt-programme-options__included')).backgroundColor;
    const hall=[...document.querySelectorAll('.tbt-sec3-page .tbt-programme-track')].map(i=>{const b=i.getBoundingClientRect();return {w:Math.round(b.width),h:Math.round(b.height),nat:Math.round(i.naturalHeight/i.naturalWidth*100)/100,box:Math.round(b.height/b.width*100)/100,fit:cs(i,'objectFit')}});
    const frame=q('.tbt-programme-lesson iframe');const fb=frame?frame.getBoundingClientRect():null;
    const lessonGrid=q('.tbt-programme-lesson__grid').getBoundingClientRect();
    const cover=cs(q('.tbt-programme-cover'),'backgroundColor');
    const topics=[...document.querySelectorAll('.tbt-sec3-page .tbt-programme-topics li')].map(li=>({t:li.textContent.trim().slice(0,12).trim(),x:Math.round(li.getBoundingClientRect().left),y:Math.round(li.getBoundingClientRect().top)}));
    const lines=(()=>{const w=document.createTreeWalker(h1,NodeFilter.SHOW_TEXT);const m=new Map();let n;while((n=w.nextNode())){const re=/\S+/g;let x;while((x=re.exec(n.textContent))){const rg=document.createRange();rg.setStart(n,x.index);rg.setEnd(n,x.index+x[0].length);const top=Math.round(rg.getBoundingClientRect().top/20);m.set(top,(m.get(top)||[]).concat(x[0]))}}return [...m.entries()].sort((a,b)=>a[0]-b[0]).map(e=>e[1].join(' '))})();
    const topicsCard=getComputedStyle(q('.tbt-programme-topics'));
    const accents=['.tbt-programme-lesson h2 span','.tbt-programme-inclusions-section h2 span'].map(sel=>cs(q(sel),'color'));
    return {topicsRadius:topicsCard.borderTopLeftRadius,accents,lines,eyebrowTransform:cs(eyebrow,'textTransform'),eyebrow:{show:cs(eyebrow,'display')!=='none'&&eyebrow.getBoundingClientRect().height>0,font:font(eyebrow),style:cs(eyebrow,'fontStyle')},
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
   assert.ok(r.opts.every(o=>o.items.length===6&&o.items.every(i=>i.bold>=1&&/✓/.test(i.check))),'Class options need check marks and bold keywords '+JSON.stringify(r.opts));
   assert.equal(r.both,'rgb(8, 93, 73)','Both-formats block must use the original teal');
   assert.equal(r.hall.length,2);
   assert.ok(r.hall.every(h=>h.nat>=1.5&&Math.abs(h.box-h.nat)<0.08&&h.fit==='contain'),'Hall of Fame must show the two result tables uncropped '+JSON.stringify(r.hall));
   assert.ok(r.video&&/youtube(-nocookie)?\.com\/embed\/e55iPGQL6ng/.test(r.video.src)&&Math.abs(r.video.w/r.video.h-16/9)<0.05&&r.video.w>=Math.min(width-32,700)&&r.video.gap<=80,'Lesson video must be the original YouTube video, 16:9, with no big gap '+JSON.stringify(r.video));
   assert.equal(r.cover,'rgb(8, 93, 73)','What-we-cover section must use the original teal');
   if(width>=768){const left=r.topics.filter(t=>t.x===Math.min(...r.topics.map(x=>x.x))).map(t=>t.t);assert.deepEqual(left,['Cells','Movement of','Biomolecules','Enzymes','Nutrition in'],'Topics 1-5 must run down the left column '+JSON.stringify(r.topics));}
   assert.equal(r.topicsRadius,'0px','Topics list must join the title above without a notch');
   assert.ok(r.accents.every(c=>c==='rgb(141, 225, 94)'),'Headings on dark sections use the original light green '+JSON.stringify(r.accents));
   assert.equal(r.overflow,0,'No sideways scroll at '+width+'px');
  }
  console.log('PASS: sec 3 page runtime');
 } finally { await browser.close(); }
})().catch(e=>{console.error(e);process.exit(1)});
