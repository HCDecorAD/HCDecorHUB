import crypto from "node:crypto";
import {readFileSync} from "node:fs";

const clean=v=>String(v||"").trim();
const envFlag=(...names)=>names.some(n=>Boolean(clean(process.env[n])));
const CF_MODELS={
 content:"@cf/meta/llama-3.2-3b-instruct",
 image:"@cf/black-forest-labs/flux-1-schnell",
 trend:"@cf/meta/llama-3.2-3b-instruct"
};
const VERTEX_MODELS={
 content:"gemini-2.5-flash",
 image:"gemini-2.5-flash-image",
 video:"veo-3.1-generate-001",
 trend:"gemini-2.5-flash"
};

function vertexCredentials(){
 let raw=process.env.GOOGLE_SERVICE_ACCOUNT_JSON;
 const p=clean(process.env.GOOGLE_APPLICATION_CREDENTIALS);
 if(!raw&&p)try{raw=readFileSync(p,"utf8")}catch{}
 if(!raw)return null;
 try{const x=JSON.parse(raw);return x?.client_email&&x?.private_key&&x?.project_id?x:null}catch{return null}
}
async function vertexToken(){
 const c=vertexCredentials();if(!c)throw new Error("vertex_credentials_missing");
 const enc=v=>Buffer.from(v).toString("base64url"),now=Math.floor(Date.now()/1000);
 const h=enc(JSON.stringify({alg:"RS256",typ:"JWT"}));
 const b=enc(JSON.stringify({iss:c.client_email,scope:"https://www.googleapis.com/auth/cloud-platform",aud:"https://oauth2.googleapis.com/token",iat:now,exp:now+1800}));
 const input=h+"."+b,s=crypto.createSign("RSA-SHA256");s.update(input);s.end();
 const assertion=input+"."+s.sign(c.private_key).toString("base64url");
 const r=await fetch("https://oauth2.googleapis.com/token",{method:"POST",headers:{"content-type":"application/x-www-form-urlencoded"},body:new URLSearchParams({grant_type:"urn:ietf:params:oauth:grant-type:jwt-bearer",assertion}),cache:"no-store",signal:AbortSignal.timeout(8000)});
 if(!r.ok)throw new Error("vertex_oauth_"+r.status);
 return {token:(await r.json()).access_token,project_id:c.project_id};
}
function reasonFromError(j={}){for(const d of j.error?.details||[]){if(d.reason)return d.reason}return j.error?.status||null}
async function vertexValidate(token,project,model,method){
 const u=`https://aiplatform.googleapis.com/v1/projects/${encodeURIComponent(project)}/locations/global/publishers/google/models/${model}:${method}`;
 try{
  const r=await fetch(u,{method:"POST",headers:{authorization:"Bearer "+token,"content-type":"application/json"},body:"{}",cache:"no-store",signal:AbortSignal.timeout(8000)});
  const j=await r.json().catch(()=>({}));
  const endpointReached=r.ok||r.status===400||r.status===422;
  return {model,http_status:r.status,endpoint_reached:endpointReached,reason:endpointReached?null:reasonFromError(j)};
 }catch{return {model,http_status:null,endpoint_reached:false,reason:"UNREACHABLE"}}
}

export function aiConnectionStatus(){
 const openai=envFlag("OPENAI_API_KEY"),gemini=envFlag("GEMINI_API_KEY","GOOGLE_API_KEY"),vertex=Boolean(vertexCredentials());
 const providers=[
  {id:"cloudflare-workers-ai",configured:true,auth:"CLOUDFLARE_BINDING",model:CF_MODELS.content},
  {id:"openai",configured:openai,auth:"API_KEY_SERVER",model:clean(process.env.OPENAI_MODEL)||null},
  {id:"gemini",configured:gemini,auth:"API_KEY_SERVER",model:clean(process.env.GEMINI_MODEL)||null},
  {id:"vertex",configured:vertex,auth:"GOOGLE_ADC_SERVER",model:VERTEX_MODELS.content}
 ];
 const defaults={
  content:"cloudflare-workers-ai:"+CF_MODELS.content,
  image:"cloudflare-workers-ai:"+CF_MODELS.image,
  video:"external-required",
  trend:"cloudflare-workers-ai:"+CF_MODELS.trend
 };
 const bindings={
  content:clean(process.env.HC_AI_CONTENT_MODEL)||defaults.content,
  image:clean(process.env.HC_AI_IMAGE_MODEL)||defaults.image,
  video:clean(process.env.HC_AI_VIDEO_MODEL)||defaults.video,
  trend:clean(process.env.HC_AI_TREND_MODEL)||defaults.trend
 };
 return {
  providers,
  bindings,
  binding_status:{content:"BOUND",image:"BOUND",video:"EXTERNAL_REQUIRED",trend:"BOUND"},
  recommended_bindings:defaults,
  secret_policy:"SERVER_ONLY",
  production_write:false
 };
}
async function probeOpenAI(){const key=clean(process.env.OPENAI_API_KEY);if(!key)return {id:"openai",configured:false,liveHealth:"not_checked"};try{const r=await fetch("https://api.openai.com/v1/models",{headers:{authorization:"Bearer "+key},cache:"no-store",signal:AbortSignal.timeout(8000)});return {id:"openai",configured:true,liveHealth:r.ok?"healthy":"degraded",http_status:r.status}}catch{return {id:"openai",configured:true,liveHealth:"unreachable"}}}
async function probeGemini(){const key=clean(process.env.GEMINI_API_KEY||process.env.GOOGLE_API_KEY);if(!key)return {id:"gemini",configured:false,liveHealth:"not_checked"};try{const r=await fetch("https://generativelanguage.googleapis.com/v1beta/models?key="+encodeURIComponent(key),{cache:"no-store",signal:AbortSignal.timeout(8000)});return {id:"gemini",configured:true,liveHealth:r.ok?"healthy":"degraded",http_status:r.status}}catch{return {id:"gemini",configured:true,liveHealth:"unreachable"}}}
async function probeVertex(){if(!vertexCredentials())return {id:"vertex",configured:false,liveHealth:"not_checked"};try{const {token,project_id}=await vertexToken();const capabilities={content:await vertexValidate(token,project_id,VERTEX_MODELS.content,"generateContent"),image:await vertexValidate(token,project_id,VERTEX_MODELS.image,"generateContent"),video:await vertexValidate(token,project_id,VERTEX_MODELS.video,"predictLongRunning")};const vals=Object.values(capabilities);const healthy=vals.every(x=>x.endpoint_reached);const reason=vals.find(x=>!x.endpoint_reached)?.reason||null;return {id:"vertex",configured:true,liveHealth:healthy?"healthy":reason==="SERVICE_DISABLED"?"service_disabled":"degraded",oauth:true,capabilities}}catch(e){return {id:"vertex",configured:true,liveHealth:"unreachable",oauth:false,reason:String(e?.message||e)}}}
async function cloudflareBinding(){
 try{
  const {getCloudflareContext}=await import("@opennextjs/cloudflare");
  const {env}=await getCloudflareContext({async:true});
  return env?.AI||null;
 }catch{return null}
}
async function probeCloudflare({deep=false}={}){
 const ai=await cloudflareBinding();
 if(!ai)return {id:"cloudflare-workers-ai",configured:true,liveHealth:"context_unavailable",capabilities:{video:{supported:false,route:"external-required"}}};
 try{
  const text=await ai.run(CF_MODELS.content,{messages:[{role:"user",content:"Reply with a short health token for HCDecor HUB."}],max_tokens:24});
  const textOk=Boolean(clean(text?.response||text?.result?.response||""));
  let imageOk=null;
  if(deep){
   const image=await ai.run(CF_MODELS.image,{prompt:"simple blue square icon on white background"});
   imageOk=Boolean(image);
  }
  const healthy=textOk&&(deep?imageOk:true);
  return {
   id:"cloudflare-workers-ai",
   configured:true,
   liveHealth:healthy?"healthy":"degraded",
   capabilities:{
    content:{model:CF_MODELS.content,live:textOk},
    image:{model:CF_MODELS.image,live:deep?Boolean(imageOk):"not_checked"},
    trend:{model:CF_MODELS.trend,live:textOk},
    video:{supported:false,route:"external-required"}
   }
  };
 }catch(e){return {id:"cloudflare-workers-ai",configured:true,liveHealth:"degraded",reason:String(e?.message||e),capabilities:{video:{supported:false,route:"external-required"}}}}
}
export async function probeAIConnections(options={}){return Promise.all([probeCloudflare(options),probeOpenAI(),probeGemini(),probeVertex()])}
