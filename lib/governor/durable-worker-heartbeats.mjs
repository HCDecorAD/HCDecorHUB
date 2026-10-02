import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';

function clone(v){return JSON.parse(JSON.stringify(v));}
function ensureDir(file){fs.mkdirSync(path.dirname(file),{recursive:true});}
function atomicWrite(file,value){ensureDir(file);const tmp=file+'.tmp-'+process.pid+'-'+crypto.randomBytes(4).toString('hex');fs.writeFileSync(tmp,JSON.stringify(value,null,2));fs.renameSync(tmp,file);}

export class DurableWorkerHeartbeatStore {
  constructor(file,{clock=()=>Date.now(),staleAfterMs=60000}={}){this.file=file;this.clock=clock;this.staleAfterMs=staleAfterMs;this.state={version:1,workers:{}};this.load();}
  load(){if(!fs.existsSync(this.file)){this.persist();return;}const x=JSON.parse(fs.readFileSync(this.file,'utf8'));if(!x||x.version!==1||typeof x.workers!=='object')throw new Error('INVALID_HEARTBEAT_STATE');this.state=x;}
  persist(){atomicWrite(this.file,this.state);}
  beat({worker_id,mission_id=null,correlation_id=null,state='RUNNING',checkpoint=null,capabilities=[]}={}){if(!worker_id)throw new Error('WORKER_ID_REQUIRED');const now=this.clock();this.state.workers[worker_id]={worker_id,mission_id,correlation_id:correlation_id||mission_id||null,state,checkpoint:checkpoint===undefined?null:clone(checkpoint),capabilities:[...capabilities],last_heartbeat_at:new Date(now).toISOString()};this.persist();return this.get(worker_id);}
  get(workerId){const w=this.state.workers[workerId];return w?this._decorate(w):null;}
  list(){return Object.values(this.state.workers).map(w=>this._decorate(w)).sort((a,b)=>a.worker_id.localeCompare(b.worker_id));}
  _decorate(w){const age_ms=Math.max(0,this.clock()-Date.parse(w.last_heartbeat_at));const stale=age_ms>this.staleAfterMs;return {...clone(w),age_ms,stale,health:stale?'SUSPECT':'HEALTHY'};}
}
