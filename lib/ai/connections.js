const clean=v=>String(v||"").trim();
const envFlag=(...names)=>names.some(n=>Boolean(clean(process.env[n])));
export function aiConnectionStatus(){
 const openai=envFlag("OPENAI_API_KEY");
 const gemini=envFlag("GEMINI_API_KEY","GOOGLE_API_KEY");
 const providers=[
  {id:"openai",configured:openai,auth:"API_KEY_SERVER",model:clean(process.env.OPENAI_MODEL)||null},
  {id:"gemini",configured:gemini,auth:"API_KEY_SERVER",model:clean(process.env.GEMINI_MODEL)||null}
 ];
 const bindings={content:clean(process.env.HC_AI_CONTENT_MODEL)||null,image:clean(process.env.HC_AI_IMAGE_MODEL)||null,video:clean(process.env.HC_AI_VIDEO_MODEL)||null,trend:clean(process.env.HC_AI_TREND_MODEL)||null};
 return {providers,bindings,secret_policy:"SERVER_ONLY",production_write:false};
}
async function probeOpenAI(){const key=clean(process.env.OPENAI_API_KEY);if(!key)return {id:"openai",configured:false,liveHealth:"not_checked"};try{const r=await fetch("https://api.openai.com/v1/models",{headers:{authorization:"Bearer "+key},cache:"no-store",signal:AbortSignal.timeout(8000)});return {id:"openai",configured:true,liveHealth:r.ok?"healthy":"degraded",http_status:r.status}}catch{return {id:"openai",configured:true,liveHealth:"unreachable"}}}
async function probeGemini(){const key=clean(process.env.GEMINI_API_KEY||process.env.GOOGLE_API_KEY);if(!key)return {id:"gemini",configured:false,liveHealth:"not_checked"};try{const r=await fetch("https://generativelanguage.googleapis.com/v1beta/models?key="+encodeURIComponent(key),{cache:"no-store",signal:AbortSignal.timeout(8000)});return {id:"gemini",configured:true,liveHealth:r.ok?"healthy":"degraded",http_status:r.status}}catch{return {id:"gemini",configured:true,liveHealth:"unreachable"}}}
export async function probeAIConnections(){return Promise.all([probeOpenAI(),probeGemini()])}
