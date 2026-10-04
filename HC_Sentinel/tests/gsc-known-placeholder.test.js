import test from "node:test";import assert from "node:assert/strict";import {mkdtemp,rm} from "node:fs/promises";import {tmpdir} from "node:os";import {join} from "node:path";
import {createLiveController} from "../runtime/live-controller.js";import {ProjectRegistry} from "../core/project-registry.js";
test("GSC known placeholder finding is suppressed and mission can PASS",async()=>{const d=await mkdtemp(join(tmpdir(),"sentinel-suppress-"));try{
 const p=new ProjectRegistry();p.register({id:"gsc",name:"GSC"});const ev=[],fs=[];
 const c=createLiveController({
  projects:p,
  targets:[{id:"gsc-public",projectId:"gsc",url:"https://example.test",suppressFindings:["BROKEN_MEDIA_HINT"],viewports:{mobile:{width:390,height:844}}}],
  observer:{capture:async()=>({dom:"<img src=''>",failedRequests:[],consoleMessages:[],screenshot:Buffer.from("x"),title:"GSC"})},
  inspectSnapshot:()=>[{kind:"BROKEN_MEDIA_HINT",severity:"medium",count:3}],
  evidence:{add:async x=>ev.push(x)},
  findings:{upsert:async x=>fs.push(x)},
  logs:{append:async()=>{}},
  evidenceDir:d
 });
 const r=await c.run("Sentinel kiem tra GSC mobile");
 assert.equal(r.status,"DONE");
 assert.equal(r.findings.length,0);
 assert.equal(r.suppressedFindings.length,1);
 assert.equal(r.suppressedFindings[0].count,3);
 assert.equal(fs.length,0);
 assert.equal(ev[0].suppressedFindings[0].kind,"BROKEN_MEDIA_HINT");
}finally{await rm(d,{recursive:true,force:true});}});
