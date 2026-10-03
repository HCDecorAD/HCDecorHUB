export function mapHcdrIssueState(issue){
  if(!issue) return {state:"UNKNOWN",resolved:false};
  if(issue.state==="closed"){
    return {state:"RESOLVED",resolved:true,reason:issue.state_reason??"completed"};
  }
  const comments=Number(issue.comments??0);
  return {state:comments>0?"ACKNOWLEDGED":"ROUTED",resolved:false};
}
