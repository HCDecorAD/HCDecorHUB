import assert from 'node:assert/strict';
import fs from 'node:fs';

const projectApi=fs.readFileSync('app/api/projects/route.js','utf8');
const projectPage=fs.readFileSync('app/hub/projects/page.js','utf8');
const google=fs.readFileSync('lib/crm/google.js','utf8');
const aiApi=fs.readFileSync('app/api/ai/status/route.js','utf8');
const aiLib=fs.readFileSync('lib/ai/connections.js','utf8');
const aiClient=fs.readFileSync('app/hub/ai/AIConnectionsClient.jsx','utf8');
const business=fs.readFileSync('app/hub/business/page.js','utf8');
const shell=fs.readFileSync('components/HubShell.js','utf8');

assert.ok(google.includes('export async function listProjects('));
assert.ok(projectApi.includes('export async function GET('));
for(const guard of ['requireSameOriginMutation','authorized(req)','freshApproval(d)','rateLimited(request)'])assert.ok(projectApi.includes(guard),guard);
assert.ok(projectPage.includes('listProjects(100)'));
assert.ok(projectPage.includes('token + fresh approval guarded'));
assert.ok(!projectPage.includes('type="password"'));

for(const id of ['AI_PROVIDERS','AI_MODELS','AI_ROUTER','AI_KEYS','AI_LOGIN','CONNECTIONS','PLUGIN_CENTER','AI_FLOW','AI_TEST','AI_BINDING'])assert.ok(aiClient.includes(id),id);
assert.ok(aiApi.includes('probeAIConnections'));
assert.ok(aiLib.includes('secret_policy:"SERVER_ONLY"'));
assert.ok(aiLib.includes('OPENAI_API_KEY'));
assert.ok(aiLib.includes('GEMINI_API_KEY'));
assert.ok(!aiClient.includes('process.env'));
assert.ok(aiClient.includes('/api/ai/status?live=1'));

for(const stage of ['LEADS','CUSTOMERS','QUOTATIONS','ORDER / PROJECT','PAYMENT'])assert.ok(business.includes(stage),stage);
assert.ok(business.includes('NOT CONFIGURED'));
assert.ok(shell.includes('["WIN_BUSINESS","Business Center","Trung Tâm Kinh Doanh","/hub/business"]'));

console.log('HCDECOR_V9_CENTERS_FOUNDATION_PASS projects_read=1 project_write_guarded=1 ai_modules=10 ai_secret_server_only=1 business_real_gaps_visible=1');
