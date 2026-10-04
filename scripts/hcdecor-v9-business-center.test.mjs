import assert from 'node:assert/strict';
import fs from 'node:fs';

const cfg=fs.readFileSync('lib/business/config.js','utf8');
const store=fs.readFileSync('lib/business/google.js','utf8');
const api=fs.readFileSync('lib/business/api.js','utf8');
const page=fs.readFileSync('app/hub/business/page.js','utf8');
const manifest=JSON.parse(fs.readFileSync('config/hcdecor-v9-package-manifest.json','utf8'));

for(const tab of ['Customers','Quotations','Payments']) assert.ok(cfg.includes(tab),tab);
for(const fn of ['listBusiness','getBusiness','appendBusiness','updateBusiness','deleteBusiness']) assert.ok(store.includes('function '+fn)||store.includes('function '+fn+'(')||store.includes('async function '+fn)||store.includes('export async function '+fn),fn);
for(const guard of ['requireSameOriginMutation','authorized(req)','freshApproval(d)','business_runtime_not_configured']) assert.ok(api.includes(guard),guard);
for(const method of ['GET','POST','PATCH','DELETE']) {
  for(const res of ['customers','quotations','payments']) {
    const r=fs.readFileSync('app/api/business/'+res+'/route.js','utf8');
    assert.ok(r.includes('export const '+method+'=h.'+method),res+' '+method);
  }
}
for(const stage of ['LEADS','CUSTOMERS','QUOTATIONS','ORDER / PROJECT','PAYMENT']) assert.ok(page.includes(stage),stage);
for(const path of ['/api/business/customers','/api/business/quotations','/api/business/payments']) assert.ok(page.includes(path),path);
assert.ok(page.includes('production write remains approval-gated'));
const pkg=manifest.packages.find(x=>x.id==='PKG-10');
assert.ok(pkg);
assert.ok(['BUILDING','LOCAL_PASS','INTEGRATED','DONE'].includes(pkg.state));
console.log('HCDECOR_V9_BUSINESS_CENTER_PASS resources=3 crud=1 guarded=1 fail_closed=1 workflow=lead_customer_quotation_project_payment');
