import {mkdtemp,mkdir,writeFile,rm} from "node:fs/promises";import {tmpdir} from "node:os";import {join} from "node:path";import {JsonStore} from "../core/json-store.js";import {LogStore} from "../core/log-store.js";import {FindingStore} from "../core/finding-store.js";import {EvidenceIndex} from "../core/evidence-index.js";import {createLocalServer} from "../runtime/local-server.js";
const root=await mkdtemp(join(tmpdir(),"sentinel-daily-"));
try{
  const state=join(root,"state");await mkdir(state);
  const logs=new LogStore(new JsonStore(join(state,"logs.json")));
  const findings=new FindingStore(new JsonStore(join(state,"findings.json")));
  const evidence=new EvidenceIndex(new JsonStore(join(state,"evidence.json")));
  await logs.append({level:"info",message:"startup"});
  await findings.upsert({id:"gsc-1265",projectId:"gsc",state:"ROUTED"});
  await evidence.add({id:"ev1",projectId:"gsc",type:"report"});
  const server=createLocalServer({controller:{run:async()=>({status:"RUNNING"})},status:()=>({status:"SENTINEL_READY"}),logs,findings,evidence,uiDir:new URL("../ui/",import.meta.url)});
  await new Promise(r=>server.listen(0,"127.0.0.1",r));
  try{
    const base=`http://127.0.0.1:${server.address().port}`;
    const ui=(await (await fetch(base+"/")).text()).includes("HC Sentinel");
    const st=await (await fetch(base+"/api/status")).json();
    const fs=await (await fetch(base+"/api/findings")).json();
    const ev=await (await fetch(base+"/api/evidence")).json();
    const lg=await (await fetch(base+"/api/logs")).json();
    const result={ui,status:st.status,findings:fs.items.length,evidence:ev.items.length,logs:lg.items.length};
    console.log(JSON.stringify(result));
    if(!ui||result.status!=="SENTINEL_READY"||result.findings!==1||result.evidence!==1||result.logs!==1)process.exit(2);
  }finally{await new Promise(r=>server.close(r));}
}finally{await rm(root,{recursive:true,force:true});}
