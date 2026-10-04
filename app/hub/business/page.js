import {HubShell} from "../../../components/HubShell";
import {crmRuntime} from "../../../lib/crm/config";
export default function Business(){
 const crm=crmRuntime();
 const stages=[
  ["LEADS","/api/crm/status","Verified CRM runtime/capability status"],
  ["CUSTOMERS",null,"Customer authority not yet configured"],
  ["QUOTATIONS",null,"Quotation authority not yet configured"],
  ["ORDER / PROJECT","/hub/projects","Project authority and guarded provisioning"],
  ["PAYMENT",null,"Payment authority not yet configured"]
 ];
 return <HubShell title="Business Center" eyebrow="WIN_BUSINESS / REAL WORKFLOW">
  <div className="notice">Business Center does not treat Commerce inventory as CRM. Unconfigured stages stay visibly unavailable; no fake actions.</div>
  <section className="masterFlow businessFlow">{stages.map((x,i)=><div key={x[0]}><b>{String(i+1).padStart(2,"0")}</b><span>{x[0]}</span></div>)}</section>
  <section className="opsCards">{stages.map(([name,url,note])=><article key={name}><small>{name}</small><strong>{url?"CONNECTED PATH":"NOT CONFIGURED"}</strong><p>{note}</p>{url&&<a href={url}>OPEN →</a>}</article>)}</section>
  <section className="opsPanel"><small>CRM RUNTIME</small><h2>{crm.configured?"Authority configured":"Authority needs runtime configuration"}</h2><p>Lead write: {crm.writeEnabled?"enabled":"disabled"} · Project provisioning: {crm.projectProvisionEnabled?"enabled":"guarded/disabled"} · production write remains approval-gated.</p><a className="commerceLink" href="/hub/commerce">Open Commerce / Shop Manager →</a></section>
 </HubShell>
}
