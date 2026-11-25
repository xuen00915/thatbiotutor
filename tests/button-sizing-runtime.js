const assert = require('node:assert/strict');
const {chromium} = require('playwright');
const selector = '#main-content :is(.tbt-home-button,.tbt-button,.tbt-programme-button,.tbt-programme-text-link,.tbt-programme-hall__action,.tbt-testimonials-button,.tbt-resource-button,.tbt-institutional-button,.tbt-reviews-controls__more)';
(async()=>{
 const browser=await chromium.launch();
 try {
  const page=await browser.newPage({reducedMotion:'reduce'});
  const base=(process.env.TBT_RUNTIME_URL||'http://localhost:4173/').replace(/\/?$/,'/');
  for(const width of [1440,1280,1024,768,390,320]) {
   await page.setViewportSize({width,height:900});
   for(const route of ['', 'classes/','sec-3-pure-g3-biology-tuition/','sec-4-pure-g3-biology-tuition/','testimonials/','notes/','celldivision/','contact/','intensivefix/']) {
    await page.goto(new URL(route,base).href,{waitUntil:'domcontentloaded'});
    await page.evaluate(()=>document.fonts.ready);
    const buttons=await page.locator(selector).evaluateAll(es=>es.filter(e=>e.getBoundingClientRect().height>0).map(e=>{
     const b=e.getBoundingClientRect(),s=getComputedStyle(e),r=document.createRange();r.selectNodeContents(e);const t=r.getBoundingClientRect();
     return {singleLine:t.height<parseFloat(s.lineHeight)*1.6,text:e.textContent.trim(),width:b.width,height:b.height,radius:parseFloat(s.borderRadius),fits:t.left>=b.left-1&&t.right<=b.right+1&&t.top>=b.top-1&&t.bottom<=b.bottom+1};
    }));
    assert.ok(buttons.length,route+' has content buttons');
    if(route==='testimonials/') assert.ok(buttons.filter(b=>b.text==='Transform My Grade Now').every(b=>b.singleLine),'Transform My Grade Now stays on one line at '+width);
    const standard=route==='intensivefix/'?(width<=1180?300:340):(width<=1180?240:272); // IntensiveFix: wider one-line buttons
    for(const b of buttons) assert.ok(Math.abs(b.width-Math.min(standard,width-32))<=1&&b.height===60&&b.radius>=999&&b.fits,`${route} @${width}: ${JSON.stringify(b)}`);
    assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1),`${route} @${width}: no overflow`);
   }
   console.log('PASS: global content button sizing @'+width);
  }
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1});
