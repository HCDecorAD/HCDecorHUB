import {HubShell} from "../../../components/HubShell";
import {crmRuntime} from "../../../lib/crm/config";
import {listProjects} from "../../../lib/crm/google";

export default async function Projects(){
 const runtime=crmRuntime(); let items=[],readError=null;
 if(runtime.configured){try{items=await listProjects(100)}catch{readError="Project authority is configured but current server credentials could not read it."}}
 return <HubShell title="Project Center" eyebrow="WIN_PROJECTS / PROJECT CENTER">
   <div className="notice">Project authority: Google Sheets + Drive. Reads are server-side; project creation remains token + fresh approval guarded.</div>
   <section className="masterArchitecture">
    <div><span>PROJECTS</span><h3>{items.length}</h3><p>{readError|| (runtime.configured?"Verified authority read path":"Project authority not configured in this runtime.")}</p></div>
    <div><span>PROVISION</span><h3>{runtime.projectProvisionEnabled?"AVAILABLE":"GUARDED"}</h3><p>Qualified Lead → Project ID → Drive Folder → Projects row. No browser secret is exposed.</p></div>
    <div><span>STATUS</span><h3>Real APIs</h3><p>/api/projects · /api/projects/status · /api/crm/status</p></div>
   </section>
   <section className="subActions" aria-label="Project Center real actions"><a href="/api/projects">Project API</a><a href="/hub/workspaces">Workspaces</a><a href="/hub/agents">Worker Binding</a><a href="/hub/reports">Outputs</a></section>
   <section className="opsPanel"><div className="panelHead"><div><small>PROJECT AUTHORITY</small><h2>Recent Projects</h2></div></div>
    {items.length?<div className="commerceTable">{items.map(x=><article className="commerceRow projectRow" key={x.project_id}><span><b>{x.project_id}</b><small>{x.client||"No client"}</small></span><span>{x.service||"-"}</span><span>{x.status||"-"}</span><span>{x.created_at||"-"}</span>{x.folder_url?<a href={x.folder_url} target="_blank" rel="noreferrer">Drive ↗</a>:<span>-</span>}</article>)}</div>:<p className="muted">{readError||"No verified project rows available in this runtime."}</p>}
   </section>
 </HubShell>
}
