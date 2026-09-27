const PROJECT_ID="prj_wI19Kt7XcfB12dBgaXUsrBzKvgkr";
const TEAM_ID="team_IOGcnIwha0LrtpDekjmEMsVg";
const REPO_ID="1387933577";
const REPO="HCDecorAD/HCDecorHUB";
const BASE="https://api.vercel.com";
function token(){return process.env.VERCEL_AUTOMATION_TOKEN}
async function vget(path){const r=await fetch(BASE+path+(path.includes("?")?"&":"?")+"teamId="+TEAM_ID,{headers:{Authorization:"Bearer "+token()},cache:"no-store"});const d=await r.json();return {r,d}}
async function state(){
 if(!token())return {configured:false,canDeploy:false,reason:"NEEDS_TOKEN",branch:"main",target:"production"};
 try{
  const {r,d}=await vget("/v6/deployments?projectId="+PROJECT_ID+"&limit=20");
  if(!r.ok)return {configured:true,canDeploy:false,reason:"VERCEL_STATUS_ERROR",http:r.status};
  const list=d.deployments||[];
  const active=list.find(x=>["BUILDING","QUEUED","INITIALIZING"].includes(x.readyState||x.state));
  const latest=list.find(x=>x.target==="production");
  return {configured:true,canDeploy:!active,reason:active?"DEPLOYMENT_ACTIVE":"READY",active:active?{id:active.uid||active.id,state:active.readyState||active.state,url:active.url}:null,latest:latest?{id:latest.uid||latest.id,state:latest.readyState||latest.state,url:latest.url,sha:latest.meta?.githubCommitSha||null,ref:latest.meta?.githubCommitRef||null}:null,branch:"main",target:"production"};
 }catch{return {configured:true,canDeploy:false,reason:"STATUS_FAILED"}}
}
export async function GET(){return Response.json(await state())}
export async function POST(){
 const s=await state();
 if(!s.configured)return Response.json({ok:false,error:"VERCEL_AUTOMATION_TOKEN chưa được cấu hình",status:s},{status:503});
 if(!s.canDeploy)return Response.json({ok:false,error:"Đã có deployment đang chạy. HCDeploy đã chặn deployment trùng.",status:s},{status:409});
 try{
  const r=await fetch(BASE+"/v13/deployments?teamId="+TEAM_ID,{method:"POST",headers:{Authorization:"Bearer "+token(),"Content-Type":"application/json"},body:JSON.stringify({name:"hcdecorhub",project:PROJECT_ID,target:"production",gitSource:{type:"github",repoId:REPO_ID,ref:"main"}})});
  const d=await r.json();
  if(!r.ok){
   const msg=d.error?.message||"Vercel API rejected deployment";
   const quota=/limited|100|free-per-day|api-deployments/i.test(msg+JSON.stringify(d.error||{}));
   return Response.json({ok:false,http:r.status,error:msg,quotaBlocked:quota,action:quota?"WAIT_FOR_QUOTA_RESET":"CHECK_VERCEL",details:d.error||null,status:{...s,canDeploy:false,reason:quota?"DAILY_QUOTA":"VERCEL_REJECTED"}},{status:r.status});
  }
  return Response.json({ok:true,id:d.id,url:d.url,state:d.readyState||d.status||"QUEUED",branch:"main",target:"production",status:{configured:true,canDeploy:false,reason:"DEPLOYMENT_ACTIVE"}});
 }catch{return Response.json({ok:false,error:"Vercel API request failed",status:s},{status:500})}
}