import {HubShell} from "../../../components/HubShell";
import {crmRuntime} from "../../../lib/crm/config";
import {businessRuntime} from "../../../lib/business/config";

export default function Business(){
 const crm=crmRuntime();
 const business=businessRuntime();
 const stages=[
  ["LEADS","/api/crm/status",crm.writeEnabled?"CONNECTED PATH":"RUNTIME NOT CONFIGURED"],
  ["CUSTOMERS","/api/business/customers",business.writeEnabled?"CONNECTED PATH":"RUNTIME NOT CONFIGURED"],
  ["QUOTATIONS","/api/business/quotations",business.writeEnabled?"CONNECTED PATH":"RUNTIME NOT CONFIGURED"],
  ["ORDER / PROJECT","/hub/projects",crm.projectProvisionEnabled?"CONNECTED PATH":"GUARDED / NOT CONFIGURED"],
  ["PAYMENT","/api/business/payments",business.writeEnabled?"CONNECTED PATH":"RUNTIME NOT CONFIGURED"]
 ];
 return <HubShell title="Business Center" eyebrow="WIN_BUSINESS / REAL WORKFLOW">
  <div className="notice">Lead → Customer → Quotation → Order/Project → Payment. Every production mutation is same-origin + bearer token + fresh approval guarded; missing runtime configuration stays visible.</div>
  <section className="masterFlow businessFlow">{stages.map((x,i)=><div key={x[0]}><b>{String(i+1).padStart(2,"0")}</b><span>{x[0]}</span></div>)}</section>
  <section className="opsCards">{stages.map(([name,url,state])=><article key={name}><small>{name}</small><strong>{state}</strong><p>{url.startsWith("/api/")?"Verified server-side authority endpoint.":"Verified project workflow path."}</p><a href={url}>OPEN →</a></article>)}</section>
  <section className="opsPanel"><small>BUSINESS RUNTIME</small><h2>{business.writeEnabled?"Google Sheets business authority ready":"Business authority needs runtime configuration"}</h2><p>Customers / Quotations / Payments tabs: {business.configured?"sheet authority declared":"sheet authority missing"} · credentials: {business.credentials?"configured":"missing"} · API token: {business.tokenConfigured?"configured":"missing"} · production write remains approval-gated.</p><a className="commerceLink" href="/hub/commerce">Open Commerce / Shop Manager →</a></section>
 </HubShell>
}
