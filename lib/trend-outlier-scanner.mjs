import {outlierScore,opportunityScore} from "./trend-opportunity.mjs";

const median=xs=>{const a=xs.map(Number).filter(Number.isFinite).sort((a,b)=>a-b);if(!a.length)return 0;const m=Math.floor(a.length/2);return a.length%2?a[m]:(a[m-1]+a[m])/2};

export function scanOutliers(videos=[],opts={}){
 const minSamples=Math.max(3,Number(opts.min_samples)||5);
 const valid=videos.filter(v=>Number.isFinite(Number(v.views))&&Number(v.views)>=0);
 if(valid.length<minSamples)return {status:"INSUFFICIENT_EVIDENCE",sample_size:valid.length,results:[]};
 const baseline=median(valid.map(v=>v.views));
 const results=valid.map(v=>{
  const outlier=outlierScore({views:v.views,baseline_views:baseline,age_hours:v.age_hours});
  const growth=Math.min(100,Math.round(outlier.ratio*20+Math.log10(Math.max(1,outlier.velocity))*12));
  const opportunity=opportunityScore({
   freshness:v.freshness??Math.max(0,100-Math.min(100,(Number(v.age_hours)||0)*2)),
   growth,audience_fit:v.audience_fit??50,content_gap:v.content_gap??50,
   production_speed:v.production_speed??50,money_fit:v.money_fit??50,
   observed:true,evidence:v.evidence||[]
  });
  return {...v,baseline_views:baseline,outlier,opportunity};
 }).sort((a,b)=>b.opportunity.score-a.opportunity.score||b.outlier.ratio-a.outlier.ratio);
 return {status:"OBSERVED",sample_size:valid.length,baseline_views:baseline,results};
}
