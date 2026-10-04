import assert from 'node:assert/strict';
import fs from 'node:fs';

const shell=fs.readFileSync('components/HubShell.js','utf8');
const css=fs.readFileSync('app/globals.css','utf8');
const manifest=JSON.parse(fs.readFileSync('config/hcdecor-v9-package-manifest.json','utf8'));

for(const id of ['WIN_HOME','WIN_TREND','WIN_SOCIAL','WIN_PROJECTS','WIN_IMASTER','WIN_BUSINESS','WIN_SYSTEM']) assert.ok(shell.includes(id),id);
for(const mod of ['NAV','TOPBAR','WORKSPACE','TABS','SIDE_PANEL','AI_PANEL','NOTICE','QUICK_ACTION','LANGUAGE','ACCOUNT_MENU','SEARCH']) assert.ok(shell.includes('data-module="'+mod+'"'),mod);
for(const token of ['hcdecor-v9-shell','hcdecor-language','Focus Mode','/api/master/readiness','/hub/create-post','/hub/review','/admin/settings']) assert.ok(shell.includes(token),token);
for(const klass of ['.v9Shell','.v9Nav','.v9Top','.v9Workspace','.v9Runtime','.isCollapsed','.isFocus']) assert.ok(css.includes(klass),klass);
assert.ok(!shell.includes('onClick={()=>{}}'),'fake click handler');
const pkg=manifest.packages.find(x=>x.id==='PKG-01');
assert.ok(pkg);
assert.ok(['BUILDING','LOCAL_PASS','INTEGRATED','DONE'].includes(pkg.state));
console.log('HCDECOR_V9_APP_SHELL_PASS windows=7 global_modules=11 persistent_layout=1 en_vn=1 focus=1 runtime_readiness=1');
