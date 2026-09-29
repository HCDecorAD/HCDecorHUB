import {getHubConfig,getWorkspaceView,getIntegrationView} from "./hub-config";
import {readWordPressSite} from "./cms/wordpress";
const stamp=()=>new Date().toISOString();
export async function executeMasterPlan(plan){
 if(!plan||plan.execution?.allowed!==true)return {ok:false,status:403,error:"execution_not_allowed"};
 if(plan.task?.action!=="view")return {ok:false,status:409,error:"plan_only_action"};
 const {workspace,task,worker}=plan;
 let data=null;
 if(task.module==="agents")data=getHubConfig().agents;
 else if(task.module==="reports"||task.module==="audit")data={workspace:workspace.workspace_id,worker:worker.id,state:"source-ready"};
 else if(task.module==="website"){const source=getWorkspaceView().find(x=>x.workspace_id===workspace.workspace_id)||null;if(source?.adapter==="wordpress"){let live;try{live=await readWordPressSite()}catch{live={ok:false,status:"unreachable"}}data={source,live}}else if(source?.productionUrl){let live;try{const r=await fetch(source.productionUrl,{method:"HEAD",cache:"no-store",redirect:"manual",signal:AbortSignal.timeout(5000)});live={ok:r.ok,http:r.status}}catch{live={ok:false,status:"unreachable"}}data={source,live}}else data={source,live:{ok:false,status:"not_configured"}}}
 else data={workspace:workspace.workspace_id,module:task.module,integrations:getIntegrationView().filter(x=>x.state!=="deferred").slice(0,12)};
 const verified=Boolean(data);
 return {ok:verified,status:verified?200:404,execution:{mode:"read-only",worker:worker.id,adapter:plan.adapter.type,data,verification:{passed:verified,checks:plan.verification},audit:{...plan.audit,result:verified?"executed-read-only":"verification-failed",timestamp:stamp()}}};
}
