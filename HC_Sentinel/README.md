# HC Sentinel — Autonomous Visual Operations

Local mirror: `D:\HCDecorHUB\HC_Sentinel`

Status: **AUTONOMOUS_OPERATIONS_READY**
Latest completed scope: **P1 -> P54**
Full regression: **88/88 PASS, 0 FAIL**

## Purpose

HC Sentinel is the local-first visual operations, verification, watch, evidence, policy, repair-routing, desktop operations, self-recovery, and autonomous maintenance layer for the HC ecosystem.

Core flow:

`Command -> Observe -> Detect -> Compare -> Diagnose -> Route -> Repair -> Verify -> Evidence -> Alert -> DONE`

Autonomous operations flow:

`Heartbeat -> Watchdog -> Recovery -> Maintenance -> Retention -> Health Aggregate -> Release Gate`

## Capability milestones

- P1-P10: Sentinel foundation
- P11-P19: Operator runtime
- P20-P24: Operations hardening
- P25-P29: Daily operations runtime
- P30-P34: App-ready integration
- P35-P39: Desktop operations
- P40-P44: Daily-use integration
- P45-P49: Self-operating runtime
- P50-P54: Autonomous operations

## P50-P54 autonomous operations

### Watchdog Scheduled Task
`runtime/register-watchdog-task.ps1`

Registers the local watchdog in Windows Task Scheduler:
- hidden PowerShell execution
- every 2 minutes
- overlapping instances ignored
- start when available

### Maintenance Runner
`core/maintenance-runner.js`

Runs independent maintenance jobs and reports PASS/FAIL per job without crashing the entire maintenance cycle.

### Runtime Health Aggregate
`core/runtime-health.js`

Combines:
- heartbeat
- workers
- targets
- critical findings

States:
- HEALTHY
- DEGRADED
- STALE

### Release Freeze
`runtime/release-freeze.json`

Requires:
- green regression
- no unresolved critical findings
- watchdog
- backup
- retention

Runtime remains local-first and `productionDeploy=false`.

### Soak Acceptance
Simulated 24 operating cycles with one forced runtime outage.

Verified:
`cycles=24 -> restarts=1 -> maintenance=true -> finalHealth=HEALTHY -> recovered=true`

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
- Full regression: **88/88 PASS, 0 FAIL**
- P50-P54 workflow: `37164073803`

## Current tracked live finding

GSC public:
- `BROKEN_MEDIA_HINT`
- severity: medium
- count: 3
- HCDR issue: #1265
- state: OPEN / ROUTED / TRACKED

Medium findings are not auto-repaired by default.
