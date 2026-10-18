/**
 * Token cấp cho ID/Pass dùng trang quản lý cửa hàng AMO (shop-admin.html) qua hc-shop-engine.
 * Định dạng giống phiên HUB: payload (base64url JSON) + "." + HMAC-SHA256. Secret HUB_SHOP_SECRET đặt trên CẢ HUB và hc-shop-engine.
 * Không chứa mật khẩu. Hết hạn sau SHOP_TOKEN_TTL giây (1 giờ) để khóa ID có hiệu lực trong vòng 1 giờ.
 */
import {sign,b64u,timingSafeEqual} from "./github-auth.js";

export const SHOP_TOKEN_TTL=3600;
export const SHOP_STORE="store_amo";
// Map vai trò HUB -> vai trò shop-engine (ROLES: viewer 1, staff 2, manager 3, owner 4)
export const shopRoleOf=role=>role==="manager"?"manager":role==="staff"?"staff":"viewer";

export async function signShopToken({login,role},secret,now=Math.floor(Date.now()/1000)){
 const payload=b64u(JSON.stringify({sub:login,role:shopRoleOf(role),store:SHOP_STORE,iat:now,exp:now+SHOP_TOKEN_TTL,v:1}));
 return "hub1."+payload+"."+await sign(payload,secret);
}
// Trả về {sub,role,store} nếu token hợp lệ và chưa hết hạn; ngược lại null.
// Token quản trị cửa hàng (chủ shop / quản lý) do hc-shop-engine ký sau khi xác thực mem_ key. Chỉ dùng server-to-server.
export const SHOP_ADMIN_ROLES=["owner","manager"];
export async function signShopAdminToken({sub,role},secret,now=Math.floor(Date.now()/1000)){
 const payload=b64u(JSON.stringify({sub,role,store:SHOP_STORE,kind:"admin",iat:now,exp:now+SHOP_TOKEN_TTL,v:1}));
 return "hub1."+payload+"."+await sign(payload,secret);
}
async function parseShopToken(token,secret,now){
 try{
  if(typeof token!=="string"||!token.startsWith("hub1."))return null;
  const [, p, s]=token.split(".");
  if(!p||!s||!secret)return null;
  if(!timingSafeEqual(s,await sign(p,secret)))return null;
  const d=JSON.parse(new TextDecoder().decode(Uint8Array.from(atob(p.replace(/-/g,"+").replace(/_/g,"/")),c=>c.charCodeAt(0))));
  if(!d||d.v!==1||!(d.exp>now)||d.store!==SHOP_STORE)return null;
  return d;
 }catch{return null}
}
// Token ID/Pass nhân viên. Từ chối token quản trị.
export async function verifyShopToken(token,secret,now=Math.floor(Date.now()/1000)){
 const d=await parseShopToken(token,secret,now);
 if(!d||d.kind==="admin")return null;
 return {sub:d.sub,role:d.role,store:d.store};
}
// Token quản trị: chỉ chấp nhận owner/manager.
export async function verifyShopAdminToken(token,secret,now=Math.floor(Date.now()/1000)){
 const d=await parseShopToken(token,secret,now);
 if(!d||d.kind!=="admin"||!SHOP_ADMIN_ROLES.includes(d.role))return null;
 return {sub:d.sub,role:d.role,store:d.store};
}
