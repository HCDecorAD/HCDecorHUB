import {getWorkspaceView} from "./hub-config";
import {getDeploymentProfile} from "./deployment-adapter";
import {resolveCapability} from "./capability-router";

export async function crossWorkspaceCheck(){
 const views=getWorkspaceView();
 const rows=await Promise.all(views.map(async w=>{
  const deployment=getDeploymentProfile(w.workspace_id);
  const route=resolveCapability({workspace_id:w.workspace_id,module:"website",action:"view"});
  let live={state:"not_checked"};
  if(w.productionUrl)try{const r=await fetch(w.productionUrl,{method:"HEAD",cache:"no-store",redirect:"follow",signal:AbortSignal.timeout(5000)});live={state:r.ok?"reachable":"http_error",http:r.status}}catch{live={state:"unreachable"}}
  return {workspace_id:w.workspace_id,site_id:w.site_id,source:{repository:w.repository,production_url:w.productionUrl,config_state:w.configState},routing:{ok:route.ok,worker:route.worker||null,capability:route.capability||null},deployment:{ok:deployment.ok,provider:deployment.ok?deployment.providers.primary?.provider||null:null,requires_approval:true},live,production_write:false};
 }));
 const passed=rows.every(x=>x.routing.ok&&x.deployment.ok&&Boolean(x.source.production_url));
 return {ok:passed,status:passed?"verified":"needs-attention",check:"cross-workspace-read-only",parallel:true,workspaces:rows,production_write:false,production_execution_started:false};
}
