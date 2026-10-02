# HC GROUP — ACTIVE FOLLOW-THROUGH LAW

Version: 2026-10-02
Status: ACTIVE POLICY

## Core
Dispatching work is not a stopping point.

After assigning work, the responsible manager must keep the execution loop active:
DISPATCH -> POLL/OBSERVE -> VERIFY -> CHECKPOINT -> ADVANCE NEXT READY WORK.

The manager may yield only when one of these is true:
- WAITING on real external delay,
- resource/load pressure requires throttling,
- OWNER_REQUIRED,
- SAFETY_STOP,
- exhausted HARD_BLOCKED.

## Required behavior
TASK_ASSIGNED != WORK_FINISHED
DISPATCH_THEN_STOP = DEFECT
READY_RESULT + SAFE_NEXT_STEP -> EXECUTE_NEXT
MULTIPLE_READY_RESULTS -> ADVANCE IN PARALLEL
NO_USER_NUDGE_REQUIRED

## Manager cadence
- Poll short-running jobs until result or meaningful delay.
- When result arrives, classify immediately.
- PASS -> checkpoint and advance.
- FAIL -> diagnose/research/fix.
- WAITING -> release reusable capacity and run unrelated READY work.
