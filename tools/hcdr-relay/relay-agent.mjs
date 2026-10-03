import {execFile} from "node:child_process";
import {promisify} from "node:util";
import fs from "node:fs/promises";
import {pathToFileURL} from "node:url";
import {resultEnvelope} from "./correlation.mjs";

const execFileAsync=promisify(execFile);
const ROOT=process.env.HCDR_ROOT||"D:/HCDecorHUB";
const RELAY=process.env.HCDR_RELAY_REPO;
const INTERVAL=Math.max(3000,Number(process.env.HCDR_RELAY_INTERVAL||5000));
const HEARTBEAT_MS=Math.max(2000,Number(process.env.HCDR_HEARTBEAT_MS||5000));
const MAX_WORKERS=Math.max(1,Math.min(8,Number(process.env.HCDR_MAX_WORKERS||4)));
const JOB_TIMEOUT_MS=Math.max(30000,Number(process.env.HCDR_JOB_TIMEOUT_MS||15*60*1000));
const SELF_UPDATE=process.env.HCDR_RELAY_SELF_UPDATE==="1";
const REPO_ROOT=ROOT+"/repos/HCDecorHUB";
if(!RELAY) throw new Error("HCDR_RELAY_REPO is required");

const runtime=ROOT+"/runtime";
const stateFile=runtime+"/hcdr-relay-state-v2.json";
const lockFile=runtime+"/hcdr-relay.lock";
const heartbeatFile=runtime+"/hcdr-relay-heartbeat.json";
await fs.mkdir(runtime,{recursive:true});

let lock;
async function pidAlive(pid){try{process.kill(Number(pid),0);return true}catch{return false}}
try{
  lock=await fs.open(lockFile,"wx");
  await lock.writeFile(String(process.pid));
}catch{
  let stale=true;
  try{
    const pid=(await fs.readFile(lockFile,"utf8")).trim();
    stale=!(await pidAlive(pid));
  }catch{}
  if(!stale){
    console.error("HCDR_REMOTE_FREE_ALREADY_RUNNING");
    process.exit(2);
  }
  try{await fs.unlink(lockFile)}catch{}
  lock=await fs.open(lockFile,"wx");
  await lock.writeFile(String(process.pid));
  console.log("HCDR_STALE_LOCK_RECOVERED");
}
const cleanup=async()=>{try{await lock.close()}catch{}try{await fs.unlink(lockFile)}catch{}};
process.on("SIGINT",async()=>{await cleanup();process.exit(0)});
process.on("SIGTERM",async()=>{await cleanup();process.exit(0)});

const hybrid=await import(pathToFileURL(ROOT+"/tools/hybrid-remote/src/server.js").href);
async function gh(args,input){const o=await execFileAsync("gh",args,{input,windowsHide:true,maxBuffer:2*1024*1024});return o.stdout}
async function api(path,method="GET",fields=[]){const a=["api",path,"--method",method];for(const [k,v] of fields)a.push("-f",k+"="+v);return JSON.parse(await gh(a))}
async function load(){try{return JSON.parse(await fs.readFile(stateFile,"utf8"))}catch{return {jobs:{}}}}
const state=await load();
let saveChain=Promise.resolve();
function saveState(){
  saveChain=saveChain.catch(()=>{}).then(()=>fs.writeFile(stateFile,JSON.stringify(state,null,2)));
  return saveChain;
}
async function comment(n,obj){return api(`repos/${RELAY}/issues/${n}/comments`,"POST",[["body","\`\`\`json\n"+JSON.stringify(obj,null,2)+"\n\`\`\`"]])}
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

const SAFE_TOOLS=new Set(["health","list_directory","read_file","process_output","git_status","git_diff"]);
const MUTATING_TOOLS=new Set(["write_file","edit_file","run_command","start_process","git_commit","build"]);
const ALLOWED=new Set([...SAFE_TOOLS,...MUTATING_TOOLS]);
function parseBody(issue){
  const body=JSON.parse(issue.body||"{}");
  const schemas=new Set(["hcdr-relay/v1","hcdr-relay/v1.1","hcdr-relay/v1.2"]);
  if(!schemas.has(body.schema)) throw Error("bad_schema");
  if(!ALLOWED.has(body.tool)) throw Error("tool_not_allowed");
  if(body.tool==="read_file" && /(^|[\\/])(\.env|.*secret.*|.*credential.*|.*token.*|.*\.key|.*\.pem)([\\/]|$)/i.test(String(body.args?.path||""))) throw Error("secret_path_denied");
  return body;
}
function laneFor(body){
  if(SAFE_TOOLS.has(body.tool)) return null;
  const requested=String(body.lane||body.resource_key||"default").trim();
  return "mutate:"+requested.slice(0,80);
}
async function runJob(body){
  const context={
    localReachable:true,
    approved:body.approved===true,
    approval_scope:body.approval_scope||null,
    source:body.source||null,
    source_id:body.source_id||null,
    mission_id:body.mission_id||null,
    correlation_id:body.correlation_id||null
  };
  return hybrid.execute(body.tool,body.args||{},context);
}

const active=new Map();
const activeLanes=new Set();
const quarantinedLanes=new Set();
let pollBusy=false;
let sourceRefresh={updated:false,disabled:!SELF_UPDATE};

async function heartbeat(){
  const workers=[...active.values()].map(x=>({
    job:x.issue.number,
    tool:x.body.tool,
    lane:x.lane,
    started_at:x.startedAt,
    deadline_at:x.deadlineAt
  }));
  await fs.writeFile(heartbeatFile,JSON.stringify({
    ok:true,
    pid:process.pid,
    at:new Date().toISOString(),
    relay:RELAY,
    worker_pool:{
      max:MAX_WORKERS,
      active:workers.length,
      available:Math.max(0,MAX_WORKERS-workers.length),
      active_lanes:[...activeLanes],
      quarantined_lanes:[...quarantinedLanes]
    },
    workers,
    source_refresh:sourceRefresh
  },null,2));
}

async function finalize(issue,body,result,status="completed"){
  try{
    await comment(issue.number,resultEnvelope({job:issue.number,body,result}));
    state.jobs[String(issue.number)]={status,ok:result.ok!==false,error:result.error||null,at:new Date().toISOString()};
    await saveState();
    await close(issue.number);
  }catch(e){
    state.jobs[String(issue.number)]={status:"result_pending",error:String(e?.message||e),at:new Date().toISOString()};
    await saveState();
  }
}

async function dispatch(issue,body,lane){
  const key=String(issue.number);
  const startedAt=new Date().toISOString();
  const deadlineAt=new Date(Date.now()+JOB_TIMEOUT_MS).toISOString();
  state.jobs[key]={status:"claimed",worker_pid:process.pid,lane,started_at:startedAt,deadline_at:deadlineAt};
  await saveState();

  const worker={issue,body,lane,startedAt,deadlineAt};
  active.set(key,worker);
  if(lane) activeLanes.add(lane);

  const execution=Promise.resolve()
    .then(()=>runJob(body))
    .then(result=>({result}))
    .catch(error=>({result:{ok:false,error:String(error?.message||error)}}));

  let timer;
  const timeout=new Promise(resolve=>{
    timer=setTimeout(()=>resolve({__timeout:true}),JOB_TIMEOUT_MS);
    timer.unref?.();
  });

  Promise.race([execution,timeout]).then(async outcome=>{
    if(outcome?.__timeout){
      const result={
        ok:false,
        error:"job_lease_timeout",
        state:"QUARANTINED",
        retry_safe:false,
        timeout_ms:JOB_TIMEOUT_MS,
        lane
      };
      if(lane) quarantinedLanes.add(lane);
      await finalize(issue,body,result,"quarantined");
      active.delete(key);
      await heartbeat().catch(()=>{});

      // The underlying execution may still be finishing. Never open the same
      // mutating lane until that execution actually settles.
      execution.finally(()=>{
        if(lane){
          quarantinedLanes.delete(lane);
          activeLanes.delete(lane);
        }
        heartbeat().catch(()=>{});
      });
      return;
    }

    clearTimeout(timer);
    await finalize(issue,body,outcome.result,"completed");
    active.delete(key);
    if(lane) activeLanes.delete(lane);
    await heartbeat().catch(()=>{});
  }).catch(e=>console.error("worker_finalize_error",issue.number,e.message));
}
async function quarantineInterrupted(issue,body,prior){
  const result={
    ok:false,
    error:"uncertain_previous_execution",
    state:"QUARANTINED",
    retry_safe:false,
    previous_claim:prior
  };
  await finalize(issue,body,result,"quarantined");
}

async function tick(){
  if(pollBusy)return;
  pollBusy=true;
  try{
    sourceRefresh=await refreshSource();
    const issues=await api(`repos/${RELAY}/issues?state=open&labels=hcdr-job&per_page=100&sort=created&direction=asc`);
    for(const issue of issues){
      if(active.size>=MAX_WORKERS) break;
      const key=String(issue.number);
      const prior=state.jobs[key];
      if(prior?.status==="completed" || prior?.status==="quarantined" || active.has(key)) continue;

      let body;
      try{body=parseBody(issue)}
      catch(e){
        await finalize(issue,{}, {ok:false,error:String(e?.message||e)},"completed");
        continue;
      }

      if(prior?.status==="claimed"){
        await quarantineInterrupted(issue,body,prior);
        continue;
      }

      const lane=laneFor(body);
      if(lane && (activeLanes.has(lane) || quarantinedLanes.has(lane))) continue;
      await dispatch(issue,body,lane);
    }
  }finally{
    pollBusy=false;
  }
}

console.log("HCDR_REMOTE_FREE_V3_READY",RELAY,"interval",INTERVAL,"workers",MAX_WORKERS,"timeout_ms",JOB_TIMEOUT_MS,"pid",process.pid);
await heartbeat();
await tick();
setInterval(()=>heartbeat().catch(e=>console.error("relay_heartbeat_error",e.message)),HEARTBEAT_MS);
setInterval(()=>tick().catch(e=>console.error("relay_tick_error",e.message)),INTERVAL);
