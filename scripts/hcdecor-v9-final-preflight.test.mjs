import assert from 'node:assert/strict';
import fs from 'node:fs';
import {v9Readiness,finalGate} from '../lib/v9-final-preflight.mjs';

const m=JSON.parse(fs.readFileSync('config/hcdecor-v9-package-manifest.json','utf8'));
const r=v9Readiness();
assert.equal(r.program,'HCDECOR_HUB_V9_APP');
assert.equal(r.total,17);
assert.equal(r.done,17);
assert.equal(r.integration_ready,true);
assert.deepEqual(r.integration_blockers,[]);
assert.equal(r.release_ready,true);
assert.equal(finalGate(r).pass,true);
assert.equal(finalGate(r).status,'V9_FROZEN_DONE');
assert.equal(m.status,'DONE');
assert.deepEqual(m.lifecycle,['HCDECOR_HUB_V9','FROZEN','DONE']);
assert.equal(m.packages.find(x=>x.id==='PKG-15')?.state,'DONE');
assert.equal(m.packages.find(x=>x.id==='PKG-16')?.state,'DONE');
console.log('HCDECOR_V9_FINAL_PREFLIGHT_PASS done=17/17 final_gate=V9_FROZEN_DONE no_fake_green=1');
