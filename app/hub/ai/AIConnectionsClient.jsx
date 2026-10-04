"use client";
import {useState} from "react";
const modules=[["AI_PROVIDERS","AI Providers"],["AI_MODELS","Models"],["AI_ROUTER","AI Router"],["AI_KEYS","API Keys"],["AI_LOGIN","Login / OAuth"],["CONNECTIONS","Connections"],["PLUGIN_CENTER","Plugins"],["AI_FLOW","Flow / Creative"],["AI_TEST","Test Connection"],["AI_BINDING","Model Binding"]];
export default function AIConnectionsClient({initial}){
 const [state,setState]=useState(initial),[busy,setBusy]=useState(false);
 async function test(){setBusy(true);try{const r=await fetch("/api/ai/status?live=1",{cache:"no-store"});setState(await r.json())}finally{setBusy(false)}}
 const health=new Map(Array.isArray(state.liveHealth)?state.liveHealth.map(x=>[x.id,x]):[]);
 return <><div className="notice">Secrets stay server-side. Configuration state is never shown as live health until Test Connection runs.</div>
 <section className="opsCards">{modules.map(([id,name])=><article key={id} data-module={id}><small>{id}</small><strong>{name}</strong><p>{id==="AI_KEYS"?"Server-only secret presence; values are never returned.":id==="AI_BINDING"?Object.entries(state.bindings||{}).map(([k,v])=>k+":"+(v||"unbound")).join(" · "):id==="AI_TEST"?"Runs real provider probes only when server credentials exist.":"V9 AI & Connections contract module."}</p></article>)}</section>
 <section className="opsPanel"><div className="panelHead"><div><small>CONNECTIONS</small><h2>Provider Status</h2></div><button className="primary" onClick={test} disabled={busy}>{busy?"Testing…":"Test Connection"}</button></div>
 {(state.providers||[]).map(p=>{const h=health.get(p.id);return <div className="capRow" key={p.id}><span><b>{p.id}</b><small>{p.model||"model not bound"} · {p.auth}</small></span><strong className={h?.liveHealth==="healthy"?"capOn":"capOff"}>{h?.liveHealth?.toUpperCase()||(p.configured?"CONFIGURED / NOT TESTED":"NOT CONFIGURED")}</strong></div>})}
 </section></>
}
