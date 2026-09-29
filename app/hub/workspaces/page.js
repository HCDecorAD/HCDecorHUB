import {HubShell} from "../../../components/HubShell";
import {getWorkspaceView} from "../../../lib/hub-config";
import {WorkspaceSmoke} from "./WorkspaceSmoke";

export default function Workspaces(){
  const workspaces=getWorkspaceView();
  return <HubShell title="Workspaces" eyebrow="MASTER AGENT / BUSINESS CONTEXT">
    <div className="notice">Source: config/workspaces.json + config/adapters.json. Không hiển thị trạng thái runtime nếu source không cung cấp.</div>
    <section className="workspaceGrid">{workspaces.map(w=><article className="workspaceCard" key={w.workspace_id}>
      <div className="workspaceHead"><span>{w.site_id}</span><em>{w.sourceState}</em></div>
      <h2>{w.name}</h2><small>{w.adapterType}</small>
      <h3>{w.modules.length} modules</h3><p>{w.modules.join(" · ")}</p>
      {w.repository&&<p>Source: {w.repository}</p>}
      {w.rule&&<p>{w.rule}</p>}
      {w.productionUrl?<a href={w.productionUrl} target="_blank" rel="noreferrer">Mở website ↗</a>:<span>Production URL: unknown</span>}
    </article>)}</section>
    <WorkspaceSmoke/>
    <section className="masterArchitecture">
      <div><span>ISOLATION</span><h3>Independent by default</h3><p>Business data stays inside its workspace.</p></div>
      <div><span>ROUTING</span><h3>Context first</h3><p>Resolve workspace → adapter → worker/tool.</p></div>
      <div><span>STATUS</span><h3>Source-driven</h3><p>Adapter status is descriptive source state, not a fabricated live health check.</p></div>
    </section>
  </HubShell>;
}
