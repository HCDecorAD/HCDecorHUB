import assert from 'node:assert/strict';
import fs from 'node:fs';

const agents=fs.readFileSync('app/hub/agents/page.js','utf8');
const consoleSrc=fs.readFileSync('app/hub/agents/MasterConsole.jsx','utf8');
const admin=fs.readFileSync('app/admin/page.js','utf8');
const boot=JSON.parse(fs.readFileSync('config/imaster-bootstrap.json','utf8'));
const auth=JSON.parse(fs.readFileSync('config/imaster-global-authority.json','utf8'));
const manifest=JSON.parse(fs.readFileSync('config/hcdecor-v9-package-manifest.json','utf8'));

assert.ok(agents.includes('<MasterConsole/>'));
for(const api of ['/api/master','/api/master/runs','/api/master/preflight','/api/master/actions','/api/runtime','/api/master/operational']) assert.ok(consoleSrc.includes(api),api);
assert.ok(consoleSrc.includes('execution?.verification?.passed'));
for(const word of ['PLAN','EXECUTE','VERIFY','EVIDENCE','DONE']) assert.ok(boot.identity.mission.includes(word),word);
assert.ok(boot.skill_autoload.includes('config/imaster-skills/continuation-engine.json'));
assert.ok(boot.skill_autoload.includes('config/imaster-skills/learning-memory.json'));
assert.ok(boot.skill_autoload.includes('config/imaster-skills/repair-council.json'));

for(const api of ['/api/health','/api/cms/status','/api/publish/status','/api/wp/status','/api/runtime','/api/crm/status','/api/projects/status']) assert.ok(admin.includes(api),api);
for(const token of ['runtimeCapabilities','CMS Write Config','Drive Write Config','CAPABILITIES','DIAGNOSTICS']) assert.ok(admin.includes(token),token);

assert.equal(boot.panda_24_7.status,'DONE');
assert.ok(auth.laws.includes('LOCAL_FIRST_OPERATING_LAW'));
assert.equal(auth.operating_law_local_first.rdc_policy,'RESCUE_ONLY');

for(const id of ['PKG-02','PKG-04','PKG-12','PKG-14']) assert.ok(manifest.packages.find(x=>x.id===id),id);
console.log('HCDECOR_V9_CORE_CENTERS_PASS imaster=1 system=1 panda_reuse=1 local_first_reuse=1');
