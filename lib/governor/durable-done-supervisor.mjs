const TERMINAL=new Set(['DONE','OWNER_REQUIRED','SAFETY_STOP','HARD_BLOCKED']);
const YIELDING=new Set(['WAITING_RESOURCE','QUEUED','WAITING_RUNNER','WAITING_EXTERNAL']);

export class DurableDoneSupervisor {
  constructor(queue,{workerId='hc-done-supervisor',capabilities=['code','debug'],maxAttempts=3}={}){
    this.queue=queue; this.workerId=workerId; this.capabilities=capabilities; this.maxAttempts=maxAttempts;
  }
  async tick(executor){
    const ready=this.queue.ready({workerCapabilities:this.capabilities});
    if(!ready.length) return {state:'IDLE',mission_id:null};
    const mission=ready[0];
    const claimed=this.queue.claim(mission.mission_id,this.workerId,{workerCapabilities:this.capabilities});
    const fence=claimed.fence_token;
    this.queue.transition(claimed.mission_id,this.workerId,fence,'RUNNING',{checkpoint:claimed.checkpoint||null});
    try{
      const result=await executor(this.queue.get(claimed.mission_id));
      if(result?.owner_required){
        const x=this.queue.transition(claimed.mission_id,this.workerId,fence,'OWNER_REQUIRED',{reason:result.reason||'owner_required',checkpoint:result.checkpoint||null});
        return {state:x.state,mission_id:x.mission_id};
      }
      if(result?.safety_stop){
        const x=this.queue.transition(claimed.mission_id,this.workerId,fence,'SAFETY_STOP',{reason:result.reason||'safety_stop',checkpoint:result.checkpoint||null});
        return {state:x.state,mission_id:x.mission_id};
      }
      const yieldState=result?.yield_state||(result?.waiting_resource?'WAITING_RESOURCE':null);
      if(yieldState){
        if(!YIELDING.has(yieldState)) throw Object.assign(new Error('invalid_yield_state'),{recoverable:false});
        const x=this.queue.transition(claimed.mission_id,this.workerId,fence,yieldState,{resource:result.resource||null,resume_after:result.resume_after||null,checkpoint:result.checkpoint||null,evidence:result.evidence||null});
        return {state:x.state,mission_id:x.mission_id,yielded:true};
      }
      const x=this.queue.transition(claimed.mission_id,this.workerId,fence,'DONE',{result:result??null,checkpoint:result?.checkpoint||null,evidence:result?.evidence||null,fingerprint:result?.fingerprint||null});
      return {state:x.state,mission_id:x.mission_id};
    }catch(error){
      const current=this.queue.get(claimed.mission_id);
      const attempts=Number(current?.attempts||1);
      const recoverable=error?.recoverable!==false;
      const evidence={error:String(error?.message||error),recoverable,attempts,checkpoint:error?.checkpoint||current?.checkpoint||null};
      if(recoverable&&attempts<this.maxAttempts){
        const x=this.queue.transition(claimed.mission_id,this.workerId,fence,'READY',evidence);
        return {state:'RETRY_READY',mission_id:x.mission_id,attempts};
      }
      const x=this.queue.transition(claimed.mission_id,this.workerId,fence,'HARD_BLOCKED',evidence);
      return {state:x.state,mission_id:x.mission_id,attempts};
    }
  }
  reusable(id,fingerprint){
    const x=this.queue.get(id);
    return Boolean(x&&x.state==='DONE'&&fingerprint&&x.fingerprint===fingerprint&&x.evidence);
  }
  missionTerminal(id){const x=this.queue.get(id);return Boolean(x&&TERMINAL.has(x.state))}
}
