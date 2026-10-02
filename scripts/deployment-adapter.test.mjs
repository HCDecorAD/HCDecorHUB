import assert from 'node:assert/strict';
import {getDeploymentProfile,planDeployment} from '../lib/deployment-adapter.js';

const cases=[
  ['hcdecor','wordpress'],
  ['gsc-senior','github-pages'],
  ['amo-nguyen','github-pages']
];
let pass=0;
for(const [workspace,provider] of cases){
  const p=getDeploymentProfile(workspace);
  assert.equal(p.ok,true,workspace);
  assert.equal(p.providers.primary.provider,provider,workspace);
  assert.equal(p.source_of_truth,'github-main-or-workspace-repository');
  assert.equal(p.production_write,false);
  assert.equal(p.deploy_requires_approval,true);
  const plan=planDeployment(workspace,{environment:'production'});
  assert.equal(plan.ok,true,workspace);
  assert.equal(plan.provider,provider,workspace);
  assert.equal(plan.requires_approval,true,workspace);
  assert.equal(plan.execution_started,false,workspace);
  assert.equal(plan.production_write,false,workspace);
  pass++;
}
const unknown=planDeployment('hcdecor',{provider:'vercel',environment:'production'});
assert.equal(unknown.ok,false);
assert.equal(unknown.error,'deployment_provider_not_configured');
pass++;
const missing=getDeploymentProfile('missing-workspace');
assert.equal(missing.ok,false);
assert.equal(missing.error,'workspace_not_found');
pass++;
console.log(`DEPLOYMENT_ADAPTER_PASS ${pass}/5 authorities=wordpress,github-pages retired_vercel=blocked`);
