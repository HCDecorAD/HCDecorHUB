"use client";
import {useEffect,useMemo,useState} from "react";
import ThemeToggle from "./ThemeToggle";

const UI_KEY="hcdecor-v9-shell";
const LANG_KEY="hcdecor-language";

export const hubNav=[
  ["WIN_HOME","Home","Hôm Nay","/hub"],
  ["WIN_TREND","Trend & Monetization","Trend & Kiếm Tiền","/hub/trend"],
  ["WIN_SOCIAL","Social Center","Trung Tâm MXH","/hub/social"],
  ["WIN_PROJECTS","Project Center","Trung Tâm Dự Án","/hub/projects"],
  ["WIN_IMASTER","iMaster Center","Trung Tâm iMaster","/hub/agents"],
  ["WIN_BUSINESS","Business Center","Trung Tâm Kinh Doanh","/hub/business"],
  ["WIN_SYSTEM","System Center","Trung Tâm Hệ Thống","/admin"]
];

export function HubShell({title,eyebrow="HCDECOR HUB / MASTER",children,sidePanel=null,aiPanel=null}){
  const [collapsed,setCollapsed]=useState(false);
  const [focus,setFocus]=useState(false);
  const [lang,setLang]=useState("EN");
  const [query,setQuery]=useState("");
  const [runtimeOpen,setRuntimeOpen]=useState(false);
  const [runtime,setRuntime]=useState({state:"idle",data:null,error:null});

  useEffect(()=>{
    try{
      const ui=JSON.parse(localStorage.getItem(UI_KEY)||"{}");
      setCollapsed(Boolean(ui.collapsed));
      setFocus(Boolean(ui.focus));
      setLang(localStorage.getItem(LANG_KEY)==="VN"?"VN":"EN");
    }catch{}
  },[]);

  const persist=(patch)=>{
    const next={collapsed,focus,...patch};
    localStorage.setItem(UI_KEY,JSON.stringify(next));
  };
  const toggleCollapsed=()=>setCollapsed(v=>{const n=!v;persist({collapsed:n});return n});
  const toggleFocus=()=>setFocus(v=>{const n=!v;persist({focus:n});return n});
  const toggleLang=()=>setLang(v=>{const n=v==="EN"?"VN":"EN";localStorage.setItem(LANG_KEY,n);return n});

  const visibleNav=useMemo(()=>{
    const q=query.trim().toLowerCase();
    if(!q) return hubNav;
    return hubNav.filter(([id,en,vi])=>[id,en,vi].some(x=>x.toLowerCase().includes(q)));
  },[query]);

  const openRuntime=async()=>{
    setRuntimeOpen(true);
    if(runtime.state==="loading"||runtime.state==="ok") return;
    setRuntime({state:"loading",data:null,error:null});
    try{
      const res=await fetch("/api/master/readiness",{cache:"no-store"});
      const data=await res.json();
      if(!res.ok) throw new Error(data?.error||("HTTP "+res.status));
      setRuntime({state:"ok",data,error:null});
    }catch(err){
      setRuntime({state:"error",data:null,error:String(err?.message||err)});
    }
  };

  const shellClass=["v9Shell",collapsed?"isCollapsed":"",focus?"isFocus":""].filter(Boolean).join(" ");
  return <main className={shellClass}>
    <aside className="v9Nav" data-module="NAV">
      <div className="v9BrandRow">
        <a className="v9Brand" href="/hub"><b>HC</b><span>Decor HUB<small>iMASTER V9</small></span></a>
        <button className="v9IconBtn" onClick={toggleCollapsed} aria-label={collapsed?"Expand sidebar":"Collapse sidebar"} title={collapsed?"Expand":"Collapse"}>{collapsed?"»":"«"}</button>
      </div>
      <nav>
        {visibleNav.map(([id,en,vi,u])=><a href={u} key={id} data-window-id={id} title={lang==="EN"?vi:en}><b>{collapsed?id.replace("WIN_","").slice(0,2):lang==="EN"?en:vi}</b>{!collapsed&&<small>{id}</small>}</a>)}
      </nav>
      {!collapsed&&<div className="v9NavFoot"><a href="/">Public Website</a><a href="/admin/settings">Settings</a></div>}
    </aside>

    <section className="v9Main">
      <header className="v9Top" data-module="TOPBAR">
        <div className="v9TopLeft">
          <button className="v9IconBtn v9MobileNav" onClick={toggleCollapsed} aria-label="Toggle navigation">☰</button>
          <label className="v9Search" data-module="SEARCH">
            <span>⌕</span>
            <input value={query} onChange={e=>setQuery(e.target.value)} placeholder={lang==="EN"?"Search windows...":"Tìm cửa sổ..."} aria-label="Global Search"/>
          </label>
        </div>
        <div className="v9TopActions">
          <a className="v9ActionLink" href="/hub/content" data-module="QUICK_ACTION">+ {lang==="EN"?"Create":"Tạo Mới"}</a>
          <button className="v9ActionBtn" onClick={toggleLang} data-module="LANGUAGE" title="EN | VN">{lang}</button>
          <ThemeToggle/>
          <a className="v9IconLink" href="/hub/review" data-module="NOTICE" title={lang==="EN"?"Notifications / approvals":"Thông báo / duyệt"}>●</a>
          <button className={"v9ActionBtn "+(focus?"active":"")} onClick={toggleFocus} title="Focus Mode">{focus?"Exit Focus":"Focus"}</button>
          <a className="v9Account" href="/admin/settings" data-module="ACCOUNT_MENU">HC</a>
        </div>
      </header>

      <section className="v9Workspace" data-module="WORKSPACE">
        <div className="v9PageHead">
          <div><small>{eyebrow}</small><h1>{title}</h1></div>
          <div className="v9WorkspaceTabs" data-module="TABS"><a href="/hub">Overview</a><a href="/hub/projects">Projects</a><a href="/hub/agents">iMaster</a></div>
        </div>
        <div className={sidePanel?"v9WorkspaceGrid":""}>
          <div className="v9WorkspaceBody">{children}</div>
          {sidePanel&&<aside className="v9SidePanel" data-module="SIDE_PANEL">{sidePanel}</aside>}
        </div>
      </section>

      {aiPanel&&<aside className="v9AiPanel" data-module="AI_PANEL">{aiPanel}</aside>}

      <section className={"v9Runtime "+(runtimeOpen?"open":"")} aria-label="Runtime drawer">
        <button className="v9RuntimeHandle" onClick={()=>runtimeOpen?setRuntimeOpen(false):openRuntime()}>
          <span>Runtime</span><b>{runtime.state==="ok"?"LIVE":runtime.state==="error"?"ERROR":runtime.state==="loading"?"LOADING":"READY"}</b><em>{runtimeOpen?"⌄":"⌃"}</em>
        </button>
        {runtimeOpen&&<div className="v9RuntimeBody">
          {runtime.state==="loading"&&<p>Reading /api/master/readiness…</p>}
          {runtime.state==="error"&&<p>{runtime.error}</p>}
          {runtime.state==="ok"&&<pre>{JSON.stringify(runtime.data,null,2)}</pre>}
          {runtime.state==="idle"&&<p>Runtime data loads only on request.</p>}
        </div>}
      </section>
    </section>
  </main>
}

export function PageCards({items}){return <div className="opsCards">{items.map(x=><article key={x[0]}><small>{x[0]}</small><strong>{x[1]}</strong><p>{x[2]}</p>{x[3]&&<a href={x[3]}>OPEN -&gt;</a>}</article>)}</div>}
