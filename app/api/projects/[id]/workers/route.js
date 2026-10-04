import {getProjectById} from "../../../../../lib/crm/google";
import {resolveCapability} from "../../../../../lib/capability-router";

const MODULES=["content","media","publishing","reports","agents"];

export async function GET(_req,{params}){
  const {id}=await params;
  const project=await getProjectById(id);
  if(!project)return Response.json({ok:false,error:"project_not_found"},{status:404});
  const bindings=MODULES.map(module=>{
    const action=module==="reports"||module==="agents"?"view":"create";
    const route=resolveCapability({workspace_id:"hcdecor",module,action});
    return {module,action,ok:route.ok,worker:route.worker||null,capability:route.capability||null,requires_approval:route.requires_approval===true,error:route.error||null};
  });
  return Response.json({ok:true,project_id:id,workspace_id:"hcdecor",bindings,production_write:false});
}
