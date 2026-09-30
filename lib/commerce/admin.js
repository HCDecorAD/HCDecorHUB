const ENGINE=process.env.HC_SHOP_ENGINE_URL||"https://hc-shop-engine.huycuongonline.workers.dev";
const STORE=process.env.HC_SHOP_STORE_ID||"store_amo";
const ALLOWED=new Set(["stores","products","variants","channels","listings","warehouses","locations","inventory","stock-movements","reservations","orders","import-jobs"]);
export function commerceAdminCapability(){return {configured:Boolean(process.env.HC_SHOP_ADMIN_TOKEN),mode:"server_only",production_write:false}}
export async function commerceAdminRead(resource){
 if(!ALLOWED.has(resource))return {ok:false,status:400,error:"resource_not_allowed",items:[]};
 const token=process.env.HC_SHOP_ADMIN_TOKEN;if(!token)return {ok:false,status:503,error:"credential_not_configured",items:[]};
 try{const r=await fetch(ENGINE+"/api/v1/"+resource,{headers:{"x-store-id":STORE,authorization:"Bearer "+token},cache:"no-store"});const data=await r.json().catch(()=>({}));return {ok:r.ok,status:r.status,error:r.ok?null:(data.error||"upstream_error"),items:Array.isArray(data.items)?data.items:[],data}}catch{return {ok:false,status:503,error:"engine_unreachable",items:[]}}
}