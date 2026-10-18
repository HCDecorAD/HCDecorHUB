/** Quản lý nhân viên cửa hàng AMO (HUB). Chỉ gọi server-to-server từ hc-shop-engine (token quản trị ký bằng HUB_SHOP_SECRET) hoặc nhân viên tự đổi Pass. */
import {verifyShopAdminToken,verifyShopToken,SHOP_STORE} from "./shop-token.js";
import {provisionShopUser,setShopPassword,changePassword,deleteUser,updateUser,userOnlyHasSite,listUsers,ROLES,checkOwnPassword} from "./user-auth.js";

const SITE="amo";
const json=(status,body)=>new Response(JSON.stringify(body),{status,headers:{"content-type":"application/json; charset=utf-8","cache-control":"no-store"}});
const bearer=request=>(request.headers.get("authorization")||"").replace(/^Bearer\s+/i,"");
const body=async req=>{try{return await req.json()}catch{return null}};
const publicShopUser=u=>({id:u.id,role:u.role,disabled:u.disabled,must_change_password:u.must_change_password,updated_at:u.updated_at});

// /api/shop/staff: owner/manager quản lý nhân viên của cửa hàng AMO.
export async function handleShopStaff({request,env,kv}){
 if(!kv||!env?.HUB_SHOP_SECRET)return json(503,{ok:false,error:"unavailable"});
 const admin=await verifyShopAdminToken(bearer(request),env.HUB_SHOP_SECRET);
 if(!admin)return json(401,{ok:false,error:"unauthorized"});
 if(request.method==="GET"){
  const users=(await listUsers(kv)).filter(u=>Array.isArray(u.site_ids)&&u.site_ids.length===1&&u.site_ids[0]===SITE);
  return json(200,{ok:true,store:SHOP_STORE,users:users.map(publicShopUser)});
 }
 const b=await body(request);
 if(!b||typeof b.username!=="string")return json(400,{ok:false,error:"invalid_request"});
 const act=b.action;
 if(act==="create"){
  if(!ROLES.includes(b.role))return json(400,{ok:false,error:"invalid_role"});
  const r=await provisionShopUser(kv,{username:b.username,role:b.role,site_ids:[SITE],password:b.password});
  if(!r.ok)return json(r.error==="exists"?409:400,r);
  return json(200,{ok:true,user:publicShopUser(r.user),password:r.password});
 }
 // Các thao tác còn lại chỉ áp dụng cho nhân viên chỉ có quyền AMO (không đụng tài khoản dùng chung nhiều site)
 if(!await userOnlyHasSite(kv,b.username,SITE))return json(404,{ok:false,error:"not_found"});
 if(act==="reset"){
  const r=await setShopPassword(kv,b.username,b.password);
  if(!r.ok)return json(r.error==="not_found"?404:400,r);
  return json(200,{ok:true,user:publicShopUser(r.user),password:r.password});
 }
 if(act==="lock"||act==="unlock"){
  const r=await updateUser(kv,b.username,{disabled:act==="lock"});
  return json(r.ok?200:400,r.ok?{ok:true}:r);
 }
 if(act==="delete"){
  const r=await deleteUser(kv,b.username);
  if(!r.ok)return json(r.error==="not_disabled"?409:r.error==="not_found"?404:400,r);
  return json(200,{ok:true});
 }
 return json(400,{ok:false,error:"invalid_action"});
}

// /api/shop/change-password: nhân viên tự đổi Pass bằng token ID/Pass của mình.
export async function handleShopChangePassword({request,env,kv,origin}){
 const cors=origin?{"access-control-allow-origin":origin,"vary":"Origin","access-control-allow-methods":"POST,OPTIONS","access-control-allow-headers":"content-type,authorization"}:{};
 const out=(status,body)=>new Response(JSON.stringify(body),{status,headers:{"content-type":"application/json; charset=utf-8","cache-control":"no-store",...cors}});
 if(!origin)return out(403,{ok:false,error:"forbidden_origin"});
 if(!kv||!env?.HUB_SHOP_SECRET)return out(503,{ok:false,error:"unavailable"});
 const me=await verifyShopToken(bearer(request),env.HUB_SHOP_SECRET);
 if(!me||!me.sub.startsWith("user:"))return out(401,{ok:false,error:"unauthorized"});
 const b=await body(request);
 if(!b)return out(400,{ok:false,error:"invalid_request"});
 const r=await changePassword(kv,me.sub.slice(5),b.current,b.next);
 if(!r.ok)return out(r.error==="weak_password"?400:401,{ok:false,error:r.error});
 return out(200,{ok:true});
}

// /api/shop/verify-password: nhân viên xác nhận Pass của chính mình trước thao tác nhạy cảm (vd. xóa SP).
// Không đếm lần sai, không khóa ID. Trả 200 nếu đúng, 403 nếu sai.
export async function handleShopVerifyPassword({request,env,kv,origin}){
 const cors=origin?{"access-control-allow-origin":origin,"vary":"Origin","access-control-allow-methods":"POST,OPTIONS","access-control-allow-headers":"content-type,authorization"}:{};
 const out=(status,body)=>new Response(JSON.stringify(body),{status,headers:{"content-type":"application/json; charset=utf-8","cache-control":"no-store",...cors}});
 if(!origin)return out(403,{ok:false,error:"forbidden_origin"});
 if(!kv||!env?.HUB_SHOP_SECRET)return out(503,{ok:false,error:"unavailable"});
 const me=await verifyShopToken(bearer(request),env.HUB_SHOP_SECRET);
 if(!me||!me.sub.startsWith("user:"))return out(401,{ok:false,error:"unauthorized"});
 const b=await body(request);
 if(!b||typeof b.password!=="string")return out(400,{ok:false,error:"invalid_request"});
 const ok=await checkOwnPassword(kv,me.sub.slice(5),b.password);
 if(!ok)return out(403,{ok:false,error:"wrong_password"});
 return out(200,{ok:true});
}
