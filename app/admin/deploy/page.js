"use client";
import {useEffect,useState} from "react";
export default function DeployTool(){
 const [status,setStatus]=useState(null),[busy,setBusy]=useState(false),[result,setResult]=useState(null),[health,setHealth]=useState(null);
 const load=()=>{fetch("/api/deploy",{cache:"no-store"}).then(r=>r.json()).then(setStatus).catch(()=>setStatus({configured:false,canDeploy:false}));fetch("/api/deploy/health",{cache:"no-store"}).then(r=>r.json()).then(setHealth).catch(()=>setHealth({ok:false,checks:[]}))};
 useEffect(()=>{load();const id=setInterval(load,15000);return()=>clearInterval(id)},[]);
 async function deploy(){setBusy(true);setResult(null);try{const r=await fetch("/api/deploy",{method:"POST"});const j=await r.json();setResult(j);if(j.status)setStatus(j.status);else load()}catch{setResult({ok:false,error:"Không thể gọi deploy API"})}finally{setBusy(false)}}
 const ready=status?.configured&&status?.canDeploy&&!busy;
 const badge=!status?.configured?"NEEDS TOKEN":status?.reason==="DAILY_QUOTA"?"QUOTA LOCK":status?.active?"RUNNING":"READY";
 return <main className="admin realAdmin"><div className="adminTop"><div><small>HCDECOR / ADMIN / DEPLOY</small><h1>HCDeploy</h1><p>Production deployment control · chống deploy trùng · bảo vệ quota.</p></div><div className="actions"><a className="btn" href="/admin">← Admin</a><a className="btn" href="https://vercel.com/huycuongonline-4247/hcdecorhub" target="_blank">Vercel</a></div></div>
 <section className="panel deployPanel"><div className="panelHead"><div><small>PRODUCTION</small><h2>main → Production</h2></div><span className={ready?"capOn":"capOff"}>{badge}</span></div>
 <p className="hint">HCDeploy kiểm tra trạng thái trước khi gửi yêu cầu. Nếu deployment đang chạy hoặc Vercel khóa quota, tool không gửi thêm request deploy.</p>
 <button className="btn primary deployMain" disabled={!ready} onClick={deploy}>{busy?"Đang tạo Deployment…":"▶ Deploy Main"}</button>
 {!status?.configured&&<div className="notice"><span>SETUP</span>Thêm <b>VERCEL_AUTOMATION_TOKEN</b> vào Vercel Environment Variables.</div>}
 {status?.active&&<div className="notice"><span>RUNNING</span>Deployment đang chạy: <b>{status.active.state}</b>. Tool đã khóa nút Deploy.</div>}
 {status?.latest&&<div className="notice"><span>LATEST</span><b>{status.latest.state}</b> · {status.latest.ref||"production"} · {status.latest.sha?.slice(0,7)||"SHA chưa có"}</div>}
 {result&&<div className={"deployResult "+(result.ok?"success":"failed")}><b>{result.ok?"Deployment đã được tạo":result.quotaBlocked?"Vercel đã khóa quota hôm nay":"Deployment chưa được tạo"}</b>{result.quotaBlocked&&<p>HCDeploy đã dừng gửi thêm deployment. Chờ quota Vercel reset rồi chạy lại.</p>}<pre>{JSON.stringify(result,null,2)}</pre></div>}</section>
 <section className="panel"><div className="panelHead"><div><small>PRODUCTION HEALTH</small><h2>Website · HUB · Admin · API</h2></div><span className={health?.ok?"capOn":"capOff"}>{health?.ok?"HEALTHY":"CHECK"}</span></div><div className="adminModules">{health?.checks?.map(x=><div className="adminModule" key={x.path}><span><b>{x.path}</b><small>HTTP {x.http||"ERR"}</small></span><strong className={x.ok?"capOn":"capOff"}>{x.ok?"OK":"ERROR"}</strong></div>)}</div><p className="hint">Tự kiểm tra lại mỗi 15 giây.</p></section>
 <section className="panel"><small>GUARD</small><h2>Chống lãng phí deployment</h2><div className="flow"><div><b>1</b><span>Kiểm tra Vercel</span></div><div><b>2</b><span>Chặn trùng</span></div><div><b>3</b><span>Quota guard</span></div><div><b>4</b><span>main</span></div><div><b>5</b><span>Production</span></div><div><b>6</b><span>READY</span></div><div><b>7</b><span>Refresh 15s</span></div></div></section></main>
}