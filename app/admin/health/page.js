"use client";
import {useEffect,useState} from "react";
export default function P(){
 const [h,setH]=useState(null);
 useEffect(()=>{fetch("/api/health").then(r=>r.json()).then(setH).catch(()=>setH({service:"unreachable"}))},[]);
 return <main className="adminSub"><a href="/admin">← Admin</a><small>SYSTEM HEALTH</small><h1>Health Center</h1>
  <div className="subActions"><a href="/api/health">Mở Health API</a><a href="/api/cms/status">CMS Status</a><a href="/api/publish/status">Publish Status</a></div>
  <section><h2>Authority readiness</h2><pre>{h?JSON.stringify({durable:h.durable,identity:h.identity,productionWrite:h.master?.productionWrite},null,2):"Đang kiểm tra…"}</pre></section>
 </main>
}