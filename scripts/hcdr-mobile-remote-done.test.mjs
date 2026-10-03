import fs from 'node:fs';
import assert from 'node:assert/strict';

const bat=fs.readFileSync('hcdr-mobile-remote-done.bat','utf8');
const ps=fs.readFileSync('scripts/done/hcdr-mobile-remote-done.ps1','utf8');

assert.ok(bat.includes('RunAs'));
assert.ok(bat.includes('hcdr-mobile-remote-done.ps1'));
for(const token of [
  'git pull --ff-only',
  'test:hcdr-always-on',
  'test:hcdr-relay-contract',
  'mobile-hc-done-cloud.test.mjs',
  'final-freeze-v2.ps1',
  'hcdr-install-always-on.bat',
  'watchdog.ps1',
  'hc-done-02-hcdr-live.bat',
  "state='CLOUD_DONE_LOCAL_WAITING'",
  "state='WAITING_RESOURCE'",
  'AUTO_RESUME_WHEN_RESOURCE_AVAILABLE',
  'HCDR_MOBILE_REMOTE_YIELD',
  'HCDR_MOBILE_REMOTE_DONE_PASS'
]) assert.ok(ps.includes(token),token);
assert.ok(ps.includes("cloud_lane=[ordered]"));
assert.ok(ps.includes("local_lane=[ordered]"));
assert.ok(ps.includes("state='NOT_DONE'"));
assert.ok(ps.includes("state='DONE'"));
console.log('HCDR_MOBILE_REMOTE_V2_PASS cloud_lane=1 local_lane=1 yield=1 auto_resume=1 no_fake_green=1');
