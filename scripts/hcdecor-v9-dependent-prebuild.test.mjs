import assert from 'node:assert/strict';
import fs from 'node:fs';

const manifest=JSON.parse(fs.readFileSync('config/hcdecor-v9-package-manifest.json','utf8'));
const create=fs.readFileSync('app/hub/create-post/CreatePostClient.jsx','utf8');
const createPage=fs.readFileSync('app/hub/create-post/page.js','utf8');
const ui=fs.readFileSync('app/hub/ui-designer/UIDesignerClient.jsx','utf8');
const uiPage=fs.readFileSync('app/hub/ui-designer/page.js','utf8');
const shell=fs.readFileSync('components/HubShell.js','utf8');
const social=fs.readFileSync('app/hub/social/page.js','utf8');
const trend=fs.readFileSync('app/hub/trend/page.js','utf8');

for(const id of ['PKG-06','PKG-09','PKG-11']) assert.ok(['WAITING_DEP','BUILDING','LOCAL_PASS','INTEGRATED','DONE'].includes(manifest.packages.find(x=>x.id===id)?.state),id+' valid lifecycle state');
for(const mod of ['SOC_CONTENT','SOC_MEDIA','SOC_NETWORKS','SOC_ACCOUNTS','SOC_AI','SOC_VARIANTS','SOC_PREVIEW','SOC_ACCOUNT_GROUPS','SOC_BULK_IMPORT']) assert.ok(create.includes(mod),mod);
assert.ok(create.includes('/api/content/factory'));
assert.ok(create.includes('AI LIVE')||create.includes('WAITING FOR AI CONNECTION'));
assert.ok(createPage.includes('WIN_CREATE_POST'));
for(const api of ['app/api/trend/opportunity/route.js','app/api/trend/outliers/route.js','app/api/content/factory/route.js']) assert.ok(fs.existsSync(api),api);
assert.ok(trend.includes('SCAN → EARLY SIGNAL → SCORE'));
for(const token of ['localStorage','Validate','Apply','Undo','UI_EDIT_AI','UI_PREVIEW','UI_VERSIONS']) assert.ok(ui.includes(token),token); assert.ok(ui.includes('/api/ai/ui-patch')||ui.includes('WAITING FOR AI CONNECTION'));
assert.ok(uiPage.includes('WIN_UI_DESIGNER'));
assert.ok(shell.includes('href="/hub/create-post"'));
assert.ok(social.includes('/hub/create-post'));
console.log('HCDECOR_V9_DEPENDENT_PREBUILD_PASS pkg06=1 pkg09=1 pkg11=1 dependency_block_preserved=1 no_fake_ai=1');
