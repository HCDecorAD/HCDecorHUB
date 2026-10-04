const root=document.documentElement;
const input=document.getElementById("commandInput");
const mission=document.getElementById("missionStatus");
const projectSelect=document.getElementById("projectSelect");
const viewportSelect=document.getElementById("viewportSelect");
const saved=localStorage.getItem("hc-sentinel-theme");if(saved)root.dataset.theme=saved;

async function api(path,options){const res=await fetch(path,options);if(!res.ok)throw new Error(`HTTP_${res.status}`);return res.json();}
function renderList(el,items,formatter){el.innerHTML="";if(!items.length){el.innerHTML='<span class="muted">No items</span>';return;}for(const item of items.slice().reverse().slice(0,25)){const row=document.createElement("div");row.className="listrow";row.textContent=formatter(item);el.appendChild(row);}}
function templateCommand(){input.value=`Sentinel kiem tra ${projectSelect.value} ${viewportSelect.value}`;}
function setStatus(s){mission.textContent=s;mission.dataset.state=s;document.getElementById("resultBadge").textContent=s;}
function showResult(result,action){
  const status=result.status??action;setStatus(status);
  document.getElementById("resultTitle").textContent=`${action} · ${status}`;
  document.getElementById("resultProject").textContent=result.projectId??result.job?.projectId??projectSelect.value??"—";
  document.getElementById("resultAction").textContent=result.action??action;
  document.getElementById("resultViewport").textContent=result.viewport??result.job?.viewport??viewportSelect.value??"—";
  document.getElementById("resultEvidence").textContent=result.evidenceId??result.job?.evidenceId??"—";
  let msg="";
  if(result.comparison)msg=result.comparison.changed===false?"Không thay đổi so với evidence trước.":result.comparison.changed===true?"Có thay đổi so với evidence trước.":"Chưa có baseline/evidence trước để so sánh.";
  else if(result.findings?.length)msg=`${result.findings.length} finding cần xử lý.`;
  else if(result.suppressedFindings?.length)msg=`PASS — ${result.suppressedFindings.length} finding đã được policy bỏ qua.`;
  else if(result.job)msg=`Repair job ${result.job.id} đã vào queue.`;
  else if(result.reason)msg=result.reason;
  else msg="Hoàn tất.";
  document.getElementById("resultMessage").textContent=msg;
}
async function runAction(action){
  const command=input.value.trim();if(!command){setStatus("READY");return;}
  setStatus(action==="CHECK"?"RUNNING":action);
  try{
    const path=action==="CHECK"?"/api/command":"/api/action";
    const body=action==="CHECK"?{command}:{action,command};
    const result=await api(path,{method:"POST",headers:{"content-type":"application/json"},body:JSON.stringify(body)});
    showResult(result,action);
    await Promise.allSettled([loadEvidence(),loadFindings(),loadLogs(),loadRepairs()]);
  }catch(e){showResult({status:"BLOCKED",reason:String(e.message||e)},action);}
}
async function loadEvidence(){const el=document.getElementById("evidenceList");try{const x=await api("/api/evidence");renderList(el,x.items||[],i=>`${i.projectId??"-"} · ${i.viewport??"-"} · ${i.id}`);}catch{el.textContent="Evidence unavailable";}}
async function loadFindings(){const el=document.getElementById("findingList");try{const x=await api("/api/findings");renderList(el,x.items||[],i=>`${i.projectId??"-"} · ${i.state??"UNKNOWN"} · ${i.kind??i.id}`);}catch{el.textContent="Findings unavailable";}}
async function loadLogs(){const el=document.getElementById("logList");try{const x=await api("/api/logs?limit=100");renderList(el,x.items||[],i=>`${i.level??"info"} · ${i.type??"log"} · ${i.message??""}`);}catch{el.textContent="Logs unavailable";}}
async function loadRepairs(){const el=document.getElementById("repairList");try{const x=await api("/api/repairs");renderList(el,x.items||[],i=>`${i.state} · ${i.projectId} · ${i.kind} · ${i.id}`);}catch{el.textContent="Repair queue unavailable";}}

let desktopSources={windows:[],chats:[]};

async function loadWatchSources(){
  const status=document.getElementById("watchFormStatus");
  try{
    const [w,c]=await Promise.all([api("/api/windows"),api("/api/chat-targets")]);
    desktopSources={windows:w.items||[],chats:c.items||[]};
    const dispatch=document.getElementById("watchDispatchAlias");
    dispatch.innerHTML='<option value="">— Alert only —</option>';
    for(const x of desktopSources.chats){
      const o=document.createElement("option");o.value=x.alias;o.textContent=`${x.alias}${x.enabled?"":" (disabled)"}`;dispatch.appendChild(o);
    }
    renderWatchSourceOptions();
    status.textContent=`${desktopSources.windows.length} windows · ${desktopSources.chats.length} exact chats`;
  }catch(e){status.textContent="Source discovery BLOCKED: "+String(e.message||e);}
}
function renderWatchSourceOptions(){
  const type=document.getElementById("watchSourceType").value;
  const sel=document.getElementById("watchSourceSelect");sel.innerHTML="";
  if(type==="CHATGPT"){
    for(const x of desktopSources.chats){
      const o=document.createElement("option");o.value=x.alias;o.textContent=`${x.alias} · ${x.title||x.conversation_id}`;sel.appendChild(o);
    }
    const d=document.getElementById("watchDispatchAlias");if(sel.value)d.value=sel.value;
  }else{
    for(const x of desktopSources.windows){
      const o=document.createElement("option");o.value=String(x.hwnd);o.textContent=`${x.processName} · ${x.title}`;o.dataset.process=x.processName;o.dataset.title=x.title;sel.appendChild(o);
    }
  }
}
async function saveWatch(){
  const type=document.getElementById("watchSourceType").value;
  const source=document.getElementById("watchSourceSelect");
  const selected=source.options[source.selectedIndex];
  if(!selected){document.getElementById("watchFormStatus").textContent="Chưa chọn source.";return;}
  const autoSend=document.getElementById("watchAutoSend").checked;
  let dispatchAlias=document.getElementById("watchDispatchAlias").value;
  const payload={
    name:selected.textContent,
    sourceType:type,
    intervalSec:Number(document.getElementById("watchInterval").value||60),
    stuckSec:Number(document.getElementById("watchStuck").value||300),
    cooldownSec:Number(document.getElementById("watchCooldown").value||600),
    command:document.getElementById("watchCommand").value.trim()||"iMaster next",
    autoSend,
    dispatchAlias:dispatchAlias||null,
    stopOnDone:document.getElementById("watchStopDone").checked,
    donePatterns:["DONE"],
    enabled:false
  };
  if(type==="CHATGPT"){
    payload.chatAlias=source.value;
    if(autoSend&&!payload.dispatchAlias)payload.dispatchAlias=source.value;
  }else{
    payload.processName=selected.dataset.process;
    payload.titleContains=selected.dataset.title;
  }
  try{
    const r=await api("/api/watchers",{method:"POST",headers:{"content-type":"application/json"},body:JSON.stringify(payload)});
    document.getElementById("watchFormStatus").textContent=`SAVED · ${r.watch.id} · bấm Start để arm`;
    await loadWatchers();
  }catch(e){document.getElementById("watchFormStatus").textContent="BLOCKED: "+String(e.message||e);}
}
async function watcherAction(id,op){
  try{
    const r=await api(`/api/watchers/${encodeURIComponent(id)}/${op}`,{method:"POST"});
    if(op==="tick"&&r.result)document.getElementById("watchFormStatus").textContent=`${id} → ${r.result.state}${r.result.sent?" · SENT":""}`;
    await loadWatchers();
  }catch(e){document.getElementById("watchFormStatus").textContent="BLOCKED: "+String(e.message||e);}
}
async function loadWatchers(){
  const el=document.getElementById("watchList");if(!el)return;
  try{
    const x=await api("/api/watchers"),items=x.items||[];el.innerHTML="";
    if(!items.length){el.innerHTML='<span class="muted">No watches</span>';return;}
    for(const w of items.slice().reverse()){
      const row=document.createElement("div");row.className="watch-row";
      const info=document.createElement("div");info.className="watch-info";
      const title=document.createElement("b");title.textContent=w.name||w.id;
      const meta=document.createElement("small");meta.textContent=`${w.sourceType} · ${w.intervalSec}s · stuck ${w.stuckSec}s · ${w.autoSend?"AUTO":"ALERT"}`;
      info.append(title,meta);
      const state=document.createElement("span");state.className="watch-state";state.dataset.state=w.runtime?.state|| (w.enabled?"ARMED":"STOPPED");state.textContent=w.runtime?.state|| (w.enabled?"ARMED":"STOPPED");
      const actions=document.createElement("div");actions.className="watch-row-actions";
      for(const [op,label] of [["start","Start"],["stop","Stop"],["tick","Run Once"],["remove","Remove"]]){
        const b=document.createElement("button");b.textContent=label;b.onclick=()=>watcherAction(w.id,op);actions.appendChild(b);
      }
      row.append(info,state,actions);el.appendChild(row);
    }
  }catch(e){el.textContent="Watch list unavailable";}
}

async function loadProjects(){
  try{
    const x=await api("/api/projects"), current=projectSelect.value;
    projectSelect.innerHTML="";
    for(const t of x.items||[]){const o=document.createElement("option");o.value=t.projectId;o.textContent=t.name??t.projectId.toUpperCase();if(![...projectSelect.options].some(v=>v.value===o.value))projectSelect.appendChild(o);}
    if([...projectSelect.options].some(v=>v.value===current))projectSelect.value=current;
  }catch{}
}
function openTab(name){
  document.querySelectorAll(".tab-btn").forEach(b=>b.classList.toggle("active",b.dataset.tab===name));
  document.querySelectorAll(".tab-body").forEach(p=>p.classList.toggle("active",p.id===`tab-${name}`));
}
function openProjectModal(){document.getElementById("projectModal").hidden=false;}
function closeProjectModal(){document.getElementById("projectModal").hidden=true;}

document.getElementById("runNow").onclick=()=>runAction("CHECK");
document.getElementById("compareNow").onclick=()=>runAction("COMPARE");
document.getElementById("verifyNow").onclick=()=>runAction("VERIFY");
document.getElementById("repairNow").onclick=()=>runAction("REPAIR");
document.getElementById("toggleAddProject").onclick=openProjectModal;
document.getElementById("addProjectQuick").onclick=openProjectModal;
document.getElementById("openProjects").onclick=openProjectModal;
document.getElementById("closeProjectModal").onclick=closeProjectModal;
document.getElementById("projectModal").onclick=e=>{if(e.target.id==="projectModal")closeProjectModal();};
document.getElementById("saveProject").onclick=async()=>{
  const id=document.getElementById("projectIdInput").value.trim().toLowerCase(),name=document.getElementById("projectNameInput").value.trim(),url=document.getElementById("projectUrlInput").value.trim(),status=document.getElementById("addProjectStatus");
  try{
    const result=await api("/api/projects",{method:"POST",headers:{"content-type":"application/json"},body:JSON.stringify({id:`${id}-public`,projectId:id,name:name||id.toUpperCase(),url})});
    status.textContent=`ADDED: ${result.target.projectId}`;await loadProjects();projectSelect.value=result.target.projectId;templateCommand();setTimeout(closeProjectModal,500);
  }catch{status.textContent="BLOCKED — kiểm tra Project ID / URL";}
};
projectSelect.onchange=templateCommand;viewportSelect.onchange=templateCommand;
document.querySelectorAll(".project-chip[data-project]").forEach(b=>b.onclick=()=>{projectSelect.value=b.dataset.project;document.querySelectorAll(".project-chip").forEach(x=>x.classList.remove("active"));b.classList.add("active");templateCommand();});
document.querySelectorAll("[data-tab]").forEach(b=>b.addEventListener("click",()=>{if(b.dataset.tab)openTab(b.dataset.tab);}));
document.getElementById("refreshEvidence").onclick=loadEvidence;
document.getElementById("refreshFindings").onclick=loadFindings;
document.getElementById("refreshLogs").onclick=loadLogs;
document.getElementById("refreshRepairs").onclick=loadRepairs;
document.getElementById("refreshWatchSources").onclick=loadWatchSources;
document.getElementById("refreshWatchers").onclick=loadWatchers;
document.getElementById("watchSourceType").onchange=renderWatchSourceOptions;
document.getElementById("watchSourceSelect").onchange=()=>{if(document.getElementById("watchSourceType").value==="CHATGPT")document.getElementById("watchDispatchAlias").value=document.getElementById("watchSourceSelect").value;};
document.getElementById("saveWatch").onclick=saveWatch;
document.getElementById("themeToggle").onclick=async()=>{const next=root.dataset.theme==="dark"?"light":"dark";root.dataset.theme=next;localStorage.setItem("hc-sentinel-theme",next);try{await api("/api/settings",{method:"POST",headers:{"content-type":"application/json"},body:JSON.stringify({theme:next})});}catch{}};
document.getElementById("toggleSidebar").onclick=()=>document.getElementById("sidebar").classList.toggle("collapsed");
document.getElementById("toggleOps").onclick=()=>document.getElementById("opsDrawer").classList.toggle("open");
document.getElementById("closeOps").onclick=()=>document.getElementById("opsDrawer").classList.remove("open");

(async()=>{try{const s=await api("/api/status");setStatus(s.status??"READY");}catch{setStatus("OFFLINE");}await loadProjects();templateCommand();await Promise.allSettled([loadEvidence(),loadFindings(),loadLogs(),loadRepairs(),loadWatchSources(),loadWatchers()]);})();
