# HC GROUP CORPORATE BLUEPRINT V9
Version: 2026-10-04
Owner: Ho Cuong
Status: HUMAN-READABLE CORPORATE MAP
Machine source of truth: config/corporate-catalog.json + config/active-asset-inventory.json + config/capability-registry.json + config/site-registry.json + config/workspaces.json

## 1. Mission
HC Group is a design, construction, digital-product, commerce, education and AI-automation ecosystem.
Operating mission: ONE OWNER GOAL -> PLAN -> ROUTE -> EXECUTE -> VERIFY -> EVIDENCE -> DONE.
The owner gives goals; HCDecor HUB coordinates systems, workers, tools and business workspaces. Chat is an operator surface, not the durable mission owner.

## 2. Corporate structure

OWNER — Ho Cuong
└─ HCDecor HUB V9 — AI Operating System / Multi-Business Command Center
   ├─ Executive & Orchestration
   │  ├─ Group Governor — portfolio scheduling, durable queue, dependencies
   │  ├─ HC DONE / iMaster — owns Goal/Mission lifecycle until valid terminal state
   │  ├─ Task DAG / Capability Router — decomposes and routes work
   │  └─ Policy Engine / Deployment Adapter — approval and production-write planning
   ├─ Operations & Infrastructure
   │  ├─ HCDR v3 — canonical local execution transport to HOCUONG
   │  ├─ TransWarp — capability/mission routing and durable recovery
   │  ├─ HC Agent Control — worker/agent coordination
   │  └─ HC AutoChat — operator/conversation automation; never mission owner
   ├─ Reliability, QA & Evidence
   │  ├─ HC Sentinel — desktop/project monitoring, compare, verify and repair routing
   │  ├─ HC AutoDebug / Immune Core — diagnose, minimal repair, regression, fix memory
   │  ├─ HC MOONSHOT — mission/readiness gate
   │  └─ Unified Evidence / Operator Dashboard — audit, telemetry, checkpoints
   ├─ Design, Web & Creative
   │  ├─ HC Design AI Studio — architecture/interior/exterior AI design and rendering
   │  ├─ HC Visual Builder — shared visual website editor
   │  ├─ HC Creative Factory — creative/media production pipeline
   │  └─ HC Video Downloader — media acquisition utility
   ├─ Content, Media & Social
   │  └─ HC MediaFlow / Publishing Center
   │     └─ Campaign -> Content -> Creative -> Asset/Variants -> Multi-Social -> Bulk Schedule -> Publish -> Verify -> Analytics
   ├─ Business Workspaces
   │  ├─ HCDecor — public/business presence; WordPress is production authority
   │  ├─ GSC Senior Living & Wellness — Digital Twin; GitHub Pages is production authority
   │  └─ AMO Nguyen — premium men's footwear commerce; GitHub Pages is production authority
   └─ Education Ecosystem
      ├─ HC Future — THCS -> THPT -> University learning ecosystem
      └─ HC English World — independent English Core, Pre-A1 -> C1+ and advanced tracks

## 3. What the Group currently has
Machine-declared HCDecorHUB inventory at the recorded baseline: 34 active assets = 3 projects + 11 systems + 15 tools + 5 resources, with catalog coverage 34/34.
In addition, separate first-class repositories exist for Creative Factory, Design AI Studio, TransWarp Infrastructure, HC Future and HC English World; these must be incorporated into future catalog expansion rather than forgotten because they are outside the original 34-asset denominator.

Current HCDecorAD repositories observed on 2026-10-04:
- HCDecorAD/HCDecorHUB
- HCDecorAD/GSC
- HCDecorAD/AMONguyen
- HCDecorAD/HCDecor-HCDR-Relay
- HCDecorAD/HC-TransWarp-Infrastructure
- HCDecorAD/HC-Design-AI-Studio
- HCDecorAD/HC-Creative-Factory
- HCDecorAD/HC-Future
- HCDecorAD/HC-English-World

## 4. Production authority
- HCDecor public: WordPress
- GSC: GitHub Pages
- AMO Nguyen: GitHub Pages
- GitHub main: source/version/rollback authority
- HOCUONG local execution: HCDR/local-first
- Cloudflare may serve registered control-plane/API functions where configured.
- Vercel is NOT part of the current HC Group production architecture and must not be treated as a deployment authority.

## 5. Data placement
Authoritative machine data:
- config/corporate-catalog.json — projects/systems/tools/resources
- config/active-asset-inventory.json — independent active-asset denominator
- config/capability-registry.json — capability -> provider/gate/risk
- config/site-registry.json — site/deployment authority
- config/workspaces.json — workspace modules/repos/domains/policy

Authoritative human operating data:
- docs/HC-GROUP-MASTER-REGISTRY.md — living master registry/status
- docs/HC-GROUP-CORPORATE-BLUEPRINT-V9.md — this corporate map
- docs/MASTER_AGENT_ARCHITECTURE_V2.md — orchestration architecture

Evidence/checkpoints:
- GitHub commits, Actions, evidence store and project checkpoint folders.
- PASS is not repeated blindly; no evidence -> no GREEN.

Backups:
- backups/corporate-registry/<date>/ — immutable human-readable snapshots of the corporate map and key registry data.
- Separate project repositories remain independent backup/version boundaries.

## 6. Operating laws
- WORK_EXISTS + SAFE_CAPACITY_EXISTS -> SOMETHING_MUST_BE_RUNNING.
- WAITING_ONE_MISSION != WAITING_THE_COMPANY.
- Blocked lanes checkpoint and yield capacity to independent READY work.
- ONE PROMPT may create many missions; missions outlive chat.
- Production writes are fail-closed and approval/gate controlled.
- Never fabricate runtime LIVE/PASS state.
- Never rediscover a verified capability if the registry already knows it.
- Never mix workspace/business context.

## 7. V9 direction
HCDecor HUB V9 is not merely a website or dashboard. It is the AI Operating System for HC Group:
OWNER GOAL -> Context -> Planner -> Task DAG -> Capability Router -> Workspace -> Workers/Tools -> Policy -> Execute -> Verify -> Evidence -> Repair if needed -> DONE.

Current roadmap lineage:
S5.1 Task DAG -> S5.2 Capability Router -> S5.3 Workspace Schema -> S5.4 Deployment Adapter -> S5.5 Policy Engine -> Durable Runtime -> business/product E2E.

## 8. Anti-amnesia rule
Before answering “HC Group has what?”, planning a new tool, or declaring a capability missing:
1. Read this Blueprint.
2. Read corporate-catalog + active-asset-inventory + capability-registry.
3. Read Master Registry for current status.
4. Check the relevant independent repository/local runtime.
5. Only then create new work.
