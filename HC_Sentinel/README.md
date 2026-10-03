# HC Sentinel — Autonomous Visual Operations

Local mirror: `D:\HCDecorHUB\HC_Sentinel`

Status: **OPERATOR_READY**
Latest completed scope: **P1 -> P19**
Full regression: **39/39 PASS, 0 FAIL**

## Purpose

HC Sentinel is the visual operations and verification layer for the HC ecosystem.

Core flow:

`Command -> Observe -> Detect -> Compare -> Diagnose -> Route -> Repair -> Verify -> Evidence -> DONE`

It is independent, local-first, command-driven, and designed to supervise multiple HC projects without making schedule/remote infrastructure the owner of execution.

## Operator modes

- MANUAL_NOW
- COMMAND
- EVENT
- WATCH
- SCHEDULED
- AUTO

Manual/user command always has priority and must never be blocked by a schedule.

## Current capabilities

### P1-P3 — Core visual runtime
- mission/control kernel
- checkpoint/state transition
- duplicate mission protection
- effect lock
- Playwright observer
- screenshot + DOM + console/network capture
- visual diff + thresholds

### P4-P10 — Autonomous visual operations
- quality sensors
- axe-core accessibility
- responsive/overflow checks
- finding routing
- verify-to-DONE loop
- UNCERTAIN_EFFECT fail-closed handling
- trigger broker + lease recovery
- observability adapter
- project/worker registry
- lane isolation
- golden safety scenarios

### P11-P14 — Operator core
- natural operator command parser
- project profile registry
- baseline manager
- repair bridge
- project + viewport resolution
- approved-baseline requirement
- capability-based worker dispatch

### P15-P19 — Practical operator runtime
- real target profiles for GSC and AMO public sites
- Command Center UI
- dark/light theme toggle
- light theme uses gray-white background
- atomic JSON persistence adapter
- HCDR repair-envelope adapter
- live HCDR transport dispatch
- real public target E2E capture
- screenshot/evidence artifact generation

Verified operator smoke:

`Sentinel kiểm tra GSC mobile -> GSC -> Mobile -> Approved Baseline -> DISPATCHED -> autodebug-ui`

Verified public targets:
- GSC: reachable, no failed requests; Sentinel detected 3 medium broken-media hints and routed them to HCDR issue #1265 for inspection.
- AMO: reachable, no failed requests, no quality findings in the P19 scan.

## Command Center UI

The real UI lives in `ui/`.

Main features:
- command input
- Run Now / Compare / Verify / Route Repair
- project cards
- Pxx phase progress
- mission runtime pipeline
- workers
- evidence
- recent activity
- full-regression status

Theme:
- Dark: deep navy
- Light: gray-white
- theme preference persisted in localStorage

Display rule:
- 🔵 Pxx / Phase
- 🟢 PASS
- ✅✨ DONE
- 🔴 FAIL / BLOCKED
- 🟡 WAITING / REVIEW_REQUIRED

## Project structure

- `core/` — provider-independent kernel/runtime
- `operator/` — operator command layer
- `adapters/` — browser, visual diff, accessibility, telemetry, replay, repair
- `ui/` — Command Center app UI
- `rules/` — visual/operational policies
- `data/` — sanitized target/project definitions
- `checkpoints/` — checkpoint metadata
- `evidence/` — evidence indexes and execution evidence
- `vendor/autowatch_snapshot/` — read-only AutoWatch reuse snapshot
- `tests/` — phase and regression tests
- `scripts/` — smoke/E2E scripts
- `docs/` — architecture and roadmap

## Data policy

GitHub stores:
- source
- sanitized manifests/data
- schemas
- checkpoints
- evidence indexes
- docs

Local runtime stores:
- live DB
- active queue
- locks
- browser/session profiles
- raw private screenshots/video
- cache
- secrets/tokens

GitHub is the management/source-of-record layer, not the live runtime.

## Verified milestones

- P1-P10: **SENTINEL_READY**
- P11-P14: **OPERATOR_READY_CORE**
- P15-P19: **OPERATOR_READY**
- Full regression: **39/39 PASS**
- P15-P19 workflow: `37150454260`
- P15-P19 evidence SHA256: `f8c5e6e31dd64b1262415a87dace2035e491f4cc0d0df60db6b25e7b6949152`

## Current live finding

GSC public scan detected:
- `BROKEN_MEDIA_HINT`
- severity: medium
- count: 3
- failed requests: 0

Repair/inspection dispatch:
- HCDR issue #1265

This finding does not invalidate the Sentinel build gate; it is evidence that the detect -> route path works against a real target.
