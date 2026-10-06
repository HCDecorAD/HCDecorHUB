# CONTINUATION-WATCH — NEVER ABANDON IN-FLIGHT WORK

Status: ACTIVE / HARD / GLOBAL
Priority: P1
Rule ID: CONTINUATION_WATCH
Version: 1.0
Scope: ALL EXECUTION / ASYNC / LONG-RUNNING / EXTERNAL STATE

## Principle
DISPATCH IS NOT COMPLETION. Starting, sending, queueing, spawning, clicking, submitting, syncing, building, deploying, downloading, uploading, rendering, testing, restarting, installing, publishing, or handing off work never proves the requested outcome completed.

## Trigger
Any requested operation can remain in progress after the initiating action returns, including background processes, jobs, queues, workflows, downloads/uploads, builds, deploys, syncs, renders, tests, services, restarts, browser/UI actions, worker handoffs, or remote commands.

## Mandatory continuation loop
1. DISPATCH the operation and capture the strongest available handle/evidence: PID, job/run ID, CID, task ID, process name, output path, URL, expected artifact/state, or equivalent.
2. Mark state RUNNING, not PASS/DONE.
3. WATCH using the cheapest authorized observation path: job status, process/session output, health endpoint, log tail, artifact/state check, window/UI state when materially required.
4. While work is legitimately RUNNING, continue polling/checking at a reasonable cadence without asking the Owner for NEXT.
5. If the observation lane fails, use another authorized read/status lane according to routing rules. Loss of one observer does not imply job failure.
6. On terminal success, VERIFY the requested outcome/artifact/state, then run the minimum meaningful acceptance required by the applicable mission rule.
7. If terminal failure occurs and a self-service repair path exists: diagnose -> fix/re-dispatch -> resume WATCH.
8. Emit PASS only for meaningful acceptance. If scope remains, continue automatically.
9. Emit DONE only when the full requested scope reaches terminal success and acceptance.
10. Emit BLOCKED only at a true Owner Boundary or after authorized self-service/fallback paths are exhausted.

## Window / UI observation
Checking a visible window is required only when the contract is genuinely UI/window dependent (popup, dialog, browser state, user-visible launch, etc.). Prefer machine-readable job/process/state evidence otherwise.

## Cadence
- Do not busy-loop.
- Use a cadence appropriate to expected duration and system cost.
- Prefer event/job completion APIs when available.
- For work that outlives the current interactive turn/session, hand continuation to an authorized persistent watcher/controller/automation when available, preserving the job handle and acceptance contract.
- Never pretend a normal chat turn will wake itself after it has ended.

## Reporting
Default intermediate reporting is compact and non-blocking:
RUNNING | <work> | <strongest status evidence>
Do not turn routine polling into Owner checkpoints.

## Relationship
- Complements FAST_DELIVERY: PASS is not STOP; continue until terminal state.
- Complements TEST_DONE: terminal success still requires real targeted acceptance.
- Obeys RULE_GUARD, RULE_BOOTSTRAP, WRITE_SAFETY_RDC and authorization/security boundaries.
- This is a GENERAL rule; it is not limited to sync/deploy/build.

## Acceptance
A representative long-running/async operation must prove:
DISPATCH -> RUNNING -> WATCH -> TERMINAL -> VERIFY -> meaningful PASS -> continue if needed -> DONE,
without an Owner NEXT between reversible/self-service steps.
