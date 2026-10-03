# HC Sentinel — Autonomous Visual Operations

Local mirror: `D:\HCDecorHUB\HC_Sentinel`

Status: **DESKTOP_OPERATIONS_READY**
Latest completed scope: **P1 -> P39**
Full regression: **70/70 PASS, 0 FAIL**

## Purpose

HC Sentinel is the visual operations, verification, watch, alerting, evidence, policy, repair-routing, and desktop operations layer for the HC ecosystem.

Core flow:

`Command -> Observe -> Detect -> Compare -> Diagnose -> Route -> Repair -> Verify -> Evidence -> Alert -> DONE`

It is independent, local-first, command-driven, multi-project capable, and includes a live local app surface.

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
Local bootstrap, live UI/API binding, Evidence Index, Settings Store, Golden App Acceptance.

### P35-P39 — Desktop operations
- Windows local install/bootstrap script
- hidden launcher using VBS with window style 0
- Evidence Viewer in Command Center
- Finding Inbox in Command Center
- persisted Finding Store
- `GET /api/findings`
- HCDR issue-state mapping:
  - open + no comment -> ROUTED
  - open + comment -> ACKNOWLEDGED
  - closed -> RESOLVED
- tracked GSC issue #1265 remains visible and unresolved

Verified smoke:

`tracked=1 -> state=ROUTED -> issue=1265`

## Local app

Default port:
`43110`

Launchers:
- `runtime/start-sentinel.bat`
- `runtime/start-hidden.vbs`

Installer/bootstrap:
`runtime/install-local.ps1`

The hidden launcher starts Sentinel without showing a command window.

Local API:
- `GET /api/status`
- `POST /api/command`
- `GET /api/evidence`
- `GET /api/findings`
- `GET /api/settings`
- `POST /api/settings`

## UI

Theme:
- Dark: deep navy
- Light: gray-white
- preference persists locally

Live panels:
- Command Center
- Evidence Viewer
- Finding Inbox
- Workers
- tracked repair state

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
- P35-P39: **DESKTOP_OPERATIONS_READY**
- Full regression: **70/70 PASS, 0 FAIL**
- P35-P39 workflow: `37152351196`

## Current tracked live finding

GSC public:
- `BROKEN_MEDIA_HINT`
- severity: medium
- count: 3
- HCDR issue: #1265
- state: OPEN / ROUTED / TRACKED

Medium findings are not auto-repaired by default.
