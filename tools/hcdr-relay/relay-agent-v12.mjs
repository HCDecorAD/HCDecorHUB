import {execFile} from "node:child_process";
import {promisify} from "node:util";
import fs from "node:fs/promises";
import {pathToFileURL} from "node:url";
const execFileAsync=promisify(execFile);
const HOST_ROOT=process.env.HCDR_HOST_ROOT||"D:/HCDecorHUB";\nconst ROOT=process.env.HCDR_V11_ROOT||"D:/HCDecorHUB/HCDR Remote MCP";
const RELAY=process.env.HCDR_RELAY_REPO||"HCDecorAD/HCDecor-HCDR-Relay";
const LABEL=process.env.HCDR_V12_LABEL||"hcdr-v12-test";
const SOURCE=process.env.HCDR_V12_SOURCE||"hocuong-v12-production";
const INTERVAL=Math.max(5000,Number(process.env.HCDR_RELAY_INTERVAL||10000));
const runtime=ROOT+"/runtime",lockFile=runtime+"/hcdr-v12.lock",heartbeatFile=runtime+"/hcdr-v12-heartbeat.json",stateFile=runtime+"/hcdr-v12-state.json";
await fs.mkdir(runtime,{recursive:true});
let lock;
async function pidAlive(pid){try{process.kill(Number(pid),0);return true}catch{return false}}
try{lock=await fs.open(lockFile,"wx");await lock.writeFile(String(process.pid))}
catch{
 let stale=true;try{const pid=(await fs.readFile(lockFile,"utf8")).trim();stale=!(await pidAlive(pid))}catch{}
 if(!stale){console.error("HCDR_V12_ALREADY_RUNNING");process.exit(2)}
 try{await fs.unlink(lockFile)}catch{}
 lock=await fs.open(lockFile,"wx");await lock.writeFile(String(process.pid));
}
const cleanup=async()=>{try{await lock.close()}catch{}try{await fs.unlink(lockFile)}catch{}};
process.on("SIGINT",async()=>{await cleanup();process.exit(0)});process.on("SIGTERM",async()=>{await cleanup();process.exit(0)});
process.env.HCDR_ROOT=ROOT;\nconst hybrid=await import(pathToFileURL(HOST_ROOT+"/tools/hybrid-remote/src/server.js").href);
async function gh(args,input){return (await execFileAsync("gh",args,{input,windowsHide:true,maxBuffer:2*1024*1024})).stdout}
async function api(path,method="GET",fields=[]){const a=["api",path,"--method",method];for(const [k,v] of fields)a.push("-f",k+"="+v);return JSON.parse(await gh(a))}
async function load(){try{return JSON.parse(await fs.readFile(stateFile,"utf8"))}catch{return {jobs:{}}}}
async function save(s){await fs.writeFile(stateFile,JSON.stringify(s,null,2))}
async function comment(n,obj){return api(`repos/${RELAY}/issues/${n}/comments`,"POST",[["body","\`\`\`json\n"+JSON.stringify(obj,null,2)+"\n\`\`\`"]])}
async function close(n){try{await api(`repos/${RELAY}/issues/${n}`,"PATCH",[["state","closed"]])}catch{}}
const allowed=new Set(["health","list_directory","read_file","git_status","git_diff","run_command","start_process","process_output","build"]);
async function runJob(issue){
 const b=JSON.parse(issue.body||"{}");
 if(b.schema!=="hcdr-relay/v1.2")throw Error("bad_schema");
 if(b.source && b.source!==SOURCE)throw Error("source_denied");
 if(!allowed.has(b.tool))throw Error("tool_not_allowed");
 return hybrid.execute(b.tool,b.args||{},{localReachable:true});
}
let busy=false;
async function tick(){
 if(busy)return;busy=true;
 try{
  await fs.writeFile(heartbeatFile,JSON.stringify({ok:true,pid:process.pid,at:new Date().toISOString(),relay:RELAY,label:LABEL,source:SOURCE},null,2));
  const s=await load();const issues=await api(`repos/${RELAY}/issues?state=open&labels=${encodeURIComponent(LABEL)}&per_page=100&sort=created&direction=asc`);
  for(const i of issues){
   const k=String(i.number);if(["completed","executing","result_pending"].includes(s.jobs[k]?.status))continue;
   s.jobs[k]={status:"claimed",at:new Date().toISOString()};await save(s);
   let result;try{s.jobs[k]={status:"executing",at:new Date().toISOString()};await save(s);result=await runJob(i)}catch(e){result={ok:false,error:String(e?.message||e)}}
   try{await comment(i.number,{schema:"hcdr-result/v2",job:i.number,source:SOURCE,...result});s.jobs[k]={status:"completed",ok:result.ok!==false,at:new Date().toISOString()};await save(s);await close(i.number)}
   catch(e){s.jobs[k]={status:"result_pending",error:String(e?.message||e),at:new Date().toISOString()};await save(s)}
  }
 }finally{busy=false}
}
console.log("HCDR_V12_READY",LABEL,SOURCE,process.pid);await tick();setInterval(()=>tick().catch(e=>console.error("v12_tick_error",e.message)),INTERVAL);
