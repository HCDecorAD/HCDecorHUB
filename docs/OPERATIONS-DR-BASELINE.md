# HCDecor HUB — Operations / DR Baseline

## Current state
- Production health checks exist, but their local snapshot/history files are diagnostic and are not production authority.
- Runtime backups currently target the local HOCUONG filesystem and are therefore **development/operator backups**, not disaster-recovery authority.
- SHA-256 manifests provide integrity checking for those local backups.
- Production mutation remains fail-closed until durable state and authenticated authority are implemented.

## Current upgrade checkpoint
- Durable provider boundary: implemented in source, unbound by default, fail-closed.
- Durable provider contract is enforced by the Persistence Contract gate.
- No production provider credentials or mutation authority have been introduced.

## Required production evolution
1. Durable centralized audit/event store.
2. Durable run/workflow/approval state.
3. Off-device encrypted backup with retention policy.
4. Automated backup verification and periodic restore test.
5. Centralized health/latency/error history independent of the operator workstation.
6. Recovery point / recovery time objectives defined before production mutation is enabled.
7. Documented rollback procedure for Worker, data schema and frontend releases.

## Safety boundary
Never represent a local runtime directory or operator-generated snapshot as production backup authority. Never declare disaster recovery complete merely because a backup file exists; a restore test is required.

## Current operational flow
```
Production endpoints
      |
      +--> GitHub Production Verify (independent smoke)
      |
      +--> Operator health snapshot (diagnostic)
      |
      +--> Local backup + SHA256 verification (development/operator)
      |
      `--> Future: durable monitoring + off-device backup + tested restore
```
