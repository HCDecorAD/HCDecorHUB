import {getDeploymentProfile,planDeployment} from "../../../../lib/deployment-adapter";
import {requireSameOriginMutation} from "../../../../lib/request-guard";
export async function GET(request){const id=new URL(request.url).searchParams.get("workspace")||"";const x=getDeploymentProfile(id);return Response.json(x,{status:x.ok?200:404})}
export async function POST(request){const guard=requireSameOriginMutation(request);if(guard)return guard;let body;try{body=await request.json()}catch{return Response.json({ok:false,error:"invalid_json"},{status:400})}const x=planDeployment(body.workspace_id,{provider:body.provider,environment:body.environment});return Response.json(x,{status:x.ok?200:400})}
