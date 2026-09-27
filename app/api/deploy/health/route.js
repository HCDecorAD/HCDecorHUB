const BASE=process.env.HCDECOR_PRODUCTION_URL||"https://hcdecorhub.vercel.app";
const paths=["/","/hub","/admin","/api/health"];
export async function GET(){
 const checks=await Promise.all(paths.map(async path=>{try{const r=await fetch(BASE+path,{cache:"no-store",redirect:"follow"});return {path,ok:r.ok,http:r.status}}catch{return {path,ok:false,http:0}}}));
 return Response.json({ok:checks.every(x=>x.ok),base:BASE,checkedAt:new Date().toISOString(),checks});
}