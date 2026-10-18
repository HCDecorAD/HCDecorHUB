// Kiểm tra checkOwnPassword: đúng/sai, không đếm lần sai, không khóa ID.
import assert from "node:assert/strict";
import {createUser,setPassword,login,checkOwnPassword,MAX_FAILS} from "../lib/user-auth.js";

const mem=()=>{const m=new Map();return {get:async k=>m.has(k)?m.get(k):null,put:async(k,v)=>{m.set(k,v)},delete:async k=>{m.delete(k)},list:async({prefix})=>({keys:[...m.keys()].filter(k=>k.startsWith(prefix)).map(name=>({name}))}),_m:m}};
const kv=mem();
await setPassword(kv,"amo",null).catch(()=>{});
await createUser(kv,{username:"amo",role:"manager",site_ids:["amo"]});
await setPassword(kv,"amo","matkhau-dung-123");

assert.equal(await checkOwnPassword(kv,"amo","matkhau-dung-123"),true);
for(let i=0;i<MAX_FAILS+3;i++) assert.equal(await checkOwnPassword(kv,"amo","sai-"+i),false);
assert.equal(await checkOwnPassword(kv,"amo","matkhau-dung-123"),true,"không bị khóa sau nhiều lần sai");
assert.equal(await checkOwnPassword(kv,"khong-co","x"),false);
assert.equal(kv._m.has("fail:amo"),false,"không ghi bộ đếm lần sai");
// login thường vẫn khóa như cũ (không thay đổi hành vi đăng nhập)
for(let i=0;i<MAX_FAILS;i++) await login(kv,"amo","sai");
assert.equal((await login(kv,"amo","matkhau-dung-123")).status,429);
console.log("shop-verify-password: all tests passed");
