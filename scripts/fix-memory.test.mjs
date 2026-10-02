import fs from 'node:fs';
import assert from 'node:assert/strict';
const m=JSON.parse(fs.readFileSync('config/fix-memory.json','utf8'));
assert.equal(m.schema_version,'1.0.0');
assert.ok(m.fixes.length>=4);
for(const f of m.fixes){assert.ok(f.fingerprint);assert.ok(f.component);assert.ok(f.symptom);assert.ok(f.fix);assert.ok(Array.isArray(f.verified_commits)&&f.verified_commits.length);assert.ok(f.evidence);}
assert.ok(/terminal verification evidence/i.test(m.promotion_rule));
console.log('FIX_MEMORY_PASS fixes='+m.fixes.length+' verified_only=1');
