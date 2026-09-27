const systems=[
["Website","/","Public site","Mở"],
["HUB","/hub","Operations","Mở"],
["Leads","/admin/leads","CRM intake","Mở"],
["Projects","/hub/projects","Project control","Mở"],
["Media / Drive","/hub/media","Project assets","Mở"],
["Content","/hub/content","Content workflow","Mở"],
["Review","/hub/review","Approval gate","Mở"],
["Web Publish","/hub/publish","Publish control","Mở"],
["Integrations","/admin/integrations","Connections","Mở"],
["Health","/admin/health","Runtime status","Mở"],
["Settings","/admin/settings","Configuration","Mở"]
];
async function wp(){try{const b=process.env.HCDECOR_WP_BASE_URL;if(!b)return {ok:false,label:"Chưa cấu hình"};const r=await fetch(b.replace(/\/$/,"")+"/wp-json/",{cache:"no-store"});return {ok:r.ok,label:r.ok?"Đọc được":"Lỗi HTTP "+r.status}}catch{return {ok:false,label:"Không kết nối"}}}
export default async function Admin(){const cms=await wp();const drive=Boolean(process.env.HCDECOR_DRIVE_ROOT_FOLDER_ID),agent=Boolean(process.env.HCDECOR_AGENT_BASE_URL),write=Boolean(process.env.HCDECOR_WP_API_TOKEN);
return <main className="admin realAdmin"><div className="adminTop"><div><small>HCDECOR / ADMIN</small><h1>System Control Center</h1><p>Điều khiển các luồng thật. Chức năng chưa có backend sẽ khóa, không mô phỏng.</p></div><div className="actions"><a className="btn" href="/">Website</a><a className="btn primary" href="/hub">Mở HUB</a></div></div>
<div className="realStatus"><Status n="Website" ok/><Status n="HUB" ok/><Status n="CMS Read" ok={cms.ok} detail={cms.label}/><Status n="CMS Write" ok={write} detail={write?"Đã xác thực":"Chưa có quyền ghi"}/><Status n="Drive Runtime" ok={drive} detail={drive?"Đã cấu hình":"Chưa cấu hình runtime"}/><Status n="Agent Runtime" ok={agent} detail={agent?"External":"Chưa cấu hình"}/></div>
<section className="panel"><div className="panelHead"><div><small>OPERATIONS</small><h2>Quản lý theo luồng</h2></div><a className="btn primary" href="/contact">+ Nhận Lead</a></div><div className="adminModules">{systems.map(([n,u,d,a])=><a href={u} className="adminModule" key={u}><span><b>{n}</b><small>{d}</small></span><strong>{a} →</strong></a>)}</div></section>
<div className="adminGrid"><section className="panel"><small>EXTERNAL SYSTEMS</small><h2>Hệ thống thật</h2><External n="WordPress CMS" u="https://hcdecorhub.wordpress.com" state={cms.ok?"Online":"Check"}/><External n="Google Drive" u="https://drive.google.com/drive/folders/1NqJuLhum63XVea8wPjZtTETl29bm3U7r" state="Open"/><External n="GitHub" u="https://github.com/HCDecorAD/HCDecorHUB" state="Source"/><External n="Vercel" u="https://vercel.com/huycuongonline-4247/hcdecorhub" state="Deploy"/></section>
<section className="panel"><small>CAPABILITIES</small><h2>Quyền thao tác</h2><Capability n="Đọc CMS" ok={cms.ok}/><Capability n="Publish CMS" ok={write} reason="Cần WordPress write credential"/><Capability n="Drive API trong Web" ok={drive} reason="Hiện chỉ có cấu hình folder; chưa có runtime OAuth"/><Capability n="Agent external" ok={agent} reason="Chưa có Agent endpoint"/><Capability n="Social Publish" ok={false} reason="OAuth/API chưa triển khai"/></section></div>
<section className="panel"><small>DIAGNOSTICS</small><h2>API kiểm tra thật</h2><div className="diagLinks"><a href="/api/health">Health API</a><a href="/api/cms/status">CMS Status</a><a href="/api/publish/status">Publish Status</a><a href="/api/wp/status">WordPress Status</a></div></section></main>}
function Status({n,ok,detail}){return <div className={"realStat "+(ok?"good":"warn")}><i/><span>{n}</span><b>{ok?"ACTIVE":"ACTION REQUIRED"}</b>{detail&&<small>{detail}</small>}</div>}
function External({n,u,state}){return <a className="service" href={u} target="_blank"><span className="serviceIcon">LIVE</span><b>{n}</b><strong>{state} →</strong></a>}
function Capability({n,ok,reason}){return <div className="capRow"><span><b>{n}</b>{!ok&&<small>{reason}</small>}</span><strong className={ok?"capOn":"capOff"}>{ok?"ENABLED":"DISABLED"}</strong></div>}