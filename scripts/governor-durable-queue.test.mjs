import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import {DurableMissionQueue} from '../lib/governor/durable-queue.mjs';

const dir=fs.mkdtempSync(path.join(os.tmpdir(),'hc-gov-'));
const file=path.join(dir,'queue.json');
let now=Date.parse('2026-10-02T16:10:00Z');
const clock=()=>now;
let pass=0; const t=(name,fn)=>{fn();pass++;console.log('PASS',name)};

t('persists missions across restart',()=>{
  const q=new DurableMissionQueue(file,{leaseMs:1000,clock});
  q.upsert({mission_id:'a',state:'READY',priority:1,required_capabilities:['code']});
  const q2=new DurableMissionQueue(file,{leaseMs:1000,clock});
  assert.equal(q2.get('a').state,'READY');
});
t('dependency-aware ready selection',()=>{
  const q=new DurableMissionQueue(file,{leaseMs:1000,clock});
  q.upsert({mission_id:'b',state:'READY',depends_on:['a'],required_capabilities:['code']});
  assert.deepEqual(q.ready({workerCapabilities:['code']}).map(x=>x.mission_id),['a']);
});
t('claim creates monotonic fence token',()=>{
  const q=new DurableMissionQueue(file,{leaseMs:1000,clock});
  const c=q.claim('a','w1',{workerCapabilities:['code']});
  assert.equal(c.state,'CLAIMED'); assert.ok(Number.isInteger(c.fence_token));
});
t('expired lease recovers after restart',()=>{
  now+=2000;
  const q=new DurableMissionQueue(file,{leaseMs:1000,clock});
  const a=q.get('a'); assert.equal(a.state,'READY'); assert.equal(a.worker_id,null);
});
t('stale worker cannot mutate after reassignment',()=>{
  const q=new DurableMissionQueue(file,{leaseMs:1000,clock});
  const c=q.claim('a','w2',{workerCapabilities:['code']});
  assert.throws(()=>q.heartbeat('a','w1',c.fence_token-1),/STALE_FENCE/);
  q.transition('a','w2',c.fence_token,'DONE',{gate:'unit'});
  assert.equal(q.get('a').state,'DONE');
});
t('completed dependency unlocks next mission after restart',()=>{
  const q=new DurableMissionQueue(file,{leaseMs:1000,clock});
  const q2=new DurableMissionQueue(file,{leaseMs:1000,clock});
  assert.deepEqual(q2.ready({workerCapabilities:['code']}).map(x=>x.mission_id),['b']);
});
t('waiting mission releases ownership without becoming failed',()=>{
  const q=new DurableMissionQueue(file,{leaseMs:1000,clock});
  const c=q.claim('b','w3',{workerCapabilities:['code']});
  q.transition('b','w3',c.fence_token,'WAITING_RESOURCE',{resource:'vercel'});
  const b=q.get('b'); assert.equal(b.state,'WAITING_RESOURCE'); assert.equal(b.worker_id,null);
});
console.log(`GOVERNOR_DURABLE_QUEUE_PASS ${pass}/7`);
fs.rmSync(dir,{recursive:true,force:true});
