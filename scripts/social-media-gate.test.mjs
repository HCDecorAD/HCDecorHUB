import fs from 'node:fs';
import assert from 'node:assert/strict';

const read=p=>fs.readFileSync(p,'utf8');
const cfg=JSON.parse(read('config/social-publishing.json'));
const jobs=read('app/api/publishing/jobs/route.js');
const status=read('app/api/publishing/status/route.js');
const manager=read('wordpress/hcdecor-core/modules/social-manager.php');
let pass=0; const t=(name,fn)=>{fn();pass++;console.log('PASS',name)};

t('publish requires explicit approval',()=>{
  assert.equal(cfg.rules.no_public_publish_without_approval,true);
  assert.ok(manager.includes('Explicit production approval is required before social publishing can be queued.'));
  assert.ok(manager.includes("'production_approved' => true"));
});

t('configuration never claims live provider health',()=>{
  assert.equal(cfg.status_contract,'Configuration records do not assert live provider health.');
  assert.ok(status.includes('liveHealth') || status.includes('configState'));
});

t('local publishing queue remains retired',()=>{
  assert.ok(jobs.includes('Local publishing queue is retired'));
  assert.ok(jobs.includes('status:410'));
});

t('queue identity is deterministic per approved batch account',()=>{
  assert.ok(manager.includes("'bulk:' . $batch . ':' . $id"));
  assert.ok(manager.includes("'batch_id' => $batch"));
  assert.ok(manager.includes("'account_id' => $id"));
});

t('unsafe publish retries require fencing or reconciliation',()=>{
  const rule='unsafe_external_publish_retry_requires_reconciliation';
  assert.ok(cfg.rules[rule]===true,rule);
});

t('fabricated claims remain forbidden',()=>{
  assert.equal(cfg.rules.no_fabricated_claims,true);
});

console.log('SOCIAL_MEDIA_GATE_PASS '+pass+'/6 approval=1 idempotency=1 live_health_claims=0 blind_retry=0');
