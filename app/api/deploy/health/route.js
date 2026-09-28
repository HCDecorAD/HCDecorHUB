const BASE="https://hcdecorhub.com";
export async function GET(){
 const checkedAt=new Date().toISOString();
 try{
  const r=await fetch(BASE,{method:"HEAD",cache:"no-store",redirect:"follow"});
  return Response.json({state:r.ok?"reachable":"http-error",reachable:r.ok,http:r.status,base:BASE,checkedAt,source:"live-http-probe",productionAuthority:"WordPress"},{status:r.ok?200:502});
 }catch{
  return Response.json({state:"unreachable",reachable:false,base:BASE,checkedAt,source:"live-http-probe",productionAuthority:"WordPress"},{status:502});
 }
}
