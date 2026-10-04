const clamp=(n,min=0,max=100)=>Math.max(min,Math.min(max,Number.isFinite(Number(n))?Number(n):0));

export function opportunityScore(input={}){
 const weights={freshness:.18,growth:.22,audience_fit:.18,content_gap:.14,production_speed:.10,money_fit:.18};
 const parts=Object.fromEntries(Object.keys(weights).map(k=>[k,clamp(input[k])]));
 const score=Math.round(Object.entries(weights).reduce((s,[k,w])=>s+parts[k]*w,0));
 const decision=score>=80?"DO_NOW":score>=65?"DO":score>=45?"TEST_3":"SKIP";
 return {score,decision,parts,observed:Boolean(input.observed),evidence_count:Array.isArray(input.evidence)?input.evidence.length:0};
}

export function outlierScore({views=0,baseline_views=0,age_hours=0}={}){
 const base=Math.max(1,Number(baseline_views)||1);
 const ratio=Math.max(0,Number(views)||0)/base;
 const age=Math.max(1,Number(age_hours)||1);
 const velocity=(Number(views)||0)/age;
 return {ratio:Number(ratio.toFixed(2)),velocity:Number(velocity.toFixed(2)),is_outlier:ratio>=2};
}

export function canScale({opportunity,verified_metrics=false,sample_size=0}={}){
 return Boolean(verified_metrics && Number(sample_size)>=3 && opportunity?.score>=65);
}
