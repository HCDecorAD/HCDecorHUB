export const REQUIRED=["auto_wake","post_merge_verify","durable_restart","missed_event_recovery","duplicate_safe","bounded_repair","owner_boundary","soak"];
export function pandaLiveGate(e={}){const missing=REQUIRED.filter(k=>e[k]!==true);return {live:missing.length===0,status:missing.length?"ACCEPTANCE_PENDING":"PANDA_24_7_LIVE",missing};}
export function terminalEvidence({mission_state,receipts=[],main_verify=false}={}){return {done:mission_state==="DONE"&&main_verify===true&&receipts.length>0,state:mission_state||"UNKNOWN",receipt_count:receipts.length};}
