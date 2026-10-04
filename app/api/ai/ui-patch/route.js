import {runWorkersAIText} from "../../../../lib/ai/connections";

const clean=v=>String(v||"").trim();
export async function POST(request){
  try{
    const body=await request.json(),schema=body?.schema,instruction=clean(body?.instruction);
    if(!schema?.window||!Array.isArray(schema?.sections)||!instruction)return Response.json({ok:false,error:"invalid_schema_or_instruction"},{status:400});
    const ai=await runWorkersAIText("UI change request: "+instruction+"\nExisting window: "+schema.window+"\nReturn only a concise title for one safe new panel.",{maxTokens:48});
    const title=clean(ai.text).split(/\r?\n/)[0].replace(/^["'*-]+|["']+$/g,"").slice(0,100)||"AI Suggested Panel";
    const id="ai-"+Date.now().toString(36);
    const next={...schema,sections:[...schema.sections,{id,type:"panel",title,source:"AI_PATCH"}]};
    return Response.json({ok:true,schema:next,ai:{provider:ai.provider,model:ai.model},production_write:false});
  }catch{return Response.json({ok:false,error:"ai_patch_failed"},{status:502})}
}
