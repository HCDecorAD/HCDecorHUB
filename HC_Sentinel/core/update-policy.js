export function evaluateUpdate({current,candidate,testsGreen,criticalOpen=0}){
  if(!candidate||candidate===current)return {status:"NO_CHANGE"};
  if(!testsGreen)return {status:"BLOCKED",reason:"TESTS_NOT_GREEN"};
  if(criticalOpen>0)return {status:"BLOCKED",reason:"CRITICAL_FINDINGS"};
  return {status:"READY",from:current,to:candidate};
}
