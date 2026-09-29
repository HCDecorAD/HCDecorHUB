import {buildTaskDag} from "../../../../lib/task-dag";
export async function POST(request){let body;try{body=await request.json()}catch{return Response.json({ok:false,error:"invalid_json"},{status:400})}const result=buildTaskDag(body);return Response.json(result,{status:result.status||200})}
