"use client";
import {useEffect,useMemo,useState} from "react";

const STORE="hcdecor-v9-ui-designer";
const DEFAULT_SCHEMA={window:"WIN_HOME",layout:"workspace",sections:[{id:"hero",type:"panel",title:"HCDecor HUB"}]};

export default function UIDesignerClient(){
  const [raw,setRaw]=useState(JSON.stringify(DEFAULT_SCHEMA,null,2));
  const [applied,setApplied]=useState(DEFAULT_SCHEMA);
  const [versions,setVersions]=useState([]);
  const [error,setError]=useState("");
  const [aiRequest,setAiRequest]=useState("Add a compact KPI panel");
  const [aiBusy,setAiBusy]=useState(false);

  useEffect(()=>{try{const x=JSON.parse(localStorage.getItem(STORE)||"null");if(x?.schema){setApplied(x.schema);setRaw(JSON.stringify(x.schema,null,2));setVersions(Array.isArray(x.versions)?x.versions:[])}}catch{}},[]);
  const parsed=useMemo(()=>{try{return JSON.parse(raw)}catch{return null}},[raw]);

  function validate(){if(!parsed){setError("Invalid JSON");return false}if(!parsed.window||!Array.isArray(parsed.sections)){setError("Schema requires window + sections[]");return false}setError("");return true}
  function apply(){if(!validate())return;const next=[...versions,{at:new Date().toISOString(),schema:applied}].slice(-20);setVersions(next);setApplied(parsed);localStorage.setItem(STORE,JSON.stringify({schema:parsed,versions:next}))}
  function undo(){const last=versions[versions.length-1];if(!last)return;const next=versions.slice(0,-1);setApplied(last.schema);setRaw(JSON.stringify(last.schema,null,2));setVersions(next);setError("");localStorage.setItem(STORE,JSON.stringify({schema:last.schema,versions:next}))}
  async function aiPatch(){if(!validate()||!aiRequest.trim())return;setAiBusy(true);setError("");try{const r=await fetch("/api/ai/ui-patch",{method:"POST",headers:{"content-type":"application/json"},body:JSON.stringify({schema:parsed,instruction:aiRequest})});const j=await r.json();if(!r.ok||!j.schema)throw new Error(j.error||"AI patch failed");setRaw(JSON.stringify(j.schema,null,2))}catch(e){setError(String(e?.message||e))}finally{setAiBusy(false)}}

  return <div className="v9WorkspaceGrid">
    <section className="opsPanel">
      <div className="panelHead"><div><small>UI_SCHEMA</small><h2>Schema Editor</h2></div><strong>{parsed?"VALID JSON":"INVALID JSON"}</strong></div>
      <textarea value={raw} onChange={e=>setRaw(e.target.value)} rows={24} spellCheck={false}/>
      <div className="subActions">
        <button onClick={validate}>Validate</button>
        <button className="primary" onClick={apply}>Apply</button>
        <button onClick={undo} disabled={!versions.length}>Undo</button>
      </div>
      <div className="subActions">
        <input value={aiRequest} onChange={e=>setAiRequest(e.target.value)} placeholder="Describe UI change"/>
        <button onClick={aiPatch} disabled={aiBusy}>{aiBusy?"AI Patching…":"AI Patch"}</button>
      </div>
      {error&&<p className="muted">{error}</p>}
      <p className="muted">AI Patch uses the server-side Workers AI binding, then leaves the proposed schema in the editor for Validate → Apply. It never auto-applies production UI.</p>
    </section>
    <aside className="v9SidePanel">
      <small>UI_PREVIEW</small><h2>Live Preview</h2>
      <div className="opsCards">{(applied.sections||[]).map((x,i)=><article key={x.id||i}><small>{x.type||"panel"}</small><strong>{x.title||x.id||"Untitled"}</strong><p>{x.id||"section-"+i}</p></article>)}</div>
      <hr/><small>UI_VERSIONS</small><p>{versions.length} undo checkpoint(s)</p>
      <div className="notice">UI_EDIT_AI: LIVE · Cloudflare Workers AI · preview-first</div>
    </aside>
  </div>;
}
