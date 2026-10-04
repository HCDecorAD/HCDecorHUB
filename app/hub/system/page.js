import {HubShell,PageCards} from "../../../components/HubShell";
import {runtimeCapabilities} from "../../../lib/data/store";
import {durableRuntimeStatus} from "../../../lib/durable-runtime";

export default function SystemCenter(){
  const cap=runtimeCapabilities();
  const durable=durableRuntimeStatus();
  const items=[
    ["CONNECTIONS","AI & Connections","Providers, models, API keys, OAuth and connection tests.","/hub/ai"],
    ["HEALTH","Health Center","Verified runtime and authority health.","/admin/health"],
    ["INTEGRATIONS","Integrations","Configured plugins, connectors and production sources.","/admin/integrations"],
    ["SETTINGS","Settings","Server-side configuration and operating controls.","/admin/settings"],
    ["HCDR","Local-First / HCDR","Primary local execution path and relay controls.","/api/runtime/diagnostics"],
    ["DEPLOY","Release Gate","Production deployment verification and guarded release.","/admin/deploy"]
  ];
  return <HubShell title="System Center" eyebrow="WIN_SYSTEM / SYSTEM CENTER">
    <div className="notice">System Center exposes verified configuration and runtime state only. Secrets remain server-side; production writes stay guarded.</div>
    <section className="masterArchitecture">
      <div><span>RUNTIME</span><h3>{durable.available?"DURABLE READY":"GUARDED"}</h3><p>{durable.reason||"Durable runtime status available."}</p></div>
      <div><span>CAPABILITIES</span><h3>{Object.values(cap).filter(Boolean).length} configured</h3><p>CMS read/write · Drive · Lead · Project capability state from runtime authority.</p></div>
      <div><span>LOCAL-FIRST</span><h3>HCDR PRIMARY</h3><p>CODE → HOCUONG LOCAL → HCDR → LOCAL TEST/EVIDENCE → GitHub → CI. RDC rescue-only.</p></div>
    </section>
    <PageCards items={items}/>
  </HubShell>
}
