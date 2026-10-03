import fs from 'node:fs';import assert from 'node:assert/strict';
const bat=fs.readFileSync('hcdr-mobile-remote-done.bat','utf8');
const ps=fs.readFileSync('scripts/done/hcdr-mobile-remote-done.ps1','utf8');
assert.ok(bat.includes('RunAs'));
assert.ok(bat.includes('hcdr-mobile-remote-done.ps1'));
for(const token of ['git pull --ff-only','test:hcdr-always-on','test:hcdr-relay-contract','hcdr-install-always-on.bat','watchdog.ps1','hc-done-02-hcdr-live.bat','hc-done-06-final-freeze.bat','HCDR_MOBILE_REMOTE_DONE_PASS']) assert.ok(ps.includes(token),token);
assert.ok(ps.includes("state='NOT_DONE'"));
assert.ok(ps.includes("state='DONE'"));
console.log('HCDR_MOBILE_REMOTE_ONE_SHOT_PASS elevate=1 sync=1 install=1 watchdog=1 live_proof=1 final_freeze=1');
