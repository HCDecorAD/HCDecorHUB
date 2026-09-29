import {requireSameOriginMutation} from "../../../lib/request-guard";
import {planMasterTask} from "../../../lib/master-agent";

export async function POST(request){
 const guard=requireSameOriginMutation(request);if(guard)return guard;
 let body;try{body=await request.json()}catch{return Response.json({ok:false,error:"invalid_json"},{status:400})}
 if(!body||typeof body!=="object"||Array.isArray(body))return Response.json({ok:false,error:"invalid_body"},{status:400});
 const result=planMasterTask(body);
 return Response.json(result.ok?{ok:true,plan:result.plan}:{ok:false,error:result.error},{status:result.status});
}
