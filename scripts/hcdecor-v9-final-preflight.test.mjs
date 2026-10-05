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
assert.equal(m.packages.find(x=>x.id==='PKG-15')?.state,'LOCAL_PASS');
assert.equal(m.packages.find(x=>x.id==='PKG-16')?.state,'WAITING_DEP');
const expected={
 WIN_HOME:'app/hub/page.js',WIN_TREND:'app/hub/trend/page.js',WIN_SOCIAL:'app/hub/social/page.js',
 WIN_PROJECTS:'app/hub/projects/page.js',WIN_IMASTER:'app/hub/agents/page.js',WIN_BUSINESS:'app/hub/business/page.js',
 WIN_SYSTEM:'app/hub/system/page.js',WIN_CREATE_POST:'app/hub/create-post/page.js',WIN_MULTI_PUBLISH:'app/hub/multi-publish/page.js',
 WIN_AI_CONNECTIONS:'app/hub/ai/page.js',WIN_UI_DESIGNER:'app/hub/ui-designer/page.js'
};
for(const [id,p] of Object.entries(expected)) assert.ok(fs.existsSync(p),id+' '+p);
console.log('HCDECOR_V9_FINAL_PREFLIGHT_PASS integration_ready=1 release_frozen=0 no_fake_green=1');
