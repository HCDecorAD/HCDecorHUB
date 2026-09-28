# HCDecor HUB — Master Agent Architecture v2

## Core principle
One Core → One Master Agent → Multiple Workspaces → Specialized Workers → Tools/Adapters → Verification/Audit.

The Master Agent is the only orchestration authority. It owns intent understanding, context loading, planning, routing, permission checks, verification and audit. Specialist agents are workers, not independent controllers.

## Runtime flow
User / Admin
→ Master Agent
→ Context Resolver (HUB → Workspace → Project → Task → Runtime)
→ Planner
→ Permission Gate
→ Worker Router / Tool Router
→ HCDecor | GSC | AMO adapters
→ QA / Verification
→ Audit Log
→ Result

Safe independent tasks may run in parallel. Writes to the same resource remain sequential.

## Workspaces
HCDecor: primary/internal, WordPress + Elementor, Design · Build · Fabrication · Media AI.
GSC: connected/external, Senior Living & Wellness. HUB manages connection/context; public GSC implementation remains isolated.
AMO NGUYEN: connected/external, Men's Footwear / Quiet Luxury. No fabricated products, prices, inventory, orders or customer data.

## Worker layer
Website Agent: website, UI/UX, responsive, code, release preparation.
Content Agent: content plan, copy, SEO, localization.
Media Agent: media classification, preparation, optimization, visual specifications.
Publishing Agent: preview, scheduling and publishing behind approval gates.
Project Assistant: project context, CRM linkage and reporting.
QA Agent: validation, regression checks and release gates.

Legacy agent IDs remain mapped for compatibility: developer, design, content, media, publishing, crm, report.

## External AI
Gemini and Claude are optional specialist workers. They do not control HUB architecture. Their output is candidate work and requires Master review before integration.

## Source of truth
Registry, workspaces, adapters and integrations configs remain authoritative. UI/runtime status must be config-driven. Unknown data stays unknown; missing last-run stays “Chưa có dữ liệu”.

## Production protection
Page 44 remains protected. Existing hcdecor-core is inherited, never duplicated. Publish, deploy, destructive management and core replacement require permission gates.
