"use client";
import {useEffect,useState} from "react";
export function MasterConsole(){
 const [workspace,setWorkspace]=useState("hcdecor");
 const [module,setModule]=useState("website");
 const [action,setAction]=useState("view");
 const [intent,setIntent]=useState("");
 const [result,setResult]=useState(null);
 const [busy,setBusy]=useState(false);const [runs,setRuns]=useState([]);const [preflight,setPreflight]=useState(null);
 async function refreshPreflight(){try{const r=await fetch(`/api/master/preflight?workspace=${encodeURIComponent(workspace)}`,{cache:"no-store"});const x=await r.json();setPreflight(x.ok?x:null)}catch{setPreflight(null)}}
 async function refreshRuns(){try{const r=await fetch(`/api/master/runs?limit=5&workspace=${encodeURIComponent(workspace)}`,{cache:"no-store"});const x=await r.json();setRuns(Array.isArray(x.runs)?x.runs:[])}catch{setRuns([])}}
 useEffect(()=>{refreshRuns();refreshPreflight()},[workspace]);
 async function run(e){e.preventDefault();if(!intent.trim())return;setBusy(true);setResult(null);
  try{const body=JSON.stringify({workspace_id:workspace,module,action,intent,execute:action==="view"||(action==="create"&&["agents","reports","audit","content","media","publishing"].includes(module))});const r=await fetch("/api/master",{method:"POST",headers:{"content-type":"application/json"},body});setResult(await r.json())}
  catch{setResult({ok:false,error:"request_failed"})}finally{setBusy(false);await refreshRuns()}}
 return <section className="masterConsole"><div className="masterConsoleHead"><div><span>COMMAND CENTER</span><h2>Run Master Agent</h2></div><small>Plan-first · production guarded</small></div>
 {preflight&&<div className="masterPreflight"><b>Preflight</b><span>{preflight.summary.operational?"Operational":"Needs config"}</span><small>{preflight.summary.needs_config.length?preflight.summary.needs_config.join(" · "):`${preflight.summary.modules} modules ready`}</small></div>}
 <form onSubmit={run}><div className="masterFields"><select value={workspace} onChange={e=>setWorkspace(e.target.value)}><option value="hcdecor">HCDecor</option><option value="gsc-senior">GSC</option><option value="amo-nguyen">AMO Nguyen</option></select><input value={module} onChange={e=>setModule(e.target.value)} placeholder="Module: website, content..." maxLength={80}/><select value={action} onChange={e=>setAction(e.target.value)}>{["view","create","edit","export","approve","publish","delete","manage","deploy","rollback"].map(x=><option key={x}>{x}</option>)}</select></div>
 <textarea value={intent} onChange={e=>setIntent(e.target.value)} placeholder="Nhập yêu cầu cho Master Agent..." maxLength={1000}/><button className="primary" disabled={busy||!intent.trim()}>{busy?"Planning...":"Plan & Route"}</button></form>
 {result&&<div className={"masterResult "+(result.ok?"ok":"bad")}>{result.ok?<><b>{result.plan.worker.id} → {result.plan.workspace.name}</b><p>{result.plan.task.action} / {result.plan.task.module} · {result.execution?.verification?.passed?"Executed + verified":result.plan.execution.requires_approval?"Approval required":"Planned safely"}</p></>:<b>{result.error||"request_failed"}</b>}</div>}
 {runs.length>0&&<div className="masterRuns"><b>Recent Runs</b>{runs.map(x=><div key={x.run_id}><span>{x.status}</span><code>{x.agent} · {x.action} · {x.workspace_id}</code></div>)}</div>}</section>;
}
