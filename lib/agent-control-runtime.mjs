export class AgentControlRuntime{
 constructor(){this.missions=new Map();this.heartbeats=new Map();}
 assign({mission_id,worker_id,correlation_id,checkpoint=null}){if(!mission_id||!worker_id||!correlation_id)throw new Error('INVALID_ASSIGNMENT');const m={mission_id,worker_id,correlation_id,state:'RUNNING',checkpoint,last_verified_evidence:null,blocker:null,next_action:'continue'};this.missions.set(mission_id,m);return {...m};}
 heartbeat({mission_id,state='RUNNING',checkpoint=null,last_verified_evidence=null,blocker=null,next_action='continue'}){const m=this.missions.get(mission_id);if(!m)throw new Error('UNKNOWN_MISSION');Object.assign(m,{state,checkpoint,last_verified_evidence,blocker,next_action});this.heartbeats.set(mission_id,{mission_id,state,checkpoint,last_verified_evidence,blocker,next_action,at:new Date().toISOString()});return {...this.heartbeats.get(mission_id)};}
 terminal({mission_id,state,evidence_ref}){if(!['DONE','OWNER_REQUIRED','SAFETY_STOP','HARD_BLOCKED'].includes(state))throw new Error('INVALID_TERMINAL');if(state==='DONE'&&!evidence_ref)throw new Error('DONE_REQUIRES_EVIDENCE');const m=this.missions.get(mission_id);if(!m)throw new Error('UNKNOWN_MISSION');m.state=state;m.last_verified_evidence=evidence_ref||m.last_verified_evidence;m.next_action='none';return {...m};}
 snapshot(){return [...this.missions.values()].map(x=>({...x}));}
}
export function createAutoChatBridge(agent){
 return {
  submit({mission_id,worker_id,correlation_id,text}){if(!text)throw new Error('EMPTY_CHAT');return {accepted:true,authority:'operator-ui-only',production_write:false,mission:agent.assign({mission_id,worker_id,correlation_id}),text};},
  status(mission_id){const m=agent.missions.get(mission_id);return m?{mission_id,state:m.state,checkpoint:m.checkpoint,evidence:m.last_verified_evidence}:null;}
 };
}
