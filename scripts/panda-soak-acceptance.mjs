import assert from "node:assert/strict";
import fs from "node:fs";
import os from "node:os";
import path from "node:path";
import {DurableMissionQueue} from "../lib/governor/durable-queue.mjs";
import {reconcile} from "../lib/imaster-panda-cluster.mjs";
import {authorize,repairRoute} from "../lib/panda-safe-executor.mjs";
import {dedupe,circuit,shouldRecover} from "../lib/panda-hardening.mjs";

const dir=fs.mkdtempSync(path.join(os.tmpdir(),"panda-soak-"));
const file=path.join(dir,"queue.json");
let now=Date.parse("2026-10-04T15:00:00Z");
const clock=()=>now, ledger=new Set(), cycles=[];
try{
 for(let i=0;i<48;i++){
  const id="soak-"+i;
  let q=new DurableMissionQueue(file,{leaseMs:100,clock});
  q.upsert({mission_id:id,state:"READY",required_capabilities:["panda"]});
  const c=q.claim(id,"worker-"+i,{workerCapabilities:["panda"]});
  if(i%8===3){ now+=150; q=new DurableMissionQueue(file,{leaseMs:100,clock}); assert.equal(q.get(id).state,"READY"); }
  const active=q.get(id).state==="READY"?q.claim(id,"recovery-"+i,{workerCapabilities:["panda"]}):c;
  const key="event-"+i; assert.equal(dedupe(ledger,key),true); assert.equal(dedupe(ledger,key),false);
  const rec=reconcile({mission:{repo:"HCDecorAD/HCDecorHUB",mission_id:id,state:"RUNNING",trigger_sha:"a",controller_sha:"b",desired_sha:"c"},event:{event_id:key,action:"CHECK"},actual_sha:"c"});
  assert.notEqual(rec.action,"RECONCILE_STALE_STATE");
  assert.equal(authorize({idempotency_key:key,target_sha:"c"}).allowed,true);
  assert.equal(authorize({idempotency_key:key,target_sha:"c",production_write:true}).state,"OWNER_REQUIRED");
  assert.equal(repairRoute({class:"TRANSIENT"}),"BOUNDED_RETRY");
  assert.equal(circuit({failures:3,limit:3}).action,"OWNER_REQUIRED");
  assert.equal(shouldRecover({event_seen:i%9!==4,lease_expired:false,last_seen:"RUNNING"}),i%9===4);
  q.transition(id,active.worker_id,active.fence_token,"DONE",{checkpoint:i});
  cycles.push({cycle:i,restart:i%8===3,missed_event:i%9===4,state:q.get(id).state});
  now+=25;
 }
 const result={soak:true,cycles:cycles.length,durable_restart:cycles.filter(x=>x.restart).length,missed_event_recovery:cycles.filter(x=>x.missed_event).length,duplicate_safe:true,bounded_repair:true,owner_boundary:true,all_done:cycles.every(x=>x.state==="DONE")};
 assert.equal(result.cycles,48); assert.equal(result.all_done,true); assert.ok(result.durable_restart>=6); assert.ok(result.missed_event_recovery>=5);
 console.log(JSON.stringify(result));
} finally {fs.rmSync(dir,{recursive:true,force:true});}
