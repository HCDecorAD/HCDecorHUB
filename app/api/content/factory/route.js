import {buildContentPack} from "../../../../lib/content-factory.mjs";
import {runWorkersAIText,runWorkersAIImage} from "../../../../lib/ai/connections";
export async function POST(request){
  try{
    const body=await request.json(),pack=buildContentPack(body);
    if(body.ai===false||pack.status!=="READY_FOR_AI_DRAFT")return Response.json({ok:true,result:pack,production_write:false});
    const prompt="Topic: "+pack.topic+"\nAngle: "+pack.angle+"\nHooks: "+pack.hooks.join(" | ")+"\nCreate one concise caption, one short script, and 3 CTA variants.";
    const [text,image]=await Promise.allSettled([runWorkersAIText(prompt,{maxTokens:420}),runWorkersAIImage(pack.topic+" - "+pack.angle)]);
    const ai={provider:"cloudflare-workers-ai",text_status:text.status==="fulfilled"?"PASS":"DEGRADED",media_status:image.status==="fulfilled"?"PASS":"DEGRADED",draft:text.status==="fulfilled"?text.value.text:null,image_data_url:image.status==="fulfilled"?"data:"+image.value.mime+";base64,"+image.value.image_base64:null};
    return Response.json({ok:true,result:{...pack,status:"AI_DRAFT_READY",ai},production_write:false});
  }catch{return Response.json({ok:false,error:"invalid_or_ai_request"},{status:400})}
}
