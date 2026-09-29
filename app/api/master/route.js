import {requireSameOriginMutation} from "../../../lib/request-guard";
import {planMasterTask} from "../../../lib/master-agent";
import {executeMasterPlan} from "../../../lib/master-executor";

export async function POST(request){
 const guard=requireSameOriginMutation(request);if(guard)return guard;
 let body;try{body=await request.json()}catch{return Response.json({ok:false,error:"invalid_json"},{status:400})}
 if(!body||typeof body!=="object"||Array.isArray(body))return Response.json({ok:false,error:"invalid_body"},{status:400});
 const result=planMasterTask(body);
 if(!result.ok)return Response.json({ok:false,error:result.error},{status:result.status});
 if(body.execute===true){const executed=await executeMasterPlan(result.plan);return Response.json(executed.ok?{ok:true,plan:result.plan,execution:executed.execution}:{ok:false,plan:result.plan,error:executed.error},{status:executed.status})}
 return Response.json({ok:true,plan:result.plan},{status:result.status});
}
