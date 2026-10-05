import fs from "node:fs";
import path from "node:path";
const BASE=process.env.ZEUS_BASE||"http://127.0.0.1:8766";
const ROOT=process.env.ZEUS_ROOT||"D:/HCDecorHUB/Zeus247";
const STATE=path.join(ROOT,"state.json");
const INTERVAL=Number(process.env.ZEUS_INTERVAL_MS||15000);
const wanted=["HCDecorHUB V10","Update PASS","Tiếp tục PANDA LIVE"];
const BOOTSTRAP=process.env.HC_BOOTSTRAP_PATH||"D:/HCDecorHUB/repos/HCDecorHUB/HC_BOOTSTRAP.md";
const FULL_CATALOG=process.env.IMASTER_FULL_CATALOG||"D:/HCDecorHUB/repos/HCDecorHUB/docs/IMASTER-FULL-SKILL-CATALOG.md";
const IMASTER_SKILL_ROUTER=process.env.IMASTER_SKILL_ROUTER||"D:/HCDecorHUB/repos/HCDecorHUB/config/imaster-skill-router.json";
const IMASTER_HOT_SYNC_STATE=process.env.IMASTER_HOT_SYNC_STATE||"D:/HCDecorHUB/Zeus247/imaster-hot-sync.json";
fs.mkdirSync(ROOT,{recursive:true});
function load(){try{return JSON.parse(fs.readFileSync(STATE,"utf8"))}catch{return {version:1,chats:{},lastCycle:null}}}
function save(s){const t=STATE+".tmp";fs.writeFileSync(t,JSON.stringify(s,null,2));fs.renameSync(t,STATE)}
async function get(p){const r=await fetch(BASE+p);if(!r.ok)throw new Error("HTTP "+r.status);return r.json()}
async function post(p,b){const token=process.env.ZEUS_OWNER_TOKEN;if(!token)throw new Error("ZEUS_OWNER_TOKEN_REQUIRED");const r=await fetch(BASE+p,{method:"POST",headers:{"content-type":"application/json","x-zeus-owner":token},body:JSON.stringify(b)});return r.json()}
function newChatPrompt(title,mission=""){const full=/\\biMaster\\s+FULL\\b/i.test(mission);return "[iMaster NEW CHAT AUTO-BOOTSTRAP]\
Apply this bootstrap as the hard operating rule for the entire chat.\
\
"+bootstrap()+(full?"\
\
[iMaster SKILL ROUTER]\
"+skillRouter()+"\
\
[iMaster FULL SKILL CATALOG]\
"+fullCatalog():"\
\
[iMaster SKILL ROUTER]\
"+skillRouter())+"\
\
[MISSION] "+(mission||("Continue "+title+" autonomously."))+"\
\
Start under iMaster immediately. Handle retries/recovery internally. WORKS -> ONE REAL TEST -> PASS -> USE / DONE."}
async function createNewChat(title,mission=""){return post("/create",{title,prompt:newChatPrompt(title,mission)})}
function bootstrap(){try{return fs.readFileSync(BOOTSTRAP,"utf8")}catch{return "HC_BOOTSTRAP unavailable: use LOCAL-FIRST and WORKS -> ONE REAL TEST -> PASS -> USE / DONE."}}
function fullCatalog(){try{return fs.readFileSync(FULL_CATALOG,"utf8")}catch{return "FULL skill catalog unavailable; continue with bootstrap Skill Router."}}
function skillRouter(){try{return fs.readFileSync(IMASTER_SKILL_ROUTER,"utf8")}catch{return "{\\\"mode\\\":\\\"ON_DEMAND_DEFAULT\\\",\\\"status\\\":\\\"fallback\\\"}"}}
function prompt(title){return "[iMaster AUTO-BOOTSTRAP]\
The following bootstrap is authoritative for this worker. Apply it before project instructions.\
\
"+bootstrap()+"\
\
[PROJECT] Continue "+title+" from latest checkpoint. Never replay PASS. Start with iMaster Next. Handle retry/recovery internally; use minimum sufficient proof then ship."}
async function cycle(){
 const s=load(),now=new Date().toISOString(),x=await get("/tabs"),tabs=Array.isArray(x.tabs)?x.tabs:[];
 await hotSyncActiveChats(tabs);
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

function iMasterFingerprint(){
  const crypto=require("node:crypto");
  const parts=[BOOTSTRAP,FULL_CATALOG,IMASTER_SKILL_ROUTER].map(p=>{try{return fs.readFileSync(p)}catch{return Buffer.from("")}});
  return crypto.createHash("sha256").update(Buffer.concat(parts)).digest("hex").slice(0,16);
}
function iMasterHotSyncPrompt(){
  const fp=iMasterFingerprint();
  return `[iMaster HOT_SYNC ${fp}] HARD GLOBAL UPDATE.
Reload current iMaster Bootstrap + Skill Router + FULL Catalog before the next mutating action.
Preserve this chat's Mission, CID, Checkpoint and every PASS/DONE fact. DO NOT redo PASS.
Continue from the next valid action after reload. Reply IMASTER_SYNCED_${fp} only when reload is accepted.`;
}
async function hotSyncActiveChats(knownTabs=null){
  const fp=iMasterFingerprint();
  let prev={}; try{prev=JSON.parse(fs.readFileSync(IMASTER_HOT_SYNC_STATE,"utf8"))}catch{}
  if(prev.fingerprint===fp)return {changed:false,fingerprint:fp};
  const tabs=knownTabs || (Array.isArray((await get("/tabs")).tabs)?(await get("/tabs")).tabs:[]);
  const active=(tabs||[]).filter(t=>t&&t.cid&&!["DONE","CLOSED","STALE"].includes(String(t.status||"").toUpperCase()));
  const results=[];
  for(const t of active){try{await post("/send",{cid:t.cid,prompt:iMasterHotSyncPrompt()});results.push({cid:t.cid,ok:true})}catch(e){results.push({cid:t.cid,ok:false,error:String(e?.message||e)})}}
  fs.mkdirSync(require("node:path").dirname(IMASTER_HOT_SYNC_STATE),{recursive:true});
  fs.writeFileSync(IMASTER_HOT_SYNC_STATE,JSON.stringify({fingerprint:fp,updatedAt:new Date().toISOString(),results},null,2));
  return {changed:true,fingerprint:fp,results};
}
