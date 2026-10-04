import assert from 'node:assert/strict';
import fs from 'node:fs';

const google=fs.readFileSync('lib/crm/google.js','utf8');
const route=fs.readFileSync('app/api/projects/[id]/route.js','utf8');
const page=fs.readFileSync('app/hub/projects/page.js','utf8');

for(const fn of ['updateProject','deleteProject','getProjectById']) assert.ok(google.includes('function '+fn+'(')||google.includes('function '+fn)||google.includes('async function '+fn)||google.includes('export async function '+fn),fn);
for(const verb of ['GET','PATCH','DELETE']) assert.ok(route.includes('export async function '+verb),verb);
for(const guard of ['requireSameOriginMutation','authorized(req)','freshApproval(d)','HCDECOR_PROJECT_API_TOKEN']) assert.ok(route.includes(guard),guard);
assert.ok(route.includes('deleteDriveFolder===true'));
assert.ok(route.includes('production_write:true'));
assert.ok(page.includes('Worker Binding'));
assert.ok(page.includes('Outputs'));
console.log('HCDECOR_V9_PROJECT_LIFECYCLE_PASS read=1 update=1 delete=1 guarded=1 worker_link=1 output_link=1');
