import assert from 'node:assert/strict';
import {checkSiteHealth,checkAllSites} from '../lib/site-health.mjs';
import {isOwnerRequest} from '../lib/owner-gate.mjs';
let pass=0;const t=async(n,f)=>{await f();pass++;console.log('PASS',n)};
await t('200 => ok with status and ms',async()=>{
  let c=0;const r=await checkSiteHealth('https://x.test',{fetchImpl:async()=>({ok:true,status:200}),now:()=>(c+=40)});
  assert.deepEqual([r.state,r.status,r.ms],['ok',200,40]);
});
await t('500 => lỗi with status',async()=>{
  const r=await checkSiteHealth('https://x.test',{fetchImpl:async()=>({ok:false,status:500})});
  assert.equal(r.label,'lỗi');assert.equal(r.status,500);
});
await t('network error => lỗi, no fake status',async()=>{
  const r=await checkSiteHealth('https://x.test',{fetchImpl:async()=>{throw new Error('boom')}});
  assert.equal(r.label,'lỗi');assert.equal(r.status,null);assert.equal(r.ms,null);
});
await t('timeout aborts => lỗi timeout',async()=>{
  const r=await checkSiteHealth('https://x.test',{timeoutMs:10,fetchImpl:(u,{signal})=>new Promise((_,rej)=>signal.addEventListener('abort',()=>rej(Object.assign(new Error('a'),{name:'AbortError'}))))});
  assert.equal(r.error,'timeout');assert.equal(r.label,'lỗi');
});
await t('missing url => chưa kiểm tra, fetch not called',async()=>{
  const r=await checkSiteHealth(null,{fetchImpl:async()=>{throw new Error('called')}});
  assert.equal(r.label,'chưa kiểm tra');
});
await t('checkAllSites keeps rows',async()=>{
  const r=await checkAllSites([{site_id:'A',productionUrl:'https://a.test'},{site_id:'B',productionUrl:null}],{fetchImpl:async()=>({ok:true,status:200})});
  assert.equal(r.length,2);assert.equal(r[1].health.label,'chưa kiểm tra');
});
const tok='a'.repeat(40);
await t('owner gate fails closed',()=>{
  assert.equal(isOwnerRequest('Bearer '+tok,undefined),false);
  assert.equal(isOwnerRequest('Bearer short','short'),false);
  assert.equal(isOwnerRequest(null,tok),false);
  assert.equal(isOwnerRequest('Bearer '+'b'.repeat(40),tok),false);
  assert.equal(isOwnerRequest('Bearer '+tok,tok),true);
});
console.log('site-health tests passed: '+pass);
