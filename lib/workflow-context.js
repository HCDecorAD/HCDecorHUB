const clip=(v,n)=>String(v||"").trim().slice(0,n);
export function resolveWorkflowContext({project_id="",project_context={}}={}){
 const supplied=project_context&&typeof project_context==="object"&&!Array.isArray(project_context)?project_context:{};
 const context={project_id:clip(project_id||supplied.project_id,120),project_name:clip(supplied.project_name,200),client:clip(supplied.client,200),channel:clip(supplied.channel,120)};
 const hasExplicit=Boolean(context.project_name||context.client||context.channel||supplied.project_id);
 if(hasExplicit)return {context,state:"resolved",source:"request",durable:false};
 if(context.project_id)return {context,state:"unavailable",source:null,durable:false,reason:"project_read_source_not_configured"};
 return {context,state:"not_requested",source:null,durable:false};
}
