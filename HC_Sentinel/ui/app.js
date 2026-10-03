const root=document.documentElement;
const toggle=document.getElementById("themeToggle");
const input=document.getElementById("commandInput");
const mission=document.getElementById("missionStatus");
const saved=localStorage.getItem("hc-sentinel-theme");
if(saved) root.dataset.theme=saved;

async function api(path,options){
  const res=await fetch(path,options);
  if(!res.ok) throw new Error(`HTTP_${res.status}`);
  return res.json();
}

function renderList(el,items,formatter){
  el.innerHTML="";
  if(!items.length){el.innerHTML='<span class="muted">No items</span>';return;}
  for(const item of items){
    const row=document.createElement("div");
    row.className="listrow";
    row.textContent=formatter(item);
    el.appendChild(row);
  }
}

async function loadEvidence(){
  const el=document.getElementById("evidenceList");
  try{
    const x=await api("/api/evidence");
    renderList(el,x.items||[],i=>`${i.projectId??"-"} • ${i.type??"evidence"} • ${i.id}`);
  }catch{el.textContent="Evidence unavailable";}
}

async function loadLogs(){\n  const el=document.getElementById("logList");\n  try{\n    const x=await api("/api/logs?limit=100");\n    renderList(el,x.items||[],i=>`${i.level??"info"} • ${i.type??"log"} • ${i.message??""}`);\n  }catch{el.textContent="Logs unavailable";}\n}\n\nasync function loadFindings(){
  const el=document.getElementById("findingList");
  try{
    const x=await api("/api/findings");
    renderList(el,x.items||[],i=>`${i.projectId??"-"} • ${i.state??"UNKNOWN"} • ${i.id}`);
  }catch{el.textContent="Findings unavailable";}
}

toggle.addEventListener("click",async()=>{
  const next=root.dataset.theme==="dark"?"light":"dark";
  root.dataset.theme=next;localStorage.setItem("hc-sentinel-theme",next);
  try{await api("/api/settings",{method:"POST",headers:{"content-type":"application/json"},body:JSON.stringify({theme:next})});}catch{}
});

document.getElementById("runNow").addEventListener("click",async()=>{
  const command=input.value.trim();
  if(!command){mission.textContent="READY";mission.className="chip ready";return;}
  mission.textContent="RUNNING";mission.className="chip ok";
  try{
    const result=await api("/api/command",{method:"POST",headers:{"content-type":"application/json"},body:JSON.stringify({command})});
    mission.textContent=result.status??"RUNNING";
    mission.className="chip "+(["BLOCKED","REVIEW_REQUIRED"].includes(result.status)?"ready":"ok");
  }catch{mission.textContent="BLOCKED";mission.className="chip ready";}
});

document.getElementById("refreshEvidence").addEventListener("click",loadEvidence);
document.getElementById("refreshFindings").addEventListener("click",loadFindings);\ndocument.getElementById("refreshLogs").addEventListener("click",loadLogs);

(async()=>{
  try{const s=await api("/api/status");document.body.dataset.sentinelStatus=s.status??"UNKNOWN";}
  catch{document.body.dataset.sentinelStatus="OFFLINE";}
  await Promise.allSettled([loadEvidence(),loadFindings(),loadLogs()]);
})();
