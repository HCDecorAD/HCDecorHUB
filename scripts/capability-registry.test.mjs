import fs from 'node:fs';
import assert from 'node:assert/strict';
const r=JSON.parse(fs.readFileSync('config/capability-registry.json','utf8'));
assert.equal(r.schema_version,'1.2.0');
assert.ok(r.capabilities.length>=16);
for(const c of r.capabilities){assert.ok(c.capability_id);assert.ok(c.provider_tool);assert.ok(Array.isArray(c.inputs));assert.ok(Array.isArray(c.outputs));assert.ok(c.risk_class);assert.ok(c.gate);assert.ok(c.cost_class);assert.equal(c.fail_closed,true);}
const dep=r.capabilities.find(c=>c.provider_tool==='deployment-adapter');
assert.equal(dep.production_write,true);
const policy=r.capabilities.find(c=>c.provider_tool==='policy-engine');
assert.ok(policy.inputs.includes('approved'));
for(const id of ['local-execution-transport','failure-diagnosis','repair-verification']){
  const c=r.capabilities.find(x=>x.capability_id===id);
  assert.ok(c,id);
  assert.ok(c.inputs.includes('mission_id'),id);
  assert.ok(c.inputs.includes('correlation_id'),id);
  if(['local-execution-transport','failure-diagnosis','repair-verification'].includes(id)) assert.equal(c.contract_state,'source-implementation-gated');
  else assert.equal(c.contract_state,'source-contract-only');
}
assert.ok(r.capabilities.length>=16, 'expected expanded iMaster capability registry');
console.log('CAPABILITY_REGISTRY_PASS capabilities='+r.capabilities.length+' fail_closed=1 correlation_contracts=3 hcdr_source_implementation=1 autodebug_diagnosis_implementation=1 autodebug_repair_implementation=1');
