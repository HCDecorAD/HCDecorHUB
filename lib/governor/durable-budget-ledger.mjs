import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';

function clone(v){return JSON.parse(JSON.stringify(v));}
function ensureDir(file){fs.mkdirSync(path.dirname(file),{recursive:true});}
function atomicWrite(file,value){ensureDir(file);const tmp=file+'.tmp-'+process.pid+'-'+crypto.randomBytes(4).toString('hex');fs.writeFileSync(tmp,JSON.stringify(value,null,2));fs.renameSync(tmp,file);}

export class DurableBudgetLedger {
  constructor(file,{clock=()=>Date.now()}={}){this.file=file;this.clock=clock;this.state={version:1,buckets:{}};this.load();}
  load(){if(!fs.existsSync(this.file)){this.persist();return;}const x=JSON.parse(fs.readFileSync(this.file,'utf8'));if(!x||x.version!==1||typeof x.buckets!=='object')throw new Error('INVALID_BUDGET_STATE');this.state=x;}
  persist(){atomicWrite(this.file,this.state);}
  configure(id,{capacity,refill_per_ms=0}={}){if(!id)throw new Error('BUDGET_ID_REQUIRED');capacity=Number(capacity);refill_per_ms=Number(refill_per_ms);if(!(capacity>=0)||!(refill_per_ms>=0))throw new Error('INVALID_BUDGET_CONFIG');const now=this.clock();const old=this.state.buckets[id];this.state.buckets[id]={capacity,refill_per_ms,tokens:old?Math.min(capacity,this._tokens(old,now)):capacity,last_refill_at:now,decisions:old?.decisions||{}};this.persist();return this.stats(id);}
  _tokens(b,now=this.clock()){const elapsed=Math.max(0,now-Number(b.last_refill_at||now));return Math.min(Number(b.capacity),Number(b.tokens)+elapsed*Number(b.refill_per_ms||0));}
  stats(id){const b=this.state.buckets[id];if(!b)throw new Error('BUDGET_NOT_CONFIGURED');const now=this.clock();return {budget_id:id,capacity:b.capacity,refill_per_ms:b.refill_per_ms,tokens:this._tokens(b,now),last_refill_at:b.last_refill_at};}
  take(id,cost=1,{decision_id}={}){const b=this.state.buckets[id];if(!b)throw new Error('BUDGET_NOT_CONFIGURED');cost=Number(cost);if(!(cost>0))throw new Error('INVALID_BUDGET_COST');if(decision_id&&b.decisions[decision_id])return clone(b.decisions[decision_id]);const now=this.clock();const tokens=this._tokens(b,now);let out;if(tokens>=cost){b.tokens=tokens-cost;b.last_refill_at=now;out={allowed:true,budget_id:id,cost,remaining:b.tokens,retry_after_ms:0,decision_id:decision_id||null};}else{b.tokens=tokens;b.last_refill_at=now;const rate=Number(b.refill_per_ms||0);out={allowed:false,budget_id:id,cost,remaining:tokens,retry_after_ms:rate>0?Math.ceil((cost-tokens)/rate):null,decision_id:decision_id||null};}if(decision_id)b.decisions[decision_id]=out;this.persist();return clone(out);}
}
