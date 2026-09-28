import {HubShell} from "../../components/HubShell";
import {getHubConfig,getWorkspaceView,getIntegrationView} from "../../lib/hub-config";

const title=s=>s.split(/[-_]/).map(x=>x.charAt(0).toUpperCase()+x.slice(1)).join(" ");

export default function Hub(){
  const {agents}=getHubConfig();
  const workspaces=getWorkspaceView();
  const integrations=getIntegrationView();
  return <HubShell title="Master Overview" eyebrow="HCDECOR HUB / CONTROL PLANE">
    <section className="masterHero">
      <div><span className="masterBadge">CONFIG-DRIVEN</span><h2>{agents.master_agent.name}</h2><p>One Core → One Master Agent → Multiple Workspaces → Specialized Workers → Tools / Adapters → Verification / Audit.</p></div>
      <div className="masterFlow">{agents.routing.flow.map((x,i)=><div key={x}><b>{String(i+1).padStart(2,"0")}</b><span>{title(x)}</span></div>)}</div>
    </section>
    <div className="notice">Dashboard không hiển thị queue, reach, lỗi hay completed giả. Runtime metrics chỉ xuất hiện khi có nguồn runtime xác minh.</div>
    <section className="masterArchitecture">
      <div><span>WORKSPACES</span><h3>{workspaces.length} configured</h3><p>{workspaces.map(w=>w.site_id).join(" · ")}</p></div>
      <div><span>WORKERS</span><h3>{agents.workers.length} registered</h3><p>{agents.workers.map(w=>title(w.id)).join(" · ")}</p></div>
      <div><span>RUNTIME METRICS</span><h3>Not configured</h3><p>No verified runtime telemetry source is declared for queue totals or reach metrics.</p></div>
    </section>
    <section className="workspaceGrid">{workspaces.map(w=><article className="workspaceCard" key={w.workspace_id}><div className="workspaceHead"><span>{w.site_id}</span><em>{w.sourceState}</em></div><h2>{w.name}</h2><small>{w.adapterType}</small><h3>{w.modules.length} modules</h3><p>{w.modules.join(" · ")}</p>{w.productionUrl&&<a href={w.productionUrl} target="_blank" rel="noreferrer">Website ↗</a>}</article>)}</section>
    <section className="masterArchitecture">{integrations.slice(0,6).map(x=><div key={x.id}><span>INTEGRATION</span><h3>{title(x.id)} · {x.state}</h3><p>{x.purpose.length?x.purpose.map(title).join(" · "):"Purpose configured outside dashboard"}{x.note?" · "+x.note:""}</p></div>)}</section>
  </HubShell>;
}
