import fs from 'node:fs'; import assert from 'node:assert/strict';
const j=p=>JSON.parse(fs.readFileSync(p,'utf8'));
const c=j('config/corporate-catalog.json'),i=j('config/active-asset-inventory.json'),s=j('config/corporate-census.json'),cap=j('config/capability-registry.json');
assert.equal(c.schema_version,'2.0.0');assert.equal(i.schema_version,'2.0.0');assert.equal(c.discovery.policy,'discover-before-create');assert.ok(c.portfolio_assets.length>=9);
const cr=new Set(c.portfolio_assets.map(x=>x.repository)),ir=new Set(i.repositories),sr=new Set(s.repositories.map(x=>x.repository));for(const r of cr){assert.ok(ir.has(r),'inventory missing '+r);assert.ok(sr.has(r),'census missing '+r);}assert.equal(sr.size,s.repository_count);assert.equal(s.policy.create_new_capability,'DISCOVER_MATCH_REUSE_EXTEND_CREATE');
const ids=cap.capabilities.map(x=>x.capability_id);assert.equal(new Set(ids).size,ids.length,'duplicate capability IDs');for(const a of c.portfolio_assets){assert.ok(a.asset_id&&a.kind&&a.repository&&a.mission&&a.authority);}
const active=JSON.stringify(c).toLowerCase()+JSON.stringify(i).toLowerCase()+JSON.stringify(s).toLowerCase();assert.equal(active.includes('vercel'),false,'retired provider must not return to active corporate data');
console.log('ANTI_AMNESIA_GATE_PASS portfolio_assets='+c.portfolio_assets.length+' repositories='+sr.size+' discover_before_create=1 retired_provider=0');
