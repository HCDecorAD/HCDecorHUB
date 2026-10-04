import assert from 'node:assert/strict';
import fs from 'node:fs';

const inv=JSON.parse(fs.readFileSync('config/active-asset-inventory.json','utf8'));
const cat=JSON.parse(fs.readFileSync('config/corporate-catalog.json','utf8'));
assert.equal(inv.schema_version,'2.0.0');
assert.ok(Array.isArray(inv.portfolio_assets)&&inv.portfolio_assets.length>=9);
assert.ok(Array.isArray(inv.repositories)&&inv.repositories.length>=9);
assert.equal(new Set(inv.repositories).size,inv.repositories.length);
assert.ok(!inv.purpose.includes('corporate-catalog.json') || inv.purpose.includes('not generated'));
for(const k of ['projects','systems','tools','resources']) assert.ok(Array.isArray(inv[k])&&inv[k].length>0,k);

const actual={
  projects:new Set(cat.projects.map(x=>x.project_id)),
  systems:new Set(cat.systems.map(x=>x.system_id)),
  tools:new Set(cat.tools.map(x=>x.tool_id)),
  resources:new Set(cat.resources.map(x=>x.resource_id))
};
let total=0,covered=0;
const missing={};
for(const k of ['projects','systems','tools','resources']){
  missing[k]=[];
  for(const id of inv[k]){
    total++;
    if(actual[k].has(id)) covered++;
    else missing[k].push(id);
  }
}
const percent=total?covered/total*100:0;
assert.equal(total,34);
assert.deepEqual(missing,{projects:[],systems:[],tools:[],resources:[]});
assert.ok(percent>=95);
console.log(`ACTIVE_ASSET_COVERAGE_PASS covered=${covered} total=${total} percent=${percent.toFixed(2)} declared_inventory=1 portfolio_repos=${inv.repositories.length} enterprise_completeness_not_claimed=1`);
