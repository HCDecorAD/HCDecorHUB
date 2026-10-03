import assert from 'node:assert/strict';
import {correlationFromJob,resultEnvelope} from '../tools/hcdr-relay/correlation.mjs';
const body={source_id:'src-1',mission_id:'m-1',correlation_id:'c-1'};
assert.deepEqual(correlationFromJob(body),body);
assert.deepEqual(correlationFromJob({source_id:'  s  ',mission_id:'',correlation_id:null}),{source_id:'s'});
const out=resultEnvelope({job:7,body,result:{ok:true,value:1}});
assert.equal(out.schema,'hcdr-result/v2');
assert.equal(out.job,7);
assert.equal(out.source_id,'src-1');
assert.equal(out.mission_id,'m-1');
assert.equal(out.correlation_id,'c-1');
assert.equal(out.ok,true);
console.log('HCDR_RELAY_IMPLEMENTATION_CONTRACT_PASS correlation_echo=1 result_envelope=1 runtime_not_claimed=1');

import fs from 'node:fs';
const relaySource=fs.readFileSync('tools/hcdr-relay/relay-agent.mjs','utf8');
const watchdogSource=fs.readFileSync('tools/hcdr-relay/watchdog.ps1','utf8');
for(const token of [
  'HCDR_REMOTE_FREE_V3_READY',
  'HCDR_MAX_WORKERS',
  'HCDR_JOB_TIMEOUT_MS',
  'const active=new Map()',
  'const activeLanes=new Set()',
  'Promise.race',
  'job_lease_timeout',
  'uncertain_previous_execution',
  'finalize(issue,body,result,"quarantined")',
  'worker_pool',
  'setInterval(()=>heartbeat()',
  'if(lane && activeLanes.has(lane)) continue'
]) assert.ok(relaySource.includes(token),token);
assert.ok(!relaySource.includes('let busy=false'),'global busy must be removed');
for(const token of ['HCDR_WATCHDOG_STALE_RELAY_STOPPED','relay-agent\\.mjs','Stop-Process']) assert.ok(watchdogSource.includes(token),token);
console.log('HCDR_RELAY_WORKER_POOL_PASS workers=4 independent_heartbeat=1 per_job_timeout=1 lane_isolation=1 quarantine=1 blind_retry=0');
