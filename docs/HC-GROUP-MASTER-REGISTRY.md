# HC GROUP MASTER REGISTRY
Version: 2026-10-02
Owner: Ho Cuong
Status: LIVING SOURCE OF TRUTH

## Mission
HC Group is a design, construction, digital-product and AI-automation ecosystem. Operating target: one owner goal -> portfolio planning -> autonomous execution -> evidence -> DONE, while useful work continues whenever safe capacity and READY work exist.

Corporate laws:
- WORK_EXISTS + SAFE_CAPACITY_EXISTS -> SOMETHING_MUST_BE_RUNNING
- WAITING_ONE_MISSION != WAITING_THE_COMPANY
- BLOCKED_PROJECT -> CHECKPOINT -> SCHEDULE_NEXT_READY_WORK
- USER_NUDGE_REQUIRED = DEFECT
- CHAT_TURN_IS_THE_LOOP = DEFECT
- GOAL_MUST_OUTLIVE_CHAT = REQUIRED
- HCDR ONLY for normal HC local execution
- NO REAL GATE -> NO GREEN

## Operating model
OWNER -> GROUP GOVERNOR -> CORPORATE CATALOG + PORTFOLIO SCHEDULER -> HC DONE -> TransWarp -> HCDR -> Workers/Tools/Project Adapters -> AutoDebug -> QA/Release Gates -> Evidence/Fix Memory.

Canonical hierarchy:
Domain -> System -> Project -> Goal -> Mission -> Task -> Worker/Tool -> Evidence -> Checkpoint -> Release.

## Departments
1. Executive / Portfolio Office
   - Group Governor: priorities, dependencies, capacity, next-ready-work.
   - Corporate Catalog: projects, tools, APIs, resources, lifecycle, health.
   - Portfolio Scheduler: READY/RUNNING/WAITING/BLOCKED/VERIFYING/DONE.
2. Mission Operations
   - HC DONE: owns approved Goal until valid terminal state.
   - TransWarp: capability/path routing; no rediscovery of verified capability.
   - HC Agent Control: worker/agent coordination.
   - HC AutoChat: control/conversation UI, never mission owner.
3. Infrastructure / Transport
   - HCDR: canonical local execution transport.
   - GitHub: source/issues/CI/evidence.
   - Vercel/hosting adapters: deploy resources; quota never stops unrelated work.
   - Drive/Library: assets/media/backups.
4. Reliability / QA
   - HC AutoDebug / Immune Core: diagnose -> minimal repair -> verify -> regression -> fix memory.
   - HC MOONSHOT: mission-readiness gate.
   - Security Lite: known-good fast path; suspicious/high-risk gets checks.
5. Product / Creative Engineering
   - HCDecor HUB, HC Design AI Studio, HC Visual Builder, HC MediaFlow, HC Video Downloader, Social/Publishing automation.
6. Business Workspaces
   - HCDecor business/public presence.
   - GSC Senior Living & Wellness Digital Twin.
   - AMO Nguyen premium men's footwear commerce.
   - Future workspaces register by schema; no context mixing.

## Current GitHub repositories
- HCDecorAD/HCDecorHUB — main platform/core.
- HCDecorAD/GSC — GSC application.
- HCDecorAD/AMONguyen — AMO application.
- HCDecorAD/HCDecor-HCDR-Relay — HCDR transport/control queue.

Gap: many active local projects/tools are not first-class catalog entities. The registry must cover local-only assets too.

## Portfolio
| Project/System | Role | Known state | Next gate |
|---|---|---|---|
| HCDecor HUB | Main corporate AI/control platform | ACTIVE | Continue S5.x core then durable runtime |
| HC DONE | Goal lifecycle | ACTIVE | Durable local supervisor + autonomous recovery proof |
| TransWarp | Capability/mission routing | ACTIVE/WAITING | Resume checkpoint; injected recoverable failure; final gate |
| HCDR | Local transport | GREEN baseline | Keep single production consumer; evidence health |
| HC AutoDebug | Reliability/repair | GREEN baseline, evolving | Recovery worker under durable supervisor |
| HC Agent Control | Agent control | NOT PROVEN DONE | Restore deps/gates and run acceptance |
| HC AutoChat | Control automation | ACTIVE | Never become mission loop |
| HC MOONSHOT | Readiness exercise | FIRST LAUNCH GREEN | Re-run only after meaningful architecture/release change |
| HC Design AI Studio | AI design platform | ACTIVE | Continue latest checkpoint/local-first roadmap |
| HC Visual Builder | Shared visual editor | ACTIVE | Stabilize/reuse across sites |
| GSC | Senior Living Digital Twin | ACTIVE/public | UX/mobile/media/VN-EN/performance |
| AMO Nguyen | Commerce | BLOCKED QA | Diagnose failing QA before release |
| HC MediaFlow | Multi-platform media | ACTIVE | Consolidate media capabilities/adapters |
| HC Video Downloader | Media utility | ACTIVE | Continue latest tested checkpoint; avoid duplication |
| HCDecor public web | Business presence | PRODUCTION known | Maintain and register ownership/dependencies |

## HUB roadmap
S5.1 Task DAG; S5.2 Capability Router; S5.3 Workspace Schema (NOW); S5.4 Deployment Adapter; S5.5 Policy Engine; S6 Durable Runtime Adapters; S7 GSC E2E; S8 AMO backend/data; S9 Social/Media; S10 Visual Editor.
Infrastructure supports this roadmap; it must not permanently replace product delivery.

## Goal/Mission board
ACTIVE:
- DONE HC DONE: durable mission ownership, automatic recovery, resume without owner nudge.
- DONE TransWarp: acceptance mission for durable execution.
- HCDecor HUB core roadmap.
- GSC public refinement.
- AMO QA recovery.

WAITING/BLOCKED:
- TransWarp acceptance waits for proven durable local supervisor/execution path.
- AMO release blocked by QA failures.
- Vercel quota constraints checkpoint deploy work and release capacity to non-deploy missions.

VERIFIED BASELINES:
- HC MOONSHOT First Launch: AutoDebug 139/139; DONE targeted 5/5; lint PASS at recorded checkpoint.
- HCDR post-legacy-cleanup exactly-once acceptance passed at recorded checkpoint.
- HC DONE approval-inheritance module tests 7/7 at recorded checkpoint; end-to-end dispatch still requires proof.

## Scheduling policy
Priority considers business value, dependency criticality, readiness, risk, time-to-evidence, resource/quota constraints and starvation prevention.
- Independent READY work may run in parallel.
- WAITING yields worker/capacity.
- BLOCKED checkpoints exact blocker/evidence.
- PASS stages are not blindly repeated.
- Same failure fingerprint gets bounded retries then strategy change.
- Normal progress never requires owner commands.

## Missing capabilities
P0 Corporate Catalog / Registry:
machine-readable Project/Tool/Resource/Goal/Mission/Worker/Evidence manifests; owns/dependsOn/provides/consumes/blockedBy; lifecycle, health, checkpoint.

P0 Durable Group Governor:
persistent mission queue; lease/heartbeat/fencing; dependency-aware READY selection; restart recovery; work-conserving scheduling.

P0 Unified Evidence / Observability:
Mission ID/correlation ID across HCDR, DONE, AutoDebug, GitHub and project gates; logs/metrics/traces/events; evidence-based health.

P1 Capability Registry:
every tool declares capability, I/O contract, risk class, gate and cost; TransWarp routes known capability.

P1 Resource/Budget Governor:
Vercel quota, API credits, local worker capacity and rate limits are resources; one blocked resource must not idle company.

P1 Knowledge/Fix Memory:
only verified fixes/research promoted; fingerprints linked to evidence/components.

P2 Operator Dashboard:
portfolio, goals, missions, workers, blockers, ETA ranges and evidence; owner sees exceptions/decisions rather than internal chatter.

## External patterns to adopt selectively
- Backstage: central catalog model of Components/APIs/Resources grouped into Systems/Domains. Adopt the model, not necessarily the full product.
- Dagster: desired-state/dependency-aware daemon continuously decides what needs work.
- Temporal: durable execution/event history, retries/heartbeats and resumable workflows; adopt principles first and stay lightweight.
- OpenTelemetry: shared traces, metrics and logs with correlation IDs.

## MOON readiness
- Catalog coverage >=95% of active projects/tools/resources.
- Every active Goal has state, dependencies, acceptance gate and checkpoint.
- Group Governor survives chat/process restart.
- Injected recoverable failure self-recovers without owner nudge.
- Waiting one mission does not idle portfolio.
- HCDR remains canonical normal local route.
- No false-green releases.
- HUB roadmap continues advancing.

## Implementation sequence
CP0 Inventory: ingest repos, local roots, docs and checkpoints.
CP1 Catalog schema: Project/Tool/Resource/Goal/Mission/Worker/Evidence.
CP2 Governor: persistent queue + dependency scheduler + next-ready selection.
CP3 Durable DONE: autonomous recovery/resume + owner-exception rules.
CP4 TransWarp acceptance: injected failure -> recovery -> resume -> final gate.
CP5 Portfolio pilot: HC DONE/TransWarp + HUB + AMO/GSC; blocked lane must not stall others.
CP6 MOON gate: restart/recovery/observability/security/release evidence.

## Source-of-truth rule
This document is the human-readable corporate map. The machine-readable registry must become runtime source of truth. Chat summaries are inputs, not authoritative runtime state. Every status change requires timestamped evidence.
