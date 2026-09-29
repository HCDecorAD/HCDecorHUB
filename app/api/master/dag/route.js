import {buildTaskDag} from "../../../../lib/task-dag";
import {requireSameOriginMutation} from "../../../../lib/request-guard";
export async function POST(request){const guard=requireSameOriginMutation(request);if(guard)return guard;let body;try{body=await request.json()}catch{return Response.json({ok:false,error:"invalid_json"},{status:400})}const result=buildTaskDag(body);return Response.json(result,{status:result.status||200})}
