import {GET as commerceStatus} from "../../commerce/status/route";
async function commerceVerified(){try{const r=await commerceStatus();const d=await r.json();return d.ready===true}catch{return false}}
export async function GET(){
 const commerce_verified=await commerceVerified();
 return Response.json({
  service:"hcdecor-hub-agent-public",
  contract_version:"1.1",
  mode:"public-control-plane",
  authorities:{hcdecor:"wordpress",gsc:"github-pages",amo:"github-pages",commerce:"engine-live-data-verified"},
  public_surfaces:["/hub/agents","/api/public/status","/api/runtime","/api/master/readiness","/api/commerce/status"],
  guarantees:{default_deny:true,production_write:false,approval_required_for_mutation:true,secrets_exposed:false,test_mode_in_production:false},
  monitoring:{health_interval:"hourly",confirmed_degradation_after:2,history_retention_days:14,backup_interval:"daily-03:30",backup_retention_days:14},
  persistence:{production_state:"durable-provider-required",local_spool_authority:false,mutation_execution_enabled:false},
  blockers:{commerce_verified,custom_domain_verified:true},
  checkedAt:new Date().toISOString()
 },{headers:{"cache-control":"no-store"}});
}
