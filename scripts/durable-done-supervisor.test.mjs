import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import {DurableMissionQueue} from '../lib/governor/durable-queue.mjs';
import {DurableDoneSupervisor} from '../lib/governor/durable-done-supervisor.mjs';

const dir=fs.mkdtempSync(path.join(os.tmpdir(),'hc-done-'));
const file=path.join(dir,'queue.json');
let now=Date.parse('2026-10-02T16:20:00Z');
const clock=()=>now;
let pass=0; const t=async(name,fn)=>{await fn();pass++;console.log('PASS',name)};

await t('recoverable failure retries without owner nudge',async()=>{
 const q=new DurableMissionQueue(file,{leaseMs:1000,clock});
 q.upsert({mission_id:'m1',state:'READY',required_capabilities:['code'],checkpoint:{stage:1}});
 const s=new DurableDoneSupervisor(q,{capabilities:['code'],maxAttempts:3});
 let calls=0;
 const ex=async()=>{calls++;if(calls===1){const e=new Error('injected');e.recoverable=true;e.checkpoint={stage:1};throw e;}return {checkpoint:{stage:2},ok:true}};
 const a=await s.tick(ex); assert.equal(a.state,'RETRY_READY'); assert.equal(q.get('m1').state,'READY');
 const b=await s.tick(ex); assert.equal(b.state,'DONE'); assert.equal(q.get('m1').state,'DONE'); assert.equal(calls,2);
});
await t('restart preserves retry-ready mission',async()=>{
 const q=new DurableMissionQueue(file,{leaseMs:1000,clock});
 q.upsert({mission_id:'m2',state:'READY',required_capabilities:['debug']});
 const s=new DurableDoneSupervisor(q,{capabilities:['debug'],maxAttempts:3});
 const e=async()=>{const x=new Error('recover');x.recoverable=true;throw x};
 const a=await s.tick(e);assert.equal(a.state,'RETRY_READY');
 const q2=new DurableMissionQueue(file,{leaseMs:1000,clock});
 assert.equal(q2.get('m2').state,'READY');
});
await t('owner exception terminates explicitly',async()=>{
 const q=new DurableMissionQueue(file,{leaseMs:1000,clock});
 const s=new DurableDoneSupervisor(q,{capabilities:['debug'],maxAttempts:3});
 const x=await s.tick(async()=>({owner_required:true,reason:'credential'}));
 assert.equal(x.state,'OWNER_REQUIRED');assert.ok(s.missionTerminal('m2'));
});
await t('resource wait releases mission ownership',async()=>{
 const q=new DurableMissionQueue(file,{leaseMs:1000,clock});
 q.upsert({mission_id:'m3',state:'READY',required_capabilities:['code']});
 const s=new DurableDoneSupervisor(q,{capabilities:['code']});
 const x=await s.tick(async()=>({waiting_resource:true,resource:'external-provider'}));
 assert.equal(x.state,'WAITING_RESOURCE');assert.equal(q.get('m3').worker_id,null);
});
await t('bounded retry becomes hard blocked',async()=>{
 const q=new DurableMissionQueue(file,{leaseMs:1000,clock});
 q.upsert({mission_id:'m4',state:'READY',required_capabilities:['code']});
 const s=new DurableDoneSupervisor(q,{capabilities:['code'],maxAttempts:2});
 const ex=async()=>{const e=new Error('repeat');e.recoverable=true;throw e};
 const a=await s.tick(ex);assert.equal(a.state,'RETRY_READY');
 const b=await s.tick(ex);assert.equal(b.state,'HARD_BLOCKED');assert.ok(s.missionTerminal('m4'));
});
console.log(`DURABLE_DONE_SUPERVISOR_PASS ${pass}/5`);
fs.rmSync(dir,{recursive:true,force:true});


{
  const fsMod=await import('node:fs');
  const osMod=await import('node:os');
  const pathMod=await import('node:path');
  const dir=fsMod.mkdtempSync(pathMod.join(osMod.tmpdir(),'hc-correlation-'));
  const file=pathMod.join(dir,'queue.json');
  const q=new (await import('../lib/governor/durable-queue.mjs')).DurableMissionQueue(file);
  q.upsert({mission_id:'corr-mission',state:'READY',required_capabilities:['code'],checkpoint:{stage:'start'}});
  let calls=0;
  const s=new DurableDoneSupervisor(q,{workerId:'corr-worker',capabilities:['code'],maxAttempts:3});
  const ex=async m=>{
    calls++;
    assert.equal(m.correlation_id,'corr-mission');
    if(calls===1){const e=new Error('retry');e.recoverable=true;e.checkpoint={stage:'retry'};throw e;}
    return {checkpoint:{stage:'done'},evidence:{ok:true}};
  };
  const a=await s.tick(ex);
  assert.equal(a.state,'RETRY_READY');
  assert.equal(q.get('corr-mission').evidence.correlation_id,'corr-mission');
  const q2=new (await import('../lib/governor/durable-queue.mjs')).DurableMissionQueue(file);
  const s2=new DurableDoneSupervisor(q2,{workerId:'corr-worker-2',capabilities:['code'],maxAttempts:3});
  const b=await s2.tick(ex);
  assert.equal(b.state,'DONE');
  const done=q2.get('corr-mission');
  assert.equal(done.correlation_id,'corr-mission');
  assert.equal(done.evidence.correlation_id,'corr-mission');
  fsMod.rmSync(dir,{recursive:true,force:true});
  console.log('CORRELATION_RESTART_PASS mission_id=corr-mission correlation_id=corr-mission');
}
