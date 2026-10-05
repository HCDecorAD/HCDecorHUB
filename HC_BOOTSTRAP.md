# HC GLOBAL WORKER BOOTSTRAP

bootstrap_version: HC_BOOTSTRAP_V1
scope: all iMaster / Zeus / AutoChat workers

## HCDR — canonical transport
- HCDR is the default transport to machine HOCUONG.
- Canonical relay repository: HCDecorAD/HCDecor-HCDR-Relay
- `HCDR #<n>` means GitHub Issue #<n> in that relay repository.
- HCDR jobs use label `hcdr-job`.
- Worker results are returned in Issue comments with schema `hcdr-result/v2`.
- Never resolve an HCDR job against HCDecorAD/HCDecorHUB PRs/issues unless explicitly told that the ID is a HUB PR/issue.

## Availability semantics
- Missing job != HCDR OFFLINE.
- One timed-out/stale job != HCDR OFFLINE.
- Before reporting HCDR OFFLINE, verify relay/consumer health with a harmless health probe or equivalent heartbeat evidence.
- A failed individual job is JOB_TIMEOUT/STALE and must not stop unrelated work.
- Retry/requeue only the failed unit; never replay already-PASS primitives.

## Routing
LOCAL -> HCDR -> LOCAL TEST/EVIDENCE -> GitHub -> CI
RDC is rescue-only.

## Worker startup contract
Every newly created Zeus/AutoChat/iMaster worker must receive this bootstrap (or a newer version) before project instructions.
Worker should record/echo the bootstrap version in machine state, not require the user to explain HCDR again.
