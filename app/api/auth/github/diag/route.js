import {timingSafeEqual} from "../../../../../lib/github-auth";
export const dynamic="force-dynamic";
const NAMES=["GITHUB_CLIENT_ID","GITHUB_CLIENT_SECRET","GITHUB_OWNER_LOGIN","HUB_PUBLIC_URL","SESSION_SECRET"];
const has=v=>typeof v==="string"&&v.trim()!=="";
const notFound=()=>new Response("Not Found",{status:404,headers:{"cache-control":"no-store"}});
// Names only, never values. Requires header x-hc-diag equal to SESSION_SECRET, else 404. Remove after use.
export async function GET(request){
 let bindings={},ctxOk=false;
 try{
  const {getCloudflareContext}=await import("@opennextjs/cloudflare");
  const ctx=await getCloudflareContext({async:true});
  bindings=ctx?.env||{};ctxOk=true;
 }catch{}
 const secret=bindings.SESSION_SECRET||process.env.SESSION_SECRET;
 const given=request.headers.get("x-hc-diag");
 if(!has(secret)||!has(given)||!timingSafeEqual(given,secret))return notFound();
 const present={},source={};
 for(const k of NAMES){
  const c=has(bindings[k]),p=has(process.env[k]);
  present[k]=c||p;
  source[k]=c?"getCloudflareContext":p?"process.env":null;
 }
 return Response.json({cloudflare_context:ctxOk,present,source},{headers:{"cache-control":"no-store"}});
}
