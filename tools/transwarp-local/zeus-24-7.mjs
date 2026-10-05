import fs from "node:fs";
import path from "node:path";
const BASE=process.env.ZEUS_BASE||"http://127.0.0.1:8766";
const ROOT=process.env.ZEUS_ROOT||"D:/HCDecorHUB/Zeus247";
const STATE=path.join(ROOT,"state.json");
const INTERVAL=Number(process.env.ZEUS_INTERVAL_MS||15000);
const wanted=["HCDecorHUB V10","Update PASS","Tiếp tục PANDA LIVE"];
fs.mkdirSync(ROOT,{recursive:true});
function load(){try{return JSON.parse(fs.readFileSync(STATE,"utf8"))}catch{return {version:1,chats:{},lastCycle:null}}}
function save(s){const t=STATE+".tmp";fs.writeFileSync(t,JSON.stringify(s,null,2));fs.renameSync(t,STATE)}
async function get(p){const r=await fetch(BASE+p);if(!r.ok)throw new Error("HTTP "+r.status);return r.json()}
async function post(p,b){const token=process.env.ZEUS_OWNER_TOKEN;if(!token)throw new Error("ZEUS_OWNER_TOKEN_REQUIRED");const r=await fetch(BASE+p,{method:"POST",headers:{"content-type":"application/json","x-zeus-owner":token},body:JSON.stringify(b)});return r.json()}
function prompt(title){return "[iMaster ZEUS247] Inherit HC_BOOTSTRAP_V2_LOCAL_FIRST. Continue "+title+" from latest checkpoint. Never replay PASS. When current unit finishes, continue with next valid runnable action. If blocked, checkpoint and hand off without stopping the fleet. Start with iMaster Next."}
async function cycle(){
 const s=load(),now=new Date().toISOString(),x=await get("/tabs"),tabs=Array.isArray(x.tabs)?x.tabs:[];
 for(const t of tabs){if(t.cid)s.chats[t.cid]={...(s.chats[t.cid]||{}),cid:t.cid,tabId:t.tabId,title:t.title,busy:!!t.busy,ready:!!t.ready,lastSeen:now,state:t.busy?"BUSY":t.ready?"IDLE":"UNKNOWN"}}
 for(const title of wanted){
  const matches=tabs.filter(t=>String(t.title||"").toLowerCase()===title.toLowerCase());
  if(matches.length!==1){s.missing=s.missing||{};s.missing[title]={state:"UNKNOWN",at:now};continue}
  const t=matches[0],c=s.chats[t.cid];
  if(t.busy||!t.ready)continue;
  const key=title+"|"+t.cid+"|"+(c.checkpoint||"default");
  if(c.lastDispatchKey===key)continue;
  const r=await post("/send",{cid:t.cid,prompt:prompt(title)});
  c.lastDispatch={at:now,result:r};if(r?.ok)c.lastDispatchKey=key;
 }
 s.lastCycle=now;save(s);return {ok:true,tabs:tabs.length,lastCycle:now};
}
let running=false;
async function tick(){if(running)return;running=true;try{console.log(JSON.stringify(await cycle()))}catch(e){console.error(JSON.stringify({ok:false,error:String(e.message||e)}))}finally{running=false}}
await tick();setInterval(tick,INTERVAL);
