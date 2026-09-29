import {assessArchitectureChange,getArchitectureBaseline} from "../../../../lib/architecture-baseline";
export async function GET(){return Response.json({ok:true,baseline:getArchitectureBaseline(),production_write:false})}
export async function POST(request){let body;try{body=await request.json()}catch{return Response.json({ok:false,error:"invalid_json"},{status:400})}return Response.json(assessArchitectureChange(body))}
