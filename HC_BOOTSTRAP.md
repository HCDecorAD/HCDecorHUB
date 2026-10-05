# HC GLOBAL WORKER BOOTSTRAP

bootstrap_version: HC_BOOTSTRAP_V2_LOCAL_FIRST
scope: all iMaster / Zeus / AutoChat workers

## Authority
- iMaster owns the global operating rules.
- Zeus is the 24/7 control plane under iMaster and MUST inherit iMaster rules. Zeus must not define a conflicting transport order.
- AutoChat and every worker inherit the same routing policy.

## HARD LOCAL-FIRST
For any action targeting machine HOCUONG, always choose the shortest healthy execution path:
1. LOCAL DIRECT / existing local capability.
2. LOCAL EXECUTOR / Zeus Bridge / Extension / local filesystem/process/app control as appropriate.
3. HCDR only when the required local action cannot be reached directly from the active controller, or for transport/recovery.
4. Other fallbacks only when necessary.
5. RDC is rescue-only and last resort.

Hard rule: NEVER USE HCDR FOR A LOCAL ACTION WHEN A HEALTHY LOCAL EXECUTOR IS AVAILABLE.
Hard rule: Do not route a local filesystem/process/Edge action through GitHub merely because HCDR exists.

## HCDR — canonical fallback transport
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
LOCAL DIRECT -> LOCAL EXECUTOR -> HCDR -> LOCAL TEST/EVIDENCE -> GitHub -> CI
RDC = rescue-only.

## Zeus continuity
- SCAN -> CLASSIFY -> DISPATCH -> JUMP -> SCAN.
- BUSY worker: skip temporarily; do not block the fleet.
- IDLE + unfinished mission: resume from checkpoint.
- DONE: do not replay.
- Before controller exit, checkpoint and hand off any remaining runnable mission.
- NO IDLE WHILE RUNNABLE.
- NO DONE WHILE HANDOFF EXISTS.

## Worker startup contract
Every newly created Zeus/AutoChat/iMaster worker must receive this bootstrap (or a newer version) before project instructions.
Worker should record/echo the bootstrap version in machine state, not require the user to explain LOCAL-FIRST or HCDR again.
