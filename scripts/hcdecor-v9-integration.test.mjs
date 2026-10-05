import assert from 'node:assert/strict';
import fs from 'node:fs';
import {v9Readiness} from '../lib/v9-final-preflight.mjs';

const m=JSON.parse(fs.readFileSync('config/hcdecor-v9-package-manifest.json','utf8'));
const byId=new Map(m.packages.map(x=>[x.id,x]));
for(const id of ['PKG-02','PKG-03','PKG-04','PKG-05','PKG-06','PKG-07','PKG-08','PKG-09','PKG-10','PKG-11','PKG-12','PKG-13','PKG-14']){
  assert.equal(byId.get(id)?.state,'DONE',id+' dependency');
}
assert.equal(byId.get('PKG-15')?.state,'DONE');
assert.equal(byId.get('PKG-16')?.state,'DONE');
const expected={
 WIN_HOME:'app/hub/page.js',WIN_TREND:'app/hub/trend/page.js',WIN_SOCIAL:'app/hub/social/page.js',
 WIN_PROJECTS:'app/hub/projects/page.js',WIN_IMASTER:'app/hub/agents/page.js',WIN_BUSINESS:'app/hub/business/page.js',
 WIN_SYSTEM:'app/hub/system/page.js',WIN_CREATE_POST:'app/hub/create-post/page.js',WIN_MULTI_PUBLISH:'app/hub/multi-publish/page.js',
 WIN_AI_CONNECTIONS:'app/hub/ai/page.js',WIN_UI_DESIGNER:'app/hub/ui-designer/page.js'
};
for(const [id,p] of Object.entries(expected)) assert.ok(fs.existsSync(p),id+' '+p);
const css=fs.readFileSync('app/globals.css','utf8');
for(const token of ['.v9Shell','.v9Workspace','.v9Runtime','.isCollapsed','.isFocus']) assert.ok(css.includes(token),token);
const r=v9Readiness();
assert.equal(r.integration_ready,true);
assert.deepEqual(r.integration_blockers,[]);
console.log('HCDECOR_V9_INTEGRATION_PASS deps=13/13 windows=11 responsive_shell=1 integration_done=1');
