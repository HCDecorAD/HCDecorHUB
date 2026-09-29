import {getHubConfig,getWorkspaceView} from "./hub-config";
import {readWordPressSite} from "./cms/wordpress";
import {createInternalDraft} from "./draft-store";
import {getModuleStatus} from "./module-status";
import {createPreview} from "./preview-store";
const stamp=()=>new Date().toISOString();
export async function executeMasterPlan(plan,context={}){
 if(!plan||plan.execution?.allowed!==true)return {ok:false,status:403,error:"execution_not_allowed"};
 const {workspace,task,worker}=plan;
 if(task?.action==="create"){const draft=await createInternalDraft(plan);if(draft)return {ok:true,status:201,execution:{mode:"internal-draft",worker:worker.id,adapter:"local-spool",data:draft,verification:{passed:true,checks:[...plan.verification,"internal-draft-created"]},audit:{...plan.audit,result:"executed-internal-draft",timestamp:stamp()}}};const preview=await createPreview(plan,context);if(preview)return {ok:true,status:201,execution:{mode:"preview-only",worker:worker.id,adapter:"local-spool",data:preview,verification:{passed:true,checks:[...plan.verification,"preview-created","external-write-blocked"]},audit:{...plan.audit,result:"prepared-preview",timestamp:stamp()}}};return {ok:false,status:409,error:"safe_write_not_available"}}
 if(task?.action!=="view")return {ok:false,status:409,error:"plan_only_action"};
 let data=null;
 if(task.module==="agents")data=getHubConfig().agents;
 else if(task.module==="reports"||task.module==="audit")data={workspace:workspace.workspace_id,worker:worker.id,state:"source-ready"};
 else if(task.module==="website"){const source=getWorkspaceView().find(x=>x.workspace_id===workspace.workspace_id)||null;if(source?.adapter==="wordpress"){let live;try{live=await readWordPressSite()}catch{live={ok:false,status:"unreachable"}}data={source,live}}else if(source?.productionUrl){let live;try{const r=await fetch(source.productionUrl,{method:"HEAD",cache:"no-store",redirect:"manual",signal:AbortSignal.timeout(5000)});live={ok:r.ok,http:r.status}}catch{live={ok:false,status:"unreachable"}}data={source,live}}else data={source,live:{ok:false,status:"not_configured"}}}
 else data=getModuleStatus(task.module,workspace.workspace_id);
 const verified=Boolean(data);
 return {ok:verified,status:verified?200:404,execution:{mode:"read-only",worker:worker.id,adapter:plan.adapter.type,data,verification:{passed:verified,checks:plan.verification},audit:{...plan.audit,result:verified?"executed-read-only":"verification-failed",timestamp:stamp()}}};
}
