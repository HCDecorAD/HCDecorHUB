import {FileQueueAdapter} from "./adapters/file-queue.mjs";

const q=new FileQueueAdapter(process.env.IMASTER_MESH_QUEUE||"D:/HCDecorHUB/TransportMesh/queue");
const GATEWAY=process.env.HC_GATEWAY_BASE||"http://127.0.0.1:8770";
const TOKEN=process.env.HC_GATEWAY_TOKEN||"";
const allowed=new Set(["gateway.health","zeus.status","zeus.tabs","zeus.send","zeus.create","zeus.rename"]);

async function execute(req){
 if(!allowed.has(req.action)) return {ok:false,error:"action_not_allowlisted"};
 if(req.action==="gateway.health"){
  const r=await fetch(GATEWAY+"/health",{signal:AbortSignal.timeout(3000)});
  return {ok:r.ok,evidence:await r.json()};
 }
 const map={"zeus.status":["GET","/status"],"zeus.tabs":["GET","/tabs"],"zeus.send":["POST","/zeus/send"],"zeus.create":["POST","/zeus/create"],"zeus.rename":["POST","/zeus/rename"]};
 const [method,url]=map[req.action];
 const headers={"content-type":"application/json"};if(TOKEN)headers["x-hc-gateway"]=TOKEN;
 const opt={method,headers,signal:AbortSignal.timeout(10000)};
 if(method==="POST")opt.body=JSON.stringify(req.payload||{});
 const r=await fetch(GATEWAY+url,opt);const text=await r.text();
 let evidence;try{evidence=JSON.parse(text)}catch{evidence={text}};
 return {ok:r.ok,evidence,error:r.ok?null:"gateway_http_"+r.status};
}
console.log(JSON.stringify({ok:true,id:"IMASTER_MESH_WORKER",queue:q.root,allowed:[...allowed]}));
for(;;){
 const claim=q.claim();
 if(!claim){await new Promise(r=>setTimeout(r,1000));continue}
 try{q.complete(claim,await execute(claim.request))}
 catch(e){q.complete(claim,{ok:false,error:String(e.message||e)})}
}
