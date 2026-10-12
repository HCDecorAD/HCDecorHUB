import assert from "node:assert/strict";
import crypto from "node:crypto";
import {resolveCloudflareAccessPrincipal,productionMutationAuthorized} from "../lib/identity-runtime.js";
import {canViewSuperAdmin} from "../lib/superadmin.js";

// Fake fixtures only: throwaway keys generated at test time, no network.
const TEAM="team.example.test",AUD="aud-fixture",OWNER="owner@example.test",NOW=1_800_000_000;
const b64u=b=>Buffer.from(b).toString("base64url");
const mk=()=>{const {publicKey,privateKey}=crypto.generateKeyPairSync("rsa",{modulusLength:2048});return {privateKey,jwk:{...publicKey.export({format:"jwk"}),kid:"k1"}}};
const good=mk(),other=mk();
const jwks={keys:[good.jwk]};
function token(claims,signer=good.privateKey,alg="RS256"){
 const h=b64u(JSON.stringify({alg,kid:"k1"})),p=b64u(JSON.stringify(claims));
 const s=crypto.sign("sha256",Buffer.from(h+"."+p),signer);
 return h+"."+p+"."+b64u(s);
}
const claims=(o={})=>({iss:"https://"+TEAM,aud:[AUD],exp:NOW+600,email:OWNER,sub:"u1",...o});
const env={CF_ACCESS_TEAM_DOMAIN:TEAM,CF_ACCESS_AUD:AUD,CF_ACCESS_OWNER_EMAILS:"x@example.test, "+OWNER.toUpperCase()};
const run=(tok,e=env)=>resolveCloudflareAccessPrincipal({headers:new Headers(tok?{"Cf-Access-Jwt-Assertion":tok}:{}),env:e,jwks,now:NOW});
const denied=p=>{assert.equal(p.authenticated,false);assert.equal(canViewSuperAdmin(p),false)};
const realFetch=globalThis.fetch;globalThis.fetch=()=>{throw Error("network call forbidden in test")};

const ok=await run(token(claims()));
assert.equal(ok.authenticated,true);assert.equal(ok.role,"owner");assert.equal(canViewSuperAdmin(ok),true);
assert.equal(productionMutationAuthorized(ok),false);
denied(await run(token(claims()),{...env,CF_ACCESS_AUD:""}));
denied(await run(token(claims()),{...env,CF_ACCESS_TEAM_DOMAIN:undefined}));
denied(await run(token(claims()),{...env,CF_ACCESS_OWNER_EMAILS:""}));
denied(await run(token(claims({aud:["other"]}))));
denied(await run(token(claims({exp:NOW-1}))));
denied(await run(token(claims({email:"intruder@example.test"}))));
denied(await run(token(claims({iss:"https://evil.example.test"}))));
denied(await run(token(claims(),other.privateKey)));
denied(await run(token(claims().aud&&claims(),good.privateKey,"HS256")));
denied(await run(""));
denied(await run("not.a.jwt"));
globalThis.fetch=realFetch;
console.log("identity-cf-access: all checks passed");
