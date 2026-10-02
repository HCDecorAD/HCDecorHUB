import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import {DurableWorkerHeartbeatStore} from '../lib/governor/durable-worker-heartbeats.mjs';

let pass=0;const t=(n,fn)=>{fn();pass++;console.log('PASS',n)};
const dir=fs.mkdtempSync(path.join(os.tmpdir(),'hc-heartbeat-'));const file=path.join(dir,'heartbeats.json');let now=1000;

t('heartbeat persists mission and correlation',()=>{let s=new DurableWorkerHeartbeatStore(file,{clock:()=>now,staleAfterMs:1000});const x=s.beat({worker_id:'w1',mission_id:'m1',state:'RUNNING',checkpoint:{stage:'s1'}});assert.equal(x.correlation_id,'m1');s=new DurableWorkerHeartbeatStore(file,{clock:()=>now,staleAfterMs:1000});assert.equal(s.get('w1').mission_id,'m1');assert.deepEqual(s.get('w1').checkpoint,{stage:'s1'});});
t('fresh worker is healthy',()=>{const s=new DurableWorkerHeartbeatStore(file,{clock:()=>now,staleAfterMs:1000});assert.equal(s.get('w1').health,'HEALTHY');});
t('stale worker becomes suspect without deleting checkpoint',()=>{now+=1500;const s=new DurableWorkerHeartbeatStore(file,{clock:()=>now,staleAfterMs:1000});const x=s.get('w1');assert.equal(x.health,'SUSPECT');assert.equal(x.stale,true);assert.deepEqual(x.checkpoint,{stage:'s1'});});
t('new heartbeat recovers suspect worker',()=>{const s=new DurableWorkerHeartbeatStore(file,{clock:()=>now,staleAfterMs:1000});const x=s.beat({worker_id:'w1',mission_id:'m1',correlation_id:'c1',state:'VERIFYING'});assert.equal(x.health,'HEALTHY');assert.equal(x.correlation_id,'c1');});
t('multiple workers remain independently visible',()=>{const s=new DurableWorkerHeartbeatStore(file,{clock:()=>now,staleAfterMs:1000});s.beat({worker_id:'w2',mission_id:'m2'});assert.deepEqual(s.list().map(x=>x.worker_id),['w1','w2']);});
console.log(`DURABLE_WORKER_HEARTBEAT_PASS ${pass}/5 durable=1 suspect=1 checkpoint_preserved=1`);
