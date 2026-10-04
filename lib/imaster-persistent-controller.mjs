const TERMINAL=new Set(["DONE","OWNER_REQUIRED","SAFETY_STOP","HARD_EXTERNAL_BLOCKED"]);
export function nextControllerState({mission={},event={}}={}){
 if(TERMINAL.has(mission.state)) return {action:"STOP",state:mission.state};
 if(event.type==="CHECK_FAILED") return {action:"REPAIR",state:"RETRY_PENDING"};
 if(event.type==="CHECK_PASSED") return {action:"ADVANCE",state:"NEXT_READY"};
 if(event.type==="MERGED") return {action:"WATCH_MAIN_VERIFY",state:"POST_MERGE_VERIFY"};
 if(["CI_RUNNING","QUEUE_WAITING","EXTERNAL_JOB_RUNNING","POST_MERGE_VERIFY"].includes(mission.state)) return {action:"ARM_WATCH",state:mission.state};
 return {action:"COMPUTE_NEXT",state:mission.state||"READY"};
}
export function buildWatchRecord({mission_id,state,target,next_on_success,next_on_failure}){
 if(!mission_id||!state||!target) throw new Error("watch record requires mission_id,state,target");
 return {mission_id,state,target,next_on_success:next_on_success||"ADVANCE",next_on_failure:next_on_failure||"REPAIR",armed:true};
}
