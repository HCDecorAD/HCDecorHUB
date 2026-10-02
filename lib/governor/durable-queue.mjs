import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';

const TERMINAL=new Set(['DONE','OWNER_REQUIRED','SAFETY_STOP','HARD_BLOCKED']);
const WAITING=new Set(['WAITING_DEPENDENCY','WAITING_RESOURCE','BLOCKED']);

function nowIso(now=Date.now()){return new Date(now).toISOString()}
function clone(v){return JSON.parse(JSON.stringify(v))}
function ensureDir(file){fs.mkdirSync(path.dirname(file),{recursive:true})}

export class DurableMissionQueue {
  constructor(file,{leaseMs=30000,clock=()=>Date.now()}={}){
    this.file=file; this.leaseMs=leaseMs; this.clock=clock;
    this.state={version:1,nextFence:1,missions:{}};
    this.load();
  }
  load(){
    if(!fs.existsSync(this.file)) return this.persist();
    const x=JSON.parse(fs.readFileSync(this.file,'utf8'));
    if(!x||x.version!==1||typeof x.missions!=='object') throw new Error('INVALID_QUEUE_STATE');
    this.state=x; return this.recoverExpiredLeases();
  }
  persist(){
    ensureDir(this.file);
    const tmp=this.file+'.tmp-'+process.pid+'-'+crypto.randomBytes(4).toString('hex');
    fs.writeFileSync(tmp,JSON.stringify(this.state,null,2));
    fs.renameSync(tmp,this.file);
  }
  upsert(mission){
    if(!mission?.mission_id) throw new Error('MISSION_ID_REQUIRED');
    const old=this.state.missions[mission.mission_id]||{};
    this.state.missions[mission.mission_id]={
      state:'READY',priority:0,depends_on:[],required_capabilities:[],
      attempts:0,created_at:old.created_at||nowIso(this.clock()),
      ...old,...clone(mission),updated_at:nowIso(this.clock())
    };
    this.persist(); return clone(this.state.missions[mission.mission_id]);
  }
  list(){return Object.values(this.state.missions).map(clone)}
  get(id){const x=this.state.missions[id]; return x?clone(x):null}
  recoverExpiredLeases(){
    const t=this.clock(); let changed=false;
    for(const m of Object.values(this.state.missions)){
      if(['CLAIMED','RUNNING','VERIFYING'].includes(m.state)&&m.lease_expires_at&&Date.parse(m.lease_expires_at)<=t){
        m.state='READY'; m.worker_id=null; m.lease_expires_at=null; m.recovered_at=nowIso(t);
        m.updated_at=nowIso(t); changed=true;
      }
    }
    if(changed) this.persist();
    return changed;
  }
  ready({workerCapabilities=[]}={}){
    this.recoverExpiredLeases();
    const done=new Set(Object.values(this.state.missions).filter(m=>m.state==='DONE').map(m=>m.mission_id));
    const caps=new Set(workerCapabilities);
    const t=this.clock();
    return Object.values(this.state.missions).filter(m=>{
      if(m.state!=='READY') return false;
      if((m.depends_on||[]).some(d=>!done.has(d))) return false;
      return (m.required_capabilities||[]).every(c=>caps.has(c));
    }).sort((a,b)=>{
      const p=Number(b.priority||0)-Number(a.priority||0); if(p) return p;
      const aa=Date.parse(a.ready_since||a.created_at||nowIso(t));
      const bb=Date.parse(b.ready_since||b.created_at||nowIso(t));
      return aa-bb || String(a.mission_id).localeCompare(String(b.mission_id));
    }).map(clone);
  }
  claim(id,workerId,{workerCapabilities=[]}={}){
    const candidate=this.ready({workerCapabilities}).find(m=>m.mission_id===id);
    if(!candidate) throw new Error('MISSION_NOT_CLAIMABLE');
    const m=this.state.missions[id];
    const fence=this.state.nextFence++;
    m.state='CLAIMED'; m.worker_id=workerId; m.fence_token=fence; m.attempts=Number(m.attempts||0)+1;
    m.lease_expires_at=nowIso(this.clock()+this.leaseMs); m.updated_at=nowIso(this.clock());
    this.persist(); return clone(m);
  }
  heartbeat(id,workerId,fence){
    const m=this.state.missions[id]; if(!m) throw new Error('MISSION_NOT_FOUND');
    if(m.worker_id!==workerId||m.fence_token!==fence) throw new Error('STALE_FENCE');
    if(TERMINAL.has(m.state)) throw new Error('MISSION_TERMINAL');
    m.lease_expires_at=nowIso(this.clock()+this.leaseMs); m.updated_at=nowIso(this.clock());
    this.persist(); return clone(m);
  }
  transition(id,workerId,fence,state,evidence=null){
    const m=this.state.missions[id]; if(!m) throw new Error('MISSION_NOT_FOUND');
    if(m.worker_id!==workerId||m.fence_token!==fence) throw new Error('STALE_FENCE');
    m.state=state; m.updated_at=nowIso(this.clock());
    if(evidence!==null) m.evidence=clone(evidence);
    if(TERMINAL.has(state)||WAITING.has(state)){m.worker_id=null;m.lease_expires_at=null;}
    this.persist(); return clone(m);
  }
}
