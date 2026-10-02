import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import {DurableMissionQueue} from '../lib/governor/durable-queue.mjs';
import {DurableDoneSupervisor} from '../lib/governor/durable-done-supervisor.mjs';
import {scheduleResourceAware} from '../lib/governor/resource-scheduler.mjs';

const readJson=p=>JSON.parse(fs.readFileSync(p,'utf8'));
let pass=0;
const ok=(name,fn)=>{fn();pass++;console.log('PASS',name)};

ok('security remains default deny and production mutation approval-gated',()=>{
  const id=readJson('config/identity-access.json');
  assert.equal(id.rules.default,'deny');
  assert.equal(id.rules.production_mutation,'explicit-approval');
  assert.equal(id.rules.service_credentials,'server-side-only');
});

ok('release contract requires verification audit and rollback',()=>{
  const rt=readJson('config/runtime-contract.json');
  assert.equal(rt.release.verification,'required');
  assert.equal(rt.release.audit,'required');
  assert.equal(rt.release.rollback,'required');
  assert.equal(rt.tenant_isolation.fail_closed,true);
});

ok('retired Vercel is absent from active deployment registry',()=>{
  const site=fs.readFileSync('config/site-registry.json','utf8').toLowerCase();
  const ws=fs.readFileSync('config/workspaces.json','utf8').toLowerCase();
  assert.equal(site.includes('vercel'),false);
  assert.equal(ws.includes('vercel'),false);
});

ok('quality gate contains durable recovery portfolio and release evidence',()=>{
  const q=fs.readFileSync('.github/workflows/quality-gate.yml','utf8');
  for(const marker of [
    'Governor durable queue v0',
    'Durable DONE supervisor v0',
    'TransWarp durable recovery acceptance',
    'HC Group portfolio pilot',
    'Production build',
    'Master Agent local E2E',
    'Cloudflare build'
  ]) assert.ok(q.includes(marker),marker);
});

await (async()=>{
  const dir=fs.mkdtempSync(path.join(os.tmpdir(),'hc-moon-'));
  const file=path.join(dir,'queue.json');
  let now=Date.parse('2026-10-02T16:40:00Z');
  const clock=()=>now;
  const q=new DurableMissionQueue(file,{leaseMs:1000,clock});
  q.upsert({mission_id:'moon-recovery',state:'READY',required_capabilities:['moon'],checkpoint:{stage:'start'}});
  const s=new DurableDoneSupervisor(q,{capabilities:['moon'],maxAttempts:3});
  let calls=0;
  const ex=async mission=>{
    calls++;
    if(calls===1){const e=new Error('moon-injected');e.recoverable=true;e.checkpoint={stage:'resume-point',injected:true};throw e;}
    assert.equal(mission.checkpoint.injected,true);
    return {ok:true,checkpoint:{stage:'done'},evidence:{correlation_id:'moon-001',recovered:true}};
  };
  const a=await s.tick(ex); assert.equal(a.state,'RETRY_READY');
  const q2=new DurableMissionQueue(file,{leaseMs:1000,clock});
  const s2=new DurableDoneSupervisor(q2,{capabilities:['moon'],maxAttempts:3});
  const b=await s2.tick(ex); assert.equal(b.state,'DONE');
  assert.equal(q2.get('moon-recovery').evidence.result.evidence.correlation_id,'moon-001');
  fs.rmSync(dir,{recursive:true,force:true});
  pass++; console.log('PASS restart recovery preserves checkpoint and correlation evidence');
})();

ok('blocked lane does not stall independent ready work',()=>{
  const out=scheduleResourceAware({
    tasks:[
      {task_id:'blocked',state:'BLOCKED',required_capabilities:['x']},
      {task_id:'ready-a',state:'READY',required_capabilities:['a'],resources:{cpu:1}},
      {task_id:'ready-b',state:'READY',required_capabilities:['b'],resources:{cpu:1}}
    ],
    workers:[
      {worker_id:'wa',capabilities:['a']},
      {worker_id:'wb',capabilities:['b']}
    ],
    resources:{cpu:2}
  });
  assert.equal(out.dispatches.length,2);
  assert.ok(out.untouched.includes('blocked'));
});

console.log(`HC_GROUP_MOON_GATE_PASS ${pass}/6`);
