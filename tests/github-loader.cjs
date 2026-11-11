const fs=require('node:fs'),vm=require('node:vm'),assert=require('node:assert/strict');
const source=fs.readFileSync('api/index.php','utf8');
const loader=source.slice(source.indexOf('    const githubScriptLoads'),source.indexOf('    const githubContainer='));
const scripts=[];
const context=vm.createContext({Map,Promise,Error,setTimeout,clearTimeout,document:{createElement:()=>({remove(){}}),head:{appendChild:s=>scripts.push(s)}}});
vm.runInContext(loader,context);
(async()=>{
 const ready=vm.runInContext('loadGithubDependencies()',context);
 assert.equal(ready,vm.runInContext('loadGithubDependencies()',context),'Concurrent callers share one load');
 assert.equal(scripts.length,1);assert.ok(scripts[0].src.endsWith('build/three.min.js'));
 scripts[0].onload();await new Promise(setImmediate);
 assert.equal(scripts.length,2);assert.ok(scripts[1].src.endsWith('EffectComposer.js'));
 scripts[1].onload();await new Promise(setImmediate);
 assert.equal(scripts.length,8,'Six independent extensions start together');
 scripts.slice(2).forEach(script=>script.onload());await ready;
 await vm.runInContext('loadGithubDependencies()',context);assert.equal(scripts.length,8,'No duplicate downloads');
 console.log('PASS dependency order, parallel extensions, shared in-flight loads and reuse');
})().catch(error=>{console.error(error);process.exitCode=1;});
