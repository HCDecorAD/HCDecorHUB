import assert from 'node:assert/strict';
import fs from 'node:fs';

const manifest=JSON.parse(fs.readFileSync('config/hcdecor-v9-package-manifest.json','utf8'));
const shell=fs.readFileSync('components/HubShell.js','utf8');
const imaster=fs.readFileSync('app/hub/agents/page.js','utf8');
const masterConsole=fs.readFileSync('app/hub/agents/MasterConsole.jsx','utf8');
const projects=fs.readFileSync('app/hub/projects/page.js','utf8');
const system=fs.readFileSync('app/hub/system/page.js','utf8');
const ai=fs.readFileSync('app/hub/ai/AIConnectionsClient.jsx','utf8');
const aiRoute=fs.readFileSync('app/api/ai/status/route.js','utf8');
const projectRoute=fs.readFileSync('app/api/projects/route.js','utf8');
const runtimeRoute=fs.readFileSync('app/api/runtime/route.js','utf8');

for(const id of ['PKG-02','PKG-03','PKG-04','PKG-05']){
  const pkg=manifest.packages.find(x=>x.id===id);
  assert.ok(pkg,id);
  assert.ok(['BUILDING','LOCAL_PASS','INTEGRATED','DONE'].includes(pkg.state),id+' state');
}
assert.ok(shell.includes('["WIN_SYSTEM","System Center","Trung Tâm Hệ Thống","/hub/system"]'));
assert.ok(imaster.includes('data-imaster-stage={x}'));
for(const token of ['/api/master','/api/master/runs','/api/master/preflight','/api/master/actions']) assert.ok(masterConsole.includes(token),token);
for(const token of ['/api/projects','Google Sheets + Drive','Project ID']) assert.ok(projects.includes(token),token);
assert.ok(projectRoute.includes('requireSameOriginMutation'));
assert.ok(projectRoute.includes('fresh_external_write_approval_required'));
for(const token of ['WIN_SYSTEM','/hub/ai','/admin/health','/admin/integrations','/admin/settings','/api/runtime/diagnostics','/admin/deploy','HCDR PRIMARY']) assert.ok(system.includes(token),token);
for(const id of ['AI_PROVIDERS','AI_MODELS','AI_ROUTER','AI_KEYS','AI_LOGIN','CONNECTIONS','PLUGIN_CENTER','AI_FLOW','AI_TEST','AI_BINDING']) assert.ok(ai.includes(id),id);
assert.ok(aiRoute.includes('probeAIConnections'));
assert.ok(runtimeRoute.includes('productionWrite:false'));
assert.ok(!masterConsole.includes('onClick={()=>{}}'));
console.log('HCDECOR_V9_WAVE2_CORE_PASS pkg02_imaster=1 pkg03_projects=1 pkg04_system=1 pkg05_ai=1 real_actions=1 guarded_writes=1');
