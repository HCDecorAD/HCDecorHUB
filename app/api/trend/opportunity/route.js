import {opportunityScore} from "../../../../lib/trend-opportunity.mjs";
export async function POST(request){
  try{
    const body=await request.json();
    return Response.json({ok:true,result:opportunityScore(body),production_write:false});
  }catch{return Response.json({ok:false,error:"invalid_json"},{status:400})}
}
