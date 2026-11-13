const fs=require('node:fs'),vm=require('node:vm'),assert=require('node:assert/strict');
const source=fs.readFileSync('api/index.php','utf8');
const loader=source.slice(source.indexOf('    const githubScriptLoads'),source.indexOf('    const githubContainer='));
const scripts=[];
const context=vm.createContext({Map,Promise,Error,setTimeout,clearTimeout,document:{createElement:()=>({remove(){}}),head:{appendChild:s=>scripts.push(s)}}});
vm.runInContext(loader,context);
(async()=>{
 const ready=vm.runInContext('loadGithubDependencies()',context);
 assert.equal(ready,vm.runInContext('loadGithubDependencies()',context),'Concurrent callers share one load');
 assert.equal(scripts.length,2,'Local scene and Three.js download in parallel');
 const scene=scripts.find(s=>s.src==='/github-scene.js');
 const core=scripts.find(s=>s.src.endsWith('build/three.min.js'));
 assert.ok(scene);assert.ok(core);scene.onload();core.onload();await new Promise(setImmediate);
 assert.equal(scripts.length,3);assert.ok(scripts[2].src.endsWith('controls/OrbitControls.js'));
 scripts[2].onload();await ready;
 await vm.runInContext('loadGithubDependencies()',context);assert.equal(scripts.length,3,'No duplicate downloads');
 console.log('PASS dependency order, parallel scene loading, shared in-flight loads and reuse');
})().catch(error=>{console.error(error);process.exitCode=1;});
