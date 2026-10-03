import fs from 'node:fs';
import assert from 'node:assert/strict';

const c=JSON.parse(fs.readFileSync('config/corporate-catalog.json','utf8'));
assert.equal(c.schema_version,'1.1.0');
assert.equal(c.projects.length,3);
assert.deepEqual(new Set(c.projects.map(x=>x.project_id)),new Set(['HCDECOR','GSC','AMO']));
for(const p of c.projects){assert.ok(p.workspace_id);assert.ok(p.repository);assert.ok(p.production_authority);}
assert.equal(c.projects.some(p=>/vercel/i.test(p.production_authority)),false);
assert.ok(Array.isArray(c.systems)&&c.systems.length>=11);
for(const id of ['hc-done','transwarp','hcdr','hc-autodebug','hc-agent-control','hc-autochat','hc-moonshot','hc-design-ai-studio','hc-visual-builder','hc-mediaflow','hc-video-downloader']) assert.ok(c.systems.find(x=>x.system_id===id),id);
for(const s of c.systems){assert.ok(s.role);assert.ok(s.state);assert.ok(Array.isArray(s.tool_refs)&&s.tool_refs.length);assert.equal(s.production_authority,false);}
for(const id of ['group-governor','hc-done','transwarp','evidence-envelope','deployment-adapter','policy-engine','hcdr','hc-autodebug','hc-agent-control','hc-autochat','hc-moonshot','hc-visual-builder','hc-mediaflow','hc-video-downloader','hc-design-ai-studio']) assert.ok(c.tools.find(x=>x.tool_id===id),id);
for(const t of c.tools){assert.ok(Array.isArray(t.provides)&&t.provides.length);assert.ok(t.risk_class);assert.ok(t.gate);}
assert.ok(c.resources.find(x=>x.resource_id==='github-actions'));
assert.ok(c.resources.find(x=>x.resource_id==='hcdr'));
assert.ok(c.resources.find(x=>x.resource_id==='wordpress'));
assert.ok(c.resources.find(x=>x.resource_id==='github-pages'));
assert.equal(c.tools.find(x=>x.tool_id==='hc-agent-control').verification_state,'source-contract-only');
assert.equal(c.systems.find(x=>x.system_id==='hc-agent-control').state,'source-contract-only');
assert.equal(c.tools.find(x=>x.tool_id==='hc-agent-control').gate,'HC Agent Control source contract');
for(const [id,gate] of [['hc-autochat','HC AutoChat source contract'],['hc-mediaflow','HC MediaFlow source contract'],['hc-video-downloader','HC Video Downloader source contract'],['hc-design-ai-studio','HC Design AI Studio source contract']]){assert.equal(c.tools.find(x=>x.tool_id===id).verification_state,'source-contract-only',id);assert.equal(c.systems.find(x=>x.system_id===id).state,'source-contract-only',id);assert.equal(c.tools.find(x=>x.tool_id===id).gate,gate,id);}
console.log('CORPORATE_CATALOG_PASS projects=3 systems='+c.systems.length+' tools='+c.tools.length+' retired_vercel=0 expanded_known_assets=1');
