/**
 * SuperAdmin login via GitHub OAuth (owner-only, default-deny).
 * Pure functions: env, fetch and clock are injected so tests never touch GitHub.
 * Session cookie value: base64url(JSON{login,exp}) + "." + hex(HMAC-SHA256(payload, SESSION_SECRET)).
 */
export const STATE_COOKIE="hc_oauth_state";
export const SESSION_COOKIE="hc_session";
const STATE_TTL=600,SESSION_TTL=8*3600;
const REQUIRED=["GITHUB_CLIENT_ID","GITHUB_CLIENT_SECRET","GITHUB_OWNER_LOGIN","HUB_PUBLIC_URL","SESSION_SECRET"];
const enc=new TextEncoder();
const hex=buf=>[...new Uint8Array(buf)].map(b=>b.toString(16).padStart(2,"0")).join("");
export const b64u=s=>btoa(String.fromCharCode(...enc.encode(s))).replace(/\+/g,"-").replace(/\//g,"_").replace(/=+$/,"");
const unb64u=s=>new TextDecoder().decode(Uint8Array.from(atob(s.replace(/-/g,"+").replace(/_/g,"/")+"=".repeat((4-s.length%4)%4)),c=>c.charCodeAt(0)));

export function authConfigured(env){return REQUIRED.every(k=>typeof env?.[k]==="string"&&env[k].trim()!=="")}
export function redirectUri(env){return String(env.HUB_PUBLIC_URL).trim().replace(/\/+$/,"")+"/api/auth/github/callback"}
// Constant-time string comparison (no early exit on the first differing byte).
export function timingSafeEqual(a,b){
 const x=enc.encode(String(a)),y=enc.encode(String(b));
 let d=x.length^y.length;
 for(let i=0;i<Math.max(x.length,y.length);i++)d|=(x[i]||0)^(y[i]||0);
 return d===0;
}
export async function sign(payload,secret){
 const key=await crypto.subtle.importKey("raw",enc.encode(secret),{name:"HMAC",hash:"SHA-256"},false,["sign"]);
 return hex(await crypto.subtle.sign("HMAC",key,enc.encode(payload)));
}
export async function createSessionValue(login,secret,now=Math.floor(Date.now()/1000)){
 const payload=b64u(JSON.stringify({login,exp:now+SESSION_TTL}));
 return payload+"."+await sign(payload,secret);
}
export async function verifySessionValue(value,secret,now=Math.floor(Date.now()/1000)){
 try{
  if(!secret||typeof value!=="string")return null;
  const parts=value.split(".");
  if(parts.length!==2||!parts[0]||!parts[1])return null;
  if(!timingSafeEqual(await sign(parts[0],secret),parts[1]))return null;
  const c=JSON.parse(unb64u(parts[0]));
  if(typeof c.login!=="string"||!c.login||typeof c.exp!=="number"||c.exp<=now)return null;
  return {login:c.login,exp:c.exp};
 }catch{return null}
}
// Provider: any failure -> unauthenticated (default-deny). Also re-checks login against the owner.
export async function resolveGithubSessionPrincipal({cookieValue,env,now}={}){
 const deny={authenticated:false,principal_id:null,role:"viewer",workspace_ids:[],store_ids:[],grants:[]};
 try{
  if(!authConfigured(env))return deny;
  const s=await verifySessionValue(cookieValue,env.SESSION_SECRET,now);
  if(!s||s.login.toLowerCase()!==env.GITHUB_OWNER_LOGIN.trim().toLowerCase())return deny;
  return {authenticated:true,principal_id:"github:"+s.login.toLowerCase(),role:"owner",workspace_ids:[],store_ids:[],grants:[],provider:"github"};
 }catch{return deny}
}
export const cookieHeader=(name,value,maxAge)=>name+"="+value+"; Path=/; HttpOnly; Secure; SameSite=Lax; Max-Age="+maxAge;
export const clearCookieHeader=name=>cookieHeader(name,"",0);
export function readCookie(header,name){
 for(const p of String(header||"").split(";")){
  const i=p.indexOf("=");
  if(i>0&&p.slice(0,i).trim()===name)return p.slice(i+1).trim();
 }
 return null;
}
const res=(status,body,headers=[])=>{
 const h=new Headers({"content-type":"text/plain; charset=utf-8","cache-control":"no-store"});
 headers.forEach(([k,v])=>h.append(k,v));
 return new Response(body,{status,headers:h});
};

export function handleLogin({env}){
 if(!authConfigured(env))return res(503,"auth not configured");
 const state=hex(crypto.getRandomValues(new Uint8Array(24)));
 const q=new URLSearchParams({client_id:env.GITHUB_CLIENT_ID,redirect_uri:redirectUri(env),scope:"read:user",state});
 return res(302,"",[["location","https://github.com/login/oauth/authorize?"+q],["set-cookie",cookieHeader(STATE_COOKIE,state,STATE_TTL)]]);
}
export async function handleCallback({request,env,fetchImpl=fetch,now}){
 if(!authConfigured(env))return res(503,"auth not configured");
 const clear=["set-cookie",clearCookieHeader(STATE_COOKIE)];
 try{
  const url=new URL(request.url);
  const state=url.searchParams.get("state"),code=url.searchParams.get("code");
  const saved=readCookie(request.headers.get("cookie"),STATE_COOKIE);
  if(!state||!saved||!code||!timingSafeEqual(state,saved))return res(400,"invalid state",[clear]);
  const tr=await fetchImpl("https://github.com/login/oauth/access_token",{method:"POST",headers:{accept:"application/json","content-type":"application/json"},body:JSON.stringify({client_id:env.GITHUB_CLIENT_ID,client_secret:env.GITHUB_CLIENT_SECRET,code,redirect_uri:redirectUri(env)})});
  const token=tr.ok?(await tr.json())?.access_token:null;
  if(typeof token!=="string"||!token)return res(403,"forbidden",[clear]);
  const ur=await fetchImpl("https://api.github.com/user",{headers:{authorization:"Bearer "+token,accept:"application/vnd.github+json","user-agent":"hcdecor-hub"}});
  const login=ur.ok?(await ur.json())?.login:null;
  if(typeof login!=="string"||login.toLowerCase()!==env.GITHUB_OWNER_LOGIN.trim().toLowerCase())return res(403,"forbidden",[clear]);
  const value=await createSessionValue(login,env.SESSION_SECRET,now);
  return res(302,"",[["location","/hub/sites"],clear,["set-cookie",cookieHeader(SESSION_COOKIE,value,SESSION_TTL)]]);
 }catch{return res(403,"forbidden",[clear])}
}
export function handleLogout(){
 return res(302,"",[["location","/"],["set-cookie",clearCookieHeader(SESSION_COOKIE)]]);
}
