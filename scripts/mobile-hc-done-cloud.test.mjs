import assert from 'node:assert/strict';
import {spawnSync} from 'node:child_process';

const run=(env={})=>spawnSync(process.execPath,['scripts/mobile-hc-done-cloud.mjs'],{
  cwd:process.cwd(),encoding:'utf8',env:{...process.env,...env}
});

let r=run({HCDR_AVAILABLE:'offline',HC_FORCE_LOCAL_REFRESH:'0'});
assert.equal(r.status,0,r.stderr);
let s=JSON.parse(r.stdout.trim());
assert.equal(s.cloud_lane.state,'DONE');
assert.equal(s.local_lane.state,'REUSED_PASS');
assert.equal(s.state,'DONE');

r=run({HCDR_AVAILABLE:'offline',HC_FORCE_LOCAL_REFRESH:'1'});
assert.equal(r.status,0,r.stderr);
s=JSON.parse(r.stdout.trim());
assert.equal(s.cloud_lane.state,'DONE');
assert.equal(s.local_lane.state,'WAITING_RESOURCE');
assert.equal(s.state,'CLOUD_DONE_LOCAL_WAITING');
assert.equal(s.policy.blocked_local_does_not_block_cloud,true);
assert.equal(s.local_lane.resume_policy,'AUTO_RESUME_WHEN_RESOURCE_AVAILABLE');

r=run({HCDR_AVAILABLE:'online',HC_FORCE_LOCAL_REFRESH:'1'});
assert.equal(r.status,0,r.stderr);
s=JSON.parse(r.stdout.trim());
assert.equal(s.local_lane.state,'READY_FOR_LOCAL_REFRESH');
assert.equal(s.state,'DONE');

console.log('HC_MOBILE_CLOUD_ORCHESTRATOR_PASS offline_reuse=1 offline_yield=1 online_resume=1');
