/**
 * Identity/RBAC boundary.
 *
 * The configuration defines roles and default-deny policy. Runtime authentication
 * is intentionally not inferred from unverified headers, local state, or environment flags.
 *
 * Bound provider: Cloudflare Access (resolveCloudflareAccessPrincipal). It trusts the
 * Cf-Access-Jwt-Assertion header only after RS256 signature verification against the team
 * JWKS, plus aud/iss/exp checks and an email allow-list from CF_ACCESS_OWNER_EMAILS.
 * Anything else returns unauthenticatedPrincipal() (default-deny). A valid token yields
 * role "owner" for read-only SuperAdmin view only; it never grants production mutation
 * (productionMutationAuthorized still requires production_approval and authenticated_executor).
 * The provider is code only: it is not yet called from the app until Access is configured.
 */
const b64uBytes=s=>Uint8Array.from(atob(s.replace(/-/g,"+").replace(/_/g,"/")+"=".repeat((4-s.length%4)%4)),c=>c.charCodeAt(0));
const b64uJson=s=>JSON.parse(new TextDecoder().decode(b64uBytes(s)));
const TEAM_RE=/^[a-z0-9]([a-z0-9.-]*[a-z0-9])?$/i;
export function cloudflareAccessCertsUrl(team){return "https://"+team+"/cdn-cgi/access/certs"}
async function fetchJwks(team){
 const r=await fetch(cloudflareAccessCertsUrl(team));
 if(!r.ok)throw Error("jwks");
 return r.json();
}
// opts: {headers, env:{CF_ACCESS_TEAM_DOMAIN,CF_ACCESS_AUD,CF_ACCESS_OWNER_EMAILS}, jwks?, now?(seconds)}
export async function resolveCloudflareAccessPrincipal({headers,env,jwks,now}={}){
 try{
  const team=String(env?.CF_ACCESS_TEAM_DOMAIN||"").trim();
  const aud=String(env?.CF_ACCESS_AUD||"").trim();
  const owners=String(env?.CF_ACCESS_OWNER_EMAILS||"").split(",").map(x=>x.trim().toLowerCase()).filter(Boolean);
  if(!team||!aud||!owners.length||!TEAM_RE.test(team))return unauthenticatedPrincipal();
  const token=typeof headers?.get==="function"?headers.get("Cf-Access-Jwt-Assertion"):(headers?.["cf-access-jwt-assertion"]||headers?.["Cf-Access-Jwt-Assertion"]);
  if(!token||typeof token!=="string")return unauthenticatedPrincipal();
  const parts=token.split(".");
  if(parts.length!==3||parts.some(p=>!p))return unauthenticatedPrincipal();
  const header=b64uJson(parts[0]);
  if(header.alg!=="RS256"||!header.kid)return unauthenticatedPrincipal();
  const keys=(jwks||await fetchJwks(team))?.keys;
  const jwk=Array.isArray(keys)?keys.find(k=>k.kid===header.kid&&k.kty==="RSA"):null;
  if(!jwk)return unauthenticatedPrincipal();
  const key=await crypto.subtle.importKey("jwk",{kty:jwk.kty,n:jwk.n,e:jwk.e,alg:"RS256",ext:true},{name:"RSASSA-PKCS1-v1_5",hash:"SHA-256"},false,["verify"]);
  const ok=await crypto.subtle.verify("RSASSA-PKCS1-v1_5",key,b64uBytes(parts[2]),new TextEncoder().encode(parts[0]+"."+parts[1]));
  if(!ok)return unauthenticatedPrincipal();
  const claims=b64uJson(parts[1]);
  const t=typeof now==="number"?now:Math.floor(Date.now()/1000);
  if(typeof claims.exp!=="number"||claims.exp<=t)return unauthenticatedPrincipal();
  if(typeof claims.nbf==="number"&&claims.nbf>t)return unauthenticatedPrincipal();
  if(claims.iss!=="https://"+team)return unauthenticatedPrincipal();
  const auds=Array.isArray(claims.aud)?claims.aud:[claims.aud];
  if(!auds.includes(aud))return unauthenticatedPrincipal();
  const email=typeof claims.email==="string"?claims.email.trim().toLowerCase():"";
  if(!email||!owners.includes(email))return unauthenticatedPrincipal();
  return {authenticated:true,principal_id:"cf-access:"+(claims.sub||email),email,role:"owner",workspace_ids:[],store_ids:[],grants:[],provider:"cloudflare-access"};
 }catch{
  return unauthenticatedPrincipal();
 }
}
export function unauthenticatedPrincipal(){
 return {authenticated:false,principal_id:null,role:"viewer",workspace_ids:[],store_ids:[],grants:[]};
}
export function hasExplicitGrant(principal,grant){
 return Boolean(principal?.authenticated===true&&Array.isArray(principal.grants)&&principal.grants.includes(grant));
}
export function productionMutationAuthorized(principal){
 return Boolean(principal?.authenticated===true&&principal?.production_approval===true&&principal?.authenticated_executor===true);
}
export function identityRuntimeStatus(){
 return {provider:null,available:false,defaultDeny:true,productionAuthority:false};
}
