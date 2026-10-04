# HC Sentinel — Autonomous Visual Operations

Local mirror: `D:\HCDecorHUB\HC_Sentinel`

Status: **SELF_OPERATING_READY**
Latest completed scope: **P1 -> P49**
Full regression: **81/81 PASS, 0 FAIL**

## Purpose

HC Sentinel is the local-first visual operations, verification, watch, evidence, policy, repair-routing, desktop operations, and self-recovery layer for the HC ecosystem.

Core flow:

`Command -> Observe -> Detect -> Compare -> Diagnose -> Route -> Repair -> Verify -> Evidence -> Alert -> DONE`

Self-operation flow:

`Heartbeat -> Health Check -> Watchdog -> Recovery -> Notify -> Retention`

## Runtime modes

- MANUAL_NOW
- COMMAND
- EVENT
- WATCH
- SCHEDULED
- AUTO

Manual/operator command always outranks schedule/watch.

## Capability milestones

- P1-P10: Sentinel foundation
- P11-P19: Operator runtime
- P20-P24: Operations hardening
- P25-P29: Daily operations runtime
- P30-P34: App-ready integration
- P35-P39: Desktop operations
- P40-P44: Daily-use integration
- P45-P49: Self-operating runtime

## P45-P49 self-operating features

### Heartbeat
`core/heartbeat.js`

Tracks local runtime heartbeat state:
- HEALTHY
- STALE
- UNKNOWN

### Watchdog
`runtime/watchdog.js`
`runtime/watchdog-loop.ps1`

Checks local runtime health, restarts through the hidden launcher only when needed, and enforces restart cooldown to avoid restart storms.

### Retention
`core/retention.js`

Supports bounded cleanup by:
- max file count
- max file age

Intended for raw evidence/log retention, not live state deletion.

### Local notification adapter
`core/local-notifier.js`

Delivers structured local notification events through a pluggable writer.

### Self-recovery acceptance
Verified:

`UNKNOWN -> RECOVERED -> HEALTHY -> notification=1`

## Local app

Default port:
`43110`

Launchers:
- `runtime/start-sentinel.bat`
- `runtime/start-hidden.vbs`

Windows operations:
- `runtime/install-local.ps1`
- `runtime/register-startup.ps1`
- `runtime/create-desktop-shortcut.ps1`
- `runtime/watchdog-loop.ps1`

Local API:
- `GET /api/status`
- `POST /api/command`
- `GET /api/evidence`
- `GET /api/findings`
- `GET /api/logs`
- `GET /api/settings`
- `POST /api/settings`

## UI

Theme:
- Dark: deep navy
- Light: gray-white

Live panels:
- Command Center
- Evidence Viewer
- Finding Inbox
- Log Viewer
- Workers
- tracked repair state

Display rule:
- 🔵 Pxx / Phase
- 🟢 PASS
- ✅✨ DONE
- 🔴 FAIL / BLOCKED
- 🟡 WAITING / REVIEW_REQUIRED

## Verified milestones

- P1-P10: **SENTINEL_READY**
- P11-P14: **OPERATOR_READY_CORE**
- P15-P19: **OPERATOR_READY**
- P20-P24: **OPERATIONS_HARDENED**
- P25-P29: **LOCAL_OPERATOR_READY**
- P30-P34: **APP_READY**
- P35-P39: **DESKTOP_OPERATIONS_READY**
- P40-P44: **DAILY_USE_READY**
- P45-P49: **SELF_OPERATING_READY**
- Full regression: **81/81 PASS, 0 FAIL**
- P45-P49 workflow: `37163753552`

## Current tracked live finding

GSC public:
- `BROKEN_MEDIA_HINT`
- severity: medium
- count: 3
- HCDR issue: #1265
- state: OPEN / ROUTED / TRACKED

Medium findings are not auto-repaired by default.
