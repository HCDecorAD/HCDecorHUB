import assert from 'node:assert/strict';
import fs from 'node:fs';
import {publishPlan,verifyPublishPlan} from '../lib/publishing-plan.mjs';

const cfg=JSON.parse(fs.readFileSync('config/social-publishing.json','utf8'));
const manifest=JSON.parse(fs.readFileSync('config/hcdecor-v9-package-manifest.json','utf8'));
const publishing=fs.readFileSync('app/hub/publishing/page.js','utf8');
const multi=fs.readFileSync('app/hub/multi-publish/MultiPublishClient.jsx','utf8');
const route=fs.readFileSync('app/api/publishing/plan/route.js','utf8');

assert.equal(cfg.provider,'metricool');
assert.equal(cfg.brand_id,'7127887');
for(const c of ['instagram','tiktok','youtube','facebook']) assert.equal(cfg.channels[c].config_state,'configured',c);
assert.equal(cfg.channels.facebook.live_health,'not_checked');
const plan=publishPlan({items:[{id:'a',platform:'instagram'},{id:'b',platform:'tiktok'}],accounts:['instagram:zrubyshop','tiktok:quangcaohocuong'],schedule:'2026-10-10T19:00:00',timezone:'Asia/Bangkok',batch_id:'TEST'});
assert.equal(plan.status,'READY_FOR_REVIEW');
assert.equal(plan.jobs.length,4);
assert.equal(plan.publish,false);
assert.equal(plan.requires_approval,true);
assert.equal(verifyPublishPlan(plan).ok,true);
assert.equal(new Set(plan.jobs.map(x=>x.idempotency_key)).size,4);
for(const id of ['PKG-07','PKG-08']) assert.ok(['WAITING_DEP','BUILDING','LOCAL_PASS','INTEGRATED','DONE'].includes(manifest.packages.find(x=>x.id===id)?.state),id+' valid lifecycle state');
for(const token of ['SOC_SCHEDULE','SOC_PUBLISH','SOC_VERIFY','SOC_ANALYTICS']) assert.ok(publishing.includes(token),token);
assert.ok(multi.includes('/api/publishing/plan'));
assert.ok(multi.includes('NO AUTO-PUBLISH'));
assert.ok(route.includes('production_write:false'));
console.log('HCDECOR_V9_PUBLISH_PREBUILD_PASS metricool_topology=4 plan_jobs=4 idempotency=1 approval=1 no_auto_publish=1');
