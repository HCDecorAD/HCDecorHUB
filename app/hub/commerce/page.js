import {HubShell} from "../../../components/HubShell";
import {commerceAdminCapability,commerceAdminRead} from "../../../lib/commerce/admin";
const steps=["Upload / Paste","Field Mapping","Validation","Dry Run","Review","Approval","Commit"];
const resources=["products","listings","channels","inventory","warehouses","import-jobs"];
const label={products:"Product Master",listings:"Listings",channels:"Channels",inventory:"Inventory",warehouses:"Warehouses","import-jobs":"Data Center"};
export default async function Commerce(){
 const cap=commerceAdminCapability();const reads={};
 if(cap.configured)for(const x of resources)reads[x]=await commerceAdminRead(x);
 return <HubShell title="AMO Shop Manager" eyebrow="HCDECOR HUB / COMMERCE CONTROL PLANE">
  <section className="masterHero"><div><span className="masterBadge">AMO · COMMERCE V4</span><h2>Shop Manager / HC Data Center</h2><p>WordPress is the control plane; Cloudflare Shop Engine owns transactional runtime. Admin credential stays server-side.</p></div><div className="masterFlow">{steps.map((x,i)=><div key={x}><b>{String(i+1).padStart(2,"0")}</b><span>{x}</span></div>)}</div></section>
  <div className="notice"><span>{cap.configured?"CONNECTED":"SAFE MODE"}</span><b>{cap.configured?"server-side admin read configured":"credential_not_configured"}</b> · Production write remains locked.</div>
  <section className="opsCards">{resources.map(x=>{const r=reads[x];return <article key={x}><small>{label[x]}</small><strong>{cap.configured?(r?.ok?(r.items?.length||0):"—"):"—"}</strong><p>{cap.configured?(r?.ok?"Verified admin read":"Read unavailable: "+r?.error):"Waiting for server credential; no token is exposed to browser."}</p></article>})}</section>
  <section className="panel"><div className="panelHead"><div><small>DATA CENTER</small><h2>Import Jobs</h2></div><span className="status">Commit locked</span></div>
   {cap.configured&&reads["import-jobs"]?.items?.length?<table><thead><tr><th>File</th><th>Type</th><th>Status</th><th>Dry-run summary</th></tr></thead><tbody>{reads["import-jobs"].items.slice(0,20).map(j=><tr key={j.id}><td>{j.filename||j.id}</td><td>{j.kind}</td><td><span className="status">{j.status}</span></td><td>{j.summary_json||"{}"}</td></tr>)}</tbody></table>:<p className="muted">{cap.configured?"No import jobs found.":"Admin read is intentionally unavailable until HC_SHOP_ADMIN_TOKEN is configured server-side."}</p>}
  </section>
  <section className="panel"><div className="panelHead"><div><small>POLICY</small><h2>Import workflow guard</h2></div></div><table><thead><tr><th>Stage</th><th>Policy</th><th>Status</th></tr></thead><tbody><tr><td>Draft / Mapping</td><td>Authenticated admin</td><td><span className="status">Implemented</span></td></tr><tr><td>Validation / Dry Run</td><td>No catalog/inventory mutation</td><td><span className="status">PASS local</span></td></tr><tr><td>Review / Approval</td><td>Human approval required</td><td><span className="status">Required</span></td></tr><tr><td>Commit / Publish</td><td>production_mutation=false</td><td><span className="status">Locked</span></td></tr></tbody></table></section>
 </HubShell>
}