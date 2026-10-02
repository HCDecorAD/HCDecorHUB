import fs from 'node:fs';
import assert from 'node:assert/strict';
const r=JSON.parse(fs.readFileSync('config/capability-registry.json','utf8'));
assert.equal(r.schema_version,'1.0.0');
assert.ok(r.capabilities.length>=6);
for(const c of r.capabilities){assert.ok(c.capability_id);assert.ok(c.provider_tool);assert.ok(Array.isArray(c.inputs));assert.ok(Array.isArray(c.outputs));assert.ok(c.risk_class);assert.ok(c.gate);assert.ok(c.cost_class);assert.equal(c.fail_closed,true);}
const dep=r.capabilities.find(c=>c.provider_tool==='deployment-adapter');
assert.equal(dep.production_write,true);
const policy=r.capabilities.find(c=>c.provider_tool==='policy-engine');
assert.ok(policy.inputs.includes('approved'));
console.log('CAPABILITY_REGISTRY_PASS capabilities='+r.capabilities.length+' fail_closed=1');
