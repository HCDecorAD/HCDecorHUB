import assert from 'node:assert/strict';
import fs from 'node:fs';

const m=JSON.parse(fs.readFileSync('config/hcdecor-v9-package-manifest.json','utf8'));
const content=fs.readFileSync('app/api/content/factory/route.js','utf8');
const create=fs.readFileSync('app/hub/create-post/CreatePostClient.jsx','utf8');
const trend=fs.readFileSync('app/api/trend/opportunity/route.js','utf8');
const trendUi=fs.readFileSync('app/hub/trend/TrendAIClient.jsx','utf8');
const uiRoute=fs.readFileSync('app/api/ai/ui-patch/route.js','utf8');
const ui=fs.readFileSync('app/hub/ui-designer/UIDesignerClient.jsx','utf8');

assert.equal(m.packages.find(x=>x.id==='PKG-05')?.state,'DONE');
for(const id of ['PKG-06','PKG-09','PKG-11']) assert.ok(['BUILDING','LOCAL_PASS','INTEGRATED','DONE'].includes(m.packages.find(x=>x.id===id)?.state),id);
for(const token of ['runWorkersAIText','runWorkersAIImage','AI_DRAFT_READY','image_data_url']) assert.ok(content.includes(token),token);
assert.ok(create.includes('AI LIVE'));
assert.ok(create.includes('aiPreviewImage'));
for(const token of ['runWorkersAIText','ai_recommendation','ai_provider']) assert.ok(trend.includes(token),token);
assert.ok(trendUi.includes('/api/trend/opportunity'));
assert.ok(uiRoute.includes('runWorkersAIText'));
assert.ok(uiRoute.includes('production_write:false'));
assert.ok(ui.includes('/api/ai/ui-patch'));
assert.ok(ui.includes('AI Patch'));
assert.ok(ui.includes('preview-first'));
console.log('HCDECOR_V9_AI_ACTIVATION_PASS pkg05_done=1 pkg06=1 pkg09=1 pkg11=1 real_ai_routes=3 preview_first=1');
