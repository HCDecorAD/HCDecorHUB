import {publishPlan,verifyPublishPlan} from "../../../../lib/publishing-plan.mjs";
export async function POST(request){try{const body=await request.json();const plan=publishPlan(body);return Response.json({ok:true,plan,verification:verifyPublishPlan(plan),production_write:false})}catch{return Response.json({ok:false,error:"invalid_json"},{status:400})}}
