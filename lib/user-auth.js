/**
 * SuperAdmin v2: ID/Pass for staff and guests, stored in Workers KV (HC_USERS), one key per user: user:<username>.
 * Only a PBKDF2-SHA256 hash is stored. Pure functions: kv and clock are injected; nothing here logs secrets.
 * Session cookie reuses hc_session (HMAC) with login "user:<username>"; that value can never equal a GitHub owner login,
 * so a user session is never accepted as owner. Authority (disabled, must_change_password, role, site_ids) is re-read from KV.
 */
import {sign,b64u,timingSafeEqual,verifySessionValue} from "./github-auth.js";

export const PBKDF2_ITERATIONS=210000;
export const MAX_FAILS=5,LOCK_SECONDS=900,SESSION_TTL=8*3600,MIN_PASSWORD=10;
export const ROLES=["staff","guest"];
const RESERVED="hcdecorad";
const enc=new TextEncoder();
const hex=buf=>[...new Uint8Array(buf)].map(b=>b.toString(16).padStart(2,"0")).join("");
const unhex=h=>Uint8Array.from(h.match(/../g)||[],x=>parseInt(x,16));
const userKey=u=>"user:"+u,failKey=u=>"fail:"+u;
const nowSec=()=>Math.floor(Date.now()/1000);

export function normalizeUsername(u){
 const n=String(u??"").trim().toLowerCase();
 return /^[a-z0-9][a-z0-9._-]{2,31}$/.test(n)&&n!==RESERVED?n:null;
}
export const normalizeSites=s=>Array.isArray(s)?[...new Set(s.map(x=>String(x).trim().toLowerCase()).filter(x=>/^[a-z0-9._-]{1,48}$/.test(x)))]:[];

export async function hashPassword(password,{salt,iterations=PBKDF2_ITERATIONS}={}){
 const s=salt||crypto.getRandomValues(new Uint8Array(16));
 const key=await crypto.subtle.importKey("raw",enc.encode(String(password)),"PBKDF2",false,["deriveBits"]);
 const bits=await crypto.subtle.deriveBits({name:"PBKDF2",hash:"SHA-256",salt:s,iterations},key,256);
 return {password_hash:hex(bits),salt:hex(s),iterations};
}
export async function verifyPassword(password,rec){
 try{
  if(!rec?.password_hash||!rec.salt||!(rec.iterations>=1))return false;
  const h=await hashPassword(password,{salt:unhex(rec.salt),iterations:rec.iterations});
  return timingSafeEqual(h.password_hash,rec.password_hash);
 }catch{return false}
}
export function generatePassword(){
 const a="ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789";
 return [...crypto.getRandomValues(new Uint8Array(16))].map(b=>a[b%a.length]).join("");
}
async function getUser(kv,u){const v=await kv.get(userKey(u));try{return v?JSON.parse(v):null}catch{return null}}
const putUser=(kv,rec)=>kv.put(userKey(rec.id),JSON.stringify(rec));
export const publicUser=r=>({id:r.id,role:r.role,site_ids:r.site_ids,must_change_password:!!r.must_change_password,disabled:!!r.disabled,updated_at:r.updated_at});

// ---- admin operations (caller must already have verified the owner) ----
export async function createUser(kv,{username,role,site_ids},now=nowSec()){
 const id=normalizeUsername(username);
 if(!id||!ROLES.includes(role))return {ok:false,error:"invalid"};
 if(await getUser(kv,id))return {ok:false,error:"exists"};
 const password=generatePassword();
 const rec={id,role,site_ids:normalizeSites(site_ids),...await hashPassword(password),must_change_password:true,disabled:false,updated_at:now};
 await putUser(kv,rec);
 return {ok:true,user:publicUser(rec),password}; // shown once, never stored in clear
}
export async function resetPassword(kv,username,now=nowSec()){
 const id=normalizeUsername(username),rec=id&&await getUser(kv,id);
 if(!rec)return {ok:false,error:"not_found"};
 const password=generatePassword();
 Object.assign(rec,await hashPassword(password),{must_change_password:true,updated_at:now});
 await putUser(kv,rec);
 await kv.delete(failKey(id));
 return {ok:true,user:publicUser(rec),password};
}
export async function updateUser(kv,username,{disabled,site_ids},now=nowSec()){
 const id=normalizeUsername(username),rec=id&&await getUser(kv,id);
 if(!rec)return {ok:false,error:"not_found"};
 if(typeof disabled==="boolean")rec.disabled=disabled;
 if(site_ids!==undefined)rec.site_ids=normalizeSites(site_ids);
 rec.updated_at=now;
 await putUser(kv,rec);
 return {ok:true,user:publicUser(rec)};
}
export async function listUsers(kv){
 const out=[];
 const l=await kv.list({prefix:"user:"});
 for(const k of l.keys||[]){const r=await getUser(kv,k.name.slice(5));if(r)out.push(publicUser(r))}
 return out;
}

// ---- login with lockout (5 wrong in a row -> locked until the fail counter's 15 min TTL expires) ----
let dummyRec;
export async function login(kv,username,password){
 const bad={ok:false,status:401,error:"invalid_credentials"};
 const id=normalizeUsername(username);
 if(!id||typeof password!=="string"||!password)return bad;
 let fails=0;
 try{fails=JSON.parse(await kv.get(failKey(id))||"{}").count||0}catch{}
 if(fails>=MAX_FAILS)return {ok:false,status:429,error:"invalid_credentials"}; // same generic message
 const rec=await getUser(kv,id);
 dummyRec??=await hashPassword("x");
 const pass=await verifyPassword(password,rec||dummyRec); // equal work for unknown users
 if(!rec||!pass||rec.disabled){
  await kv.put(failKey(id),JSON.stringify({count:fails+1}),{expirationTtl:LOCK_SECONDS});
  return bad;
 }
 await kv.delete(failKey(id));
 return {ok:true,user:rec,must_change_password:!!rec.must_change_password};
}
export async function changePassword(kv,username,current,next,now=nowSec()){
 const id=normalizeUsername(username),rec=id&&await getUser(kv,id);
 if(!rec||rec.disabled||!await verifyPassword(current,rec))return {ok:false,error:"invalid_credentials"};
 if(typeof next!=="string"||next.length<MIN_PASSWORD||next===current)return {ok:false,error:"weak_password"};
 Object.assign(rec,await hashPassword(next),{must_change_password:false,updated_at:now});
 await putUser(kv,rec);
 return {ok:true,user:rec};
}

// ---- session ----
export async function createUserSession(rec,secret,now=nowSec()){
 const payload=b64u(JSON.stringify({login:"user:"+rec.id,role:rec.role,site_ids:rec.site_ids,exp:now+SESSION_TTL}));
 return payload+"."+await sign(payload,secret);
}
// Default-deny principal. must_change_password=true means only the change-password page may be used.
export async function resolveUserPrincipal({cookieValue,env,kv,now}={}){
 const deny={authenticated:false,principal_id:null,role:"viewer",site_ids:[],must_change_password:false};
 try{
  if(!kv||!env?.SESSION_SECRET)return deny;
  const s=await verifySessionValue(cookieValue,env.SESSION_SECRET,now);
  if(!s||!s.login.startsWith("user:"))return deny;
  const rec=await getUser(kv,s.login.slice(5));
  if(!rec||rec.disabled)return deny;
  return {authenticated:true,principal_id:"user:"+rec.id,role:rec.role,site_ids:rec.site_ids||[],must_change_password:!!rec.must_change_password,provider:"kv"};
 }catch{return deny}
}
export const canEnter=p=>Boolean(p?.authenticated&&!p.must_change_password);
export const canAccessSite=(p,site)=>canEnter(p)&&p.site_ids.includes(String(site).toLowerCase());
