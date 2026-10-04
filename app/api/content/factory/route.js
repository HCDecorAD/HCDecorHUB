import {buildContentPack} from "../../../../lib/content-factory.mjs";
export async function POST(request){
  try{
    const body=await request.json();
    return Response.json({ok:true,result:buildContentPack(body),production_write:false});
  }catch{return Response.json({ok:false,error:"invalid_json"},{status:400})}
}
