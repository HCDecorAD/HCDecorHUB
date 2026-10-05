import assert from 'node:assert/strict';
import fs from 'node:fs';
import {publishPlan,verifyPublishPlan} from '../lib/publishing-plan.mjs';

const m=JSON.parse(fs.readFileSync('config/hcdecor-v9-package-manifest.json','utf8'));
const p7=m.packages.find(x=>x.id==='PKG-07');
const p8=m.packages.find(x=>x.id==='PKG-08');
assert.equal(p7?.state,'DONE');
assert.equal(p8?.state,'DONE');
assert.equal(p7.evidence?.provider,'metricool');
assert.equal(p7.evidence?.provider_write_mode,'DRAFT_NON_PUBLIC');
assert.equal(p7.evidence?.provider_write_verified_in_queue,true);
assert.equal(p7.evidence?.real_publish_pass,'PASS_PROVIDER_DRAFT_WRITE_NO_PUBLIC');
assert.equal(p7.evidence?.public_publish_performed,false);
assert.equal(p8.evidence?.batch_publish_pass,'PASS_2_PROVIDER_DRAFT_WRITES_NO_PUBLIC');
assert.equal(p8.evidence?.queue_verified,'2/2');
assert.equal(p8.evidence?.public_publish_performed,false);
assert.equal(new Set(p8.evidence?.provider_write_ids||[]).size,2);

const plan=publishPlan({
 items:[{id:'a',platform:'facebook'},{id:'b',platform:'instagram'}],
 accounts:['facebook:223079764519203','instagram:zrubyshop'],
 schedule:'2026-10-10T19:00:00',
 timezone:'Asia/Bangkok',
 batch_id:'V9-ACTIVATION'
});
assert.equal(plan.publish,false);
assert.equal(plan.requires_approval,true);
assert.equal(verifyPublishPlan(plan).ok,true);
assert.equal(new Set(plan.jobs.map(x=>x.idempotency_key)).size,4);
console.log('HCDECOR_V9_PUBLISHING_ACTIVATION_PASS metricool_real_draft_write=1 batch_draft_write=2 queue_verify=1 public_publish=0');
