const assert = require('node:assert/strict');
const {chromium} = require('playwright');
const copy = require('./fixtures/page-seo.json');
const base = (process.env.TBT_RUNTIME_URL || 'http://localhost:8890').replace(/\/?$/, '/');
(async()=>{const browser=await chromium.launch();try{const page=await browser.newPage();
for(const [key,[title,description]] of Object.entries(copy)){
 const response=await page.goto(new URL(key==='home'?'':key+'/',base).href,{waitUntil:'domcontentloaded'});
 assert.equal(response.status(),200,key);
 assert.equal(await page.title(),title,`${key}: exact SEO title, without duplicate brand`);
 for(const [selector,expected] of [['meta[name="description"]',description],['meta[property="og:title"]',title],['meta[property="og:description"]',description]]){
  assert.equal(await page.locator(selector).count(),1,`${key}: one ${selector}`);
  assert.equal(await page.locator(selector).getAttribute('content'),expected,key);
 }
 assert.equal(await page.locator('link[rel="canonical"]').count(),1,`${key}: exactly one canonical`);
 const canonical=await page.locator('link[rel="canonical"]').getAttribute('href');
 assert.equal(new URL(canonical).pathname,new URL(key==='home'?'':key+'/',base).pathname,key);
 console.log(`PASS SEO ${key}`);
}}finally{await browser.close()}})().catch(e=>{console.error(e);process.exit(1)});
