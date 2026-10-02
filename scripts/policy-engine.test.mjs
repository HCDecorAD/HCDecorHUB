import assert from 'node:assert/strict';
import fs from 'node:fs';
import {evaluatePolicyCore,isMutationCore} from '../lib/policy-engine-core.mjs';

const permissions=JSON.parse(fs.readFileSync('config/permissions.json','utf8'));
const evalp=(input,durable_execution_available=false)=>evaluatePolicyCore({permissions,durable_execution_available},input);
let pass=0;
const t=(name,fn)=>{fn();pass++;console.log('PASS',name)};

t('unknown role fails closed',()=>{
  assert.deepEqual(evalp({role:'ghost',action:'view',workspace_id:'hcdecor',grants:['hcdecor']}),{allowed:false,reason:'role_not_registered'});
});
t('viewer cannot mutate',()=>{
  const x=evalp({role:'viewer',action:'edit',workspace_id:'hcdecor',grants:['hcdecor'],approved:true});
  assert.equal(x.allowed,false); assert.equal(x.reason,'action_not_granted');
});
t('workspace isolation is enforced',()=>{
  const x=evalp({role:'editor',action:'view',workspace_id:'gsc-senior',grants:['hcdecor']});
  assert.equal(x.allowed,false); assert.equal(x.reason,'workspace_not_granted');
});
t('restricted mutation requires explicit approval',()=>{
  const x=evalp({role:'editor',action:'edit',workspace_id:'hcdecor',grants:['hcdecor'],approved:false});
  assert.equal(x.allowed,false); assert.equal(x.reason,'approval_required'); assert.equal(x.requires_approval,true);
});
t('approved non-production mutation may proceed within grant',()=>{
  const x=evalp({role:'editor',action:'edit',workspace_id:'hcdecor',grants:['hcdecor'],approved:true,production:false});
  assert.equal(x.allowed,true); assert.equal(x.reason,'granted');
});
t('production mutation additionally requires durable authority',()=>{
  const x=evalp({role:'editor',action:'edit',workspace_id:'hcdecor',grants:['hcdecor'],approved:true,production:true},false);
  assert.equal(x.allowed,false); assert.equal(x.reason,'durable_execution_required');
  const y=evalp({role:'editor',action:'edit',workspace_id:'hcdecor',grants:['hcdecor'],approved:true,production:true},true);
  assert.equal(y.allowed,true);
});
t('mutation classifier covers release-impacting actions',()=>{
  for(const a of ['create','edit','delete','publish','manage','deploy','rollback']) assert.equal(isMutationCore(a),true,a);
  for(const a of ['view','export','approve']) assert.equal(isMutationCore(a),false,a);
});
console.log(`POLICY_ENGINE_PASS ${pass}/7 default_deny=1 approval_gate=1 durable_prod_gate=1`);
