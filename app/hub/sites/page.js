import {headers} from "next/headers";
import {notFound} from "next/navigation";
import {HubShell} from "../../../components/HubShell";
import {getWorkspaceView} from "../../../lib/hub-config";
import registry from "../../../config/site-registry.json";
import {checkAllSites} from "../../../lib/site-health.mjs";
import {isOwnerRequest} from "../../../lib/owner-gate.mjs";

export const dynamic="force-dynamic";

export default async function Sites(){
  const h=await headers();
  if(!isOwnerRequest(h.get("authorization")))notFound();
  const workspaces=getWorkspaceView();
  const sites=registry.sites.map(s=>{
    const w=workspaces.find(x=>x.workspace_id===s.workspace_id)||{};
    const url=s.production_url||w.productionUrl||null;
    return {site_id:s.site_id,name:s.name,status:s.status,domain:url,productionUrl:url,
      hosting:w.hosting?.primary?.provider||s.platform||"unknown",
      repository:s.repository||w.repository||null};
  });
  const rows=await checkAllSites(sites);
  return <HubShell title="SuperAdmin · Sites" eyebrow="HCDECOR HUB / SUPERADMIN (READ-ONLY)">
    <div className="notice">Chỉ đọc. Kiểm tra được gọi trực tiếp lúc tải trang (timeout 5 giây); lỗi hiển thị "lỗi", không có dữ liệu giả.</div>
    <section className="masterArchitecture"><div><span>SITES</span><h3>{rows.length} registered</h3>
      <table style={{width:"100%",textAlign:"left"}}>
        <thead><tr><th>site_id</th><th>domain</th><th>hosting</th><th>status</th><th>health</th><th>HTTP</th><th>ms</th><th>links</th></tr></thead>
        <tbody>{rows.map(r=><tr key={r.site_id}>
          <td>{r.site_id}</td><td>{r.domain||"—"}</td><td>{r.hosting}</td><td>{r.status}</td>
          <td>{r.health.label}{r.health.error?` (${r.health.error})`:""}</td>
          <td>{r.health.status??"—"}</td><td>{r.health.ms??"—"}</td>
          <td>{r.repository&&<a href={`https://github.com/${r.repository}`} target="_blank" rel="noreferrer">GitHub ↗</a>}{r.productionUrl&&<> · <a href={r.productionUrl} target="_blank" rel="noreferrer">Production ↗</a></>}</td>
        </tr>)}</tbody>
      </table></div></section>
  </HubShell>;
}
