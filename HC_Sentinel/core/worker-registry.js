export const WorkerState=Object.freeze({HEALTHY:"HEALTHY",DEGRADED:"DEGRADED",UNHEALTHY:"UNHEALTHY",OFFLINE:"OFFLINE",RATE_LIMITED:"RATE_LIMITED",DRAINING:"DRAINING"});
export class WorkerRegistry {
  constructor(){ this.workers=new Map(); }
  upsert(w){ this.workers.set(w.id,{state:WorkerState.HEALTHY,capabilities:[],...w}); return this.workers.get(w.id); }
  route(capability){ return [...this.workers.values()].find(w=>w.state===WorkerState.HEALTHY && w.capabilities.includes(capability)) ?? null; }
  setState(id,state){ const w=this.workers.get(id); if(!w) return null; w.state=state; return w; }
}
