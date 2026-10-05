import fs from "node:fs";
import path from "node:path";
import {GitHubIssuesCarrier} from "./carriers/github-issues.mjs";
import {FileQueueAdapter} from "./adapters/file-queue.mjs";
import {validateEnvelope} from "./envelope.mjs";

const repo=process.env.IMASTER_CARRIER_GITHUB_REPO||"";
const token=process.env.IMASTER_CARRIER_GITHUB_TOKEN||process.env.GITHUB_TOKEN||"";
const interval=Number(process.env.IMASTER_CARRIER_POLL_MS||5000);
const queue=new FileQueueAdapter(process.env.IMASTER_MESH_QUEUE||"D:/HCDecorHUB/TransportMesh/queue");
const carrier=new GitHubIssuesCarrier({repo,token,label:process.env.IMASTER_CARRIER_LABEL||"imaster-envelope"});
const seen=new Set();

console.log(JSON.stringify({ok:true,id:"IMASTER_CARRIER_PUMP",carrier:carrier.id,repo,pollMs:interval}));
for(;;){
 try{
  const health=await carrier.probe();
  if(health.health==="healthy"){
   for(const item of await carrier.pollRequests()){
    if(seen.has(item.carrierId))continue;
    const v=validateEnvelope(item.envelope);
    if(!v.ok){seen.add(item.carrierId);continue}
    queue.enqueue(item.envelope);seen.add(item.carrierId);
    const out=path.join(queue.root,"outbox",item.envelope.id+".json");
    const deadline=Date.now()+30000;
    while(Date.now()<deadline&&!fs.existsSync(out))await new Promise(r=>setTimeout(r,500));
    if(fs.existsSync(out)){
      const result=JSON.parse(fs.readFileSync(out,"utf8"));
      await carrier.publishResult(item.carrierId,result);await carrier.ack(item.carrierId);
      fs.rmSync(out,{force:true});
    }else seen.delete(item.carrierId);
   }
  }
 }catch(e){console.error(JSON.stringify({ok:false,error:String(e.message||e)}))}
 await new Promise(r=>setTimeout(r,interval));
}
