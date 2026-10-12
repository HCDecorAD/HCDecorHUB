import assert from "node:assert/strict";
import {hashPassword,verifyPassword,createUser,resetPassword,updateUser,login,changePassword,createUserSession,resolveUserPrincipal,canEnter,normalizeUsername,LOCK_SECONDS} from "../lib/user-auth.js";
import {handleUserLogin,handleChangePassword,handleAdminUsers} from "../lib/user-routes.js";
import {resolveGithubSessionPrincipal,SESSION_COOKIE} from "../lib/github-auth.js";

// Fake KV with TTL on a controllable clock. Fixtures only.
let clock=1_800_000_000;
const store=new Map();
const kv={
 async get(k){const e=store.get(k);if(!e)return null;if(e.exp&&e.exp<=clock){store.delete(k);return null}return e.v},
 async put(k,v,o){store.set(k,{v,exp:o?.expirationTtl?clock+o.expirationTtl:0})},
 async delete(k){store.delete(k)},
 async list({prefix}){return {keys:[...store.keys()].filter(k=>k.startsWith(prefix)).map(name=>({name}))}}
};
const env={SESSION_SECRET:"fixture-secret",GITHUB_CLIENT_ID:"c",GITHUB_CLIENT_SECRET:"s",GITHUB_OWNER_LOGIN:"HCDecorAD",HUB_PUBLIC_URL:"https://h.test"};

// hash: right/wrong password, distinct salts
const h1=await hashPassword("correct horse"),h2=await hashPassword("correct horse");
assert.ok(h1.iterations>=210000&&h1.salt.length===32);
assert.ok(await verifyPassword("correct horse",h1));
assert.ok(!await verifyPassword("wrong",h1));
assert.notEqual(h1.salt,h2.salt);
assert.notEqual(h1.password_hash,h2.password_hash);

// create: pass shown once, only hash stored, distinct salts across users
const a=await createUser(kv,{username:"Alice",role:"guest",site_ids:["AMO"]},clock);
const b=await createUser(kv,{username:"bob",role:"staff",site_ids:["gsc","amo"]},clock);
assert.ok(a.ok&&b.ok&&a.password.length>=16&&a.user.must_change_password===true);
assert.deepEqual(a.user.site_ids,["amo"]);
const rawA=JSON.parse(await kv.get("user:alice")),rawB=JSON.parse(await kv.get("user:bob"));
assert.notEqual(rawA.salt,rawB.salt);
assert.ok(!JSON.stringify(rawA).includes(a.password));
assert.equal((await createUser(kv,{username:"alice",role:"guest"})).error,"exists");
assert.equal(normalizeUsername("HCDecorAD"),null);
assert.equal((await createUser(kv,{username:"x y",role:"guest"})).ok,false);
assert.equal((await createUser(kv,{username:"carol",role:"owner"})).ok,false);

// login ok, generic errors, forced password change
let r=await login(kv,"alice",a.password);
assert.ok(r.ok&&r.must_change_password===true);
const sess=await createUserSession(r.user,env.SESSION_SECRET,clock);
let p=await resolveUserPrincipal({cookieValue:sess,env,kv,now:clock});
assert.ok(p.authenticated&&p.must_change_password&&!canEnter(p)); // guest forced to change pass
assert.deepEqual([(await login(kv,"nobody","x")).error,(await login(kv,"alice","bad")).error],["invalid_credentials","invalid_credentials"]);
assert.equal((await changePassword(kv,"alice",a.password,"short")).error,"weak_password");
assert.equal((await changePassword(kv,"alice","bad","a-new-long-pass")).ok,false);
assert.ok((await changePassword(kv,"alice",a.password,"a-new-long-pass",clock)).ok);
p=await resolveUserPrincipal({cookieValue:sess,env,kv,now:clock});
assert.ok(canEnter(p));
assert.ok(!(await login(kv,"alice",a.password)).ok);
assert.ok((await login(kv,"alice","a-new-long-pass")).ok);

// lockout after 5 wrong in a row, even the right pass is refused; reopens after 15 min
for(let i=0;i<5;i++)assert.equal((await login(kv,"bob","wrong"+i)).status,401);
r=await login(kv,"bob",b.password);
assert.equal(r.ok,false);assert.equal(r.error,"invalid_credentials");
clock+=LOCK_SECONDS-1;
assert.equal((await login(kv,"bob",b.password)).ok,false);
clock+=2;
assert.ok((await login(kv,"bob",b.password)).ok);
// success resets the counter
for(let i=0;i<4;i++)await login(kv,"bob","w");
assert.ok((await login(kv,"bob",b.password)).ok);
for(let i=0;i<4;i++)await login(kv,"bob","w");
assert.ok((await login(kv,"bob",b.password)).ok);

// disabled: cannot log in, existing session dies
const bs=await createUserSession((await login(kv,"bob",b.password)).user,env.SESSION_SECRET,clock);
assert.ok((await resolveUserPrincipal({cookieValue:bs,env,kv,now:clock})).authenticated);
assert.ok((await updateUser(kv,"bob",{disabled:true})).ok);
assert.equal((await login(kv,"bob",b.password)).ok,false);
assert.equal((await resolveUserPrincipal({cookieValue:bs,env,kv,now:clock})).authenticated,false);
await updateUser(kv,"bob",{disabled:false,site_ids:["gsc"]});
await kv.delete("fail:bob");
assert.ok((await login(kv,"bob",b.password)).ok);

// reset: new pass, old pass dead, must change again
const rs=await resetPassword(kv,"alice",clock);
assert.ok(rs.ok&&rs.user.must_change_password);
assert.equal((await login(kv,"alice","a-new-long-pass")).ok,false);
assert.ok((await login(kv,"alice",rs.password)).ok);

// default-deny: no / forged / expired / foreign cookies
const bad=[undefined,"","garbage",sess.slice(0,-1)+"0"];
for(const c of bad)assert.equal((await resolveUserPrincipal({cookieValue:c,env,kv,now:clock})).authenticated,false);
assert.equal((await resolveUserPrincipal({cookieValue:sess,env,kv:null,now:clock})).authenticated,false);
assert.equal((await resolveUserPrincipal({cookieValue:sess,env:{},kv,now:clock})).authenticated,false);
assert.equal((await resolveUserPrincipal({cookieValue:sess,env,kv,now:clock+9*3600})).authenticated,false);
// a user session is never an owner session
assert.equal((await resolveGithubSessionPrincipal({cookieValue:sess,env,now:clock})).authenticated,false);

// routes
const req=(url,method,data,cookie)=>new Request("https://h.test"+url,{method,headers:{"content-type":"application/json",...(cookie?{cookie}:{})},body:data?JSON.stringify(data):undefined});
let res=await handleUserLogin({request:req("/api/auth/login","POST",{username:"alice",password:rs.password}),env,kv});
assert.equal(res.status,200);
const j=await res.json();
assert.ok(j.must_change_password&&j.redirect==="/account");
const sc=res.headers.get("set-cookie");
assert.ok(sc.startsWith(SESSION_COOKIE+"=")&&/HttpOnly/.test(sc)&&/Secure/.test(sc)&&/SameSite=Lax/.test(sc));
res=await handleUserLogin({request:new Request("https://h.test/x",{method:"POST",headers:{"content-type":"text/plain"},body:"{}"}),env,kv});
assert.equal(res.status,400);
assert.equal((await handleUserLogin({request:req("/l","POST",{username:"alice",password:"x"}),env,kv:null})).status,503);
assert.equal((await handleChangePassword({request:req("/p","POST",{current:"a",next:"b"}),env,kv})).status,401);
res=await handleChangePassword({request:req("/p","POST",{current:rs.password,next:"another-long-pass"},sc.split(";")[0]),env,kv});
assert.equal(res.status,200);
assert.equal((await handleAdminUsers({request:req("/a","GET"),kv,canAdmin:false})).status,403);
res=await handleAdminUsers({request:req("/a","GET"),kv,canAdmin:true});
const list=await res.json();
assert.ok(list.users.length>=2&&!JSON.stringify(list).match(/password_hash|salt/));
res=await handleAdminUsers({request:req("/a","POST",{action:"create",username:"dave",role:"guest"}),kv,canAdmin:true});
assert.ok((await res.json()).password);
console.log("user-auth: OK");
