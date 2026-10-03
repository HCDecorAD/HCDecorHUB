import fs from 'node:fs';
import assert from 'node:assert/strict';

const c=JSON.parse(fs.readFileSync('config/hc-agent-control-acceptance.json','utf8'));
assert.equal(c.schema_version,'1.1.0');
assert.equal(c.system_id,'hc-agent-control');
assert.equal(c.verification_state,'source-contract-plus-live-transport');
assert.equal(c.runtime_done,false);
for(const k of ['mission_ownership_until_terminal','no_user_nudge_for_ordinary_progress','blocked_lane_does_not_stall_unrelated_work','heartbeat_required','checkpoint_required','evidence_required_for_done','uncertain_external_side_effects_fail_closed']) assert.equal(c.principles[k],true,k);
for(const s of ['DONE','OWNER_REQUIRED','SAFETY_STOP','HARD_BLOCKED']) assert.ok(c.terminal_states.includes(s),s);
for(const f of ['mission_id','state','checkpoint','last_verified_evidence','blocker','next_action']) assert.ok(c.required_worker_report_fields.includes(f),f);
assert.equal(c.transport.default_local_transport,'HCDR v3');
assert.equal(c.transport.status,'ACTIVE');
assert.equal(c.transport.live_verified,true);
assert.equal(c.transport.request_schema,'hcdr-relay/v1.2');
assert.equal(c.transport.result_schema,'hcdr-result/v2');
assert.equal(c.transport.worker_pool,4);
assert.match(c.acceptance_limits,/must not report HCDR as unavailable without a fresh failed health proof/i);
console.log('HC_AGENT_CONTROL_SOURCE_CONTRACT_PASS ownership=1 no_user_nudge=1 blocked_lane_isolation=1 heartbeat=1 checkpoint=1 evidence_done=1 fail_closed=1 runtime_done=0');
