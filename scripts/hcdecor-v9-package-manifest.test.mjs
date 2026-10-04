import assert from 'node:assert/strict';
import fs from 'node:fs';

const p='config/hcdecor-v9-package-manifest.json';
const m=JSON.parse(fs.readFileSync(p,'utf8'));
assert.equal(m.program_id,'HCDECOR_HUB_V9_APP');
assert.equal(m.status,'ACTIVE');
assert.deepEqual(m.execution_route,['CODE','HOCUONG_LOCAL','HCDR','LOCAL_TEST_AND_EVIDENCE','GITHUB','CI']);
for (const law of ['ONE_GOAL_REAL_WORKFLOW_REAL_ACTION_VERIFY_DONE','NO_FAKE_BUTTONS','LOCAL_FIRST_OPERATING_LAW','NO_EVIDENCE_NO_GREEN','DO_NOT_REDO_PASS']) assert.ok(m.laws.includes(law),law);

assert.equal(m.packages.length,17);
const ids=m.packages.map(x=>x.id);
assert.equal(new Set(ids).size,ids.length);
for (let i=0;i<17;i++) assert.ok(ids.includes('PKG-'+String(i).padStart(2,'0')));

const byId=new Map(m.packages.map(x=>[x.id,x]));
for (const pkg of m.packages) {
  assert.ok(Array.isArray(pkg.depends_on));
  assert.ok(Array.isArray(pkg.done_gate) && pkg.done_gate.length>0);
  for (const dep of pkg.depends_on) assert.ok(byId.has(dep),pkg.id+' missing '+dep);
}
function visit(id,stack=new Set(),done=new Set()){
  if(done.has(id)) return;
  assert.ok(!stack.has(id),'dependency cycle at '+id);
  stack.add(id);
  for(const dep of byId.get(id).depends_on) visit(dep,stack,done);
  stack.delete(id); done.add(id);
}
for(const id of ids) visit(id);

const windows=new Set(m.packages.flatMap(x=>x.windows||[]));
for(const w of ['WIN_HOME','WIN_TREND','WIN_SOCIAL','WIN_PROJECTS','WIN_IMASTER','WIN_BUSINESS','WIN_SYSTEM','WIN_CREATE_POST','WIN_MULTI_PUBLISH','WIN_AI_CONNECTIONS','WIN_UI_DESIGNER']) assert.ok(windows.has(w),w);
const modules=new Set(m.packages.flatMap(x=>x.modules||[]));
for(const mod of ['NAV','TOPBAR','WORKSPACE','TABS','SIDE_PANEL','AI_PANEL','SOC_CONTENT','SOC_MEDIA','SOC_PREVIEW','SOC_PUBLISH','SOC_VERIFY','AI_PROVIDERS','AI_ROUTER','AI_KEYS','UI_SCHEMA','UI_APPLY','UI_UNDO']) assert.ok(modules.has(mod),mod);

assert.equal(m.scheduler.policy,'run-all-unblocked-in-parallel');
assert.equal(m.scheduler.do_not_block_independent_packages,true);
console.log('HCDECOR_V9_PACKAGE_MANIFEST_PASS packages=17 waves='+m.waves.length+' dependency_cycles=0 local_first=1 no_fake_buttons=1');
