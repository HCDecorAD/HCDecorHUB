import {randomUUID} from "node:crypto";
import {planMasterTask} from "./master-agent";
import {executeMasterPlan} from "./master-executor";
import {appendMasterRun} from "./run-store";
import {resolveWorkflowContext} from "./workflow-context";

const FLOW=[{module:"content",depends_on:[]},{module:"media",depends_on:[]},{module:"publishing",depends_on:["content","media"]}];
async function runStep({module,workspace_id,intent,workflow_id,context,test_mode}){
 const planned=planMasterTask({workspace_id,module,action:"create",intent});
 if(!planned.ok)return {module,ok:false,error:planned.error};
 const executionPlan=test_mode===true?{...planned.plan,execution:{...planned.plan.execution,allowed:true,mode:"safe-preview",requires_approval:false}}:planned.plan;
 const executed=await executeMasterPlan(executionPlan,{workflow_id,project_context:context,test_mode:test_mode===true});
 await appendMasterRun({run_id:planned.plan.request_id,workflow_id,workspace_id:planned.plan.workspace.workspace_id,site_id:planned.plan.workspace.site_id,agent:planned.plan.worker.id,action:"create",status:executed.ok?"verified":"failed",created_at:planned.plan.audit.timestamp,test_mode:test_mode===true,audit:executed.execution?.audit||planned.plan.audit});
 return {module,request_id:planned.plan.request_id,ok:executed.ok,mode:executed.execution?.mode||null,preview_id:executed.execution?.data?.preview_id||null,artifact:executed.execution?.data?.artifact||null,error:executed.error||null};
}
export async function runPreviewWorkflow({workspace_id,intent,project_id="",project_context={},test_mode=false}){
 const workflow_id=randomUUID(),resolved=await resolveWorkflowContext({project_id,project_context}),steps=[];
 const first=[];for(const x of FLOW.filter(x=>x.depends_on.length===0))first.push(await runStep({module:x.module,workspace_id,intent,workflow_id,context:resolved.context,test_mode}));
 steps.push(...first);
 if(first.every(x=>x.ok))steps.push(await runStep({module:"publishing",workspace_id,intent,workflow_id,context:resolved.context,test_mode}));
 const ok=steps.length===FLOW.length&&steps.every(x=>x.ok);
 return {ok,workflow_id,workspace_id,project_context:resolved.context,context_state:resolved.state,context_source:resolved.source,context_durable:resolved.durable,context_reason:resolved.reason||null,status:ok?"preview-ready":"failed",production_write:false,external_write:false,execution_model:"dag",stages:[{stage:1,modules:["content","media"],parallel_safe:true,execution:"sequential-local-spool"},{stage:2,modules:["publishing"],parallel_safe:false,execution:"sequential",depends_on:["content","media"]}],steps};
}
