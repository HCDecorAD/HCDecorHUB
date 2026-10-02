import fs from 'node:fs';
import assert from 'node:assert/strict';
const page=fs.readFileSync('app/hub/operator/page.js','utf8');
assert.ok(page.includes('READ-ONLY PORTFOLIO'));
assert.ok(page.includes('No production mutation'));
for(const bad of ['method:"POST"','method:\'POST\'','/deploy','/publish','/rollback','/retry']) assert.equal(page.includes(bad),false,bad);
assert.ok(page.includes('corporate-catalog.json'));
assert.ok(page.includes('capability-registry.json'));
console.log('OPERATOR_DASHBOARD_PASS read_only=1 catalog=1 capabilities=1 production_write=0');
