export const HEALTH_TIMEOUT_MS=5000;
export async function checkSiteHealth(url,{fetchImpl=globalThis.fetch,timeoutMs=HEALTH_TIMEOUT_MS,now=()=>Date.now()}={}){
 if(!url||typeof fetchImpl!=="function")return {state:"unchecked",status:null,ms:null,error:null};
 const ctrl=new AbortController(),timer=setTimeout(()=>ctrl.abort(),timeoutMs),start=now();
 try{
  const res=await fetchImpl(url,{method:"GET",redirect:"follow",cache:"no-store",signal:ctrl.signal});
  const ms=now()-start;
  return {state:res.status>=200&&res.status<400?"ok":"error",status:res.status,ms,error:null};
 }catch(e){
  return {state:"error",status:null,ms:null,error:e?.name==="AbortError"?"timeout":"fetch_failed"};
 }finally{clearTimeout(timer)}
}
export function checkAllSites(urls,opts){return Promise.all(urls.map(u=>checkSiteHealth(u,opts)))}
