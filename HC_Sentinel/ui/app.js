const root=document.documentElement;
let watchers=[],selectedId=null,sources={windows:[],chats:[]},pausedIds=[];

const $=id=>document.getElementById(id);
async function api(path,options={}){const r=await fetch(path,options);const data=await r.json().catch(()=>({}));if(!r.ok)throw new Error(data.error||`HTTP_${r.status}`);return data;}
function toast(msg){const el=$("toast");el.textContent=msg;el.hidden=false;clearTimeout(toast.t);toast.t=setTimeout(()=>el.hidden=true,2200);}
function stateText(s){return ({ACTIVE:"Đang chạy",IDLE:"Đang chờ",BUSY:"Đang bận",STUCK:"Cần chú ý",SENT:"Đã gửi lệnh",COOLDOWN:"Cooldown",WAITING_RESPONSE:"Chờ phản hồi",BLOCKER_HOLD:"Đang giữ blocker",STOPPED:"Đã dừng",ARMED:"Đã arm",OFFLINE:"Offline",BLOCKED:"Blocked"})[s]||s||"—";}
function explain(w){
 const s=w.runtime?.state||"STOPPED";
 if(s==="BLOCKER_HOLD")return "Sentinel đang giữ lệnh vì phát hiện blocker rõ ràng. Sẽ không thúc lệnh vô ích.";
 if(s==="STUCK")return w.autoSend?"Đã vượt ngưỡng STUCK. Sentinel sẽ dispatch khi safety/cooldown cho phép.":"Đã vượt ngưỡng STUCK nhưng đây là Watch Only nên không tự gửi lệnh.";
 if(s==="WAITING_RESPONSE")return "Lệnh đã gửi. Sentinel đang chờ output mới, không gửi trùng.";
 if(s==="COOLDOWN")return "Đang trong thời gian chống spam sau lần gửi gần nhất.";
 if(s==="BUSY")return "Target đang bận. Sentinel không can thiệp.";
 if(s==="ACTIVE")return "Vừa phát hiện thay đổi mới. Đang theo dõi bình thường.";
 if(s==="IDLE")return "Target ổn định, chưa tới ngưỡng STUCK.";
 if(s==="STOPPED")return "Watch đang tắt.";
 return "Sentinel đang theo dõi trạng thái hiện tại.";
}
function shortTail(w){
 const t=(w.runtime?.assistantTail||"").replace(/\s+/g," ").trim();
 if(t)return t.length>140?t.slice(0,140)+"…":t;
 return explain(w);
}
function setRuntime(ok,label){
 $("runtimeStatus").textContent=ok?`● ${label}`:"● OFFLINE";
 $("footerRuntime").textContent=ok?"● Runtime Online":"● Runtime Offline";
 $("runtimeStatus").style.color=ok?"var(--green)":"var(--red)";
}
async function loadStatus(){try{const s=await api("/api/status");setRuntime(true,s.status||"READY");}catch{setRuntime(false,"OFFLINE");}}

function renderStats(){
 $("statTotal").textContent=watchers.length;
 const good=watchers.filter(w=>["ACTIVE","IDLE","BUSY","ARMED"].includes(w.runtime?.state)).length;
 const attention=watchers.filter(w=>["STUCK","BLOCKER_HOLD","BLOCKED","OFFLINE"].includes(w.runtime?.state)).length;
 $("statHealthy").textContent=good;$("statAttention").textContent=attention;
}
function renderWatchList(){
 const el=$("watchList");el.innerHTML="";
 if(!watchers.length){el.innerHTML='<div class="empty">Chưa có Watch. Bấm Add Watch.</div>';return;}
 for(const w of watchers){
   const s=w.runtime?.state||(w.enabled?"ARMED":"STOPPED");
   const b=document.createElement("button");b.className="watch-item"+(w.id===selectedId?" active":"");
   b.innerHTML=`<div class="watch-item-top"><span class="watch-title"><i class="dot ${s}"></i>${esc(w.name||w.id)}</span><span class="state ${s}">${esc(stateText(s))}</span></div><small><span>${esc(w.sourceType==="CHATGPT"?"ChatGPT Exact":"Windows Window")}</span><span>${w.autoSend?"AUTO":"WATCH"}</span></small>`;
   b.onclick=()=>{selectedId=w.id;renderWatchList();renderDetail();};
   el.appendChild(b);
 }
}
function renderDetail(){
 const w=watchers.find(x=>x.id===selectedId);
 $("emptyDetail").hidden=!!w;$("detail").hidden=!w;if(!w)return;
 const s=w.runtime?.state||(w.enabled?"ARMED":"STOPPED");
 $("detailName").textContent=w.name||w.id;
 $("detailMeta").textContent=`${w.sourceType==="CHATGPT"?"ChatGPT Exact":"Windows Window"} · ${w.autoSend?"Auto Continue":"Watch Only"}`;
 $("detailDot").className=`dot ${s}`;
 $("detailState").className=`state ${s}`;$("detailState").textContent=stateText(s);
 $("detailNote").textContent=shortTail(w);
 $("detailExplain").textContent=explain(w);
 $("detailStuck").textContent=`${w.stuckSec||300} sec`;
 $("detailCommand").textContent=w.command||"—";
 $("detailAuto").textContent=w.autoSend?"BẬT":"TẮT";
 $("watchToggleAuto").textContent=w.autoSend?"● Auto ON":"○ Auto OFF";
 $("watchToggleAuto").className="btn "+(w.autoSend?"success":"ghost");
 $("advInterval").value=`${w.intervalSec||60} sec`;$("advCooldown").value=`${w.cooldownSec||600} sec`;
 $("advTarget").value=w.chatAlias||w.titleContains||w.processName||"—";
 $("watchStart").disabled=!!w.enabled;$("watchStop").disabled=!w.enabled;
}
function esc(s){return String(s??"").replace(/[&<>"']/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[c]));}

async function loadWatchers({keep=true}={}){
 try{
   const x=await api("/api/watchers");watchers=x.items||[];
   if(!keep||!watchers.some(w=>w.id===selectedId))selectedId=watchers[0]?.id||null;
   renderStats();renderWatchList();renderDetail();
 }catch(e){toast("Watch list BLOCKED: "+e.message);}
}
async function watchAction(op){
 const w=watchers.find(x=>x.id===selectedId);if(!w)return;
 if(op==="remove"&&!confirm(`Remove watch ${w.name||w.id}?`))return;
 try{
   const r=await api(`/api/watchers/${encodeURIComponent(w.id)}/${op}`,{method:"POST"});
   toast(op==="tick"?`${w.name} → ${stateText(r.result?.state)}`:`${w.name} · ${r.status}`);
   await loadWatchers();
 }catch(e){toast("BLOCKED: "+e.message);}
}
async function toggleAuto(){
 const w=watchers.find(x=>x.id===selectedId);if(!w)return;
 if(w.sourceType==="WINDOW"&&!w.autoSend){toast("Native Window giữ Watch Only để fail-closed.");return;}
 try{
   const payload={...w,autoSend:!w.autoSend};delete payload.runtime;
   await api("/api/watchers",{method:"POST",headers:{"content-type":"application/json"},body:JSON.stringify(payload)});
   toast(`Auto Send ${payload.autoSend?"ON":"OFF"}`);await loadWatchers();
 }catch(e){toast("BLOCKED: "+e.message);}
}
async function pauseAll(){
 const btn=$("pauseAll");
 if(!pausedIds.length){
   pausedIds=watchers.filter(w=>w.enabled).map(w=>w.id);
   await Promise.allSettled(pausedIds.map(id=>api(`/api/watchers/${encodeURIComponent(id)}/stop`,{method:"POST"})));
   btn.textContent="▶ Tiếp tục";toast("Đã tạm dừng toàn bộ Watch");
 }else{
   const ids=[...pausedIds];pausedIds=[];
   await Promise.allSettled(ids.map(id=>api(`/api/watchers/${encodeURIComponent(id)}/start`,{method:"POST"})));
   btn.textContent="Ⅱ Tạm dừng";toast("Đã tiếp tục Watch");
 }
 await loadWatchers();
}

async function loadSources(){
 try{
   const [w,c]=await Promise.all([api("/api/windows"),api("/api/chat-targets")]);sources={windows:w.items||[],chats:c.items||[]};
   const d=$("watchDispatchAlias");d.innerHTML='<option value="">— Alert only —</option>';
   for(const x of sources.chats){const o=document.createElement("option");o.value=x.alias;o.textContent=x.alias+(x.enabled?"":" (disabled)");d.appendChild(o);}
   renderSourceSelect();
 }catch(e){$("watchFormStatus").textContent="Source discovery BLOCKED: "+e.message;}
}
function renderSourceSelect(){
 const type=$("watchSourceType").value,sel=$("watchSourceSelect");sel.innerHTML="";
 const rows=type==="CHATGPT"?sources.chats:sources.windows;
 for(const x of rows){
   const o=document.createElement("option");
   if(type==="CHATGPT"){o.value=x.alias;o.textContent=`${x.alias} · ${x.title||x.conversation_id}`;}
   else{o.value=String(x.hwnd);o.textContent=`${x.processName} · ${x.title}`;o.dataset.process=x.processName;o.dataset.title=x.title;}
   sel.appendChild(o);
 }
 if(type==="CHATGPT"&&sel.value)$("watchDispatchAlias").value=sel.value;
 if(type==="WINDOW")$("watchDispatchAlias").value="";
}
function applyPreset(){
 const p=$("watchPreset").value;
 if(p==="continue"){$("watchStuck").value=300;$("watchCooldown").value=600;$("watchCommand").value="iMaster next";}
 if(p==="strict"){$("watchStuck").value=600;$("watchCooldown").value=900;$("watchCommand").value="iMaster next";}
 if(p==="watch"){$("watchStuck").value=300;$("watchCooldown").value=600;}
}
async function saveWatch(){
 const type=$("watchSourceType").value,sel=$("watchSourceSelect"),opt=sel.options[sel.selectedIndex];
 if(!opt){$("watchFormStatus").textContent="Chưa chọn source.";return;}
 const preset=$("watchPreset").value;
 const autoSend=type==="CHATGPT"&&preset!=="watch";
 const payload={
   name:opt.textContent,sourceType:type,intervalSec:Number($("watchInterval").value||60),
   stuckSec:Number($("watchStuck").value||300),cooldownSec:Number($("watchCooldown").value||600),
   command:$("watchCommand").value.trim()||"iMaster next",autoSend,
   dispatchAlias:autoSend?($("watchDispatchAlias").value||sel.value):null,
   stopOnDone:false,donePatterns:["DONE"],blockerAware:true,enabled:true
 };
 if(type==="CHATGPT")payload.chatAlias=sel.value;
 else{payload.processName=opt.dataset.process;payload.titleContains=opt.dataset.title;}
 try{
   const r=await api("/api/watchers",{method:"POST",headers:{"content-type":"application/json"},body:JSON.stringify(payload)});
   $("watchFormStatus").textContent=`ADDED · ${r.watch.id}`;closeModal("watchModal");await loadWatchers({keep:false});toast("Watch đã Add & Start");
 }catch(e){$("watchFormStatus").textContent="BLOCKED: "+e.message;}
}

function openModal(id){$(id).hidden=false}function closeModal(id){$(id).hidden=true}
async function loadActivity(){
 try{
   const x=await api("/api/logs?limit=12"),el=$("activity");el.innerHTML="";
   const rows=(x.items||[]).slice().reverse().filter(i=>["DESKTOP_WATCH","STARTUP","TRAY_TEST","STATE_BACKUP"].includes(i.type)).slice(0,6);
   if(!rows.length){el.innerHTML='<div class="empty">Chưa có activity.</div>';return;}
   for(const i of rows){const d=document.createElement("div");d.className="activity-row";d.innerHTML=`<b>${esc(i.type)} · ${esc(i.message)}</b><small>${esc(new Date(i.at).toLocaleTimeString())}</small>`;el.appendChild(d);}
 }catch{$("activity").innerHTML='<div class="empty">Activity unavailable.</div>';}
}
async function loadLogs(){
 try{const x=await api("/api/logs?limit=60"),el=$("logList");el.innerHTML="";for(const i of (x.items||[]).slice().reverse()){const d=document.createElement("div");d.className="logrow";d.textContent=`${i.at||""} · ${i.level||"info"} · ${i.type||"log"} · ${i.message||""}`;el.appendChild(d);}}catch(e){$("logList").textContent="Logs unavailable: "+e.message;}
}
async function showEvidence(){
 try{const x=await api("/api/evidence"),el=$("logList");$("logPanel").hidden=false;el.innerHTML="";const rows=(x.items||[]).slice().reverse().slice(0,30);if(!rows.length){el.innerHTML='<div class="empty">No evidence.</div>';return;}for(const i of rows){const d=document.createElement("div");d.className="logrow";d.textContent=`${i.projectId||"-"} · ${i.viewport||"-"} · ${i.id||"-"}`;el.appendChild(d);}toast(`${rows.length} evidence gần nhất`);}catch(e){toast("Evidence BLOCKED: "+e.message);}
}

async function loadProjects(){
 try{const x=await api("/api/projects"),sel=$("projectSelect"),old=sel.value;sel.innerHTML="";for(const t of x.items||[]){const o=document.createElement("option");o.value=t.projectId;o.textContent=t.name||t.projectId.toUpperCase();sel.appendChild(o);}if([...sel.options].some(o=>o.value===old))sel.value=old;templateCommand();}catch{}
}
function templateCommand(){if($("projectSelect").value)$("commandInput").value=`Sentinel kiem tra ${$("projectSelect").value} ${$("viewportSelect").value}`;}
async function projectAction(action){
 const command=$("commandInput").value.trim();if(!command)return;
 $("projectResult").textContent=`${action} đang chạy…`;
 try{
   const path=action==="CHECK"?"/api/command":"/api/action",body=action==="CHECK"?{command}:{action,command};
   const r=await api(path,{method:"POST",headers:{"content-type":"application/json"},body:JSON.stringify(body)});
   $("projectResult").textContent=`${action} · ${r.status||"DONE"}${r.reason?" · "+r.reason:""}`;
 }catch(e){$("projectResult").textContent="BLOCKED · "+e.message;}
}
async function saveProject(){
 const id=$("projectIdInput").value.trim().toLowerCase(),name=$("projectNameInput").value.trim(),url=$("projectUrlInput").value.trim();
 if(!id||!url){$("projectFormStatus").textContent="Cần Project ID + URL.";return;}
 try{
   const r=await api("/api/projects",{method:"POST",headers:{"content-type":"application/json"},body:JSON.stringify({id:`${id}-public`,projectId:id,name:name||id.toUpperCase(),url})});
   $("projectFormStatus").textContent=`ADDED · ${r.target.projectId}`;await loadProjects();setTimeout(()=>closeModal("projectModal"),400);toast("Project đã thêm");
 }catch(e){$("projectFormStatus").textContent="BLOCKED: "+e.message;}
}

async function trayTest(){try{const r=await api("/api/tray-test",{method:"POST"});toast("Tray · "+r.status);await loadActivity();}catch(e){toast("Tray BLOCKED: "+e.message);}}
async function backupState(){try{const r=await api("/api/backup-state",{method:"POST"});toast("Backup · "+r.name);await loadActivity();}catch(e){toast("Backup BLOCKED: "+e.message);}}

$("watchRun").onclick=()=>watchAction("tick");$("watchStart").onclick=()=>watchAction("start");$("watchStop").onclick=()=>watchAction("stop");$("watchRemove").onclick=()=>watchAction("remove");$("watchToggleAuto").onclick=toggleAuto;
$("pauseAll").onclick=pauseAll;
$("addWatch").onclick=async()=>{openModal("watchModal");await loadSources();};
$("watchSourceType").onchange=renderSourceSelect;$("watchSourceSelect").onchange=()=>{if($("watchSourceType").value==="CHATGPT")$("watchDispatchAlias").value=$("watchSourceSelect").value;};$("watchPreset").onchange=applyPreset;$("saveWatch").onclick=saveWatch;
$("toggleTools").onclick=()=>{$("toolsPanel").hidden=!$("toolsPanel").hidden};$("closeTools").onclick=()=>$("toolsPanel").hidden=true;
$("quickProject").onclick=()=>openModal("projectModal");$("quickEvidence").onclick=showEvidence;$("quickTray").onclick=trayTest;$("quickBackup").onclick=backupState;
$("refreshActivity").onclick=loadActivity;$("toggleLog").onclick=async()=>{$("logPanel").hidden=!$("logPanel").hidden;if(!$("logPanel").hidden)await loadLogs();};$("refreshLog").onclick=loadLogs;
$("projectSelect").onchange=templateCommand;$("viewportSelect").onchange=templateCommand;$("projectRun").onclick=()=>projectAction("CHECK");$("compareNow").onclick=()=>projectAction("COMPARE");$("verifyNow").onclick=()=>projectAction("VERIFY");$("repairNow").onclick=()=>projectAction("REPAIR");$("saveProject").onclick=saveProject;
document.querySelectorAll("[data-close]").forEach(b=>b.onclick=()=>closeModal(b.dataset.close));document.querySelectorAll(".modal-backdrop").forEach(m=>m.onclick=e=>{if(e.target===m)m.hidden=true;});
$("themeToggle").onclick=async()=>{const next=root.dataset.theme==="dark"?"light":"dark";root.dataset.theme=next;localStorage.setItem("hc-sentinel-theme",next);$("themeToggle").textContent=next==="dark"?"☀ Sáng":"☾ Tối";try{await api("/api/settings",{method:"POST",headers:{"content-type":"application/json"},body:JSON.stringify({theme:next})});}catch{}};

(async()=>{
 const saved=localStorage.getItem("hc-sentinel-theme")||"light";root.dataset.theme=saved;$("themeToggle").textContent=saved==="dark"?"☀ Sáng":"☾ Tối";
 await Promise.allSettled([loadStatus(),loadProjects(),loadWatchers({keep:false}),loadActivity()]);
 setInterval(()=>{loadStatus();loadWatchers();loadActivity();},5000);
})();