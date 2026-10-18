/** Request handlers for SuperAdmin v2 (ID/Pass). Dependencies are injected so tests need no Next/Cloudflare runtime. */
import {cookieHeader,readCookie,SESSION_COOKIE} from "./github-auth.js";
import {signShopToken,shopRoleOf,SHOP_TOKEN_TTL,SHOP_STORE} from "./shop-token.js";
import {login,changePassword,createUserSession,resolveUserPrincipal,createUser,resetPassword,setPassword,updateUser,deleteUser,listUsers,SESSION_TTL} from "./user-auth.js";

const json=(status,body,headers=[])=>{
 const h=new Headers({"content-type":"application/json; charset=utf-8","cache-control":"no-store"});
 headers.forEach(([k,v])=>h.append(k,v));
 return new Response(JSON.stringify(body),{status,headers:h});
};
const body=async req=>{
 if(!/application\/json/i.test(req.headers.get("content-type")||""))return null; // blocks cross-site form posts
 try{return await req.json()}catch{return null}
};
const unavailable=()=>json(503,{ok:false,error:"unavailable"});

export async function handleUserLogin({request,env,kv}){
 if(!kv||!env?.SESSION_SECRET)return unavailable();
 const b=await body(request);
 if(!b)return json(400,{ok:false,error:"invalid_request"});
 const r=await login(kv,b.username,b.password);
 if(!r.ok)return json(r.status,{ok:false,error:r.error}); // generic message for wrong pass, unknown user, disabled, locked
 const v=await createUserSession(r.user,env.SESSION_SECRET);
 return json(200,{ok:true,must_change_password:r.must_change_password,redirect:r.must_change_password?"/account":"/hub/my-sites"},[["set-cookie",cookieHeader(SESSION_COOKIE,v,SESSION_TTL)]]);
}
export async function handleChangePassword({request,env,kv}){
 if(!kv||!env?.SESSION_SECRET)return unavailable();
 const p=await resolveUserPrincipal({cookieValue:readCookie(request.headers.get("cookie"),SESSION_COOKIE),env,kv});
 if(!p.authenticated)return json(401,{ok:false,error:"unauthorized"});
 const b=await body(request);
 if(!b)return json(400,{ok:false,error:"invalid_request"});
 const r=await changePassword(kv,p.principal_id.slice(5),b.current,b.next);
 if(!r.ok)return json(r.error==="weak_password"?400:401,{ok:false,error:r.error});
 const v=await createUserSession(r.user,env.SESSION_SECRET);
 return json(200,{ok:true},[["set-cookie",cookieHeader(SESSION_COOKIE,v,SESSION_TTL)]]);
}
// canAdmin: caller already resolved the GitHub owner principal.
export async function handleAdminUsers({request,kv,canAdmin}){
 if(!canAdmin)return json(403,{ok:false,error:"forbidden"});
 if(!kv)return unavailable();
 if(request.method==="GET")return json(200,{ok:true,users:await listUsers(kv)});
 const b=await body(request);
 if(!b)return json(400,{ok:false,error:"invalid_request"});
 const r=b.action==="create"?await createUser(kv,b)
  :b.action==="reset"?(typeof b.password==="string"?await setPassword(kv,b.username,b.password):await resetPassword(kv,b.username))
  :b.action==="update"?await updateUser(kv,b.username,b)
  :b.action==="delete"?await deleteUser(kv,b.username)
  :{ok:false,error:"invalid"};
 return json(r.ok?200:r.error==="not_found"?404:r.error==="not_disabled"?409:400,r);
}

// Đăng nhập ID/Pass cho trang quản lý cửa hàng AMO. Cần quyền site "amo" và không còn phải đổi Pass.
export async function handleShopLogin({request,env,kv,origin}){
 const cors={"access-control-allow-origin":origin,"vary":"Origin","access-control-allow-methods":"POST,OPTIONS","access-control-allow-headers":"content-type"};
 const out=(status,body)=>new Response(JSON.stringify(body),{status,headers:{"content-type":"application/json; charset=utf-8","cache-control":"no-store",...cors}});
 if(!kv||!env?.SESSION_SECRET||!env?.HUB_SHOP_SECRET||!origin)return out(503,{ok:false,error:"unavailable"});
 const b=await body(request);
 if(!b)return out(400,{ok:false,error:"invalid_request"});
 const r=await login(kv,b.username,b.password);
 if(!r.ok)return out(r.status,{ok:false,error:r.error});
 if(r.must_change_password)return out(403,{ok:false,error:"must_change_password"});
 if(!(r.user.site_ids||[]).includes("amo"))return out(403,{ok:false,error:"no_access"});
 const token=await signShopToken({login:"user:"+r.user.id,role:r.user.role},env.HUB_SHOP_SECRET);
 return out(200,{ok:true,token,store:SHOP_STORE,role:shopRoleOf(r.user.role),expires_in:SHOP_TOKEN_TTL});
}
