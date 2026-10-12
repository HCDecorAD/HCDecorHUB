export const HEALTH_TIMEOUT_MS=5000;
export async function checkSiteHealth(url,{fetchImpl=globalThis.fetch,timeoutMs=HEALTH_TIMEOUT_MS,now=()=>Date.now()}={}){
  if(!url)return {state:"unchecked",label:"chưa kiểm tra",status:null,ms:null};
  const started=now(),ctl=new AbortController(),timer=setTimeout(()=>ctl.abort(),timeoutMs);
  try{
    const res=await fetchImpl(url,{method:"GET",redirect:"follow",cache:"no-store",signal:ctl.signal});
    const ms=now()-started;
    return {state:res.ok?"ok":"error",label:res.ok?"ok":"lỗi",status:res.status,ms};
  }catch(e){
    return {state:"error",label:"lỗi",status:null,ms:null,error:e&&e.name==="AbortError"?"timeout":"network"};
  }finally{clearTimeout(timer)}
}
export function checkAllSites(sites,opts){return Promise.all(sites.map(async s=>({...s,health:await checkSiteHealth(s.productionUrl,opts)})))}
