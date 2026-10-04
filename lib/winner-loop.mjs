const n=x=>Number.isFinite(Number(x))?Number(x):0;
export function evaluateWinner(input={}){
 const baseline=Math.max(1,n(input.baseline)),current=Math.max(0,n(input.current)),sample=Math.max(0,n(input.sample_size));
 const lift=current/baseline,verified=input.verified===true;
 const status=!verified||sample<3?"HOLD":lift>=1.5?"SCALE":lift>=1.2?"VARIANT_TEST":"STOP";
 return {status,verified,sample_size:sample,lift_vs_baseline:Number(lift.toFixed(3)),evidence:input.evidence||[]};
}
export function nextWinnerActions(r={}){return r.status==="SCALE"?["CREATE_VARIANTS","SOCIAL_HANDOFF","REVERIFY"]:r.status==="VARIANT_TEST"?["CREATE_3_VARIANTS","FAST_TEST","REVERIFY"]:r.status==="STOP"?["STOP_WEAK_IDEA","LEARN","RETURN_TO_RADAR"]:["WAIT_FOR_VERIFIED_EVIDENCE"]}