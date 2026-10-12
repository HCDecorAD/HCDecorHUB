import assert from "node:assert/strict";
import {createSessionValue,verifySessionValue,resolveGithubSessionPrincipal,handleLogin,handleCallback,handleLogout,timingSafeEqual,SESSION_COOKIE,STATE_COOKIE} from "../lib/github-auth.js";

// Fake fixtures only; fetch is mocked.
const env=Object.freeze({GITHUB_CLIENT_ID:"cid",GITHUB_CLIENT_SECRET:"csec",GITHUB_OWNER_LOGIN:"OwnerLogin",HUB_PUBLIC_URL:"https://hub.example.test/",SESSION_SECRET:"fixture-secret"});
const NOW=1_800_000_000;
const mockFetch=login=>async url=>String(url).includes("access_token")?Response.json({access_token:"t"}):Response.json({login});
const cb=(state,cookieState,login,e=env)=>handleCallback({request:new Request("https://hub.example.test/api/auth/github/callback?code=c&state="+state,{headers:cookieState?{cookie:STATE_COOKIE+"="+cookieState}:{}}),env:e,fetchImpl:mockFetch(login),now:NOW});
const without=(k)=>Object.assign({},env,Object.fromEntries([[k,""]]));

// signature
const v=await createSessionValue("OwnerLogin",env.SESSION_SECRET,NOW);
assert.equal((await verifySessionValue(v,env.SESSION_SECRET,NOW)).login,"OwnerLogin");
assert.equal(await verifySessionValue(v,"wrong",NOW),null);
const [p,s]=v.split(".");
assert.equal(await verifySessionValue(p+"."+s.replace(/.$/,c=>c==="0"?"1":"0"),env.SESSION_SECRET,NOW),null);
const forged=Buffer.from(JSON.stringify({login:"OwnerLogin",exp:NOW+99999})).toString("base64url");
assert.equal(await verifySessionValue(forged+"."+s,env.SESSION_SECRET,NOW),null);
assert.equal(await verifySessionValue("garbage",env.SESSION_SECRET,NOW),null);
// expiry
assert.equal(await verifySessionValue(v,env.SESSION_SECRET,NOW+8*3600),null);
assert.ok(await verifySessionValue(v,env.SESSION_SECRET,NOW+8*3600-1));
// constant-time helper
assert.ok(timingSafeEqual("abc","abc"));
assert.ok(!timingSafeEqual("abc","abd"));
assert.ok(!timingSafeEqual("abc","abcd"));
// provider
const pr=await resolveGithubSessionPrincipal({cookieValue:v,env,now:NOW});
assert.deepEqual([pr.authenticated,pr.role,pr.provider],[true,"owner","github"]);
assert.equal((await resolveGithubSessionPrincipal({cookieValue:v,env:without("SESSION_SECRET"),now:NOW})).authenticated,false);
assert.equal((await resolveGithubSessionPrincipal({cookieValue:v,env:Object.assign({},env,{GITHUB_OWNER_LOGIN:"someone"}),now:NOW})).authenticated,false);
assert.equal((await resolveGithubSessionPrincipal({cookieValue:undefined,env,now:NOW})).authenticated,false);
// login route
const lr=handleLogin({env});
assert.equal(lr.status,302);
const loc=new URL(lr.headers.get("location"));
assert.equal(loc.origin+loc.pathname,"https://github.com/login/oauth/authorize");
assert.equal(loc.searchParams.get("redirect_uri"),"https://hub.example.test/api/auth/github/callback");
assert.equal(loc.searchParams.get("scope"),"read:user");
const sc=lr.headers.get("set-cookie");
assert.ok(sc.includes(loc.searchParams.get("state"))&&/HttpOnly/.test(sc)&&/Secure/.test(sc)&&/SameSite=Lax/.test(sc)&&/Max-Age=600/.test(sc));
// missing env -> error on every route
for(const k of Object.keys(env)){
 assert.equal(handleLogin({env:without(k)}).status,503);
 assert.equal((await cb("a","a","OwnerLogin",without(k))).status,503);
}
// callback (login case-insensitive)
const r=await cb("a","a","ownerlogin");
assert.equal(r.status,302);
const cookies=r.headers.getSetCookie();
const sess=cookies.find(c=>c.startsWith(SESSION_COOKIE+"="));
assert.ok(/HttpOnly/.test(sess)&&/Secure/.test(sess)&&/SameSite=Lax/.test(sess)&&/Max-Age=28800/.test(sess));
assert.ok(cookies.some(c=>c.startsWith(STATE_COOKIE+"=;")&&/Max-Age=0/.test(c)));
assert.equal((await cb("a","b","OwnerLogin")).status,400);
assert.equal((await cb("a",null,"OwnerLogin")).status,400);
assert.equal((await cb("a","a","intruder")).status,403);
assert.equal((await cb("a","a",undefined)).status,403);
// logout
assert.ok(/Max-Age=0/.test(handleLogout().headers.get("set-cookie")));
// getAuthEnv: falls back to process.env when no Cloudflare context is available
const {getAuthEnv}=await import("../lib/auth-env.js");
process.env.SESSION_SECRET="from-process-env";
assert.equal((await getAuthEnv()).SESSION_SECRET,"from-process-env");
console.log("github-auth: all tests passed");
