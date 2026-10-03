export class PolicyEngine{
  decide(finding){
    const sev=finding?.severity??"low"; const conf=finding?.confidence??1;
    if(conf<0.6) return {action:"REVIEW_REQUIRED",autoRepair:false};
    if(sev==="critical") return {action:"BLOCK_RELEASE",autoRepair:false};
    if(sev==="high") return {action:"ROUTE_REPAIR",autoRepair:true};
    if(sev==="medium") return {action:"ROUTE_REPAIR",autoRepair:false};
    return {action:"TRACK",autoRepair:false};
  }
}
