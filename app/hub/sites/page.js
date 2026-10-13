import {notFound} from "next/navigation";
import {HubShell} from "../../../components/HubShell";
import UserManager from "../../../components/UserManager";
import SitesTabs from "./SitesTabs";
import {getPrincipal,canViewSuperAdmin,getSiteRows} from "../../../lib/superadmin";
import {checkAllSites} from "../../../lib/site-health.mjs";
import {getUsersKv} from "../../../lib/auth-env";
import {listUsers} from "../../../lib/user-auth.js";
import {usersBySite,MANAGED_SITES} from "../../../lib/site-access.js";

export const dynamic="force-dynamic";

const label=h=>h.state==="ok"?"OK "+h.status:h.state==="error"?"lỗi"+(h.status?" "+h.status:h.error?" ("+h.error+")":""):"chưa kiểm tra";

export default async function Sites(){
  if(!canViewSuperAdmin(await getPrincipal()))notFound();
  const rows=getSiteRows();
  const health=await checkAllSites(rows.map(r=>r.healthUrl));
  const kv=await getUsersKv();
  const users=kv?await listUsers(kv):[];
  const access=usersBySite(users,MANAGED_SITES);
  const sitesPanel=<>
    <div className="notice">Chỉ đọc. Health check chạy phía server, timeout 5 giây; lỗi hiển thị là "lỗi", không có kết quả giả.</div>
    <table><thead><tr><th>site_id</th><th>Domain</th><th>Hosting</th><th>Status</th><th>HTTP</th><th>Phản hồi</th><th>Liên kết</th></tr></thead>
    <tbody>{rows.map((r,i)=><tr key={r.site_id}><td>{r.site_id}</td><td>{r.domain||"—"}</td><td>{r.hosting||"—"}</td><td>{r.status||"—"}</td><td>{label(health[i])}</td><td>{health[i].ms==null?"—":health[i].ms+" ms"}</td><td>{r.links.map(l=><a key={l.label} href={l.href} target="_blank" rel="noreferrer">{l.label} ↗ </a>)}</td></tr>)}</tbody></table>
    <h2>Quản lý truy cập AMO &amp; GSC</h2>
    {MANAGED_SITES.map(sid=><section key={sid}><h3>{sid.toUpperCase()}</h3>
    {access[sid].length===0?<p>Chưa có tài khoản NV/Khách nào.</p>:
    <table><thead><tr><th>ID</th><th>Vai trò</th><th>Trạng thái</th></tr></thead><tbody>
    {access[sid].map(u=><tr key={u.id}><td>{u.id}</td><td>{u.role==="staff"?"NV":"Khách"}</td><td>{u.status}</td></tr>)}
    </tbody></table>}</section>)}
  </>;
  return <HubShell title="SuperAdmin · Sites" eyebrow="HCDECOR HUB / SUPERADMIN">
    <SitesTabs sitesPanel={sitesPanel} managePanel={<UserManager/>}/>
  </HubShell>;
}
