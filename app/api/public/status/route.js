const TARGETS=[
 ["hcdecor","https://hcdecorhub.com"],
 ["gsc","https://gscsenior.vercel.app"],
 ["amo","https://amonguyen.vercel.app"],
 ["shop-engine","https://hc-shop-engine.huycuongonline.workers.dev/api/health"]
];
async function probe([id,url]){const started=Date.now();try{const r=await fetch(url,{cache:"no-store",signal:AbortSignal.timeout(8000)});return {id,ok:r.ok,status:r.status,latency_ms:Date.now()-started}}catch{return {id,ok:false,status:0,latency_ms:Date.now()-started}}}
export async function GET(){const services=await Promise.all(TARGETS.map(probe));const healthy=services.filter(x=>x.ok).length;return Response.json({service:"hcdecor-hub-public-status",state:healthy===services.length?"healthy":"degraded",healthy,total:services.length,services,production_write:false,checkedAt:new Date().toISOString()},{status:healthy===services.length?200:207,headers:{"cache-control":"no-store"}})}
