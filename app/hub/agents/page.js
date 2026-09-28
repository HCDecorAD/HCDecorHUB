import {HubShell} from "../../../components/HubShell";

const workers=[
 ["Website Agent","developer + design","Website · UI/UX · Responsive · Code","Không ghi production"],
 ["Content Agent","content","Draft · SEO · Localization · Content Plan","Không tự publish"],
 ["Media Agent","media + design","Classify · Prepare · Optimize · Visual Spec","Không ghi production"],
 ["Publishing Agent","publishing","Preview · Schedule · Publish","Approval bắt buộc"],
 ["Project Assistant","crm + report","Project Context · CRM Link · Reporting","Permission-gated"],
 ["QA Agent","developer + report","QA · Verify · Regression · Release Gate","Không ghi production"]
];

export default function Agents(){return <HubShell title="Master Agent" eyebrow="HUB CORE / ORCHESTRATION V2">
  <section className="masterHero">
    <div><span className="masterBadge">MASTER CONTROL PLANE</span><h2>One Master Agent.<br/>Multiple specialist workers.</h2><p>Master Agent hiểu yêu cầu, nạp context, lập kế hoạch, kiểm tra quyền, điều phối worker/tool, xác minh kết quả và ghi audit. Worker không tự quyết kiến trúc HUB.</p></div>
    <div className="masterFlow">{["Intent","Context","Plan","Permission","Route","Verify","Audit"].map((x,i)=><div key={x}><b>{String(i+1).padStart(2,"0")}</b><span>{x}</span></div>)}</div>
  </section>
  <div className="notice">Production publish, deploy, delete và thay đổi Core luôn đi qua permission gate. Runtime status chỉ lấy từ config/source of truth.</div>
  <section className="masterGrid">{workers.map(([n,m,c,p])=><article className="masterCard" key={n}><div className="masterCardTop"><span>WORKER</span><em>{p}</em></div><h3>{n}</h3><p>{c}</p><small>Legacy mapping</small><strong>{m}</strong></article>)}</section>
  <section className="masterArchitecture"><div><span>WORKSPACES</span><h3>HCDecor · GSC · AMO</h3><p>Mỗi business giữ context, adapter và dữ liệu độc lập. Master Agent kết nối qua Registry / Workspace / Adapter.</p></div><div><span>EXTERNAL AI</span><h3>Gemini · Claude</h3><p>Specialist workers tùy chọn. Output là candidate work và phải qua Master review trước khi tích hợp.</p></div><div><span>SOURCE OF TRUTH</span><h3>Config-driven</h3><p>Registry, workspaces, adapters và integrations quyết định trạng thái; không hard-code trạng thái giả.</p></div></section>
</HubShell>}