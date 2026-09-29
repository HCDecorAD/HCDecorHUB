import {getHubConfig} from "./hub-config";
import {getModuleStatus} from "./module-status";
import {planMasterTask} from "./master-agent";
import {classifyModuleReadiness} from "./readiness-semantics";

async function smokeWorkspace(w){
 const module=w.modules.includes("website")?"website":w.modules[0];
 const planned=planMasterTask({workspace_id:w.workspace_id,module,action:"view",intent:"operational workspace smoke"});
 const plan=planned.ok?planned.plan:null;
 const site=getModuleStatus("website",w.workspace_id);
 return {workspace_id:w.workspace_id,site_id:w.site_id,module,routing:{plan_ok:planned.ok,route_ok:Boolean(plan?.worker?.id),worker:plan?.worker?.id||null,verification:plan?.verification||null},adapter_readiness:classifyModuleReadiness(site),website:site,production_write:false};
}
export async function smokeWorkspaces(){
 const started=Date.now(),{workspaces}=getHubConfig();
 const rows=await Promise.all(workspaces.workspaces.map(smokeWorkspace));
 return {ok:rows.every(x=>x.routing.plan_ok&&x.routing.route_ok),parallel:true,elapsed_ms:Date.now()-started,meaning:"parallel source-only routing verification; no provider execution; adapter readiness is reported separately",live_provider_execution:false,production_write:false,checked_at:new Date().toISOString(),workspaces:rows};
}
