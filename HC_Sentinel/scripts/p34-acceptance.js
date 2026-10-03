import {mkdtemp,rm} from "node:fs/promises";import {tmpdir} from "node:os";import {join} from "node:path";import {JsonStore} from "../core/json-store.js";import {EvidenceIndex} from "../core/evidence-index.js";import {SettingsStore} from "../core/settings-store.js";import {createLocalServer} from "../runtime/local-server.js";
const root=await mkdtemp(join(tmpdir(),"sentinel-accept-"));
const evidence=new EvidenceIndex(new JsonStore(join(root,"evidence.json")));
await evidence.add({id:"ev1",projectId:"gsc",type:"report"});
const settings=new SettingsStore(new JsonStore(join(root,"settings.json")));
const controller={run:async command=>({status:"RUNNING",command})};
const server=createLocalServer({controller,status:()=>({status:"SENTINEL_READY"}),evidence,settings,uiDir:new URL("../ui/",import.meta.url)});
await new Promise(r=>server.listen(0,"127.0.0.1",r));
try{
  const base=`http://127.0.0.1:${server.address().port}`;
  const rootRes=await fetch(base+"/"); const html=await rootRes.text();
  const status=await (await fetch(base+"/api/status")).json();
  const command=await (await fetch(base+"/api/command",{method:"POST",headers:{"content-type":"application/json"},body:JSON.stringify({command:"Sentinel kiểm tra GSC mobile"})})).json();
  const ev=await (await fetch(base+"/api/evidence?projectId=gsc")).json();
  await fetch(base+"/api/settings",{method:"POST",headers:{"content-type":"application/json"},body:JSON.stringify({theme:"light"})});
  const st=await (await fetch(base+"/api/settings")).json();
  const result={ui:html.includes("HC Sentinel"),status:status.status,command:command.status,evidence:ev.items.length,theme:st.theme};
  console.log(JSON.stringify(result));
  if(!result.ui||result.status!=="SENTINEL_READY"||result.command!=="RUNNING"||result.evidence!==1||result.theme!=="light")process.exit(2);
}finally{await new Promise(r=>server.close(r));await rm(root,{recursive:true,force:true});}
