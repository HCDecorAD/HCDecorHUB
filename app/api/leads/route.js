import crypto from "node:crypto";
import {crmRuntime} from "../../../lib/crm/config";
import {appendLead,newLeadId} from "../../../lib/crm/google";
const clean=v=>typeof v==="string"?v.trim():"";
const SERVICES=new Set(["Bảng hiệu","Nội thất","Kiến trúc / 3D","Khác"]);
const buckets=new Map();
function clientKey(request){
  const raw=(request.headers.get("x-forwarded-for")||request.headers.get("x-real-ip")||"unknown").split(",")[0].trim();
  return crypto.createHash("sha256").update(raw+"|hcdecor-leads").digest("hex").slice(0,24);
}
function rateLimited(request){
  const now=Date.now(),key=clientKey(request),windowMs=10*60*1000,limit=5;
  for(const [k,v] of buckets){if(v.reset<=now)buckets.delete(k)}
  const row=buckets.get(key);
  if(!row||row.reset<=now){buckets.set(key,{count:1,reset:now+windowMs});return false}
  row.count++; return row.count>limit;
}
export async function GET(){const r=crmRuntime();return Response.json({ok:r.writeEnabled,storage:r.configured?"google-sheets":null,writeEnabled:r.writeEnabled,projectProvisionEnabled:r.projectProvisionEnabled})}
export async function POST(request){
  const r=crmRuntime();if(!r.writeEnabled)return Response.json({ok:false,error:"crm_write_not_configured"},{status:503});
  if(rateLimited(request))return Response.json({ok:false,error:"rate_limited"},{status:429,headers:{"Retry-After":"600"}});
  let d={};try{const type=request.headers.get("content-type")||"";if(type.includes("form")){const f=await request.formData();d=Object.fromEntries(f.entries())}else d=await request.json()}catch{return Response.json({ok:false,error:"invalid_request"},{status:400})}
  if(clean(d.website))return Response.json({ok:true,status:"New"},{status:201});
  const name=clean(d.name),phone=clean(d.phone),brief=clean(d.brief),service=clean(d.service),location=clean(d.location),notes=clean(d.notes);
  if(!name||!phone||!brief)return Response.json({ok:false,error:"required_fields"},{status:400});
  if(name.length>160||phone.length>40||brief.length>5000||service.length>80||location.length>240||notes.length>1000)return Response.json({ok:false,error:"field_too_long"},{status:400});
  if(service&&!SERVICES.has(service))return Response.json({ok:false,error:"invalid_service"},{status:400});
  const lead={id:newLeadId(),createdAt:new Date().toISOString(),name,phone,brief,service,location,notes};
  try{await appendLead(lead);return Response.json({ok:true,leadId:lead.id,status:"New"},{status:201})}catch{return Response.json({ok:false,error:"lead_write_failed"},{status:502})}
}
