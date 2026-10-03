# HC Sentinel — Autonomous Visual Operations

Local mirror: `D:\HCDecorHUB\HC_Sentinel`

Status: **OPERATOR_READY_CORE**
Latest completed scope: **P1 -> P14**
Full regression: **32/32 PASS, 0 FAIL**

## Purpose

HC Sentinel is the visual operations and verification layer for the HC ecosystem.

Core flow:

`Command -> Observe -> Detect -> Compare -> Diagnose -> Route -> Repair -> Verify -> Evidence -> DONE`

It is designed to work independently, remain local-first, and reuse selected AutoWatch operating patterns without depending on the live AutoWatch runtime.

## Operator modes

- MANUAL_NOW
- COMMAND
- EVENT
- WATCH
- SCHEDULED
- AUTO

Manual/user commands always have priority and must never be blocked by a schedule.

## Current capabilities

### P1-P3 — Core visual runtime
- mission/control kernel
- checkpoint/state transition
- duplicate mission protection
- effect lock / no blind duplicate side effects
- Playwright observer
- screenshot + DOM + console/network capture
- visual diff engine
- threshold handling

### P4-P10 — Autonomous visual operations
- quality sensors
- accessibility checks with axe-core
- responsive/overflow checks
- finding routing
- verify-to-DONE loop
- UNCERTAIN_EFFECT fail-closed behavior
- trigger broker and lease recovery
- observability adapter
- project/worker registry
- lane isolation
- golden-run safety scenarios

### P11-P14 — Operator layer
- natural command parser
- project profile registry
- baseline manager
- repair bridge
- operator planning flow
- project + viewport resolution
- approved-baseline requirement
- capability-based worker dispatch

Verified smoke flow:

`Sentinel kiểm tra GSC mobile -> GSC -> Mobile -> Approved Baseline -> DISPATCHED -> autodebug-ui`

## Project structure

- `core/` — provider-independent kernel and runtime logic
- `operator/` — human command/operator layer
- `adapters/` — browser, visual diff, accessibility, telemetry, replay and future providers
- `rules/` — visual/operational policies
- `data/` — sanitized project profiles, schemas, mappings and manifests
- `checkpoints/` — versioned checkpoint metadata
- `evidence/` — evidence indexes and curated test evidence
- `vendor/autowatch_snapshot/` — read-only AutoWatch reuse snapshot
- `tests/` — phase and regression tests
- `scripts/` — smoke and execution scripts
- `docs/` — architecture, build phases and technology radar

## UI direction

Command Center layout:
- command input
- Run Now / Compare / Verify / Route Repair
- project cards
- baseline status
- workers
- evidence
- recent activity
- mission runtime pipeline
- phase progress

Display rule:
- 🔵 Pxx / Phase
- 🟢 PASS
- ✅✨ DONE
- 🔴 FAIL / BLOCKED
- 🟡 WAITING / REVIEW_REQUIRED

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
- raw screenshots/video
- cache
- secrets/tokens

GitHub is the management/source-of-record layer, not the live runtime.

## Current verified milestone

- P1-P10: **SENTINEL_READY**
- P11-P14: **OPERATOR_READY_CORE**
- Full regression: **32/32 PASS**
- Main merge for P11-P14: `4dd7c08a69d07ffd62981e7cd180a0b692036c65`

## Next build direction

Next practical scope should focus on:
1. real target binding for GSC/AMO and other HC projects
2. Command Center UI
3. persistent project/baseline storage
4. live Repair Bridge integration
5. real end-to-end verify-after-repair runs
