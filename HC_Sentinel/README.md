# HC Sentinel — Autonomous Visual Operations

Local mirror: `D:\HCDecorHUB\HC_Sentinel`

Status: **LOCAL_OPERATOR_READY**
Latest completed scope: **P1 -> P29**
Full regression: **59/59 PASS, 0 FAIL**

## Purpose

HC Sentinel is the visual operations, verification, watch, alerting, and repair-routing layer for the HC ecosystem.

Core flow:

`Command -> Observe -> Detect -> Compare -> Diagnose -> Route -> Repair -> Verify -> Evidence -> Alert -> DONE`

It is independent, local-first, command-driven, and multi-project capable.

## Current runtime modes

- MANUAL_NOW
- COMMAND
- EVENT
- WATCH
- SCHEDULED
- AUTO

Manual/user command always has priority and must never be blocked by a schedule.

## Verified capability groups

### P1-P10 — Sentinel foundation
Kernel, visual observer, visual diff, quality sensors, routing, verify-to-DONE, autonomy, observability, multi-project registry, golden run.

### P11-P19 — Operator runtime
Operator commands, project profiles, baseline manager, repair bridge, real targets, Command Center UI, dark/light theme, persistence, live HCDR transport, GSC/AMO public E2E.

### P20-P24 — Operations hardening
Finding lifecycle, mission ledger, policy engine, health supervisor, release gate.

### P25-P29 — Daily operations runtime
- notification center with pluggable sinks
- governed baseline promotion: CANDIDATE -> REVIEW -> APPROVED / REJECTED
- WATCH runtime with per-target intervals and finding alerts
- local command API binding
- local release manifest
- Windows launcher `runtime/start-sentinel.bat`
- local server entry `runtime/start-sentinel.js`

Verified smoke:

`baseline APPROVED -> WATCH run 1 -> alert 1`

## Command Center UI

UI lives in `ui/`.

Theme:
- Dark: deep navy
- Light: gray-white
- theme preference persisted locally

Display rule:
- 🔵 Pxx / Phase
- 🟢 PASS
- ✅✨ DONE
- 🔴 FAIL / BLOCKED
- 🟡 WAITING / REVIEW_REQUIRED

## Local runtime

Release manifest:
`runtime/release-manifest.json`

Default local port:
`43110`

Launcher:
`runtime/start-sentinel.bat`

The launcher and transport scripts are ASCII-safe.

## Data policy

GitHub stores source, sanitized manifests/data, schemas, checkpoints, evidence indexes, and docs.

Local runtime stores live state, active queues, locks, browser/session profiles, raw private evidence, cache, and secrets.

## Current verified milestones

- P1-P10: **SENTINEL_READY**
- P11-P14: **OPERATOR_READY_CORE**
- P15-P19: **OPERATOR_READY**
- P20-P24: **OPERATIONS_HARDENED**
- P25-P29: **LOCAL_OPERATOR_READY**
- Full regression: **59/59 PASS, 0 FAIL**

## Current tracked live finding

GSC public:
- `BROKEN_MEDIA_HINT`
- severity: medium
- count: 3
- HCDR issue: #1265
- state: OPEN / ROUTED / TRACKED

It is intentionally not auto-repaired because current policy requires operator-reviewed handling for medium findings.
