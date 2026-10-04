"use client";
import {useState} from "react";

const modules=["SOC_CONTENT","SOC_MEDIA","SOC_NETWORKS","SOC_ACCOUNTS","SOC_AI","SOC_VARIANTS","SOC_PREVIEW","SOC_ACCOUNT_GROUPS","SOC_BULK_IMPORT"];

export default function CreatePostClient(){
  const [topic,setTopic]=useState("");
  const [angle,setAngle]=useState("");
  const [hooks,setHooks]=useState("3 mistakes\nbefore/after\n5 quick tips");
  const [result,setResult]=useState(null);
  const [busy,setBusy]=useState(false);

  async function preview(e){
    e.preventDefault();
    setBusy(true); setResult(null);
    try{
      const r=await fetch("/api/content/factory",{method:"POST",headers:{"content-type":"application/json"},body:JSON.stringify({topic,angle,hooks:hooks.split(/\r?\n/),platforms:["youtube","facebook","instagram","tiktok"],provenance:["manual:create-post"]})});
      setResult(await r.json());
    }finally{setBusy(false)}
  }

  return <>
    <section className="opsCards">{modules.map(id=><article key={id} data-module={id}><small>{id}</small><strong>{id==="SOC_AI"||id==="SOC_VARIANTS"?"AI LIVE":"READY CONTRACT"}</strong><p>{id==="SOC_AI"||id==="SOC_VARIANTS"?"Cloudflare Workers AI server binding; no browser secret.":"V9 Create Post workflow module."}</p></article>)}</section>
    <section className="opsPanel">
      <div className="panelHead"><div><small>CREATE POST</small><h2>Content Pack Preview</h2></div></div>
      <form onSubmit={preview}>
        <input value={topic} onChange={e=>setTopic(e.target.value)} placeholder="Topic" required/>
        <input value={angle} onChange={e=>setAngle(e.target.value)} placeholder="Angle" required/>
        <textarea value={hooks} onChange={e=>setHooks(e.target.value)} rows={5}/>
        <button className="primary" disabled={busy}>{busy?"Building…":"Build Preview"}</button>
      </form>
      {result?.result?.ai?.image_data_url&&<img className="aiPreviewImage" src={result.result.ai.image_data_url} alt="AI generated preview"/>}
      {result&&<pre>{JSON.stringify({...result,result:result.result?{...result.result,ai:result.result.ai?{...result.result.ai,image_data_url:result.result.ai.image_data_url?"[generated image]":null}:null}:result.result},null,2)}</pre>}
    </section>
  </>;
}
