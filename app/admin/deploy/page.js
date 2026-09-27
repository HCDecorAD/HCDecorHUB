"use client";
import {useEffect,useState} from "react";
export default function DeployTool(){
 const [status,setStatus]=useState(null),[busy,setBusy]=useState(false),[result,setResult]=useState(null);
 const load=()=>fetch("/api/deploy").then(r=>r.json()).then(setStatus).catch(()=>setStatus({configured:false}));
 useEffect(()=>{load()},[]);
 async function deploy(){setBusy(true);setResult(null);try{const r=await fetch("/api/deploy",{method:"POST"});const j=await r.json();setResult(j);setStatus(j.status||status)}catch(e){setResult({ok:false,error:"Không thể gọi deploy API"})}finally{setBusy(false)}}
 return <main className="admin realAdmin"><div className="adminTop"><div><small>HCDECOR / ADMIN / DEPLOY</small><h1>HCDeploy</h1><p>Production deployment control cho nhánh main.</p></div><div className="actions"><a className="btn" href="/admin">← Admin</a><a className="btn" href="https://vercel.com/huycuongonline-4247/hcdecorhub" target="_blank">Vercel</a></div></div>
 <section className="panel deployPanel"><div className="panelHead"><div><small>PRODUCTION</small><h2>Create Deployment → main</h2></div><span className={status?.configured?"capOn":"capOff"}>{status?.configured?"READY":"NEEDS TOKEN"}</span></div>
 <p className="hint">Project: HCDecorHUB · Branch: main · Target: production</p>
 <button className="btn primary deployMain" disabled={!status?.configured||busy} onClick={deploy}>{busy?"Đang tạo Deployment…":"▶ Deploy Main"}</button>
 {!status?.configured&&<div className="notice"><span>SETUP</span>Thêm <b>VERCEL_AUTOMATION_TOKEN</b> vào Vercel Environment Variables. Token chỉ nằm ở server, không đưa vào GitHub.</div>}
 {result&&<div className={"deployResult "+(result.ok?"success":"failed")}><b>{result.ok?"Deployment đã được tạo":"Deployment lỗi"}</b><pre>{JSON.stringify(result,null,2)}</pre></div>}</section>
 <section className="panel"><small>SAFETY</small><h2>Luồng tự động</h2><div className="flow"><div><b>1</b><span>main</span></div><div><b>2</b><span>Vercel API</span></div><div><b>3</b><span>Production</span></div><div><b>4</b><span>Build</span></div><div><b>5</b><span>READY</span></div><div><b>6</b><span>Website</span></div><div><b>7</b><span>HUB/Admin</span></div></div></section></main>
}