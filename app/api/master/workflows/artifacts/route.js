import {listWorkflowPreviews} from "../../../../../lib/preview-store";
import {workflowCompleteness} from "../../../../../lib/workflow-completeness";
export async function GET(request){const u=new URL(request.url),workflow_id=(u.searchParams.get("workflow_id")||"").trim().slice(0,80);if(!workflow_id)return Response.json({ok:false,error:"workflow_id_required"},{status:400});const artifacts=await listWorkflowPreviews(workflow_id);return Response.json({ok:true,workflow_id,count:artifacts.length,completeness:workflowCompleteness(artifacts),production_write:false,artifacts})}
