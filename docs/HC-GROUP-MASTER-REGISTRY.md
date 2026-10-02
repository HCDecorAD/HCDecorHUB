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
   - Deployment authorities are only those registered in `config/site-registry.json` / `config/workspaces.json` (WordPress, GitHub Pages, Cloudflare control plane where applicable).
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
API credits, local worker capacity, rate limits, HCDR capacity and registered deployment-provider limits are resources; one blocked resource must not idle company.

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

## Worker Operating Law — 2026-10-02
This law governs all HC Group companies, projects, missions and workers. It is intentionally analogous to the existing rule that coding workers own their assigned code work through verification rather than waiting for the Owner to prompt every step.

### Mission ownership
- A Worker owns an assigned Mission until a valid terminal state: DONE, OWNER_REQUIRED, SAFETY_STOP, or exhausted HARD_BLOCKED.
- NOT_DONE + SAFE_TO_CONTINUE -> CONTINUE.
- A Worker must not return ownership to the Owner for ordinary technical progress.
- Stage N PASS -> persist evidence/checkpoint -> automatically advance to the next dependency-ready stage.
- A stage may contain parallel Tasks when dependencies and resources permit.

### Reporting and supervision
- Workers publish heartbeat/progress state to the Group Governor at a regular runtime-defined interval and on every meaningful state transition.
- Reports contain: Mission ID, current stage/task, state, last verified evidence, checkpoint, blocker if any, resource usage/need, next action, and ETA range when estimable.
- Missing/stale heartbeat does not mean project failure; Governor marks Worker SUSPECT, preserves checkpoint, investigates/reassigns safely, and keeps unrelated work running.
- Evidence, not conversational status, determines PASS/DONE.

### Portfolio concurrency
- WORK_EXISTS + SAFE_CAPACITY_EXISTS -> SOMETHING_MUST_BE_RUNNING.
- WAITING_ONE_MISSION != WAITING_THE_COMPANY.
- BLOCKED_PROJECT -> CHECKPOINT -> SCHEDULE_NEXT_READY_WORK.
- A blocked dependency pauses only dependent descendants, never an entire company or portfolio.
- Independent READY missions are dispatched concurrently subject to safety and Resource Governor limits.
- A Worker that becomes free is assigned the highest-priority dependency-ready Mission it is capable of executing.
- No global phase barrier: CP/G milestones are maturity gates, not a reason to idle independent workstreams.

### Worker handoff and recovery
- Worker loss/stall -> recover from latest verified checkpoint; never blindly redo PASS stages.
- Recoverable failure -> AutoDebug -> bounded retry -> strategy change -> verify -> resume.
- Reassignment must preserve Mission ID, evidence chain and idempotency rules.
- Uncertain external side effects fail closed and require post-verification before retry.

### Resource and bandwidth law
- CPU, RAM, disk, local worker slots, network concurrency/link capacity, API rate limits/credits, HCDR queue capacity and deployment quotas are schedulable resources.
- Resource contention throttles only affected work; it must not idle unrelated READY missions.
- High-bandwidth/download/build jobs are capacity-aware and may be queued or limited so interactive/critical lanes remain usable.

### Governance defects
- USER_NUDGE_REQUIRED = DEFECT
- BOSS_IS_THE_DAEMON = DEFECT
- CHAT_TURN_IS_THE_LOOP = DEFECT
- ONE_BLOCKED_WORKER_STALLS_GROUP = DEFECT
- GLOBAL_PHASE_BARRIER_WITH_READY_INDEPENDENT_WORK = DEFECT
- WORKER_DONE_WITHOUT_EVIDENCE = DEFECT
- FREE_SAFE_CAPACITY_WHILE_READY_WORK_EXISTS = DEFECT

### Example
TransWarp may remain WAITING/RUNNING with an exact checkpoint while its Worker advances Stage 1 -> Stage 2 -> ... -> Final Gate -> DONE. During that time, other qualified Workers continue HUB, HC DONE, AutoDebug, HCDR, GSC, AMO, Catalog or other READY missions. Only real dependency edges cause waiting.

## Retired infrastructure
- Vercel is RETIRED/REMOVED from HC Group runtime and deployment architecture.
- Vercel checks/statuses must not gate, block, schedule, deploy, or influence HC Group mission state.
- Any legacy GitHub/Vercel status context is external stale integration noise unless explicitly re-authorized by Owner.

## Source-of-truth rule
This document is the human-readable corporate map. The machine-readable registry must become runtime source of truth. Chat summaries are inputs, not authoritative runtime state. Every status change requires timestamped evidence.
