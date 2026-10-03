import fs from 'node:fs';
import assert from 'node:assert/strict';

const c=JSON.parse(fs.readFileSync('config/hc-mat-than.json','utf8'));
const s=fs.readFileSync('tools/hc-mat-than/observer.ps1','utf8');

assert.equal(c.schema,'hc-mat-than/v1');
assert.equal(c.policy.observer_is_independent,true);
assert.equal(c.policy.worker_failure_does_not_stop_observer,true);
for(const token of [
  'GetForegroundWindow',
  'CopyFromScreen',
  'hcdr-relay-heartbeat.json',
  'state.json',
  'latest.png',
  "classification='BUSY'",
  'HC_MAT_THAN'
]) assert.ok(s.includes(token),token);

console.log('HC_MAT_THAN_CONTRACT_PASS screenshot=1 active_window=1 hcdr=1 independent=1');
