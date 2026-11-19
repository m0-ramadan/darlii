/* Generate coverage against a representative homepage after CSS changes.
 * Build candidates first: node scripts/build-homepage-assets.cjs --coverage-input
 * Requires puppeteer-core and an installed Chrome. Optional REPLAY_HTML supports
 * local, controlled testing when the production WAF rejects automated browsers.
 */
const fs=require('fs'),crypto=require('crypto');
const puppeteer=require(process.env.PUPPETEER_MODULE||'puppeteer-core');
const target=process.argv[2]||'http://127.0.0.1:8766/';
const input=JSON.parse(fs.readFileSync('resources/performance/selector-candidates.json','utf8'));
const candidates=input.rules;
for(const [file,hash] of Object.entries(input.sources)){if(crypto.createHash('sha256').update(fs.readFileSync('public'+file)).digest('hex')!==hash)throw Error('Regenerate coverage candidates after changing '+file);}
(async()=>{const browser=await puppeteer.launch({executablePath:process.env.CHROME_PATH||'/usr/bin/google-chrome',headless:true,args:['--no-sandbox','--disable-dev-shm-usage']});const results={};
for(const width of [360,390,768,1440]){
 const page=await browser.newPage();await page.setViewport({width,height:1000,isMobile:width<992});
 if(process.env.REPLAY_HTML){await page.setRequestInterception(true);page.on('request',r=>{if(r.isNavigationRequest()&&r.url()===target)r.respond({status:200,contentType:'text/html',body:fs.readFileSync(process.env.REPLAY_HTML,'utf8').replaceAll('https://darlii.com',new URL(target).origin)});else r.continue();});}
 await page.coverage.startCSSCoverage();await page.goto(target,{waitUntil:'networkidle2',timeout:45000});
 const visible=await page.evaluate(rules=>rules.filter(rule=>{
  if(/modal|mobile-menu|darli-auth|darli-notif|advanced-panel|dropdown|\.fade/i.test(rule.selector))return true;
  try{if(rule.hiding&&document.querySelector(rule.selector.replace(/::[a-z-]+(?:\([^)]*\))?/gi,'')))return true;return [...document.querySelectorAll(rule.selector.replace(/::[a-z-]+(?:\([^)]*\))?/gi,''))].some(el=>{const r=el.getBoundingClientRect();return r.width>0&&r.height>0&&r.bottom>=0&&r.top<=innerHeight+100;});}catch{return false;}
 }),candidates);
 const used=await page.coverage.stopCSSCoverage();
 for(const e of used){const file=new URL(e.url).pathname;if(!file.endsWith('.css'))continue;
  if(!results[file])results[file]={sha256:crypto.createHash('sha256').update(e.text).digest('hex'),ranges:[]};
  const ranges=visible.filter(v=>v.file===file&&(v.hiding||/modal|mobile-menu|darli-auth|darli-notif|advanced-panel|dropdown|\.fade/i.test(v.selector)||e.ranges.some(r=>r.start<v.end&&r.end>v.start)));
  results[file].ranges.push(...ranges.map(r=>({start:r.start,end:r.end})));
 }
 await page.close();
}
fs.writeFileSync('resources/performance/homepage-coverage.json',JSON.stringify(results));await browser.close();console.log('Covered stylesheets:',Object.keys(results).length);
})().catch(e=>{console.error(e);process.exit(1)});
