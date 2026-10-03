# HC Sentinel — Build Phases

## Phase 0 — Freeze & Source Intake
Goal: establish a clean base without touching live AutoWatch.

Deliverables:
- read-only AutoWatch snapshot
- manifest + SHA256
- source map
- excluded runtime/secrets list
- HC Sentinel local/GitHub DATA structure

Gate:
SOURCE_INTAKE_PASS

## Phase 1 — Sentinel Kernel
Goal: build the independent control plane.

Build:
- mission model
- MANUAL_NOW / COMMAND
- priority arbitration
- pause/resume/cancel
- checkpoint
- idempotency key
- effect lock
- structured event timeline

Gate:
KERNEL_PASS

## Phase 2 — Visual Observer
Goal: Sentinel can inspect a real web/app target.

Build:
- Playwright adapter
- screenshot capture
- viewport/device presets
- DOM snapshot
- console/network capture
- target recipe format

Gate:
OBSERVER_PASS

## Phase 3 — Visual Diff Engine
Goal: detect meaningful changes without drowning in false positives.

Build:
- baseline store
- pixel diff adapter
- region masks
- threshold profiles
- perceptual similarity hook
- ignore dynamic zones
- diff image + metrics

Gate:
VISUAL_DIFF_PASS

## Phase 4 — Quality Sensors
Goal: expand from pixels to product quality.

Build:
- axe-core adapter
- responsive overflow/layout checks
- broken image/video detection
- interaction smoke tests
- optional performance hooks

Gate:
QUALITY_PASS

## Phase 5 — Decision & Repair Routing
Goal: convert findings into actionable missions.

Build:
- finding classifier
- severity/confidence
- deduplication
- route to AutoDebug / Agent Control / project worker
- repair lane separated from production lane
- REVIEW_REQUIRED logic

Gate:
ROUTER_PASS

## Phase 6 — Verify-to-DONE Loop
Goal: close the loop safely.

Build:
- rerun verification recipe
- before/after/diff evidence
- exact source/target revision
- GREEN only from execution evidence
- UNCERTAIN_EFFECT handling
- no blind retry

Gate:
VERIFY_LOOP_PASS

## Phase 7 — Watch / Event / Schedule
Goal: add automation without taking control away from the user.

Build:
- WATCH mode
- EVENT mode
- SCHEDULED mode
- AUTO mode
- heartbeat
- lease TTL
- stale mission recovery
- manual trigger preemption/priority

Gate:
AUTONOMY_PASS

## Phase 8 — Replay & Observability
Goal: make failures easy to reproduce and trace.

Build:
- optional rrweb adapter
- structured traces
- OpenTelemetry adapter
- event correlation IDs
- compact evidence timeline

Gate:
OBSERVABILITY_PASS

## Phase 9 — Multi-Project Sentinel
Goal: one Sentinel supervises multiple HC projects safely.

Build:
- project registry
- capability workers
- target profiles
- per-project policy
- independent queues/lanes
- quota and rate-limit awareness

Gate:
MULTI_PROJECT_PASS

## Phase 10 — Golden Run / Public Internal
Goal: prove stable long-running operation.

Required scenarios:
- manual command during scheduled idle
- watch detects regression
- unrelated project continues when one lane fails
- restart resumes from checkpoint
- duplicate mission does not duplicate side effects
- bad baseline cannot auto-promote
- evidence survives restart
- full regression

Gate:
SENTINEL_READY

## Build order rule
Do not implement Phase N+1 as a hard dependency before Phase N has an execution-evidence PASS.
Independent adapters may be researched in parallel.
