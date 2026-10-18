import {cookies} from "next/headers";
import {redirect} from "next/navigation";
import {getAuthEnv,getUsersKv} from "../../../lib/auth-env";
import {resolveUserPrincipal} from "../../../lib/user-auth";
import {SESSION_COOKIE} from "../../../lib/github-auth";
import registry from "../../../config/site-registry.json";

export const dynamic="force-dynamic";

// Trang của tôi: NV/Khách (ID/Pass) chỉ thấy các site được gán cho mình.
export default async function MySites(){
 const jar=await cookies();
 const p=await resolveUserPrincipal({cookieValue:jar.get(SESSION_COOKIE)?.value,env:await getAuthEnv(),kv:await getUsersKv()});
 if(!p.authenticated)redirect("/login");
 if(p.must_change_password)redirect("/account");
 const mine=registry.sites.filter(s=>p.site_ids.includes(String(s.site_id).toLowerCase()));
 return <main className="admin">
  <h1>Trang của tôi</h1>
  {mine.length===0?<p>Tài khoản chưa được gán site nào. Liên hệ quản lý.</p>:
   <ul>{mine.map(s=><li key={s.site_id}><b>{s.name}</b>{s.production_url?<> — <a href={s.production_url}>{s.production_url}</a></>:null}</li>)}</ul>}
 </main>;
}
