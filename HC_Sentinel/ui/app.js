const root=document.documentElement;
const toggle=document.getElementById("themeToggle");
const saved=localStorage.getItem("hc-sentinel-theme");
if(saved) root.dataset.theme=saved;
toggle.addEventListener("click",()=>{
  const next=root.dataset.theme==="dark"?"light":"dark";
  root.dataset.theme=next; localStorage.setItem("hc-sentinel-theme",next);
});
document.getElementById("runNow").addEventListener("click",()=>{
  const input=document.getElementById("commandInput");
  const status=document.getElementById("missionStatus");
  status.textContent=input.value.trim()?"RUNNING":"READY";
  status.className="chip "+(input.value.trim()?"ok":"ready");
});
