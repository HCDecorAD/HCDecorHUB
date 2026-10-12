import assert from "node:assert/strict";
import {checkSiteHealth} from "../lib/site-health.mjs";

const ok=await checkSiteHealth("https://x.test",{fetchImpl:async()=>({status:200})});
assert.equal(ok.state,"ok");assert.equal(ok.status,200);assert.equal(typeof ok.ms,"number");

const bad=await checkSiteHealth("https://x.test",{fetchImpl:async()=>({status:503})});
assert.equal(bad.state,"error");assert.equal(bad.status,503);

const fail=await checkSiteHealth("https://x.test",{fetchImpl:async()=>{throw new Error("boom")}});
assert.equal(fail.state,"error");assert.equal(fail.ms,null);assert.equal(fail.error,"fetch_failed");

const hang=(u,o)=>new Promise((_,rej)=>o.signal.addEventListener("abort",()=>rej(Object.assign(new Error("a"),{name:"AbortError"}))));
const to=await checkSiteHealth("https://x.test",{timeoutMs:20,fetchImpl:hang});
assert.equal(to.error,"timeout");

const none=await checkSiteHealth(null,{fetchImpl:async()=>{throw new Error("must not call")}});
assert.equal(none.state,"unchecked");

console.log("SITE-HEALTH PASS 5/5");
