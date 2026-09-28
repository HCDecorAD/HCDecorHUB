import {getIntegrationView} from "../../../lib/hub-config";
const title=s=>s.split("_").map(x=>x.charAt(0).toUpperCase()+x.slice(1)).join(" ");
export default function Integrations(){const x=getIntegrationView();return <main className="adminSub"><a href="/admin">← Admin</a><small>SOURCE OF TRUTH</small><h1>Integrations</h1><p>Config state only; this page does not claim live provider uptime.</p>{x.map(v=><section className="integration" key={v.id}><b>{title(v.id)}</b><span>{v.state} · {v.purpose.join(" · ")||"purpose not declared"}</span>{v.note&&<small>{v.note}</small>}</section>)}</main>}
