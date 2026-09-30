import {execFile} from "node:child_process";
import {promisify} from "node:util";
import fs from "node:fs/promises";
const execFileAsync=promisify(execFile);
const ROOT=process.env.HCDR_ROOT||"D:/HCDecorHUB";
const RELAY=process.env.HCDR_RELAY_REPO;
const INTERVAL=Number(process.env.HCDR_RELAY_INTERVAL||10000);
if(!RELAY) throw new Error("HCDR_RELAY_REPO is required (owner/private-repo)");
const stateFile=ROOT+"/runtime/hcdr-relay-state.json";
async function gh(args,input){const o=await execFileAsync("gh",args,{input,windowsHide:true,maxBuffer:1024*1024});return o.stdout}
async function load(){try{return JSON.parse(await fs.readFile(stateFile,"utf8"))}catch{return {seen:[]}}}
async function save(s){await fs.mkdir(ROOT+"/runtime",{recursive:true});await fs.writeFile(stateFile,JSON.stringify(s,null,2))}
async function api(path,method="GET",fields=[]){const a=["api",path,"--method",method];for(const [k,v] of fields)a.push("-f",k+"="+v);return JSON.parse(await gh(a))}
async function runJob(issue){
 const body=JSON.parse(issue.body||"{}"); if(body.schema!=="hcdr-relay/v1") throw Error("bad_schema");
 const allowed=new Set(["health","list_directory","read_file","git_status","git_diff","build"]);
 if(!allowed.has(body.tool)) throw Error("tool_not_allowed");
 const payload=JSON.stringify({tool:body.tool,args:body.args||{}});
 const ps=`$p='${payload.replace(/'/g,"''")}'; Invoke-RestMethod -Uri http://127.0.0.1:17322/api/execute -Method POST -ContentType 'application/json' -Body $p | ConvertTo-Json -Depth 20`;
 const {stdout,stderr}=await execFileAsync("powershell.exe",["-NoProfile","-Command",ps],{cwd:ROOT,windowsHide:true,maxBuffer:1024*1024});
 return {ok:true,stdout:stdout.slice(0,50000),stderr:stderr.slice(0,8000)};
}
async function tick(){
 const s=await load();
 const issues=await api(`repos/${RELAY}/issues?state=open&labels=hcdr-job&per_page=20`);
 for(const i of issues.reverse()){if(s.seen.includes(i.number))continue;let r;try{r=await runJob(i)}catch(e){r={ok:false,error:e.message}}
 await api(`repos/${RELAY}/issues/${i.number}/comments`,"POST",[["body","```json\n"+JSON.stringify({schema:"hcdr-result/v1",job:i.number,...r},null,2)+"\n```"]]);
 s.seen.push(i.number);s.seen=s.seen.slice(-500);await save(s);
 }}
console.log("HCDR_REMOTE_FREE_READY",RELAY,"interval",INTERVAL);
await tick();setInterval(()=>tick().catch(e=>console.error("relay_tick_error",e.message)),INTERVAL);
