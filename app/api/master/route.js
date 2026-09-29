import {requireSameOriginMutation} from "../../../lib/request-guard";
import {planMasterTask} from "../../../lib/master-agent";
import {executeMasterPlan} from "../../../lib/master-executor";
import {appendMasterRun} from "../../../lib/run-store";
import {queueApproval} from "../../../lib/approval-store";
import {allowTestMode} from "../../../lib/test-mode";

export async function POST(request){
 const guard=requireSameOriginMutation(request);if(guard)return guard;
 let body;try{body=await request.json()}catch{return Response.json({ok:false,error:"invalid_json"},{status:400})}
 if(!body||typeof body!=="object"||Array.isArray(body))return Response.json({ok:false,error:"invalid_body"},{status:400});
 const testMode=allowTestMode(request,body);const result=planMasterTask(body);
 if(!result.ok)return Response.json({ok:false,error:result.error},{status:result.status});
 if(result.plan.execution.requires_approval&&!testMode)await queueApproval(result.plan);
 if(body.execute===true&&result.plan.execution.requires_approval&&!testMode)return Response.json({ok:false,plan:result.plan,error:"approval_required_before_execution",execution_started:false},{status:409});
 if(body.execute===true){const executed=await executeMasterPlan(result.plan);await appendMasterRun({run_id:result.plan.request_id,workspace_id:result.plan.workspace.workspace_id,site_id:result.plan.workspace.site_id,agent:result.plan.worker.id,action:result.plan.task.action,status:executed.ok?"verified":"failed",created_at:result.plan.audit.timestamp,test_mode:testMode,audit:executed.execution?.audit||result.plan.audit});return Response.json(executed.ok?{ok:true,plan:result.plan,execution:executed.execution}:{ok:false,plan:result.plan,error:executed.error},{status:executed.status})}
 await appendMasterRun({run_id:result.plan.request_id,workspace_id:result.plan.workspace.workspace_id,site_id:result.plan.workspace.site_id,agent:result.plan.worker.id,action:result.plan.task.action,status:"planned",created_at:result.plan.audit.timestamp,test_mode:testMode,audit:result.plan.audit});
 return Response.json({ok:true,plan:result.plan},{status:result.status});
}
