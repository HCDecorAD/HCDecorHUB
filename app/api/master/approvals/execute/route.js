import {requireSameOriginMutation} from "../../../../../lib/request-guard";
export async function POST(request){
 const guard=requireSameOriginMutation(request);if(guard)return guard;
 let body;try{body=await request.json()}catch{return Response.json({ok:false,error:"invalid_json"},{status:400})}
 if(body?.confirm!==true)return Response.json({ok:false,error:"explicit_confirmation_required"},{status:409});
 return Response.json({ok:false,error:"authenticated_durable_executor_required",execution_started:false,production_write:false,production_authority:false},{status:503});
}
