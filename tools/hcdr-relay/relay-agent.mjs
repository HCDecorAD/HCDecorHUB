import {execFile} from "node:child_process";
import {promisify} from "node:util";
import fs from "node:fs/promises";
import {pathToFileURL} from "node:url";
const execFileAsync=promisify(execFile);
const ROOT=process.env.HCDR_ROOT||"D:/HCDecorHUB";
const RELAY=process.env.HCDR_RELAY_REPO;
const INTERVAL=Math.max(5000,Number(process.env.HCDR_RELAY_INTERVAL||10000));
const SELF_UPDATE=process.env.HCDR_RELAY_SELF_UPDATE==="1";
const REPO_ROOT=ROOT+"/repos/HCDecorHUB";
if(!RELAY) throw new Error("HCDR_RELAY_REPO is required");
const runtime=ROOT+"/runtime", stateFile=runtime+"/hcdr-relay-state-v2.json", lockFile=runtime+"/hcdr-relay.lock", heartbeatFile=runtime+"/hcdr-relay-heartbeat.json";
await fs.mkdir(runtime,{recursive:true});
let lock;
async function pidAlive(pid){try{process.kill(Number(pid),0);return true}catch{return false}}
try{lock=await fs.open(lockFile,"wx");await lock.writeFile(String(process.pid))}
catch{
 let stale=true;try{const pid=(await fs.readFile(lockFile,"utf8")).trim();stale=!(await pidAlive(pid))}catch{}
 if(!stale){console.error("HCDR_REMOTE_FREE_ALREADY_RUNNING");process.exit(2)}
 try{await fs.unlink(lockFile)}catch{}
 lock=await fs.open(lockFile,"wx");await lock.writeFile(String(process.pid));
}
const cleanup=async()=>{try{await lock.close()}catch{}try{await fs.unlink(lockFile)}catch{}};
process.on("SIGINT",async()=>{await cleanup();process.exit(0)});process.on("SIGTERM",async()=>{await cleanup();process.exit(0)});
const hybrid=await import(pathToFileURL(ROOT+"/tools/hybrid-remote/src/server.js").href);
async function gh(args,input){const o=await execFileAsync("gh",args,{input,windowsHide:true,maxBuffer:2*1024*1024});return o.stdout}
async function api(path,method="GET",fields=[]){const a=["api",path,"--method",method];for(const [k,v] of fields)a.push("-f",k+"="+v);return JSON.parse(await gh(a))}
async function load(){try{return JSON.parse(await fs.readFile(stateFile,"utf8"))}catch{return {jobs:{}}}}
async function save(s){await fs.writeFile(stateFile,JSON.stringify(s,null,2))}
async function comment(n,obj){return api(`repos/${RELAY}/issues/${n}/comments`,"POST",[["body","```json\n"+JSON.stringify(obj,null,2)+"\n```"]])}
async function close(n){try{await api(`repos/${RELAY}/issues/${n}`,"PATCH",[["state","closed"]])}catch{}}
async function refreshSource(){
 if(!SELF_UPDATE)return {updated:false,disabled:true};
 try{
  const before=(await execFileAsync("git",["rev-parse","HEAD"],{cwd:REPO_ROOT,windowsHide:true})).stdout.trim();
  await execFileAsync("git",["pull","--ff-only"],{cwd:REPO_ROOT,windowsHide:true});
  const after=(await execFileAsync("git",["rev-parse","HEAD"],{cwd:REPO_ROOT,windowsHide:true})).stdout.trim();
  return {updated:before!==after,before,after};
 }catch(e){return {updated:false,error:String(e?.message||e)}}
}
async function runJob(issue){
 const body=JSON.parse(issue.body||"{}"); if(body.schema!=="hcdr-relay/v1") throw Error("bad_schema");
 const allowed=new Set(["health","list_directory","read_file","write_file","edit_file","run_command","start_process","process_output","git_status","git_diff","git_commit","build"]);
 if(!allowed.has(body.tool)) throw Error("tool_not_allowed");
 if(body.tool==="read_file" && /(^|[\\/])(\.env|.*secret.*|.*credential.*|.*token.*|.*\.key|.*\.pem)([\\/]|$)/i.test(String(body.args?.path||""))) throw Error("secret_path_denied");
 return hybrid.execute(body.tool,body.args||{},{localReachable:true});
}
let busy=false;
async function tick(){
 if(busy)return;busy=true;
 try{
  const s=await load();
  const refresh=await refreshSource();
  await fs.writeFile(heartbeatFile,JSON.stringify({ok:true,pid:process.pid,at:new Date().toISOString(),relay:RELAY,source_refresh:refresh},null,2));
  const issues=await api(`repos/${RELAY}/issues?state=open&labels=hcdr-job&per_page=100&sort=created&direction=asc`);
  for(const i of issues){
   if(s.jobs[String(i.number)]?.status==="completed")continue;
   s.jobs[String(i.number)]={status:"claimed",at:new Date().toISOString()};await save(s);
   let result;try{result=await runJob(i)}catch(e){result={ok:false,error:String(e?.message||e)}}
   try{
    await comment(i.number,{schema:"hcdr-result/v2",job:i.number,...result});
    s.jobs[String(i.number)]={status:"completed",ok:result.ok!==false,at:new Date().toISOString()};await save(s);await close(i.number);
   }catch(e){
    s.jobs[String(i.number)]={status:"result_pending",error:String(e?.message||e),at:new Date().toISOString()};await save(s);
   }
  }
 }finally{busy=false}
}
console.log("HCDR_REMOTE_FREE_V2_READY",RELAY,"interval",INTERVAL,"pid",process.pid);
await tick();setInterval(()=>tick().catch(e=>console.error("relay_tick_error",e.message)),INTERVAL);
