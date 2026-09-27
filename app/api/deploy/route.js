const PROJECT_ID="prj_wI19Kt7XcfB12dBgaXUsrBzKvgkr";
const TEAM_ID="team_IOGcnIwha0LrtpDekjmEMsVg";
const REPO="HCDecorAD/HCDecorHUB";
function cfg(){return Boolean(process.env.VERCEL_AUTOMATION_TOKEN)}
export async function GET(){return Response.json({configured:cfg(),projectId:PROJECT_ID,teamId:TEAM_ID,repo:REPO,branch:"main",target:"production"})}
export async function POST(){
 const token=process.env.VERCEL_AUTOMATION_TOKEN;
 if(!token)return Response.json({ok:false,error:"VERCEL_AUTOMATION_TOKEN chưa được cấu hình"},{status:503});
 try{
  const r=await fetch("https://api.vercel.com/v13/deployments?teamId="+encodeURIComponent(TEAM_ID),{method:"POST",headers:{Authorization:"Bearer "+token,"Content-Type":"application/json"},body:JSON.stringify({name:"hcdecorhub",project:PROJECT_ID,target:"production",gitSource:{type:"github",repoId:"1387933577",ref:"main"}})});
  const d=await r.json();
  if(!r.ok)return Response.json({ok:false,http:r.status,error:d.error?.message||"Vercel API rejected deployment",details:d.error||null},{status:r.status});
  return Response.json({ok:true,id:d.id,url:d.url,state:d.readyState||d.status||"QUEUED",branch:"main",target:"production",status:{configured:true}});
 }catch(e){return Response.json({ok:false,error:"Vercel API request failed"},{status:500})}
}