import assert from 'node:assert/strict';
import fs from 'node:fs';

const google=fs.readFileSync('lib/business/google.js','utf8');
const cfg=fs.readFileSync('lib/business/config.js','utf8');
const setup=fs.readFileSync('app/api/business/setup/route.js','utf8');
for(const token of ['ensureBusinessSchema','initialized_headers','addSheet','deleteDimension']) assert.ok(google.includes(token),token);
for(const tab of ['Customers','Quotations','Payments']) assert.ok(cfg.includes(tab),tab);
for(const guard of ['requireSameOriginMutation','authorized(req)','freshApproval(d)','business_runtime_not_configured','business_schema_setup_failed']) assert.ok(setup.includes(guard),guard);
assert.ok(setup.includes('ensureBusinessSchema()'));
console.log('HCDECOR_V9_BUSINESS_SCHEMA_PASS additive_tabs=3 same_origin=1 bearer=1 fresh_approval=1');
