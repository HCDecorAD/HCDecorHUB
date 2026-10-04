import {opportunityScore} from "../../../../lib/trend-opportunity.mjs";
import {runWorkersAIText} from "../../../../lib/ai/connections";
export async function POST(request){
  try{
    const body=await request.json(),score=opportunityScore(body);let ai=null;
    if(body.ai!==false){try{ai=await runWorkersAIText("Topic: "+String(body.topic||"unspecified")+"\nScore: "+score.score+"\nDecision: "+score.decision+"\nSignals: "+JSON.stringify(score.parts)+"\nRecommend angle, fastest test, monetization path, and stop condition.",{maxTokens:220})}catch{ai={provider:"cloudflare-workers-ai",text:null}}}
    return Response.json({ok:true,result:{...score,ai_recommendation:ai?.text||null,ai_provider:ai?.provider||null},production_write:false});
  }catch{return Response.json({ok:false,error:"invalid_json"},{status:400})}
}
