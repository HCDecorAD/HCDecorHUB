import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import {DurableBudgetLedger} from '../lib/governor/durable-budget-ledger.mjs';

let pass=0;const t=(n,fn)=>{fn();pass++;console.log('PASS',n)};
const dir=fs.mkdtempSync(path.join(os.tmpdir(),'hc-budget-'));const file=path.join(dir,'budget.json');let now=1000;

t('unknown budget fails closed',()=>{const l=new DurableBudgetLedger(file,{clock:()=>now});assert.throws(()=>l.take('missing',1),/BUDGET_NOT_CONFIGURED/);});
t('spend persists across restart',()=>{let l=new DurableBudgetLedger(file,{clock:()=>now});l.configure('api',{capacity:3});assert.equal(l.take('api',2,{decision_id:'d1'}).remaining,1);l=new DurableBudgetLedger(file,{clock:()=>now});assert.equal(l.stats('api').tokens,1);});
t('decision id prevents double charge',()=>{let l=new DurableBudgetLedger(file,{clock:()=>now});const a=l.take('api',1,{decision_id:'d2'});const b=l.take('api',1,{decision_id:'d2'});assert.deepEqual(a,b);assert.equal(l.stats('api').tokens,0);});
t('insufficient quota returns wait not failure',()=>{let l=new DurableBudgetLedger(file,{clock:()=>now});const x=l.take('api',1,{decision_id:'d3'});assert.equal(x.allowed,false);assert.equal(x.retry_after_ms,null);});
t('lazy refill survives idle time',()=>{let l=new DurableBudgetLedger(file,{clock:()=>now});l.configure('rate',{capacity:2,refill_per_ms:0.001});assert.equal(l.take('rate',2).remaining,0);now+=1000;const x=l.take('rate',1);assert.equal(x.allowed,true);assert.equal(x.remaining,0);});
t('one exhausted budget does not block another',()=>{let l=new DurableBudgetLedger(file,{clock:()=>now});l.configure('other',{capacity:1});assert.equal(l.take('api',1).allowed,false);assert.equal(l.take('other',1).allowed,true);});
t('multi-budget denial is atomic',()=>{let l=new DurableBudgetLedger(file,{clock:()=>now});l.configure('a',{capacity:2});l.configure('b',{capacity:0});const before=l.stats('a').tokens;const r=l.takeMany([{budget_id:'a',cost:1},{budget_id:'b',cost:1}],{decision_id:'multi-deny'});assert.equal(r.allowed,false);assert.equal(l.stats('a').tokens,before);assert.equal(l.stats('b').tokens,0);});
t('multi-budget success charges each once and retry is idempotent',()=>{let l=new DurableBudgetLedger(file,{clock:()=>now});l.configure('c',{capacity:2});l.configure('d',{capacity:2});const a=l.takeMany([{budget_id:'c',cost:1},{budget_id:'d',cost:1}],{decision_id:'multi-ok'});const b=l.takeMany([{budget_id:'c',cost:1},{budget_id:'d',cost:1}],{decision_id:'multi-ok'});assert.equal(a.allowed,true);assert.deepEqual(a,b);assert.equal(l.stats('c').tokens,1);assert.equal(l.stats('d').tokens,1);});
console.log(`DURABLE_BUDGET_LEDGER_PASS ${pass}/8 durable=1 idempotent=1 fail_closed=1 atomic_multi=1`);
