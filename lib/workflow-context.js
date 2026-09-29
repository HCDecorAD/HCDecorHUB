import {getProjectById} from "./crm/google";
const clip=(v,n)=>String(v||"").trim().slice(0,n);
export async function resolveWorkflowContext({project_id="",project_context={}}={}){
 const supplied=project_context&&typeof project_context==="object"&&!Array.isArray(project_context)?project_context:{};
 const id=clip(project_id||supplied.project_id,120),explicit={project_id:id,project_name:clip(supplied.project_name,200),client:clip(supplied.client,200),channel:clip(supplied.channel,120)};
 if(explicit.project_name||explicit.client||explicit.channel||supplied.project_id)return {context:explicit,state:"resolved",source:"request",durable:false};
 if(!id)return {context:explicit,state:"not_requested",source:null,durable:false};
 try{const p=await getProjectById(id);if(!p)return {context:explicit,state:"unavailable",source:null,durable:false,reason:"project_not_found_or_read_source_not_configured"};return {context:{...explicit,client:clip(p.client,200),project_name:clip(p.service||p.client,200)},state:"resolved",source:"google-sheets",durable:true,project:{status:p.status,service:p.service,location:p.location,folder_url:p.folder_url}}}catch{return {context:explicit,state:"unavailable",source:null,durable:false,reason:"project_read_failed"}}
}
