import fs from "node:fs";

export function v9Readiness(manifestPath="config/hcdecor-v9-package-manifest.json"){
 const m=JSON.parse(fs.readFileSync(manifestPath,"utf8"));
 const byId=new Map(m.packages.map(x=>[x.id,x]));
 const blockers=m.packages.filter(x=>x.state!=="DONE").map(x=>({id:x.id,state:x.state,blocker:x.blocker||null,depends_on:(x.depends_on||[]).filter(d=>byId.get(d)?.state!=="DONE")}));
 const done=m.packages.filter(x=>x.state==="DONE").length;
 const integration=byId.get("PKG-15"),freeze=byId.get("PKG-16");
 const integrationDeps=(integration?.depends_on||[]).filter(d=>byId.get(d)?.state!=="DONE");
 return {
   program:m.program_id,
   status:m.status,
   done,total:m.packages.length,
   blockers,
   integration_ready:integrationDeps.length===0,
   integration_blockers:integrationDeps,
   release_ready:freeze?.state==="DONE",
   final_green:false
 };
}

export function finalGate(readiness){
 const pass=Boolean(readiness?.integration_ready&&readiness?.release_ready&&readiness?.done===readiness?.total);
 return {pass,status:pass?"V9_FROZEN_DONE":"BLOCKED",no_evidence_no_green:true};
}
