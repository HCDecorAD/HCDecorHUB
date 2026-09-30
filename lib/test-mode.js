export function allowTestMode(request,body){
 if(body?.test_mode!==true)return false;
 const marker=request.headers.get("x-hcdecor-test");
 if(marker!=="master-e2e")return false;
 if(process.env.NODE_ENV==="production"&&process.env.HC_ALLOW_LOCAL_E2E!=="true")return false;
 return true;
}
