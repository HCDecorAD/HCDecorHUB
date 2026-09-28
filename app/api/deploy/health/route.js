const normalizeBase=value=>String(value||"").trim().replace(/\/$/,"");
export async function GET(){
 const checkedAt=new Date().toISOString();
 const base=normalizeBase(process.env.HCDECOR_WP_BASE_URL);
 if(!base) return Response.json({state:"not-configured",reachable:false,base:null,checkedAt,source:"runtime-config",productionAuthority:"WordPress"},{status:503});
 try{
  const r=await fetch(base,{method:"HEAD",cache:"no-store",redirect:"follow"});
  return Response.json({state:r.ok?"reachable":"http-error",reachable:r.ok,http:r.status,base,checkedAt,source:"live-http-probe",productionAuthority:"WordPress"},{status:r.ok?200:502});
 }catch{
  return Response.json({state:"unreachable",reachable:false,base,checkedAt,source:"live-http-probe",productionAuthority:"WordPress"},{status:502});
 }
}
