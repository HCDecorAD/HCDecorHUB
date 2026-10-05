import fs from "node:fs";
import path from "node:path";
import {validateEnvelope,resultEnvelope} from "../envelope.mjs";

export class FileQueueAdapter {
 constructor(root="D:/HCDecorHUB/TransportMesh/queue"){
  this.id="file-queue";this.root=root;
  for(const d of ["inbox","processing","outbox","failed"]) fs.mkdirSync(path.join(root,d),{recursive:true});
 }
 async probe(){try{fs.accessSync(this.root,fs.constants.R_OK|fs.constants.W_OK);return {health:"healthy",root:this.root}}catch(e){return {health:"down",error:e.message}}}
 enqueue(req){const v=validateEnvelope(req);if(!v.ok)throw new Error(v.error);const f=path.join(this.root,"inbox",req.id+".json");fs.writeFileSync(f,JSON.stringify(req,null,2));return f}
 claim(){
  const dir=path.join(this.root,"inbox");const f=fs.readdirSync(dir).filter(x=>x.endsWith(".json")).sort()[0];if(!f)return null;
  const from=path.join(dir,f),to=path.join(this.root,"processing",f);fs.renameSync(from,to);
  return {file:to,request:JSON.parse(fs.readFileSync(to,"utf8"))};
 }
 complete(claim,result){
  const out=resultEnvelope(claim.request,result);
  fs.writeFileSync(path.join(this.root,"outbox",claim.request.id+".json"),JSON.stringify(out,null,2));
  fs.rmSync(claim.file,{force:true});return out;
 }
 fail(claim,error){
  fs.renameSync(claim.file,path.join(this.root,"failed",path.basename(claim.file)));
  return resultEnvelope(claim.request,{ok:false,error:String(error)});
 }
}
