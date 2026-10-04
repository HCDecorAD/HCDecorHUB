import crypto from "node:crypto";
import {ensureBusinessSchema} from "../../../../lib/business/google";
import {businessRuntime} from "../../../../lib/business/config";
import {requireSameOriginMutation} from "../../../../lib/request-guard";

const clean=v=>typeof v==="string"?v.trim():"";
function authorized(req){
  const expected=(process.env.HCDECOR_BUSINESS_API_TOKEN||process.env.HCDECOR_PROJECT_API_TOKEN||"").trim();
  const header=req.headers.get("authorization")||"";
  if(expected.length<32||!header.startsWith("Bearer "))return false;
  const supplied=header.slice(7);
  return supplied.length===expected.length&&crypto.timingSafeEqual(Buffer.from(supplied),Buffer.from(expected));
}
function freshApproval(d){
  if(d.confirmExternalWrite!==true)return false;
  const by=clean(d.approvedBy),source=clean(d.approvalSource),raw=clean(d.approvedAt);
  if(!by||!["wp_user","service_bridge"].includes(source))return false;
  const at=Date.parse(raw);
  return Number.isFinite(at)&&at>=Date.now()-15*60*1000&&at<=Date.now()+5*60*1000;
}
export async function POST(req){
  const blocked=requireSameOriginMutation(req);if(blocked)return blocked;
  const runtime=businessRuntime();
  if(!runtime.writeEnabled)return Response.json({ok:false,error:"business_runtime_not_configured"},{status:503});
  if(!authorized(req))return Response.json({ok:false,error:"unauthorized"},{status:401});
  let d={};try{d=await req.json()}catch{return Response.json({ok:false,error:"invalid_json"},{status:400})}
  if(!freshApproval(d))return Response.json({ok:false,error:"fresh_external_write_approval_required"},{status:403});
  try{return Response.json(await ensureBusinessSchema())}
  catch{return Response.json({ok:false,error:"business_schema_setup_failed"},{status:502})}
}
