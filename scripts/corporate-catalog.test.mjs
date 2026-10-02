import fs from 'node:fs';
import assert from 'node:assert/strict';

const c=JSON.parse(fs.readFileSync('config/corporate-catalog.json','utf8'));
assert.equal(c.schema_version,'1.0.0');
assert.equal(c.projects.length,3);
assert.deepEqual(new Set(c.projects.map(x=>x.project_id)),new Set(['HCDECOR','GSC','AMO']));
for(const p of c.projects){assert.ok(p.workspace_id);assert.ok(p.repository);assert.ok(p.production_authority);}
assert.equal(c.projects.some(p=>/vercel/i.test(p.production_authority)),false);
for(const id of ['group-governor','hc-done','transwarp','evidence-envelope','deployment-adapter','policy-engine']) assert.ok(c.tools.find(x=>x.tool_id===id),id);
for(const t of c.tools){assert.ok(Array.isArray(t.provides)&&t.provides.length);assert.ok(t.risk_class);assert.ok(t.gate);}
assert.ok(c.resources.find(x=>x.resource_id==='github-actions'));
assert.ok(c.resources.find(x=>x.resource_id==='wordpress'));
assert.ok(c.resources.find(x=>x.resource_id==='github-pages'));
console.log('CORPORATE_CATALOG_PASS projects=3 tools='+c.tools.length+' retired_vercel=0');
