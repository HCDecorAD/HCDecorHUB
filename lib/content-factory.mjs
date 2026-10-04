const clean=x=>String(x??"").trim();
const uniq=xs=>[...new Set(xs.map(clean).filter(Boolean))];

export function buildContentPack(input={}){
 const topic=clean(input.topic); const angle=clean(input.angle);
 const hooks=uniq(input.hooks||[]).slice(0,10);
 if(!topic||!angle||hooks.length<3)return {status:"INSUFFICIENT_BRIEF",items:[]};
 const platforms=uniq(input.platforms?.length?input.platforms:["youtube","facebook","instagram","tiktok"]);
 const items=[];
 for(const platform of platforms){
  for(const [i,hook] of hooks.entries()){
   items.push({id:`${platform}-${i+1}`,platform,topic,angle,hook,format:platform==="youtube"?"short_or_long":"short",status:"DRAFT",provenance:input.provenance||[]});
  }
 }
 return {status:"READY_FOR_AI_DRAFT",topic,angle,platforms,hooks,items};
}

export function promoteWinner(pack={},metrics={}){
 const verified=metrics.verified===true, sample=Number(metrics.sample_size)||0, lift=Number(metrics.lift_vs_baseline)||0;
 if(!verified||sample<3||lift<1.2)return {...pack,scale_status:"HOLD"};
 return {...pack,scale_status:"WINNER",winner_evidence:{sample_size:sample,lift_vs_baseline:lift}};
}
