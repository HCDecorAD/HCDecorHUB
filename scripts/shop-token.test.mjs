import assert from "node:assert/strict";
import {signShopToken,verifyShopToken,shopRoleOf,SHOP_TOKEN_TTL} from "../lib/shop-token.js";
import {createUser,updateUser} from "../lib/user-auth.js";
import {handleShopLogin} from "../lib/user-routes.js";

const SECRET="test-secret-not-real-0123456789abcdef";
const NOW=1_800_000_000;
const t=await signShopToken({login:"user:nv_test",role:"staff"},SECRET,NOW);

// Hợp lệ, đúng cửa hàng, vai trò map đúng
assert.deepEqual(await verifyShopToken(t,SECRET,NOW+10),{sub:"user:nv_test",role:"staff",store:"store_amo"});
// Khách -> viewer (chỉ xem)
const g=await signShopToken({login:"user:kh1",role:"guest"},SECRET,NOW);
assert.equal((await verifyShopToken(g,SECRET,NOW)).role,"viewer");
assert.equal(shopRoleOf("guest"),"viewer");
assert.equal(shopRoleOf("manager"),"manager");
assert.equal(shopRoleOf("staff"),"staff");
// Hết hạn sau SHOP_TOKEN_TTL
assert.equal(await verifyShopToken(t,SECRET,NOW+SHOP_TOKEN_TTL),null);
// Sai secret, sửa payload, sai định dạng
assert.equal(await verifyShopToken(t,"wrong-secret",NOW+10),null);
const [, p, s]=t.split(".");
const forged=Buffer.from(JSON.stringify({sub:"user:x",role:"staff",store:"store_amo",iat:NOW,exp:NOW+9e6,v:1})).toString("base64url");
assert.equal(await verifyShopToken("hub1."+forged+"."+s,SECRET,NOW+10),null);
assert.equal(await verifyShopToken("garbage",SECRET,NOW),null);
// Không cho token của cửa hàng khác
const other=await signShopToken({login:"user:a",role:"staff"},SECRET,NOW);
const [, op, os]=other.split(".");
const otherStore=Buffer.from(JSON.stringify({sub:"user:a",role:"staff",store:"store_other",iat:NOW,exp:NOW+600,v:1})).toString("base64url");
assert.equal(await verifyShopToken("hub1."+otherStore+"."+os,SECRET,NOW),null);

// Luồng đăng nhập: chỉ ID có quyền amo, không còn must_change_password, mới nhận token
const store=new Map();
const kv={async get(k){return store.has(k)?store.get(k):null},async put(k,v){store.set(k,v)},async delete(k){store.delete(k)},async list({prefix}){return {keys:[...store.keys()].filter(k=>k.startsWith(prefix)).map(name=>({name}))}}};
const env={SESSION_SECRET:"s1",HUB_SHOP_SECRET:SECRET};
const a=await createUser(kv,{username:"nv_amo",role:"staff",site_ids:["amo"]},NOW);
const b=await createUser(kv,{username:"nv_gsc",role:"staff",site_ids:["gsc"]},NOW);
const c=await createUser(kv,{username:"kh_amo",role:"guest",site_ids:["amo"]},NOW);
const req=(u,p)=>new Request("https://x/api/shop/login",{method:"POST",headers:{"content-type":"application/json"},body:JSON.stringify({username:u,password:p})});
const origin="https://amonguyen.com";
// Lần đầu còn must_change_password -> chặn
let r=await handleShopLogin({request:req("nv_amo",a.password),env,kv,origin});
assert.equal(r.status,403); assert.equal((await r.json()).error,"must_change_password");
// Sau khi đổi Pass (tắt cờ) -> được token
await updateUser(kv,"nv_amo",{},NOW); const rec=JSON.parse(store.get("user:nv_amo")); rec.must_change_password=false; store.set("user:nv_amo",JSON.stringify(rec));
r=await handleShopLogin({request:req("nv_amo",a.password),env,kv,origin});
assert.equal(r.status,200); const ok=await r.json();
assert.equal(r.headers.get("access-control-allow-origin"),origin);
assert.equal((await verifyShopToken(ok.token,SECRET)).sub,"user:nv_amo");
// Không có quyền amo -> 403 no_access; sai Pass -> 401; origin lạ -> 503 (không cấp token)
r=await handleShopLogin({request:req("nv_gsc",b.password),env,kv,origin}); assert.equal(r.status,403);
r=await handleShopLogin({request:req("nv_amo","sai-mat-khau-xyz"),env,kv,origin}); assert.equal(r.status,401);
r=await handleShopLogin({request:req("nv_amo",a.password),env,kv,origin:null}); assert.equal(r.status,503);
// Không có HUB_SHOP_SECRET -> không cấp token
r=await handleShopLogin({request:req("nv_amo",a.password),env:{SESSION_SECRET:"s1"},kv,origin}); assert.equal(r.status,503);
console.log("shop-token: all tests passed");
