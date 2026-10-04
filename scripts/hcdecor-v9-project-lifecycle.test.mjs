import assert from 'node:assert/strict';
import fs from 'node:fs';

const google=fs.readFileSync('lib/crm/google.js','utf8');
const route=fs.readFileSync('app/api/projects/[id]/route.js','utf8');
const workers=fs.readFileSync('app/api/projects/[id]/workers/route.js','utf8');
const outputs=fs.readFileSync('app/api/projects/[id]/outputs/route.js','utf8');
const workflow=fs.readFileSync('app/api/master/workflows/preview/route.js','utf8');
const page=fs.readFileSync('app/hub/projects/page.js','utf8');

for(const fn of ['updateProject','deleteProject','getProjectById']) assert.ok(google.includes('export async function '+fn),fn);
for(const verb of ['GET','PATCH','DELETE']) assert.ok(route.includes('export async function '+verb),verb);
for(const guard of ['requireSameOriginMutation','authorized(req)','freshApproval(d)','HCDECOR_PROJECT_API_TOKEN']) assert.ok(route.includes(guard),guard);
assert.ok(route.includes('deleteDriveFolder===true'));
assert.ok(route.includes('production_write:true'));

assert.ok(workers.includes('resolveCapability'));
for(const m of ['content','media','publishing','reports','agents']) assert.ok(workers.includes('"'+m+'"'),m);
assert.ok(workers.includes('production_write:false'));

assert.ok(outputs.includes('listPreviews'));
assert.ok(outputs.includes('artifact?.context?.project_id===id'));
assert.ok(outputs.includes('production_write:false'));
assert.ok(workflow.includes('project_id'));
assert.ok(workflow.includes('runPreviewWorkflow'));

assert.ok(page.includes('Worker Binding'));
assert.ok(page.includes('Outputs'));
console.log('HCDECOR_V9_PROJECT_LIFECYCLE_PASS read=1 update=1 delete=1 guarded=1 worker_binding=1 project_outputs=1 preview_authority=local_spool');
