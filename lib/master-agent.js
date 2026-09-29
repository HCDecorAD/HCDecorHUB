import {getHubConfig} from "./hub-config";
import {productionGuard} from "./hub-policy";

const clip=(value,max=500)=>typeof value==="string"?value.trim().slice(0,max):"";
const ACTIONS=new Set(["view","create","edit","delete","approve","publish","export","manage","deploy","rollback"]);
const MUTATIONS=new Set(["create","edit","delete","publish","manage","deploy","rollback"]);
const WORKER_HINTS={website:"website",ui:"website",ux:"website",content:"content",seo:"content",media:"media",publish:"publishing",publishing:"publishing",crm:"project-assistant",project:"project-assistant",projects:"project-assistant",report:"project-assistant",qa:"qa",verify:"qa"};

export function planMasterTask(input={}){
 const {agents,workspaces,adapters}=getHubConfig();
 const workspaceId=clip(input.workspace_id,80).toLowerCase();
 const workspace=workspaces.workspaces.find(w=>w.workspace_id===workspaceId)||null;
 if(!workspace)return {ok:false,status:400,error:"workspace_required_or_unknown"};
 const module=clip(input.module,80).toLowerCase();
 if(!module||!workspace.modules.includes(module))return {ok:false,status:400,error:"module_not_available_in_workspace"};
 const action=clip(input.action,30).toLowerCase();
 if(!ACTIONS.has(action))return {ok:false,status:400,error:"invalid_action"};
 const intent=clip(input.intent,1000);
 if(!intent)return {ok:false,status:400,error:"intent_required"};
 const adapter=adapters.adapters[workspace.site_id]||{};
 const workerId=WORKER_HINTS[module]||WORKER_HINTS[clip(input.capability,80).toLowerCase()]||"qa";
 const worker=agents.workers.find(w=>w.id===workerId)||agents.workers.find(w=>w.id==="qa");
 const guard=productionGuard();
 const productionBlocked=guard.guarded.includes(action)||["rollback"].includes(action);
 const mutation=MUTATIONS.has(action);
 const plan={
  architecture:agents.architecture,master_agent:agents.master_agent.id,
  workspace:{workspace_id:workspace.workspace_id,site_id:workspace.site_id,name:workspace.name},
  task:{intent,module,action,mutation},worker:{id:worker.id,capabilities:worker.capabilities},
  adapter:{type:adapter.type||"unknown",config_state:adapter.config_state||adapter.source_state||"unknown"},
  execution:{mode:"plan",allowed:!productionBlocked,requires_approval:productionBlocked,parallel_safe:!mutation},
  verification:["workspace-resolved","module-allowed","worker-resolved","production-policy-checked"],
  audit:{actor_type:"master-agent",workspace_id:workspace.workspace_id,site_id:workspace.site_id,module,action,result:productionBlocked?"approval-required":"planned"}
 };
 return {ok:true,status:200,plan};
}
