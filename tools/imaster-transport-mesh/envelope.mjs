import crypto from "node:crypto";

export function envelope({missionId,checkpoint,action,payload,replyTo=null}){
 return {
  schema:"imaster-transport-envelope/v1",
  id:crypto.randomUUID(),
  createdAt:new Date().toISOString(),
  missionId,checkpoint,action,payload,replyTo
 };
}
export function validateEnvelope(x){
 if(!x||x.schema!=="imaster-transport-envelope/v1") return {ok:false,error:"bad_schema"};
 for(const k of ["id","missionId","action"]) if(!x[k]) return {ok:false,error:"missing_"+k};
 return {ok:true};
}
export function resultEnvelope(req,{ok,evidence=null,error=null,checkpoint=req.checkpoint}){
 return {
  schema:"imaster-transport-result/v1",
  requestId:req.id,missionId:req.missionId,checkpoint,ok:Boolean(ok),
  evidence,error,completedAt:new Date().toISOString()
 };
}
