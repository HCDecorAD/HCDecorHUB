# HC Sentinel — Autonomous Visual Operations

Local mirror: `D:\HCDecorHUB\HC_Sentinel`

Status: **DAILY_FINAL_READY**
Latest completed scope: **P1 -> P64**
Full regression: **102/102 PASS, 0 FAIL**

Core flow:
`Command -> Observe -> Detect -> Compare -> Diagnose -> Route -> Repair -> Verify -> Evidence -> Alert -> DONE`

Autonomous flow:
`Heartbeat -> Watchdog -> Recovery -> Maintenance -> Retention -> Health -> Audit -> Safe Update -> Rollback -> Integrity`

## Latest hardening P55-P64

- P55 Config Snapshot with secret/token/password redaction
- P56 Alert Dedupe with time window suppression
- P57 Incident Timeline persistence
- P58 Offline Queue with retry and remaining-item preservation
- P59 Operator/System Audit Ledger
- P60 Safe Update Policy requiring green tests and zero critical findings
- P61 Rollback Marker for version + commit
- P62 Resource Budget with CPU/memory/queue throttle decision
- P63 Release SHA256 Integrity Digest
- P64 Golden Final Acceptance

P64 acceptance:
`alerts=[true,false,true] -> update=READY -> budget=OK -> integrity=true`

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
- P50-P54: **AUTONOMOUS_OPERATIONS_READY**
- P55-P64: **FINAL_OPERATIONS_READY**

Full regression: **97/97 PASS, 0 FAIL**
Workflow: `37164523539`

## LOCAL FINAL verified on HOCUONG

Canonical root:
`D:\HCDecorHUB\HC_Sentinel`

Verified:
- local install/bootstrap: PASS
- Startup shortcut: PASS
- Desktop shortcut: PASS
- watchdog scheduled task: PASS
- watchdog recovery simulation: PASS
- Startup-link recovery simulation: PASS
- local API: `SENTINEL_READY v1.2.0`
- real AMO mobile mission: `DONE`
- real GSC mobile mission: `ROUTED`
- local regression: `99/99 PASS — 0 FAIL`
- GitHub LOCAL FINAL workflow: `37165027922`

The first HOCUONG install exposed a Windows ScheduledTasks compatibility issue. LOCAL FINAL replaced trigger-property mutation with compatible `schtasks.exe /SC MINUTE /MO 2` registration.

The previous runtime command stub was also replaced with the live controller:
`Command -> Resolve Target -> Playwright Capture -> Inspect -> Evidence -> DONE / ROUTED`

A physical reboot was not forced during the active remote session; Startup and watchdog recovery were both exercised directly.

## GSC known media placeholder

GSC public currently contains 3 intentional media placeholders.

Operator decision:
- status: `PASS / NO_CHANGE`
- HCDR issue: `#1265` closed
- finding kind: `BROKEN_MEDIA_HINT`
- count: `3`
- handling: suppressed for `gsc-public`
- reason: media links will be configured later

Current target policy:

`suppressFindings: ["BROKEN_MEDIA_HINT"]`

When GSC media links are configured, remove this suppression and run Sentinel verification again.

Verified on HOCUONG:
- GSC mobile mission: `DONE`
- active findings: `0`
- suppressed findings: `BROKEN_MEDIA_HINT ×3`
- failed requests: `0`
- regression: `100/100 PASS — 0 FAIL`


## DAILY FINAL — Real controls

Verified on HOCUONG:
- Run Now: **REAL / DONE**
- Compare: **REAL / SAME or CHANGED**
- Verify: **REAL / VERIFIED or ROUTED**
- Route Repair: **REAL / persisted repair queue**
- Add Project: **REAL / persistent live target**
- Tray Notification: **REAL / Windows NotifyIcon singleton**

Golden smoke completed all six controls. Temporary smoke project/finding state was restored after verification.

Gate: **DAILY_FINAL_READY**
