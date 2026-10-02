import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import {DurableMissionQueue} from '../lib/governor/durable-queue.mjs';
import {DurableDoneSupervisor} from '../lib/governor/durable-done-supervisor.mjs';

const dir=fs.mkdtempSync(path.join(os.tmpdir(),'hc-transwarp-'));
const file=path.join(dir,'queue.json');
let now=Date.parse('2026-10-02T16:30:00Z');
const clock=()=>now;
const q=new DurableMissionQueue(file,{leaseMs:1000,clock});
q.upsert({
  mission_id:'transwarp-acceptance',
  state:'READY',
  priority:100,
  required_capabilities:['transwarp'],
  checkpoint:{stage:'route-ready',completed:['preflight']}
});
const s=new DurableDoneSupervisor(q,{workerId:'transwarp-worker',capabilities:['transwarp'],maxAttempts:3});
let executionCount=0;

const executor=async mission=>{
  executionCount++;
  if(executionCount===1){
    const e=new Error('INJECTED_TRANSWARP_RECOVERABLE_FAILURE');
    e.recoverable=true;
    e.checkpoint={stage:'route-ready',completed:['preflight'],failure_injected:true};
    throw e;
  }
  assert.equal(mission.evidence?.checkpoint?.failure_injected,true);
  return {
    ok:true,
    checkpoint:{stage:'final-gate',completed:['preflight','failure-recovery','resume','final-gate']},
    evidence:{
      transport:'durable-supervisor',
      recovered_without_owner_nudge:true,
      resumed_from_checkpoint:true,
      final_gate:'PASS'
    }
  };
};

const first=await s.tick(executor);
assert.equal(first.state,'RETRY_READY');
assert.equal(q.get('transwarp-acceptance').state,'READY');

const restartedQueue=new DurableMissionQueue(file,{leaseMs:1000,clock});
const restartedSupervisor=new DurableDoneSupervisor(restartedQueue,{workerId:'transwarp-worker-2',capabilities:['transwarp'],maxAttempts:3});
const second=await restartedSupervisor.tick(executor);
assert.equal(second.state,'DONE');

const done=restartedQueue.get('transwarp-acceptance');
assert.equal(done.state,'DONE');
assert.equal(done.attempts,2);
assert.equal(done.evidence.result.evidence.recovered_without_owner_nudge,true);
assert.equal(done.evidence.result.evidence.resumed_from_checkpoint,true);
assert.equal(done.evidence.result.evidence.final_gate,'PASS');
assert.deepEqual(done.evidence.result.checkpoint.completed,['preflight','failure-recovery','resume','final-gate']);

console.log('TRANSWARP_ACCEPTANCE_PASS injected_failure=1 attempts=2 restart=1 owner_nudge=0 final_gate=PASS');
fs.rmSync(dir,{recursive:true,force:true});
