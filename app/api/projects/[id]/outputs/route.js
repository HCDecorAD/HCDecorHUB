import {getProjectById} from "../../../../../lib/crm/google";
import {listPreviews} from "../../../../../lib/preview-store";

export async function GET(_req,{params}){
  const {id}=await params;
  const project=await getProjectById(id);
  if(!project)return Response.json({ok:false,error:"project_not_found"},{status:404});
  const rows=await listPreviews("hcdecor",100);
  const outputs=rows.filter(x=>x?.artifact?.context?.project_id===id).map(x=>({
    preview_id:x.preview_id,
    workflow_id:x.workflow_id,
    module:x.module,
    type:x.artifact?.type||null,
    state:x.artifact?.state||x.status||null,
    created_at:x.created_at,
    production_write:false
  }));
  return Response.json({ok:true,project_id:id,count:outputs.length,production_write:false,outputs});
}
