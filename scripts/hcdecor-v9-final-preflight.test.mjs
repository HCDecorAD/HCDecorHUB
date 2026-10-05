import assert from 'node:assert/strict';
import fs from 'node:fs';
import {v9Readiness,finalGate} from '../lib/v9-final-preflight.mjs';

const m=JSON.parse(fs.readFileSync('config/hcdecor-v9-package-manifest.json','utf8'));
const r=v9Readiness();
assert.equal(r.program,'HCDECOR_HUB_V9_APP');
assert.equal(r.total,17);
assert.equal(r.integration_ready,true);
assert.deepEqual(r.integration_blockers,[]);
assert.equal(finalGate(r).pass,false);
assert.equal(finalGate(r).status,'BLOCKED');
assert.equal(m.status,'FROZEN_CANDIDATE');
assert.equal(m.packages.find(x=>x.id==='PKG-15')?.state,'DONE');
assert.equal(m.packages.find(x=>x.id==='PKG-16')?.state,'LOCAL_PASS');
console.log('HCDECOR_V9_FINAL_PREFLIGHT_PASS integration_done=1 freeze_candidate=1 terminal_done=0 no_fake_green=1');
