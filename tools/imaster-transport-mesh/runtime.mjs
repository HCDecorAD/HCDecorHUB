import http from "node:http";
import fs from "node:fs";
import path from "node:path";
import {loadJson,saveJson,selectTransport,markHealthy,failover,DEFAULT_STATE} from "./router.mjs";

const ROOT=process.env.HCDECOR_REPO || path.resolve(import.meta.dirname,"../..");
const CONFIG=process.env.IMASTER_MESH_CONFIG || path.join(ROOT,"config","imaster-transport-mesh.json");
const STATE=process.env.IMASTER_MESH_STATE || path.join(process.env.HCDECOR_ROOT || "D:/HCDecorHUB","TransportMesh","state.json");
const HOST=process.env.IMASTER_MESH_HOST || "127.0.0.1";
const PORT=Number(process.env.IMASTER_MESH_PORT || 8771);
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
function json(res,code,obj){res.writeHead(code,{"content-type":"application/json"});res.end(JSON.stringify(obj));}
const server=http.createServer(async(req,res)=>{
 if(req.method==="GET"&&req.url==="/health") return json(res,200,{ok:true,id:"IMASTER_TRANSPORT_MESH",active:state.active});
 if(req.method==="GET"&&req.url==="/status") return json(res,200,state);
 if(req.method==="POST"&&req.url==="/scan") return json(res,200,await scan());
 return json(res,404,{ok:false,error:"not_found"});
});
await scan();
server.listen(PORT,HOST,()=>console.log(JSON.stringify({ok:true,id:"IMASTER_TRANSPORT_MESH",listen:`${HOST}:${PORT}`,active:state.active})));
