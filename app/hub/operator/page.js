import {HubShell} from "../../../components/HubShell";
import catalog from "../../../config/corporate-catalog.json";
import capabilities from "../../../config/capability-registry.json";
import lifecycle from "../../../config/lifecycle-registry.json";
import {operatorTelemetrySnapshot} from "../../../lib/operator-telemetry";

export default async function OperatorDashboard(){
  const telemetry=await operatorTelemetrySnapshot({limit:8});
  const unprovenTools=catalog.tools.filter(t=>['not-proven-done','pending','unknown'].includes(t.verification_state));
  return <HubShell title="Operator Dashboard" eyebrow="HC GROUP / READ-ONLY PORTFOLIO">
    <div className="notice">Read-only evidence surface. No production mutation, queue control, deploy, publish, retry, or approval action is exposed here.</div>
    <section className="masterArchitecture">
      <div><span>PROJECTS</span><h3>{catalog.projects.length} registered</h3><p>{catalog.projects.map(x=>x.project_id).join(" · ")}</p></div>
      <div><span>TOOLS</span><h3>{catalog.tools.length} gated</h3><p>{catalog.tools.map(x=>x.tool_id).join(" · ")}</p></div>
      <div><span>CAPABILITIES</span><h3>{capabilities.capabilities.length} declared</h3><p>Input/output · risk · gate · cost · fail-closed</p></div><div><span>UNPROVEN TOOLS</span><h3>{unprovenTools.length}</h3><p>{unprovenTools.map(x=>x.tool_id).join(" · ")||"none"}</p></div>
    </section>
    <section className="workspaceGrid">{catalog.projects.map(p=><article className="workspaceCard" key={p.project_id}><div className="workspaceHead"><span>{p.project_id}</span><em>{p.state}</em></div><h2>{p.workspace_id}</h2><small>{p.production_authority}</small><p>{p.repository}</p></article>)}</section>
    <section className="masterArchitecture">{lifecycle.goals.map(g=><div key={g.goal_id}><span>GOAL</span><h3>{g.goal_id}</h3><p>{g.state} · gate: {g.acceptance_gate}</p><small>{g.checkpoint}</small></div>)}</section>
    <section className="workspaceGrid">{lifecycle.missions.map(m=><article className="workspaceCard" key={m.mission_id}><div className="workspaceHead"><span>{m.worker_class}</span><em>{m.state}</em></div><h2>{m.mission_id}</h2><small>{m.provider_tools.join(" · ")}</small><p>{m.checkpoint}</p><p>Evidence: {m.evidence_gate}</p></article>)}</section>
    <section className="masterArchitecture"><div><span>DURABLE RUNTIME</span><h3>{telemetry.durable_runtime.available?"available":"not bound"}</h3><p>{telemetry.sources.durable_runtime.authority} · production authority: {String(telemetry.sources.durable_runtime.production_authority)}</p></div><div><span>RUN HISTORY</span><h3>{telemetry.recent_runs.length} recent</h3><p>{telemetry.sources.run_history.authority} · production authority: {String(telemetry.sources.run_history.production_authority)}</p></div><div><span>WORKERS</span><h3>{telemetry.workers.length} known · {telemetry.suspect_workers.length} suspect</h3><p>{telemetry.sources.worker_heartbeats.authority} · production authority: {String(telemetry.sources.worker_heartbeats.production_authority)}</p></div><div><span>EVIDENCE STORE</span><h3>{telemetry.evidence_store_health.state}</h3><p>{telemetry.evidence_store_health.valid_events} valid · {telemetry.evidence_store_health.malformed_lines} malformed · {telemetry.sources.evidence_events.authority}</p></div><div><span>BLOCKERS</span><h3>{telemetry.blockers.length}</h3><p>{telemetry.blockers.map(x=>x.mission_id).join(" · ")||"none in lifecycle registry"}</p></div></section>
    <section className="workspaceGrid">{telemetry.workers.map(w=><article className="workspaceCard" key={w.worker_id}><div className="workspaceHead"><span>{w.worker_id}</span><em>{w.health}</em></div><h2>{w.mission_id||"unassigned"}</h2><small>{w.state} · correlation: {w.correlation_id||"none"}</small><p>{w.checkpoint?JSON.stringify(w.checkpoint):"no checkpoint"}</p></article>)}</section>
    <section className="masterArchitecture"><div><span>RECENT EVIDENCE</span><h3>{telemetry.recent_evidence.length} events</h3><p>{telemetry.sources.evidence_events.authority} · production authority: {String(telemetry.sources.evidence_events.production_authority)}</p></div></section>
    <section className="workspaceGrid">{telemetry.recent_evidence.map(e=><article className="workspaceCard" key={e.event_id}><div className="workspaceHead"><span>{e.event_type}</span><em>{e.outcome}</em></div><h2>{e.mission_id}</h2><small>{e.component} · correlation: {e.correlation_id}</small><p>{e.timestamp}</p></article>)}</section>
    <section className="masterArchitecture">{telemetry.capabilities.map(c=><div key={c.capability_id}><span>CAPABILITY</span><h3>{c.capability_id}</h3><p>{c.provider_tool} · state: {c.contract_state} · production authority: {String(c.production_authority)} · gate: {c.gate}</p></div>)}</section>
    <section className="workspaceGrid">{catalog.tools.map(t=><article className="workspaceCard" key={t.tool_id}><div className="workspaceHead"><span>TOOL VERIFICATION</span><em>{t.verification_state||"declared"}</em></div><h2>{t.tool_id}</h2><small>{t.risk_class}</small><p>Gate: {t.gate}</p></article>)}</section>
  </HubShell>;
}
