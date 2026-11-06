const assert = require('node:assert/strict');
const {chromium} = require('playwright');
(async()=>{
 const base=(process.env.TBT_RUNTIME_URL || 'http://localhost:8888/').replace(/\/?$/,'/');
 const browser=await chromium.launch();
 try {
  const page=await browser.newPage();
  for(const width of [1440,1024,768,390,320]) {
   await page.setViewportSize({width,height:900});
   await page.goto(new URL('classes/',base).href,{waitUntil:'load'});
   await page.evaluate(async()=>{for(let y=0;y<document.body.scrollHeight;y+=500){scrollTo(0,y);await new Promise(r=>setTimeout(r,50))}});
   await page.waitForTimeout(600);
   const r=await page.evaluate(()=>{
    const lum=(c)=>{const m=c.match(/[\d.]+/g).map(Number);const f=v=>{v/=255;return v<=.03928?v/12.92:Math.pow((v+.055)/1.055,2.4)};return .2126*f(m[0])+.7152*f(m[1])+.0722*f(m[2])};
    const ratio=(a,b)=>{const x=lum(a),y=lum(b);return (Math.max(x,y)+.05)/(Math.min(x,y)+.05)};
    const contrast=[...document.querySelectorAll('.tbt-classes-page .tbt-programme-kicker')].map(k=>{let e=k,bg='rgba(0, 0, 0, 0)';while(e&&/rgba\(0, 0, 0, 0\)|transparent/.test(bg)){bg=getComputedStyle(e).backgroundColor;e=e.parentElement}return {t:k.textContent.trim(),ratio:Math.round(ratio(getComputedStyle(k).color,bg)*10)/10}});
    const photos=[...document.querySelectorAll('.tbt-classes-page .tbt-practical-photo')].map(i=>({v:getComputedStyle(i.parentElement).visibility,h:Math.round(i.getBoundingClientRect().height),r:parseFloat(getComputedStyle(i).borderTopLeftRadius)}));
    const centred=[...document.querySelectorAll('.tbt-classes-page .tbt-programme-section:not(.tbt-programme-struggles) > .tbt-programme-container > h2')].map(h=>{const rg=document.createRange();rg.selectNodeContents(h);const b=rg.getBoundingClientRect();return {t:h.textContent.slice(0,30),off:Math.round(Math.abs((b.left+b.right)/2-innerWidth/2))}});
    const cta=document.querySelector('.tbt-programme-levels__button').getBoundingClientRect();
    const rgb=c=>c.match(/[\d.]+/g).map(Number);
    const bgOf=e=>getComputedStyle(e).backgroundColor;
    const box=e=>e.getBoundingClientRect();
    const sec=n=>document.querySelector('.tbt-classes-page [data-section='+n+']');
    const shade=rgb(getComputedStyle(document.querySelector('.tbt-programme-hero--classes .tbt-programme-hero__shade')).backgroundColor);
    const cont=box(sec('struggles').querySelector('.tbt-programme-container'));
    const kick=box(sec('struggles').querySelector('.tbt-programme-kicker'));
    const h2s=getComputedStyle(sec('struggles').querySelector('h2'));
    const para=box(sec('definition').querySelector('.tbt-programme-copy > p'));
    const topics=box(sec('definition').querySelector('.tbt-topic-columns'));
    const h3=[...sec('struggles').querySelectorAll('.tbt-struggle-list h3')].map(h=>({italic:getComputedStyle(h).fontStyle,spans:[...h.querySelectorAll('span')].map(x=>getComputedStyle(x).color)}));
    const solContainer=box(sec('solutions').querySelector('.tbt-programme-container'));
    const solList=box(sec('solutions').querySelector('.tbt-solution-list'));
    const diffContainer=box(sec('difference').querySelector('.tbt-programme-container'));
    const diffList=box(sec('difference').querySelector('.tbt-difference-list'));
    const stats=[...sec('results').querySelectorAll('.tbt-programme-stats strong')].map(e=>getComputedStyle(e).color);
    const card=sec('levels').querySelector('.tbt-programme-levels__grid a');
    const cs=card.querySelector('span');
    const level={bg:bgOf(card),text:getComputedStyle(cs).color,title:getComputedStyle(card.querySelector('strong')).color,ratio:Math.round(ratio(getComputedStyle(cs).color,bgOf(card))*10)/10};
    const design={shadeAlpha:shade[3],kickerOff:Math.round(kick.left-cont.left),h2Align:h2s.textAlign,paraW:Math.round(para.width),topicsW:Math.round(topics.width),h3,strBg:bgOf(sec('struggles')),solBg:bgOf(sec('solutions')),solW:Math.round(solList.width),solContW:Math.round(solContainer.width),diffW:Math.round(diffList.width),diffContW:Math.round(diffContainer.width),stats,level};
    return {design,contrast,photos,centred,ctaOff:Math.round(Math.abs((cta.left+cta.right)/2-innerWidth/2)),overflow:document.documentElement.scrollWidth-innerWidth};
   });
   assert.ok(r.contrast.every(c=>c.ratio>=4.5),'Section labels must be readable ('+width+'px) '+JSON.stringify(r.contrast.filter(c=>c.ratio<4.5)));
   assert.equal(r.photos.length,3);
   assert.ok(r.photos.every(p=>p.v==='visible'&&p.h>=120&&p.r>=14),'Practical session photos must be visible and rounded ('+width+'px) '+JSON.stringify(r.photos));
   assert.ok(r.centred.every(c=>c.off<=8),'Section headings must be centred ('+width+'px) '+JSON.stringify(r.centred.filter(c=>c.off>8)));
   assert.ok(r.ctaOff<=8,'WhatsApp button must be centred ('+width+'px) '+r.ctaOff);
   assert.equal(r.overflow,0,'No sideways scroll at '+width+'px');
   const d=r.design;
   assert.ok(d.shadeAlpha<=0.35,'Hero overlay must be light ('+d.shadeAlpha+')');
   assert.ok(d.topicsW>=d.paraW-2||width<=768,'Topic lists must be as wide as the paragraph above ('+d.topicsW+' vs '+d.paraW+')');
   assert.ok(d.kickerOff<=2&&/left|start/.test(d.h2Align),'Struggle eyebrow and title must be left-aligned '+JSON.stringify({k:d.kickerOff,a:d.h2Align}));
   assert.ok(d.h3.length===4&&d.h3.every(h=>h.italic==='italic'&&h.spans.length>=1&&h.spans.every(c=>c!=='rgb(255, 255, 255)')),'Struggle headings must be italic with green keywords '+JSON.stringify(d.h3));
   assert.notEqual(d.strBg,d.solBg,'Struggle and How-TBT-solves sections need different backgrounds');
   assert.ok(Math.abs(d.solW-d.solContW)<=2,'How TBT solves cards must use the shared content width ('+d.solW+' vs '+d.solContW+')');
   assert.ok(Math.abs(d.diffW-d.diffContW)<=2,'TBT difference must use the shared content width ('+d.diffW+' vs '+d.diffContW+')');
   assert.ok(d.stats.every(c=>c==='rgb(255, 255, 255)'),'Result numbers must be white '+JSON.stringify(d.stats));
   assert.ok(d.level.bg==='rgb(255, 255, 255)'&&d.level.ratio>=7&&d.level.text!==d.level.title,'Level cards need a white card and dark text, not green on green '+JSON.stringify(d.level));
  }
  console.log('PASS: classes page runtime');
 } finally { await browser.close(); }
})().catch(e=>{console.error(e);process.exit(1)});
