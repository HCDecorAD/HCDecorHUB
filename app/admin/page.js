const services=[
["Website","WordPress CMS","https://hcdecorhub.wordpress.com","CMS"],
["HUB","Operations","/","HUB"],
["Drive","Project + Media","https://drive.google.com/drive/folders/1NqJuLhum63XVea8wPjZtTETl29bm3U7r","DATA"],
["Vercel","Deployments","https://vercel.com/huycuongonline-4247/hcdecorhub","DEPLOY"],
["GitHub","Source + rollback","https://github.com/HCDecorAD/HCDecorHUB","CODE"],
];
export default async function Admin(){
 let cms=false;try{const base=process.env.HCDECOR_WP_BASE_URL;if(base){cms=(await fetch(base.replace(/\/$/,"")+"/wp-json/",{cache:"no-store"})).ok}}catch{}
 const states=[["Website","Online"],["HUB","Online"],["CMS",cms?"Connected":"Check"],["Drive","Configured"],["Agent","Demo Ready"],["Social","Later"]];
 return <main className="admin"><div className="adminTop"><div><small>HCDECOR / SYSTEM ADMIN</small><h1>Admin Control Center</h1><p>Điều khiển tổng Website · HUB · CMS · Drive · Agent từ một nơi.</p></div><a className="back" href="/">← HUB</a></div>
 <div className="health">{states.map(([n,s])=><div key={n}><i className={s==="Later"?"later":""}/><span>{n}</span><b>{s}</b></div>)}</div>
 <div className="adminGrid"><section className="panel adminServices"><div className="panelHead"><div><small>SYSTEMS</small><h2>Hệ thống & truy cập nhanh</h2></div></div>{services.map(([n,d,u,t])=><a className="service" href={u} target={u.startsWith("http")?"_blank":undefined} key={n}><span className="serviceIcon">{t}</span><span><b>{n}</b><small>{d}</small></span><strong>Mở →</strong></a>)}</section>
 <section className="panel"><div className="panelHead"><div><small>SAFETY</small><h2>Production Guard</h2></div><span className="live">● ACTIVE</span></div><div className="guard"><b>Review trước Publish</b><span>Bắt buộc</span></div><div className="guard"><b>Social OAuth/API</b><span>Chưa kích hoạt</span></div><div className="guard"><b>Secrets trong GitHub</b><span>Không cho phép</span></div><div className="guard"><b>Baseline Restore</b><span>Sẵn sàng</span></div></section></div>
 <div className="adminGrid"><section className="panel"><div className="panelHead"><div><small>DATA</small><h2>Project & Drive</h2></div></div><div className="metricRow"><div><small>Project ID</small><b>HC-YYYY-XXXX</b></div><div><small>Legacy Mapping</small><b>Enabled</b></div><div><small>Drive Root</small><b>Configured</b></div></div><p className="hint">Dữ liệu cũ được ánh xạ, không tự động đổi tên hoặc xóa file.</p></section>
 <section className="panel"><div className="panelHead"><div><small>CMS / PUBLISH</small><h2>Web Publish</h2></div></div><div className="metricRow"><div><small>CMS Read</small><b>{cms?"Ready":"Check"}</b></div><div><small>CMS Write</small><b>Demo</b></div><div><small>Approval</small><b>Required</b></div></div><p className="hint">WordPress Free dùng cho DEMO; publish thật chỉ bật sau khi xác thực write access.</p></section></div>
 <section className="panel"><div className="panelHead"><div><small>AGENT / AUTOMATION</small><h2>Runtime</h2></div><button disabled>Social: Later</button></div><div className="runtime"><span>Agent Adapter <b>Demo Ready</b></span><span>Automation Queue <b>Ready</b></span><span>Review Gate <b>Active</b></span><span>System Health <b>Active</b></span></div></section>
 </main>}