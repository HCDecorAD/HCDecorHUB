export function requireSameOriginMutation(request){
  const site=new URL(request.url);
  const origin=request.headers.get("origin");
  const fetchSite=(request.headers.get("sec-fetch-site")||"").toLowerCase();
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
