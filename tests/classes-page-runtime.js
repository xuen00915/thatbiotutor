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
    const centred=[...document.querySelectorAll('.tbt-classes-page .tbt-programme-section > .tbt-programme-container > h2')].map(h=>{const rg=document.createRange();rg.selectNodeContents(h);const b=rg.getBoundingClientRect();return {t:h.textContent.slice(0,30),off:Math.round(Math.abs((b.left+b.right)/2-innerWidth/2))}});
    const cta=document.querySelector('.tbt-programme-levels__button').getBoundingClientRect();
    return {contrast,photos,centred,ctaOff:Math.round(Math.abs((cta.left+cta.right)/2-innerWidth/2)),overflow:document.documentElement.scrollWidth-innerWidth};
   });
   assert.ok(r.contrast.every(c=>c.ratio>=4.5),'Section labels must be readable ('+width+'px) '+JSON.stringify(r.contrast.filter(c=>c.ratio<4.5)));
   assert.equal(r.photos.length,3);
   assert.ok(r.photos.every(p=>p.v==='visible'&&p.h>=120&&p.r>=14),'Practical session photos must be visible and rounded ('+width+'px) '+JSON.stringify(r.photos));
   assert.ok(r.centred.every(c=>c.off<=8),'Section headings must be centred ('+width+'px) '+JSON.stringify(r.centred.filter(c=>c.off>8)));
   assert.ok(r.ctaOff<=8,'WhatsApp button must be centred ('+width+'px) '+r.ctaOff);
   assert.equal(r.overflow,0,'No sideways scroll at '+width+'px');
  }
  console.log('PASS: classes page runtime');
 } finally { await browser.close(); }
})().catch(e=>{console.error(e);process.exit(1)});
