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
| HC Visual Builder | Shared visual editor | GREEN source/CI contract | AMO + GSC visual editor gates PASS; production publish authority remains loopback/local-only |
| GSC | Senior Living Digital Twin | GREEN source E2E/public + production smoke runtime | Source E2E + Visual Editor gates PASS; scheduled production smoke run `37048049301` SUCCESS on production HEAD `6c5bc3a8044d1b332b7232546134be49dffeb088`; continue product refinement |
| AMO Nguyen | Commerce | GREEN Public V4 QA | Public V4 QA + artifact upload PASS; continue backend/data/product refinement |
| HC MediaFlow | Multi-platform media | ACTIVE | Consolidate media capabilities/adapters |
| HC Video Downloader | Media utility | ACTIVE | Continue latest tested checkpoint; avoid duplication |
| HCDecor public web | Business presence | PRODUCTION known | Maintain and register ownership/dependencies |

## HUB roadmap
S5.1 Task DAG; S5.2 Capability Router; S5.3 Workspace Schema; S5.4 Deployment Adapter; S5.5 Policy Engine; S6 Durable Runtime Adapters; S7 GSC E2E; S8 AMO backend/data; S9 Social/Media; S10 Visual Editor. Source/CI roadmap gates S5.3-S10 are GREEN at the 2026-10-02 checkpoint.
Infrastructure supports this roadmap; it must not permanently replace product delivery.

## Goal/Mission board
ACTIVE:
- DONE HC DONE: durable mission ownership, automatic recovery, resume without owner nudge.
- DONE TransWarp: acceptance mission for durable execution.
- HCDecor HUB core roadmap.
- GSC public refinement; scheduled production smoke runtime is GREEN at run `37048049301`.
- AMO backend/data refinement after Public V4 QA recovery.
- DONE Unified Evidence / Observability baseline.
- DONE Corporate Catalog / Capability Registry / Resource Governor / Verified Fix Memory baselines.
- Operator Dashboard read-only baseline GREEN; runtime mission/worker telemetry enrichment remains.

WAITING/BLOCKED:
- TransWarp acceptance waits for proven durable local supervisor/execution path.

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

## Verified capabilities
- P0 Corporate Catalog / Registry 1.1.0: machine-readable projects/systems/tools/resources in `config/corporate-catalog.json`. Eleven active HC Group systems are first-class catalog entities, including HC DONE, TransWarp, HCDR, AutoDebug, Agent Control, AutoChat, MOONSHOT, Design AI Studio, Visual Builder, MediaFlow and Video Downloader. Quality Gate + Production Verify PASS at `616f99ef7db0e71ba853b6f775332dcaffafee91`. Future/new local-only assets remain an ongoing catalog-expansion duty.
- P0 Durable Group Governor baseline: persistent mission queue, lease/heartbeat/fencing, dependency-aware READY selection, restart recovery and work-conserving scheduling; durable queue/DONE/portfolio/MOON gates PASS.
- P0 Unified Evidence / Observability baseline: evidence envelope + `mission_id`/`correlation_id` persistence through durable queue retry/restart/DONE; Quality Gate PASS. Full fleet-wide logs/metrics/traces backend is not yet claimed.
- P1 Capability Registry baseline: machine-readable capability I/O, risk class, gate, cost class and fail-closed declaration in `config/capability-registry.json`; Quality Gate PASS.
- P1 Resource/Budget Governor integrated baseline: registered resource capacities plus durable file-backed budget/quota admission fail closed to `WAITING_RESOURCE`; persisted spend survives restart, decision IDs prevent double-charge on retry, lazy refill is supported, exhausted/unknown budgets do not stall unrelated READY work, and multi-budget admission is atomic within the single-Governor file-backed ledger so a later denial cannot partially spend earlier budgets. Quality Gate + Production Verify PASS at `caa1cef073269f4aeb0d11f06f1f8ce52b01e30e`.
- P1 Knowledge/Fix Memory baseline: only terminal-verified fingerprints/fixes are promoted in `config/fix-memory.json`; Quality Gate PASS.
- P2 Operator Dashboard authority-aware telemetry baseline: `/hub/operator` is a read-only portfolio/catalog/capability + Goal/Mission + runtime telemetry surface. It shows durable-runtime diagnostics, recent run history, blockers, worker HEALTHY/SUSPECT state, mission/correlation links and checkpoints. Each source carries explicit authority metadata; worker heartbeat and local run history remain non-production-authoritative. No deploy/publish/retry/approval write path. Quality Gate + Production Verify PASS at `17fd082a267e7d9ba2931aaf2f0f24e907a98e0d`.
- Durable Evidence Store baseline: append-only validated JSONL evidence events persist locally across restart, support mission/correlation filtering, and are linked into Operator Telemetry so worker records expose recent mission evidence. Source authority remains `local-spool`, `production_authority:false`. Quality Gate + Production Verify PASS at `44686825103bcc6786b5d6ac213e1b41cefa27be`.
- Evidence store corruption hardening: malformed JSONL lines no longer crash operator telemetry; valid events remain readable while store health becomes `DEGRADED` with explicit malformed/valid counts. Operator Dashboard exposes this read-only health signal. Quality Gate + Production Verify PASS at `ee33a2330a9dd89f0e5763aa981f911a7589d790` and UI visibility PASS at `c4cb83761ba43e1d84bc12ebde386b97c3e5fa5c`.
- Goal/Mission Lifecycle Registry 1.1.0: Goal and Mission records now carry validated references into Corporate Catalog Project/System/Tool entities plus explicit evidence references. Quality Gate + Production Verify PASS at `d66acce0623d957631dca45de905bbb2a811bf4f`.

- MOON machine readiness baseline: restart/recovery, blocked-lane portfolio concurrency, security/release/no-Vercel, active Goal completeness, HCDR canonical local route, catalog System→Tool integrity, and declared active inventory coverage are machine-gated. The strengthened 10-check gate plus independent declared-inventory denominator are verified; current declared coverage is 34/34 = 100.00%. Enterprise completeness outside the declared inventory is not claimed.

- Declared active asset inventory coverage: `config/active-asset-inventory.json` defines an independent machine denominator of 34 currently declared active assets (3 projects, 11 systems, 15 tools, 5 resources). Corporate Catalog covers 34/34 = 100.00% of this declared inventory; Quality Gate + Production Verify PASS at `003d6798d7d55c20605b949b4eaaee9f81de9b55`. This is not a claim that every possible HC Group asset outside the declared inventory has been discovered.

- Budget deny diagnostics: atomic multi-budget admission preserves zero partial spend while exposing per-budget `sufficient:true/false` plus transaction-level `denied_by`; scheduler preserves these diagnostics without stalling unrelated READY work. Quality Gate + Production Verify PASS at `0270010c8cd7f31161ced8b2ccdacd26872a9218` and scheduler regression guard PASS at `910d9c825fa0ee61987e14e5cf8de885907a4850`.

- HC AutoChat source contract: operator-UI-only separation is machine-gated; chat must not own the durable mission loop, Goal/Mission must outlive chat/session loss, direct production authority is forbidden, and evidence—not conversation—determines DONE. Quality Gate + Production Verify PASS at `7136f7aca92c988cf485198a9715abb33873f400`. Runtime remains source-contract-only.
- HC MediaFlow / Video Downloader / Design AI Studio source contracts: machine-readable fail-closed contracts are now gated for media routing, bounded/restart-safe download semantics, provenance/local-first design semantics, mission correlation, and no direct production publish authority. Regression guard + Quality Gate + Production Verify PASS at `b76bbbd8307e2923856fa3798fd6b61b3a9bc97a`. These are source/CI contracts only; live runtime/provider/HOCUONG execution is not claimed.

- HC Agent Control source contract: machine-readable acceptance at `config/hc-agent-control-acceptance.json`; catalog/tool/system state is `source-contract-only`. Quality Gate + Production Verify PASS at `5a624a0a24ff473dea49c0c42eba042e87e2ff7a`. This proves control semantics in source/CI only; live runtime ownership, worker transport, and HOCUONG execution remain NOT_PROVEN.

- HCDR mission/correlation source contract: relay job/result envelope now carries `source_id`, `mission_id`, and `correlation_id`; HCDR repo source-contract CI check `correlation` PASS at `6696e4e41940582ae344925c65949c25e7b4ed0f` (Actions run `37056914452`). This proves source-level contract/echo semantics only; HOCUONG runtime end-to-end propagation remains NOT_PROVEN.

## Remaining capability expansion
- Maintain Corporate Catalog coverage at >=95% of the independent declared-active inventory; current measured coverage is 34/34 = 100.00%. Expand the inventory first when genuinely new active assets are discovered, then require catalog catch-up. Goal/Mission → Project/System/Tool/Evidence reference relations are now covered by Lifecycle Registry 1.1.0; local Worker↔Mission↔Evidence runtime relations are now covered by the durable evidence spool baseline; shared/fenced multi-writer telemetry remains an expansion area only when required.
- HCDR/AutoDebug source correlation contracts are now registered and source-gated. HC Agent Control now also has a machine-readable source acceptance contract covering mission ownership, no-user-nudge ordinary progress, blocked-lane isolation, heartbeat/checkpoint requirements, evidence-backed DONE, and fail-closed uncertain side effects. Quality Gate + Production Verify PASS at `5a624a0a24ff473dea49c0c42eba042e87e2ff7a`. Agent Control remains `source-contract-only` with `runtime_done:false`; no live Agent Control runtime/HOCUONG execution is claimed. Next expansion is real runtime end-to-end evidence where execution authority exists.
- Extend the durable budget ledger from single-Governor file-backed admission to a fenced/atomic multi-writer backend only when fleet-wide concurrent writers are actually required.
- Extend worker heartbeat and operator telemetry from single-node local-spool diagnostics to a durable shared/fenced backend only when multi-writer runtime authority is actually required; keep the current read-only dashboard authority labels fail-honest.

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

- Declared catalog/lifecycle source-contract closure: after `b76bbbd8307e2923856fa3798fd6b61b3a9bc97a`, no Corporate Catalog tool/system remains in `active` or `not-proven-done` state and no lifecycle mission remains non-DONE within the declared scope. This does not imply enterprise completeness or live-runtime completeness; runtime-only claims still require separate evidence.

- HCDR + AutoDebug source implementation gates: HCDR relay result envelopes now echo `source_id`, `mission_id`, and `correlation_id`; AutoDebug has executable verified-fix diagnosis and fail-closed known-fix repair verification. Quality Gate `37081434598` and Production Verify `37081434745` both PASS at `66b602f60f671c9874c7a15ad7f7bbe8b72b7fe2`. Capability state is `source-implementation-gated`; live HOCUONG/runtime end-to-end execution remains NOT_PROVEN.

- Operator capability/evidence visibility: capability verification state is rendered via telemetry with fail-honest authority labels, and recent evidence events are shown read-only with local-spool/non-production authority. Quality Gate `37082779143` and Production Verify `37082779181` PASS at `601600a3f7d090862a4507ff7f8bbb5a9742bf0f`.
