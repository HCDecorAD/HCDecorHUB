const ENGINE="https://hc-shop-engine.huycuongonline.workers.dev";
const STORE="store_amo";
async function get(path){try{const r=await fetch(ENGINE+path,{headers:{"x-store-id":STORE},cache:"no-store"});const data=await r.json();return {ok:r.ok,status:r.status,data}}catch{return {ok:false,status:0,data:null}}}
export async function GET(){
 const [health,store,catalog]=await Promise.all([get("/api/health"),get("/api/store"),get("/api/catalog")]);
 const items=Array.isArray(catalog.data?.items)?catalog.data.items:[];
 const reference=items.filter(x=>String(x.description||"").toLowerCase().includes("tham khảo"));
 return Response.json({service:"hc-shop-manager",engine:{live:health.ok,version:health.data?.version||null},store:store.ok?{id:store.data.id,name:store.data.name,currency:store.data.currency,locale:store.data.locale}:null,catalog:{reachable:catalog.ok,count:items.length,reference_items:reference.length,production_authority:false,verified_commerce_data:false},capabilities:{catalog_read:true,inventory_admin:true,orders:true,customers:true,reports:true},policy:{production_write:"approval-required",catalog_truth:"verified-only",reference_data_must_not_be_presented_as_verified:true},production_write:false,checkedAt:new Date().toISOString()},{status:health.ok&&store.ok&&catalog.ok?200:503});
}