import fs from 'node:fs';
import assert from 'node:assert/strict';

const c=JSON.parse(fs.readFileSync('config/corporate-catalog.json','utf8'));
assert.equal(c.schema_version,'1.0.0');
assert.equal(c.projects.length,3);
assert.deepEqual(new Set(c.projects.map(x=>x.project_id)),new Set(['HCDECOR','GSC','AMO']));
for(const p of c.projects){assert.ok(p.workspace_id);assert.ok(p.repository);assert.ok(p.production_authority);}
assert.equal(c.projects.some(p=>/vercel/i.test(p.production_authority)),false);
for(const id of ['group-governor','hc-done','transwarp','evidence-envelope','deployment-adapter','policy-engine','hcdr','hc-autodebug','hc-agent-control','hc-autochat','hc-moonshot','hc-visual-builder','hc-mediaflow','hc-video-downloader','hc-design-ai-studio']) assert.ok(c.tools.find(x=>x.tool_id===id),id);
for(const t of c.tools){assert.ok(Array.isArray(t.provides)&&t.provides.length);assert.ok(t.risk_class);assert.ok(t.gate);}
assert.ok(c.resources.find(x=>x.resource_id==='github-actions'));
assert.ok(c.resources.find(x=>x.resource_id==='wordpress'));
assert.ok(c.resources.find(x=>x.resource_id==='github-pages'));
assert.equal(c.tools.find(x=>x.tool_id==='hc-agent-control').verification_state,'not-proven-done');
console.log('CORPORATE_CATALOG_PASS projects=3 tools='+c.tools.length+' retired_vercel=0 expanded_known_tools=1');
