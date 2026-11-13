// NODE_PATH=/tmp/portfolio-qa/node_modules BROWSER_EXECUTABLE=/path/to/chromium node tests/github-scene.cjs
// Requires a local PHP server on :8765. GitHub responses are fixtures, never production stats.
const { chromium } = require('playwright');
const assert = require('node:assert/strict');
const path = require('node:path');
const fs = require('node:fs');
const vm = require('node:vm');
const sceneSource = fs.readFileSync('github-scene.js', 'utf8');
const sandbox = { window: {}, Intl, Map, Date }; vm.runInNewContext(sceneSource, sandbox);
const normalized = sandbox.window.PortfolioGithubScene.normalizeDays([
 {date:'2026-11-02',count:7}, {date:'2026-11-01',count:2}, {date:'2026-11-02',count:9},
 {date:'2026-02-30',count:3}, {date:'2026-11-03',count:-1}, {date:'invalid',count:3}, {date:'2020-01-01',count:1}
]);
assert.equal(JSON.stringify(normalized.map(d=>[d.date,d.count])),JSON.stringify([['2026-11-01',2],['2026-11-02',9]]));
const graph=Array.from({length:364},(_,i)=>({date:new Date(Date.UTC(2025,10,12)+i*86400000).toISOString().slice(0,10),count:i%11===0?0:(i*7%23)+1}));
const fixture={username:'RelV-Dev',totalRepos:48,totalCommits:1248,contributionsCurrentYear:1842,topLanguages:[{name:'JavaScript',percentage:54},{name:'PHP',percentage:28},{name:'CSS',percentage:18}],contributionGraph:graph};
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:process.env.BROWSER_EXECUTABLE||undefined,args:['--no-sandbox','--enable-unsafe-swiftshader']});
 try {
 const page=await browser.newPage({viewport:{width:1440,height:1050}});
 const errors=[];page.on('pageerror',e=>errors.push(e.message));
 // Keep the scene integration independent of unrelated fonts, images and UI CDNs.
 await page.route('https://**/*',r=>r.abort());
 await page.route('**/three@0.128.0/**',route=>route.fulfill({path:path.join(path.dirname(require.resolve('three/package.json')),route.request().url().split('three@0.128.0/')[1]),contentType:'application/javascript'}));
 await page.route('https://github-stats-api-tau.vercel.app/**',async r=>{await new Promise(resolve=>setTimeout(resolve,2500));await r.fulfill({json:fixture});});
 await page.route('**/rest/v1/**',r=>r.fulfill({json:[]}));
 // Instrument draws to verify that hidden/paused/reduced-motion scenes stop work.
 await page.addInitScript(()=>{
   window.ghDraws=0;
   for(const type of [window.WebGLRenderingContext,window.WebGL2RenderingContext]) if(type) for(const method of ['drawElements','drawArrays','drawElementsInstanced']) {
     const original=type.prototype[method];if(original)type.prototype[method]=function(...args){window.ghDraws++;return original.apply(this,args);};
   }
 });
 await page.goto('http://127.0.0.1:8765/api/index.php',{waitUntil:'domcontentloaded',timeout:60000});
 await page.locator('#github').scrollIntoViewIfNeeded();
 await page.locator('#github-3d-canvas[data-ready="true"]').waitFor({timeout:30000});
 await page.getByRole('button',{name:'Profil',exact:true}).waitFor();
 assert.equal(await page.locator('#gh-day-slider').isDisabled(),true,'Profile universe renders before delayed GitHub data');
 await page.waitForFunction(()=>document.querySelector('#gh-day-slider').max==='363');
 await page.waitForTimeout(2200);
 assert.equal(await page.locator('#gh-day-slider').getAttribute('max'),'363');
 await page.getByRole('button',{name:'Profil',exact:true}).click();
 assert.match(await page.locator('.gh-destination-detail').innerText(),/Saya Farel/);
 await page.getByRole('button',{name:'Tutup informasi planet'}).click();
 await page.locator('#gh-motion').click();
 assert.equal(await page.locator('#gh-motion').getAttribute('aria-pressed'),'true');
 await page.waitForTimeout(200);
 const draws=await page.evaluate(()=>window.ghDraws);await page.waitForTimeout(250);assert.equal(await page.evaluate(()=>window.ghDraws),draws,'Paused scene stops drawing');
 await page.locator('#gh-day-slider').focus();await page.keyboard.press('Home');
 assert.match(await page.locator('#gh-day-date').innerText(),/12 November 2025/);
 assert.equal(await page.locator('#gh-day-count').innerText(),'0 kontribusi');
 await page.keyboard.press('ArrowRight');assert.equal(await page.locator('#gh-day-count').innerText(),'8 kontribusi');
 await page.locator('[data-gh-view="top"]').click();assert.equal(await page.locator('[data-gh-view="top"]').getAttribute('aria-pressed'),'true');
 await page.locator('#gh-reset').click();assert.equal(await page.locator('[data-gh-view="orbit"]').getAttribute('aria-pressed'),'true');
 await page.locator('#gh-greet').click();await page.locator('.gh-greeting').waitFor({state:'visible'});
 await page.locator('.github-card-container').screenshot({path:'/tmp/github-orbit-desktop.png'});
 await page.locator('#gh-motion').click();await page.locator('#home').scrollIntoViewIfNeeded();await page.waitForTimeout(200);
 const offscreen=await page.evaluate(()=>window.ghDraws);await page.waitForTimeout(250);assert.equal(await page.evaluate(()=>window.ghDraws),offscreen,'Offscreen scene stops drawing');
 await page.locator('#github').scrollIntoViewIfNeeded();await page.emulateMedia({reducedMotion:'reduce'});await page.waitForTimeout(150);
 const quiet=await page.evaluate(()=>window.ghDraws);await page.waitForTimeout(250);assert.equal(await page.evaluate(()=>window.ghDraws),quiet,'Reduced motion stops animation');
 await page.locator('[data-gh-view="top"]').click();await page.waitForFunction(before=>window.ghDraws>before,quiet); // Render occurs on the next animation frame.
 await page.setViewportSize({width:390,height:844});await page.locator('#github-3d-canvas').scrollIntoViewIfNeeded();await page.locator('#gh-reset').click();
 assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1),'No mobile overflow');
 assert.equal(await page.locator('#github-3d-canvas canvas').evaluate(el=>getComputedStyle(el).touchAction),'pan-y');
 await page.locator('.github-card-container').screenshot({path:'/tmp/github-orbit-mobile.png'});
 assert.deepEqual(errors,[]);
 // Returning to the section does not create another renderer.
 assert.equal(await page.locator('#github-3d-canvas canvas').count(),1);
 // Context loss leaves keyboard-readable data usable and restores rendering.
 await page.locator('#github-3d-canvas canvas').evaluate(c=>c.dispatchEvent(new Event('webglcontextlost',{cancelable:true})));
 await page.locator('#gh-day-slider').focus();await page.keyboard.press('End');assert.match(await page.locator('#gh-day-date').innerText(),/10 November 2026/);
 await page.locator('#github-3d-canvas canvas').evaluate(c=>c.dispatchEvent(new Event('webglcontextrestored')));
 // Explicit low-motion load must show the completed city on the first draw.
 await page.reload({waitUntil:'domcontentloaded'});await page.locator('#github').scrollIntoViewIfNeeded();
 await page.locator('#github-3d-canvas button').click();await page.locator('#github-3d-canvas[data-ready="true"]').waitFor();
 assert.equal(await page.locator('#gh-motion').getAttribute('aria-pressed'),'true');
 assert.deepEqual(errors,[]);
 await page.evaluate(()=>sessionStorage.clear());
 await page.route('https://github-stats-api-tau.vercel.app/**',r=>r.fulfill({status:503,json:{error:'Unavailable'}}));
 await page.reload({waitUntil:'domcontentloaded'});await page.locator('#github').scrollIntoViewIfNeeded();
 await page.locator('#github-3d-canvas button').click();await page.locator('#github-3d-canvas[data-ready="true"]').waitFor();
 await page.locator('#gh-data-retry').waitFor({state:'visible'});
 await page.getByRole('button',{name:'Profil',exact:true}).click();
 assert.match(await page.locator('.gh-destination-detail').innerText(),/Saya Farel/);
 await page.route('https://github-stats-api-tau.vercel.app/**',r=>r.fulfill({json:fixture}));
 await page.locator('#gh-data-retry').click();await page.waitForFunction(()=>document.querySelector('#gh-day-slider').max==='363');
 assert.deepEqual(errors,[]);
 console.log('PASS scene before data, failed API recovery, profile planets, calendar normalization, 364-day scene, date keyboard controls, camera presets, pause/offscreen/reduced motion, mobile layout, context recovery and renderer reuse');
 } finally { await browser.close(); }
})().catch(e=>{console.error(e);process.exitCode=1;});
