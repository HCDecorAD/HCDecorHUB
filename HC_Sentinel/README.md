# HC Sentinel — Autonomous Visual Operations

Local mirror: `D:\HCDecorHUB\HC_Sentinel`

Status: **DAILY_USE_READY**
Latest completed scope: **P1 -> P44**
Full regression: **75/75 PASS, 0 FAIL**

## Purpose

HC Sentinel is the local-first visual operations, verification, watch, evidence, policy, repair-routing, and daily desktop operations layer for the HC ecosystem.

Core flow:

`Command -> Observe -> Detect -> Compare -> Diagnose -> Route -> Repair -> Verify -> Evidence -> Alert -> DONE`

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

## P40-P44 daily-use features

### Windows startup
`runtime/register-startup.ps1`

Registers HC Sentinel in the current user's Windows Startup folder and launches through `start-hidden.vbs`.

### Desktop shortcut
`runtime/create-desktop-shortcut.ps1`

Creates an HC Sentinel desktop shortcut opening:

`http://127.0.0.1:43110`

### Backup / restore
`core/state-backup.js`

Supports named local state snapshots and restore with traversal-safe backup names.

### Log Viewer
- persisted log store
- bounded history
- level filtering
- `GET /api/logs`
- Command Center Log Viewer
- command/startup logging

### Daily acceptance
Verified:

`ui=true -> SENTINEL_READY -> findings=1 -> evidence=1 -> logs=1`

## Local app

Default port:
`43110`

Launchers:
- `runtime/start-sentinel.bat`
- `runtime/start-hidden.vbs`

Install/bootstrap:
- `runtime/install-local.ps1`
- `runtime/register-startup.ps1`
- `runtime/create-desktop-shortcut.ps1`

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
- Full regression: **75/75 PASS, 0 FAIL**
- P40-P44 workflow: `37163324817`

## Current tracked live finding

GSC public:
- `BROKEN_MEDIA_HINT`
- severity: medium
- count: 3
- HCDR issue: #1265
- state: OPEN / ROUTED / TRACKED

Medium findings are not auto-repaired by default.
