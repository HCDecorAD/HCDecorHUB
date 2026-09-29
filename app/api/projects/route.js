import crypto from "node:crypto";
import {crmRuntime} from "../../../lib/crm/config";
import {appendProject,createProjectFolder,deleteDriveFile,newProjectId} from "../../../lib/crm/google";
import {requireSameOriginMutation} from "../../../lib/request-guard";
const clean=v=>typeof v==="string"?v.trim():"";
const buckets=new Map();
function clientKey(request){const raw=(request.headers.get("x-forwarded-for")||request.headers.get("x-real-ip")||"unknown").split(",")[0].trim().slice(0,256);return crypto.createHash("sha256").update(raw+"|hcdecor-projects").digest("hex").slice(0,24)}
function rateLimited(request){const now=Date.now(),key=clientKey(request),windowMs=10*60*1000,limit=10;for(const [k,v] of buckets){if(v.reset<=now)buckets.delete(k)}const row=buckets.get(key);if(!row&&buckets.size>=5000)return true;if(!row||row.reset<=now){buckets.set(key,{count:1,reset:now+windowMs});return false}row.count++;return row.count>limit}
function freshApproval(d){if(d.confirmExternalWrite!==true)return false;const by=clean(d.approvedBy),source=clean(d.approvalSource),raw=clean(d.approvedAt);if(!by||by.length>200||raw.length>64||!["wp_user","service_bridge"].includes(source))return false;const at=Date.parse(raw);return Number.isFinite(at)&&at>=Date.now()-15*60*1000&&at<=Date.now()+5*60*1000}
function authorized(req){const expected=(process.env.HCDECOR_PROJECT_API_TOKEN||"").trim(),header=req.headers.get("authorization")||"";if(expected.length<32||expected.length>512||!header.startsWith("Bearer "))return false;const supplied=header.slice(7);if(supplied.length!==expected.length)return false;return crypto.timingSafeEqual(Buffer.from(supplied),Buffer.from(expected))}
export async function POST(req){
 const blocked=requireSameOriginMutation(req);if(blocked)return blocked;
 const r=crmRuntime();if(!r.projectProvisionEnabled)return Response.json({ok:false,error:"project_runtime_not_configured"},{status:503});
 if(!authorized(req))return Response.json({ok:false,error:"unauthorized"},{status:401});
 if(rateLimited(req))return Response.json({ok:false,error:"rate_limited"},{status:429,headers:{"Retry-After":"600"}});
 let d={};try{d=await req.json()}catch{return Response.json({ok:false,error:"invalid_json"},{status:400})}
 if(!freshApproval(d))return Response.json({ok:false,error:"fresh_external_write_approval_required"},{status:403});
 const client=clean(d.client);if(!client)return Response.json({ok:false,error:"client_required"},{status:400});
 const leadId=clean(d.leadId),service=clean(d.service),location=clean(d.location),status=clean(d.status),notes=clean(d.notes);
 if(client.length>160||leadId.length>200||service.length>100||location.length>500||status.length>50||notes.length>5000)return Response.json({ok:false,error:"field_too_long"},{status:400});
 const projectId=newProjectId(),createdAt=new Date().toISOString();let folder=null;
 try{folder=await createProjectFolder(projectId);await appendProject({projectId,createdAt,folderId:folder.id,folderUrl:folder.folderUrl,client,leadId,service,location,status:status||"Active",notes});return Response.json({ok:true,projectId,folderId:folder.id,folderUrl:folder.folderUrl},{status:201})}
 catch{if(folder?.id){try{await deleteDriveFile(folder.id)}catch{}}return Response.json({ok:false,error:"project_create_failed"},{status:502})}
}
