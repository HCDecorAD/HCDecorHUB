import assert from 'node:assert/strict';
import fs from 'node:fs';
import {deploymentProfileFromConfig,deploymentPlanFromProfile} from '../lib/deployment-adapter-core.mjs';

const config={
  workspaces:JSON.parse(fs.readFileSync('config/workspaces.json','utf8')),
  adapters:JSON.parse(fs.readFileSync('config/adapters.json','utf8'))
};
const cases=[['hcdecor','wordpress'],['gsc-senior','github-pages'],['amo-nguyen','github-pages']];
let pass=0;
for(const [workspace,provider] of cases){
  const p=deploymentProfileFromConfig(config,workspace);
  assert.equal(p.ok,true,workspace);
  assert.equal(p.providers.primary.provider,provider,workspace);
  assert.equal(p.source_of_truth,'github-main-or-workspace-repository');
  assert.equal(p.production_write,false);
  assert.equal(p.deploy_requires_approval,true);
  const plan=deploymentPlanFromProfile(p,{environment:'production'});
  assert.equal(plan.ok,true,workspace);
  assert.equal(plan.provider,provider,workspace);
  assert.equal(plan.requires_approval,true,workspace);
  assert.equal(plan.execution_started,false,workspace);
  assert.equal(plan.production_write,false,workspace);
  pass++;
}
const p=deploymentProfileFromConfig(config,'hcdecor');
const unknown=deploymentPlanFromProfile(p,{provider:'retired-provider',environment:'production'});
assert.equal(unknown.ok,false);
assert.equal(unknown.error,'deployment_provider_not_configured');
pass++;
const missing=deploymentProfileFromConfig(config,'missing-workspace');
assert.equal(missing.ok,false);
assert.equal(missing.error,'workspace_not_found');
pass++;
console.log(`DEPLOYMENT_ADAPTER_PASS ${pass}/5 authorities=wordpress,github-pages retired_provider=blocked`);
