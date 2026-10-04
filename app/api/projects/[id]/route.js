import crypto from "node:crypto";
import {getProjectById,updateProject,deleteProject} from "../../../../lib/crm/google";
import {crmRuntime} from "../../../../lib/crm/config";
import {requireSameOriginMutation} from "../../../../lib/request-guard";

const clean=v=>typeof v==="string"?v.trim():"";

function authorized(req){
  const expected=(process.env.HCDECOR_PROJECT_API_TOKEN||"").trim();
  const header=req.headers.get("authorization")||"";
  if(expected.length<32||expected.length>512||!header.startsWith("Bearer "))return false;
  const supplied=header.slice(7);
  if(supplied.length!==expected.length)return false;
  return crypto.timingSafeEqual(Buffer.from(supplied),Buffer.from(expected));
}

function freshApproval(d){
  if(d.confirmExternalWrite!==true)return false;
  const by=clean(d.approvedBy),source=clean(d.approvalSource),raw=clean(d.approvedAt);
  if(!by||by.length>200||raw.length>64||!["wp_user","service_bridge"].includes(source))return false;
  const at=Date.parse(raw);
  return Number.isFinite(at)&&at>=Date.now()-15*60*1000&&at<=Date.now()+5*60*1000;
}

export async function GET(_req,{params}){
  const {id}=await params;
  const item=await getProjectById(id);
  if(!item)return Response.json({ok:false,error:"project_not_found"},{status:404});
  return Response.json({ok:true,item,production_write:false});
}

export async function PATCH(req,{params}){
  const blocked=requireSameOriginMutation(req); if(blocked)return blocked;
  const runtime=crmRuntime(); if(!runtime.projectProvisionEnabled)return Response.json({ok:false,error:"project_runtime_not_configured"},{status:503});
  if(!authorized(req))return Response.json({ok:false,error:"unauthorized"},{status:401});
  let d={}; try{d=await req.json()}catch{return Response.json({ok:false,error:"invalid_json"},{status:400})}
  if(!freshApproval(d))return Response.json({ok:false,error:"fresh_external_write_approval_required"},{status:403});
  const patch={};
  for(const k of ["client","lead_id","service","location","status","notes"]) if(typeof d[k]==="string") patch[k]=d[k].trim();
  if(Object.values(patch).some(v=>v.length>5000))return Response.json({ok:false,error:"field_too_long"},{status:400});
  const {id}=await params;
  try{
    const item=await updateProject(id,patch);
    if(!item)return Response.json({ok:false,error:"project_not_found"},{status:404});
    return Response.json({ok:true,item,production_write:true,approval:"fresh"});
  }catch{return Response.json({ok:false,error:"project_update_failed"},{status:502})}
}

export async function DELETE(req,{params}){
  const blocked=requireSameOriginMutation(req); if(blocked)return blocked;
  const runtime=crmRuntime(); if(!runtime.projectProvisionEnabled)return Response.json({ok:false,error:"project_runtime_not_configured"},{status:503});
  if(!authorized(req))return Response.json({ok:false,error:"unauthorized"},{status:401});
  let d={}; try{d=await req.json()}catch{return Response.json({ok:false,error:"invalid_json"},{status:400})}
  if(!freshApproval(d))return Response.json({ok:false,error:"fresh_external_write_approval_required"},{status:403});
  const {id}=await params;
  try{
    const item=await deleteProject(id,{deleteFolder:d.deleteDriveFolder===true});
    if(!item)return Response.json({ok:false,error:"project_not_found"},{status:404});
    return Response.json({ok:true,deleted:id,drive_folder_deleted:d.deleteDriveFolder===true,production_write:true,approval:"fresh"});
  }catch{return Response.json({ok:false,error:"project_delete_failed"},{status:502})}
}
