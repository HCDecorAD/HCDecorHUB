import test from "node:test";import assert from "node:assert/strict";import {mkdtemp,rm} from "node:fs/promises";import {tmpdir} from "node:os";import {join} from "node:path";
import {createLiveController} from "../runtime/live-controller.js";import {ProjectRegistry} from "../core/project-registry.js";
test("live controller captures target and reaches DONE when clean",async()=>{const d=await mkdtemp(join(tmpdir(),"sentinel-live-"));try{
 const p=new ProjectRegistry();p.register({id:"amo",name:"AMO"});
 const ev=[],fs=[],ls=[];
 const c=createLiveController({projects:p,targets:[{id:"amo-public",projectId:"amo",url:"https://example.test",viewports:{mobile:{width:390,height:844}}}],observer:{capture:async()=>({dom:"<html></html>",failedRequests:[],consoleMessages:[],screenshot:Buffer.from("x"),title:"AMO"})},inspectSnapshot:()=>[],evidence:{add:async x=>ev.push(x)},findings:{upsert:async x=>fs.push(x)},logs:{append:async x=>ls.push(x)},evidenceDir:d});
 const r=await c.run("Sentinel kiểm tra AMO mobile");assert.equal(r.status,"DONE");assert.equal(r.viewport,"mobile");assert.equal(ev.length,1);assert.equal(fs.length,0);
}finally{await rm(d,{recursive:true,force:true});}});
test("live controller routes detected finding",async()=>{const d=await mkdtemp(join(tmpdir(),"sentinel-live-"));try{
 const p=new ProjectRegistry();p.register({id:"gsc",name:"GSC"});const fs=[];
 const c=createLiveController({projects:p,targets:[{id:"gsc-public",projectId:"gsc",url:"https://example.test",viewports:{desktop:{width:1440,height:900}}}],observer:{capture:async()=>({dom:"<img src=''>",failedRequests:[],consoleMessages:[],screenshot:Buffer.from("x"),title:"GSC"})},inspectSnapshot:()=>[{kind:"BROKEN_MEDIA_HINT",severity:"medium",count:1}],evidence:{add:async()=>{}},findings:{upsert:async x=>fs.push(x)},logs:{append:async()=>{}},evidenceDir:d});
 const r=await c.run("Sentinel kiểm tra GSC desktop");assert.equal(r.status,"ROUTED");assert.equal(fs[0].state,"ROUTED");
}finally{await rm(d,{recursive:true,force:true});}});
