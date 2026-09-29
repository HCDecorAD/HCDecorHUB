import {getWorkspaceView} from "./hub-config";
import {getDeploymentProfile} from "./deployment-adapter";
import {resolveCapability} from "./capability-router";
import {probeWebPage} from "./web-read-probe";

export async function crossWorkspaceCheck(){
 const views=getWorkspaceView();
 const rows=await Promise.all(views.map(async w=>{
  const deployment=getDeploymentProfile(w.workspace_id),route=resolveCapability({workspace_id:w.workspace_id,module:"website",action:"view"});
  const live=w.productionUrl?await probeWebPage(w.productionUrl):{ok:false,status:"not_configured",production_write:false};
  const source_verified=Boolean(route.ok&&deployment.ok&&w.productionUrl),live_verified=live.ok===true;
  return {workspace_id:w.workspace_id,site_id:w.site_id,source:{repository:w.repository,production_url:w.productionUrl,config_state:w.configState,verified:source_verified},routing:{ok:route.ok,worker:route.worker||null,capability:route.capability||null},deployment:{ok:deployment.ok,provider:deployment.ok?deployment.providers.primary?.provider||null:null,requires_approval:true},live:{...live,verified:live_verified},production_write:false};
 }));
 const source_verified=rows.every(x=>x.source.verified),live_verified=rows.every(x=>x.live.verified);
 return {ok:source_verified&&live_verified,status:source_verified&&live_verified?"verified":source_verified?"source-ready-live-degraded":"needs-attention",check:"cross-workspace-read-only",parallel:true,source_verified,live_verified,workspaces:rows,production_write:false,production_execution_started:false};
}
