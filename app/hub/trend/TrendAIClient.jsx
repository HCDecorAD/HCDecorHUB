"use client";
import {useState} from "react";
const fields=["freshness","growth","audience_fit","content_gap","production_speed","money_fit"];
export default function TrendAIClient(){
  const [topic,setTopic]=useState("AI signage design");
  const [values,setValues]=useState(Object.fromEntries(fields.map(x=>[x,70])));
  const [result,setResult]=useState(null),[busy,setBusy]=useState(false);
  async function run(e){
    e.preventDefault();setBusy(true);setResult(null);
    try{
      const r=await fetch("/api/trend/opportunity",{method:"POST",headers:{"content-type":"application/json"},body:JSON.stringify({topic,...values,observed:true,evidence:["manual:test"]})});
      setResult(await r.json());
    }finally{setBusy(false)}
  }
  return <section className="opsPanel">
    <div className="panelHead"><div><small>AI_ANGLE</small><h2>Trend Opportunity Test</h2></div></div>
    <form onSubmit={run}>
      <input value={topic} onChange={e=>setTopic(e.target.value)} placeholder="Topic" required/>
      <div className="opsCards">{fields.map(k=><label key={k}><small>{k}</small><input type="number" min="0" max="100" value={values[k]} onChange={e=>setValues(v=>({...v,[k]:Number(e.target.value)}))}/></label>)}</div>
      <button className="primary" disabled={busy}>{busy?"Analyzing…":"Score + AI Recommend"}</button>
    </form>
    {result&&<pre>{JSON.stringify(result,null,2)}</pre>}
  </section>;
}
