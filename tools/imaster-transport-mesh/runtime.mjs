import http from "node:http";
import fs from "node:fs";
import path from "node:path";
import {loadJson,saveJson,selectTransport,markHealthy,failover,DEFAULT_STATE} from "./router.mjs";

const ROOT=process.env.HCDECOR_REPO || path.resolve(import.meta.dirname,"../..");
const CONFIG=process.env.IMASTER_MESH_CONFIG || path.join(ROOT,"config","imaster-transport-mesh.json");
const STATE=process.env.IMASTER_MESH_STATE || path.join(process.env.HCDECOR_ROOT || "D:/HCDecorHUB","TransportMesh","state.json");
const HOST=process.env.IMASTER_MESH_HOST || "127.0.0.1";
const PORT=Number(process.env.IMASTER_MESH_PORT || 8771);
const TOKEN=process.env.IMASTER_MESH_TOKEN || process.env.HC_GATEWAY_TOKEN || "";
const GATEWAY=process.env.HC_GATEWAY_BASE || "http://127.0.0.1:8770";
const MUTATIONS=new Map([["/control/send","/zeus/send"],["/control/create","/zeus/create"],["/control/rename","/zeus/rename"]]);
const config=loadJson(CONFIG,{adapters:[]});
let state=loadJson(STATE,DEFAULT_STATE);

async function probeHttp(url,headers={}){
 try{const r=await fetch(url,{headers,signal:AbortSignal.timeout(1800)});return {ok:r.ok,status:r.status};}
 catch(e){return {ok:false,error:String(e.message||e)}}
}
async function probe(a){
 if(a.id==="local-direct") return probeHttp(process.env.HC_GATEWAY_HEALTH || "http://127.0.0.1:8770/health");
 if(a.id==="native-connector") return {ok:false,unavailable:true,reason:"connector-owned-by-chat-runtime"};
 if(a.id==="hcdr") return {ok:false,unavailable:true,reason:"relay-probe-requires-adapter-credentials"};
 if(a.id==="rdc") return {ok:false,unavailable:true,reason:"rescue-connector-owned-by-chat-runtime"};
 if(a.id==="authenticated-tunnel") return {ok:false,unavailable:true,reason:"optional-not-configured"};
 return {ok:false,unavailable:true,reason:"unknown-adapter"};
}
async function scan(){
 for(const a of config.adapters||[]){
  const r=await probe(a);
  if(r.ok) state=markHealthy(config,state,a.id,r);
  else state.adapters={...(state.adapters||{}),[a.id]:{health:r.unavailable?"unavailable":"down",evidence:r,checkedAt:new Date().toISOString()}};
 }
 state.active=selectTransport(config,state)?.id||null;state.updatedAt=new Date().toISOString();saveJson(STATE,state);return state;
}
function json(res,code,obj){res.writeHead(code,{"content-type":"application/json","cache-control":"no-store"});res.end(JSON.stringify(obj));}
async function body(req){let s="";for await(const c of req){s+=c;if(s.length>262144)throw Error("BODY_TOO_LARGE")}return s?JSON.parse(s):{}}
async function gateway(path,method="GET",payload){
 const headers={"content-type":"application/json"}; if(method!=="GET"){if(!TOKEN)throw Error("MESH_TOKEN_REQUIRED");headers["x-hc-gateway"]=TOKEN}
 const r=await fetch(GATEWAY+path,{method,headers,body:payload?JSON.stringify(payload):undefined,signal:AbortSignal.timeout(5000)});
 const t=await r.text();let data;try{data=JSON.parse(t)}catch{data={raw:t}}return {ok:r.ok,status:r.status,data};
}
const server=http.createServer(async(req,res)=>{
 if(req.method==="GET"&&req.url==="/health") return json(res,200,{ok:true,id:"IMASTER_TRANSPORT_MESH",active:state.active});
 if(req.method==="GET"&&req.url==="/status") return json(res,200,state);
 if(req.method==="POST"&&req.url==="/scan") return json(res,200,await scan());
 if(req.method==="GET"&&req.url==="/control/status"){const r=await gateway("/status");return json(res,r.status,{ok:r.ok,data:r.data});}
 if(req.method==="GET"&&req.url==="/control/tabs"){const r=await gateway("/tabs");return json(res,r.status,{ok:r.ok,data:r.data});}
 if(req.method==="POST"&&MUTATIONS.has(req.url)){
   if(!TOKEN||req.headers["x-imaster-mesh"]!==TOKEN)return json(res,401,{ok:false,error:"UNAUTHORIZED"});
   const p=await body(req); if(req.url==="/control/send"){ const cid=String(p.cid||"").trim(), sourceCid=String(p.sourceCid||cid).trim(), ownerCid=String(p.ownerCid||cid).trim(), targetCid=String(p.targetCid||cid).trim(); if(!cid||!sourceCid||!ownerCid||!targetCid)return json(res,400,{ok:false,error:"CID_REQUIRED"}); if(sourceCid!==ownerCid||targetCid!==ownerCid||cid!==targetCid)return json(res,409,{ok:false,error:"OWNER_LOCK_CID_MISMATCH",sourceCid,ownerCid,targetCid,cid}); } const r=await gateway(MUTATIONS.get(req.url),"POST",p);return json(res,r.status,{ok:r.ok,data:r.data});
 }
 return json(res,404,{ok:false,error:"not_found"});
});
await scan();
server.listen(PORT,HOST,()=>console.log(JSON.stringify({ok:true,id:"IMASTER_TRANSPORT_MESH",listen:`${HOST}:${PORT}`,active:state.active})));
