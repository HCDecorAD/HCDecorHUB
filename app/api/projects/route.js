import {crmRuntime} from "../../../lib/crm/config";
import {appendProject,createProjectFolder,deleteDriveFile,newProjectId} from "../../../lib/crm/google";
const clean=v=>typeof v==="string"?v.trim():"";
export async function POST(req){
 const r=crmRuntime();if(!r.projectProvisionEnabled)return Response.json({ok:false,error:"project_runtime_not_configured"},{status:503});
 let d={};try{d=await req.json()}catch{return Response.json({ok:false,error:"invalid_json"},{status:400})}
 const client=clean(d.client);if(!client)return Response.json({ok:false,error:"client_required"},{status:400});
 const fields=[client,clean(d.leadId),clean(d.service),clean(d.location),clean(d.status),clean(d.notes)];if(fields.some(v=>v.length>5000)||client.length>160)return Response.json({ok:false,error:"field_too_long"},{status:400});
 const projectId=newProjectId(),createdAt=new Date().toISOString();let folder=null;
 try{folder=await createProjectFolder(projectId);await appendProject({projectId,createdAt,folderId:folder.id,folderUrl:folder.folderUrl,client,leadId:clean(d.leadId),service:clean(d.service),location:clean(d.location),status:clean(d.status)||"Active",notes:clean(d.notes)});return Response.json({ok:true,projectId,folderId:folder.id,folderUrl:folder.folderUrl},{status:201})}
 catch{if(folder?.id){try{await deleteDriveFile(folder.id)}catch{}}return Response.json({ok:false,error:"project_create_failed"},{status:502})}
}
