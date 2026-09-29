export function requireSameOriginMutation(request){
  const length=Number(request.headers.get("content-length")||0);
  if(Number.isFinite(length)&&length>65536)return Response.json({ok:false,error:"request_too_large"},{status:413});
  const site=new URL(request.url);
  const origin=request.headers.get("origin");
  const fetchSite=(request.headers.get("sec-fetch-site")||"").toLowerCase();
  if(!origin && !fetchSite)return Response.json({ok:false,error:"missing_origin_context"},{status:403});
  if(origin){
    let parsed;
    try{parsed=new URL(origin)}catch{return Response.json({ok:false,error:"invalid_origin"},{status:403})}
    if(parsed.origin!==site.origin)return Response.json({ok:false,error:"cross_origin_mutation_blocked"},{status:403});
  }
  if(fetchSite && !["same-origin","same-site","none"].includes(fetchSite)){
    return Response.json({ok:false,error:"cross_site_mutation_blocked"},{status:403});
  }
  return null;
}
