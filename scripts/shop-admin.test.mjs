import assert from "node:assert/strict";
import {signShopAdminToken,signShopToken,verifyShopAdminToken,verifyShopToken} from "../lib/shop-token.js";
import {provisionShopUser,setShopPassword,login,createUser,updateUser,deleteUser,userOnlyHasSite} from "../lib/user-auth.js";
import {handleShopStaff,handleShopChangePassword} from "../lib/shop-admin.js";

const SECRET="test-secret-not-real-0123456789abcdef";
const NOW=1_800_000_000;
const mem=()=>{const m=new Map();return{get:async k=>m.get(k)??null,put:async(k,v)=>void m.set(k,v),delete:async k=>void m.delete(k),list:async({prefix})=>({keys:[...m.keys()].filter(k=>k.startsWith(prefix)).map(name=>({name}))})}};
const kv=mem();
const env={HUB_SHOP_SECRET:SECRET};
const adminTok=await signShopAdminToken({sub:"member:owner1",role:"owner"},SECRET,NOW);
const mgrTok=await signShopAdminToken({sub:"member:mgr1",role:"manager"},SECRET,NOW);
const viewerTok=await signShopAdminToken({sub:"member:v1",role:"viewer"},SECRET,NOW);
const nvTok=await signShopToken({login:"user:nv_amo",role:"staff"},SECRET,NOW);

// Token quản trị: chỉ owner/manager; token NV không được coi là quản trị và ngược lại
assert.equal((await verifyShopAdminToken(adminTok,SECRET,NOW+1)).role,"owner");
assert.equal(await verifyShopAdminToken(viewerTok,SECRET,NOW+1),null);
assert.equal(await verifyShopAdminToken(nvTok,SECRET,NOW+1),null);
assert.equal(await verifyShopToken(adminTok,SECRET,NOW+1),null);

const req=(body,tok,method="POST")=>new Request("https://x/api/shop/staff",{method,headers:{"content-type":"application/json",...(tok?{authorization:"Bearer "+tok}:{})},body:method==="GET"?undefined:JSON.stringify(body)});

// Không có quyền quản trị -> 401
assert.equal((await handleShopStaff({request:req({action:"create",username:"a1",role:"staff"},nvTok),env,kv})).status,401);
// Owner tạo NV: không bắt đổi Pass, chỉ có quyền amo
let r=await handleShopStaff({request:req({action:"create",username:"nv_a",role:"staff",password:"matkhau123"},adminTok),env,kv});
assert.equal(r.status,200);
let j=await r.json();
assert.equal(j.user.must_change_password,false);
assert.equal(j.password,"matkhau123");
assert.equal(await userOnlyHasSite(kv,"nv_a","amo"),true);
// Manager tạo khách (viewer), Pass tự sinh
r=await handleShopStaff({request:req({action:"create",username:"khach_a",role:"guest"},mgrTok),env,kv});
j=await r.json();assert.equal(r.status,200);assert.ok(j.password.length>=8);
// Trùng ID -> 409
assert.equal((await handleShopStaff({request:req({action:"create",username:"nv_a",role:"staff"},adminTok),env,kv})).status,409);
// Vai trò sai -> 400
assert.equal((await handleShopStaff({request:req({action:"create",username:"x1",role:"admin"},adminTok),env,kv})).status,400);
// Pass quá ngắn -> 400
assert.equal((await handleShopStaff({request:req({action:"create",username:"x2",role:"staff",password:"123"},adminTok),env,kv})).status,400);

// NV đăng nhập được bằng Pass do admin đặt (không bị bắt đổi)
const lg=await login(kv,"nv_a","matkhau123");
assert.equal(lg.ok,true);assert.equal(lg.must_change_password,false);

// Không quản lý được tài khoản dùng chung nhiều site
await provisionShopUser(kv,{username:"shared1",role:"staff",site_ids:["amo","gsc"],password:"matkhau123"},NOW);
assert.equal((await handleShopStaff({request:req({action:"reset",username:"shared1"},adminTok),env,kv})).status,404);
// Khóa rồi mới xóa được
assert.equal((await handleShopStaff({request:req({action:"delete",username:"nv_a"},adminTok),env,kv})).status,409);
assert.equal((await handleShopStaff({request:req({action:"lock",username:"nv_a"},adminTok),env,kv})).status,200);
assert.equal((await handleShopStaff({request:req({action:"delete",username:"nv_a"},adminTok),env,kv})).status,200);
assert.equal(await kv.get("user:nv_a"),null);

// Danh sách chỉ hiện nhân viên của AMO
const list=await (await handleShopStaff({request:req(null,adminTok,"GET"),env,kv})).json();
assert.ok(list.users.every(u=>u.id!=="shared1"));

// NV tự đổi Pass bằng token của mình
const cp=(body,tok,origin="https://amonguyen.com")=>new Request("https://x/api/shop/change-password",{method:"POST",headers:{"content-type":"application/json",...(tok?{authorization:"Bearer "+tok}:{})},body:JSON.stringify(body)});
const nvTok2=await signShopToken({login:"user:khach_a",role:"viewer"},SECRET,NOW);
assert.equal((await handleShopChangePassword({request:cp({current:"x",next:"moi12345"},adminTok),env,kv,origin:"https://amonguyen.com"})).status,401);
assert.equal((await handleShopChangePassword({request:cp({current:"x",next:"moi12345"},nvTok2),env,kv,origin:null})).status,403);
const okCp=await handleShopChangePassword({request:cp({current:j.password,next:"moi12345"},nvTok2),env,kv,origin:"https://amonguyen.com"});
assert.equal(okCp.status,200);
assert.equal((await login(kv,"khach_a","moi12345")).ok,true);
const okCp2=await handleShopChangePassword({request:cp({current:"sai-hoan-toan",next:"moi12345"},nvTok),env,kv,origin:"https://amonguyen.com"});
assert.equal(okCp2.status,401);
assert.equal(okCp2.headers.get("access-control-allow-origin"),"https://amonguyen.com");

// Reset của chủ shop
const rs=await handleShopStaff({request:req({action:"reset",username:"khach_a",password:"matkhau999"},mgrTok),env,kv});
assert.equal(rs.status,200);
assert.equal((await login(kv,"khach_a","matkhau999")).ok,true);

console.log("shop-admin: all tests passed");
