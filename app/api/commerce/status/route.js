const ENGINE="https://hc-shop-engine.huycuongonline.workers.dev";
const STORE="store_amo";
async function get(path){try{const r=await fetch(ENGINE+path,{headers:{"x-store-id":STORE},cache:"no-store"});const data=await r.json();return {ok:r.ok,status:r.status,data}}catch{return {ok:false,status:0,data:null}}}
export async function GET(){
 const [health,store,catalog]=await Promise.all([get("/api/health"),get("/api/store"),get("/api/catalog")]);
 const items=Array.isArray(catalog.data?.items)?catalog.data.items:[];
 const reference=items.filter(x=>String(x.description||"").toLowerCase().includes("tham khảo"));
 const productionAuthority=false,verifiedCommerceData=false;
 const ready=health.ok&&store.ok&&catalog.ok&&productionAuthority&&verifiedCommerceData;
 const reason=!health.ok?"engine_unreachable":!store.ok?"store_unreachable":!catalog.ok?"catalog_unreachable":!productionAuthority?"catalog_authority_unverified":!verifiedCommerceData?"commerce_data_unverified":null;
 return Response.json({service:"hc-shop-manager",ready,reason,engine:{live:health.ok,version:health.data?.version||null},store:store.ok?{id:store.data.id,name:store.data.name,currency:store.data.currency,locale:store.data.locale}:null,catalog:{reachable:catalog.ok,count:items.length,reference_items:reference.length,production_authority:productionAuthority,verified_commerce_data:verifiedCommerceData},capabilities:{catalog_read:true,inventory_admin:true,orders:true,customers:true,reports:true},policy:{production_write:"approval-required",catalog_truth:"verified-only",reference_data_must_not_be_presented_as_verified:true},production_write:false,checkedAt:new Date().toISOString()},{status:ready?200:503});
}