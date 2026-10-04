import crypto from "node:crypto";
export const TERMINAL=new Set(["DONE","OWNER_REQUIRED","SAFETY_STOP","HARD_EXTERNAL_BLOCKED"]);
export function normalizeMission(m={}){
 if(!m.mission_id) throw new Error("mission_id required");
 return {...m,state:m.state||"READY",attempt:Number(m.attempt||0),revision:Number(m.revision||0),evidence:m.evidence||[]};
}
export function shaModel({trigger_sha,controller_sha,desired_sha,actual_sha}={}){
 if(!trigger_sha||!controller_sha||!desired_sha) return {ok:false,action:"OWNER_REQUIRED",reason:"SHA_INCOMPLETE"};
 if(actual_sha&&actual_sha!==desired_sha) return {ok:false,action:"RECONCILE_STALE_STATE",reason:"DESIRED_SHA_MOVED"};
 return {ok:true,trigger_sha,controller_sha,desired_sha};
}
export function idempotencyKey({repo,mission_id,event_id,action,target_sha}={}){
 const raw=[repo,mission_id,event_id,action,target_sha].join(":");
 if([repo,mission_id,event_id,action,target_sha].some(x=>!x)) throw new Error("idempotency fields required");
 return crypto.createHash("sha256").update(raw).digest("hex");
}
export function classifyFailure(x={}){
 const kind=String(x.kind||"unknown").toLowerCase();
 if(["network","runner","rate_limit","timeout"].includes(kind)) return "TRANSIENT";
 if(kind==="flake") return "SUSPECTED_FLAKE";
 if(["assertion","compile","test"].includes(kind)) return "DETERMINISTIC";
 if(["security","permission","policy"].includes(kind)) return "POLICY_BLOCK";
 if(kind==="stale_sha") return "STALE_STATE";
 return "UNKNOWN";
}
export function remediation({failure_class,attempt=0,max_transient_retries=2}={}){
 if(failure_class==="TRANSIENT") return attempt<max_transient_retries?{action:"RETRY",next_attempt:attempt+1}:{action:"OWNER_REQUIRED",reason:"RETRY_BUDGET_EXHAUSTED"};
 if(failure_class==="SUSPECTED_FLAKE") return attempt<1?{action:"RETRY_DIAGNOSTIC",next_attempt:attempt+1}:{action:"REPAIR",reason:"FLAKE_REPRODUCED"};
 if(failure_class==="DETERMINISTIC") return {action:"REPAIR"};
 if(failure_class==="STALE_STATE") return {action:"RECONCILE_STALE_STATE"};
 return {action:"OWNER_REQUIRED",reason:"FAIL_CLOSED"};
}
export function reconcile({mission,event={},actual_sha,ledger=new Set()}={}){
 const m=normalizeMission(mission);
 if(TERMINAL.has(m.state)) return {action:"STOP",state:m.state};
 const sha=shaModel({...m,actual_sha});
 if(!sha.ok) return {action:sha.action,state:"RECONCILE",reason:sha.reason};
 const key=idempotencyKey({repo:m.repo,mission_id:m.mission_id,event_id:event.event_id,action:event.action||"OBSERVE",target_sha:m.desired_sha});
 if(ledger.has(key)) return {action:"NOOP_DUPLICATE",state:m.state,idempotency_key:key};
 if(event.type==="CHECK_FAILED"){const failure_class=classifyFailure(event);return {...remediation({failure_class,attempt:m.attempt}),failure_class,idempotency_key:key};}
 if(event.type==="CHECK_PASSED") return {action:"ADVANCE",state:"NEXT_READY",idempotency_key:key};
 if(event.type==="MERGED") return {action:"VERIFY_POST_MERGE",state:"POST_MERGE_VERIFY",idempotency_key:key};
 return {action:"OBSERVE",state:m.state,idempotency_key:key};
}
