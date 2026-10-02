import {HubShell} from "../../../components/HubShell";
import catalog from "../../../config/corporate-catalog.json";
import capabilities from "../../../config/capability-registry.json";

export default function OperatorDashboard(){
  return <HubShell title="Operator Dashboard" eyebrow="HC GROUP / READ-ONLY PORTFOLIO">
    <div className="notice">Read-only evidence surface. No production mutation, queue control, deploy, publish, retry, or approval action is exposed here.</div>
    <section className="masterArchitecture">
      <div><span>PROJECTS</span><h3>{catalog.projects.length} registered</h3><p>{catalog.projects.map(x=>x.project_id).join(" · ")}</p></div>
      <div><span>TOOLS</span><h3>{catalog.tools.length} gated</h3><p>{catalog.tools.map(x=>x.tool_id).join(" · ")}</p></div>
      <div><span>CAPABILITIES</span><h3>{capabilities.capabilities.length} declared</h3><p>Input/output · risk · gate · cost · fail-closed</p></div>
    </section>
    <section className="workspaceGrid">{catalog.projects.map(p=><article className="workspaceCard" key={p.project_id}><div className="workspaceHead"><span>{p.project_id}</span><em>{p.state}</em></div><h2>{p.workspace_id}</h2><small>{p.production_authority}</small><p>{p.repository}</p></article>)}</section>
    <section className="masterArchitecture">{capabilities.capabilities.map(c=><div key={c.capability_id}><span>CAPABILITY</span><h3>{c.capability_id}</h3><p>{c.provider_tool} · {c.risk_class} · {c.cost_class} · gate: {c.gate}</p></div>)}</section>
  </HubShell>;
}
