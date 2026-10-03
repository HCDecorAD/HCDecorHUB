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

toggle.addEventListener("click",async()=>{
  const next=root.dataset.theme==="dark"?"light":"dark";
  root.dataset.theme=next;
  localStorage.setItem("hc-sentinel-theme",next);
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
  }catch{
    mission.textContent="BLOCKED";mission.className="chip ready";
  }
});

(async()=>{
  try{
    const s=await api("/api/status");
    document.body.dataset.sentinelStatus=s.status??"UNKNOWN";
  }catch{document.body.dataset.sentinelStatus="OFFLINE";}
})();
