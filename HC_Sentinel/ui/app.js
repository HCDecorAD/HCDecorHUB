const root=document.documentElement;
const toggle=document.getElementById("themeToggle"), input=document.getElementById("commandInput"), mission=document.getElementById("missionStatus");
const saved=localStorage.getItem("hc-sentinel-theme"); if(saved) root.dataset.theme=saved;
async function api(path,options){const res=await fetch(path,options);if(!res.ok)throw new Error(`HTTP_${res.status}`);return res.json();}
function renderList(el,items,formatter){el.innerHTML="";if(!items.length){el.innerHTML='<span class="muted">No items</span>';return;}for(const item of items){const row=document.createElement("div");row.className="listrow";row.textContent=formatter(item);el.appendChild(row);}}
async function loadEvidence(){const el=document.getElementById("evidenceList");try{const x=await api("/api/evidence");renderList(el,x.items||[],i=>`${i.projectId??"-"} • ${i.viewport??"-"} • ${i.id}`);}catch{el.textContent="Evidence unavailable";}}
async function loadFindings(){const el=document.getElementById("findingList");try{const x=await api("/api/findings");renderList(el,x.items||[],i=>`${i.projectId??"-"} • ${i.state??"UNKNOWN"} • ${i.kind??i.id}`);}catch{el.textContent="Findings unavailable";}}
async function loadLogs(){const el=document.getElementById("logList");try{const x=await api("/api/logs?limit=100");renderList(el,x.items||[],i=>`${i.level??"info"} • ${i.type??"log"} • ${i.message??""}`);}catch{el.textContent="Logs unavailable";}}
async function loadRepairs(){const el=document.getElementById("repairList");try{const x=await api("/api/repairs");renderList(el,x.items||[],i=>`${i.state} • ${i.projectId} • ${i.kind} • ${i.id}`);}catch{el.textContent="Repair queue unavailable";}}
function setMission(s){mission.textContent=s;mission.className="chip "+(["BLOCKED","REVIEW_REQUIRED","ROUTED","CHANGED"].includes(s)?"ready":"ok");}
async function runAction(action){
  const command=input.value.trim(); if(!command){setMission("READY");return;}
  setMission(action==="CHECK"?"RUNNING":action);
  try{
    const path=action==="CHECK"?"/api/command":"/api/action";
    const body=action==="CHECK"?{command}:{action,command};
    const result=await api(path,{method:"POST",headers:{"content-type":"application/json"},body:JSON.stringify(body)});
    setMission(result.status??action);
    await Promise.allSettled([loadEvidence(),loadFindings(),loadLogs(),loadRepairs()]);
  }catch{setMission("BLOCKED");}
}
toggle.addEventListener("click",async()=>{const next=root.dataset.theme==="dark"?"light":"dark";root.dataset.theme=next;localStorage.setItem("hc-sentinel-theme",next);try{await api("/api/settings",{method:"POST",headers:{"content-type":"application/json"},body:JSON.stringify({theme:next})});}catch{}});
document.getElementById("runNow").onclick=()=>runAction("CHECK");
document.getElementById("compareNow").onclick=()=>runAction("COMPARE");
document.getElementById("verifyNow").onclick=()=>runAction("VERIFY");
document.getElementById("repairNow").onclick=()=>runAction("REPAIR");
document.getElementById("toggleAddProject").onclick=()=>{const p=document.getElementById("addProjectPanel");p.hidden=!p.hidden;};
document.getElementById("saveProject").onclick=async()=>{
  const id=document.getElementById("projectIdInput").value.trim().toLowerCase(), name=document.getElementById("projectNameInput").value.trim(), url=document.getElementById("projectUrlInput").value.trim(), status=document.getElementById("addProjectStatus");
  try{
    const target=await api("/api/projects",{method:"POST",headers:{"content-type":"application/json"},body:JSON.stringify({id:`${id}-public`,projectId:id,name:name||id.toUpperCase(),url})});
    status.textContent=`ADDED: ${target.target.projectId}`; input.value=`Sentinel kiem tra ${id} desktop`;
  }catch{status.textContent="BLOCKED — check ID/URL";}
};
document.getElementById("refreshEvidence").onclick=loadEvidence;document.getElementById("refreshFindings").onclick=loadFindings;document.getElementById("refreshLogs").onclick=loadLogs;
(async()=>{try{const s=await api("/api/status");document.body.dataset.sentinelStatus=s.status??"UNKNOWN";}catch{document.body.dataset.sentinelStatus="OFFLINE";}await Promise.allSettled([loadEvidence(),loadFindings(),loadLogs(),loadRepairs()]);})();
