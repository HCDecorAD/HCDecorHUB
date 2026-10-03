import assert from 'node:assert/strict';import {AgentControlRuntime,createAutoChatBridge} from '../lib/agent-control-runtime.mjs';
const a=new AgentControlRuntime();const chat=createAutoChatBridge(a);
const s=chat.submit({mission_id:'m1',worker_id:'w1',correlation_id:'c1',text:'continue'});
assert.equal(s.accepted,true);assert.equal(s.authority,'operator-ui-only');assert.equal(s.production_write,false);assert.equal(s.mission.state,'RUNNING');
const hb=a.heartbeat({mission_id:'m1',checkpoint:'cp1',last_verified_evidence:'gate:x',next_action:'next'});assert.equal(hb.checkpoint,'cp1');
assert.throws(()=>a.terminal({mission_id:'m1',state:'DONE'}),/DONE_REQUIRES_EVIDENCE/);
const d=a.terminal({mission_id:'m1',state:'DONE',evidence_ref:'gate:final'});assert.equal(d.state,'DONE');assert.equal(chat.status('m1').evidence,'gate:final');
console.log('HC_AGENT_CONTROL_RUNTIME_PASS mission_owner=1 heartbeat=1 evidence_done=1');
