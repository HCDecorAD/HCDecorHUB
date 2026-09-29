import {getHubConfig,getWorkspaceView,getIntegrationView} from "./hub-config";
const stamp=()=>new Date().toISOString();
export async function executeMasterPlan(plan){
 if(!plan||plan.execution?.allowed!==true)return {ok:false,status:403,error:"execution_not_allowed"};
 if(plan.task?.action!=="view")return {ok:false,status:409,error:"plan_only_action"};
 const {workspace,task,worker}=plan;
 let data=null;
 if(task.module==="agents")data=getHubConfig().agents;
 else if(task.module==="reports"||task.module==="audit")data={workspace:workspace.workspace_id,worker:worker.id,state:"source-ready"};
 else if(task.module==="website")data=getWorkspaceView().find(x=>x.workspace_id===workspace.workspace_id)||null;
 else data={workspace:workspace.workspace_id,module:task.module,integrations:getIntegrationView().filter(x=>x.state!=="deferred").slice(0,12)};
 const verified=Boolean(data);
 return {ok:verified,status:verified?200:404,execution:{mode:"read-only",worker:worker.id,adapter:plan.adapter.type,data,verification:{passed:verified,checks:plan.verification},audit:{...plan.audit,result:verified?"executed-read-only":"verification-failed",timestamp:stamp()}}};
}
