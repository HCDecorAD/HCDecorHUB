# HC Sentinel — Autonomous Visual Operations

Local mirror: `D:\HCDecorHUB\HC_Sentinel`

Status: **APP_READY**
Latest completed scope: **P1 -> P34**
Full regression: **63/63 PASS, 0 FAIL**

## Purpose

HC Sentinel is the visual operations, verification, watch, alerting, evidence, policy, and repair-routing layer for the HC ecosystem.

Core flow:

`Command -> Observe -> Detect -> Compare -> Diagnose -> Route -> Repair -> Verify -> Evidence -> Alert -> DONE`

It is independent, local-first, command-driven, multi-project capable, and now ships with a live local app surface.

## Runtime modes

- MANUAL_NOW
- COMMAND
- EVENT
- WATCH
- SCHEDULED
- AUTO

Manual/user command always has priority and must never be blocked by a schedule/watch loop.

## Verified capability groups

### P1-P10 — Sentinel foundation
Kernel, visual observer, visual diff, quality sensors, routing, verify-to-DONE, autonomy, observability, multi-project registry, golden run.

### P11-P19 — Operator runtime
Operator commands, project profiles, baseline manager, repair bridge, real targets, Command Center UI, dark/light theme, persistence, live HCDR transport, GSC/AMO public E2E.

### P20-P24 — Operations hardening
Finding lifecycle, mission ledger, policy engine, health supervisor, release gate.

### P25-P29 — Daily operations runtime
Notification center, governed baseline promotion, watch runtime, local command API, Windows launcher/local release package.

### P30-P34 — App-ready integration
- local bootstrap creates required runtime/data/evidence/log folders
- local server serves the actual Command Center UI
- UI is bound to live local APIs
- Evidence Index with project/type filtering
- Settings Store with safe allowlist
- API endpoints for status, command, evidence, settings
- Golden Acceptance validates UI + status + command + evidence + settings together

Golden Acceptance:

`UI=true -> SENTINEL_READY -> command=RUNNING -> evidence=1 -> theme=light`

## Local app

Default port:
`43110`

Launcher:
`runtime/start-sentinel.bat`

Local API:
- `GET /api/status`
- `POST /api/command`
- `GET /api/evidence`
- `GET /api/settings`
- `POST /api/settings`

Command Center is served from the same local server.

## UI

Theme:
- Dark: deep navy
- Light: gray-white
- preference persists locally

Display rule:
- 🔵 Pxx / Phase
- 🟢 PASS
- ✅✨ DONE
- 🔴 FAIL / BLOCKED
- 🟡 WAITING / REVIEW_REQUIRED

## Data policy

GitHub stores source, sanitized manifests/data, schemas, checkpoints, evidence indexes, and docs.

Local runtime stores live state, queues, locks, browser/session profiles, raw private evidence, cache, logs, and secrets.

## Verified milestones

- P1-P10: **SENTINEL_READY**
- P11-P14: **OPERATOR_READY_CORE**
- P15-P19: **OPERATOR_READY**
- P20-P24: **OPERATIONS_HARDENED**
- P25-P29: **LOCAL_OPERATOR_READY**
- P30-P34: **APP_READY**
- Full regression: **63/63 PASS, 0 FAIL**
- P30-P34 workflow: `37151628113`

## Current tracked live finding

GSC public:
- `BROKEN_MEDIA_HINT`
- severity: medium
- count: 3
- HCDR issue: #1265
- state: OPEN / ROUTED / TRACKED

Medium findings are not auto-repaired by default.
