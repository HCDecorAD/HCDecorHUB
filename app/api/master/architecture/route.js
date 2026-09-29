import {assessArchitectureChange,getArchitectureBaseline} from "../../../../lib/architecture-baseline";
import {requireSameOriginMutation} from "../../../../lib/request-guard";
export async function GET(){return Response.json({ok:true,baseline:getArchitectureBaseline(),production_write:false})}
export async function POST(request){const guard=requireSameOriginMutation(request);if(guard)return guard;let body;try{body=await request.json()}catch{return Response.json({ok:false,error:"invalid_json"},{status:400})}return Response.json(assessArchitectureChange(body))}
