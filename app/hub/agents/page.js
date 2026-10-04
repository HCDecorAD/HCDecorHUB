import {HubShell} from "../../../components/HubShell";
import {getHubConfig} from "../../../lib/hub-config";
import {MasterConsole} from "./MasterConsole";

const labels={
  website:"Website Agent",
  content:"Content Agent",
  media:"Media Agent",
  publishing:"Publishing Agent",
  "project-assistant":"Project Assistant",
  qa:"QA Agent"
};
const title=s=>s.split("-").map(x=>x.charAt(0).toUpperCase()+x.slice(1)).join(" ");

export default function Agents(){
  const {agents}=getHubConfig();
  const master=agents.master_agent;
  return <HubShell title="Master Agent" eyebrow="HUB CORE / ORCHESTRATION V2">
    <section className="masterHero">
      <div><span className="masterBadge">MASTER CONTROL PLANE</span><h2>{master.name}</h2><p>{master.role} · Production write: {master.production_write}</p></div>
      <div className="masterFlow">{agents.routing.flow.map((x,i)=><div key={x}><b>{String(i+1).padStart(2,"0")}</b><span>{title(x)}</span></div>)}</div>
    </section>
    <div className="notice">Source: config/agents.json · Schema {agents.schema_version}. Runtime status không được suy diễn từ UI.</div>
    <section className="imasterSpine" aria-label="iMaster execution spine">{["GOAL","PLAN","EXECUTE","VERIFY","REPAIR","LEARN","NEXT","DONE"].map((x,i)=><div key={x} data-imaster-stage={x}><b>{String(i+1).padStart(2,"0")}</b><span>{x}</span></div>)}</section>
    <MasterConsole/>
    <section className="masterGrid">{agents.workers.map(w=><article className="masterCard" key={w.id}><div className="masterCardTop"><span>WORKER</span><em>{w.production_write===false?"No production write":w.production_write}</em></div><h3>{labels[w.id]||title(w.id)}</h3><p>{w.capabilities.map(title).join(" · ")}</p><small>Legacy mapping</small><strong>{w.maps_to.join(" + ")}</strong></article>)}</section>
    <section className="masterArchitecture">
      <div><span>CONTEXT</span><h3>{master.context_layers.length} layers</h3><p>{master.context_layers.map(title).join(" → ")}</p></div>
      <div><span>EXTERNAL AI</span><h3>{Object.keys(agents.external_workers).map(title).join(" · ")}</h3><p>Optional specialists; Master review required before integration.</p></div>
      <div><span>SAFETY</span><h3>Config enforced</h3><p>Workspace isolation · production gate · audit every execution.</p></div>
    </section>
  </HubShell>;
}
