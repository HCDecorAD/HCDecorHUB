# HC GROUP — EXECUTIVE DELEGATION LAW

Version: 2026-10-02
Status: ACTIVE POLICY

## Target operating model
Owner sets business direction.
Group Governor owns portfolio outcomes.
L2 Operations Manager owns execution flow.
Workers own assigned missions.

The upper manager must not personally supervise every stage, worker, retry, or checkpoint when a subordinate manager can own it safely.

## Delegation chain
OWNER -> GROUP GOVERNOR -> L2 OPERATIONS MANAGER -> LANE MANAGERS -> WORKERS -> EVIDENCE

## DONE delegation
When the upper manager assigns a Goal or portfolio outcome:
1. L2 Manager decomposes it into Missions and Stages.
2. L2 Manager dispatches qualified Workers.
3. L2 Manager tracks checkpoints, evidence, resource pressure, blockers and recovery.
4. L2 Manager automatically advances dependency-ready work.
5. L2 Manager invokes AutoDebug and Research Escalation for unknown failures.
6. L2 Manager returns only a concise executive result:
   - 🟢 DONE / PASS
   - 🟡 OWNER_REQUIRED
   - 🔴 HARD_BLOCKED
   - SAFETY_STOP

## Executive rule
UPPER_MANAGER_SHOULD_MANAGE_OUTCOMES_NOT_MICROTASKS
SUBORDINATE_MANAGER_OWNS_EXECUTION
WORKER_STATUS_ROLLS_UP_TO_MANAGER
MANAGER_STATUS_ROLLS_UP_TO_GOVERNOR
NO_USER_NUDGE_REQUIRED
NO_CHAT_TURN_DEPENDENCY

## Defects
UPPER_MANAGER_MICROMANAGES_EVERY_STAGE = DEFECT
MANAGER_ESCALATES_ROUTINE_TECHNICAL_WORK = DEFECT
WORKER_REPORTS_DIRECTLY_TO_OWNER_WHEN_MANAGER_EXISTS = DEFECT
SUBORDINATE_MANAGER_WITHOUT_AUTHORITY_TO_CONTINUE = DEFECT
EXECUTIVE_IDLE_WHILE_READY_WORK_EXISTS = DEFECT

## Desired experience
The Owner should be able to say:
“DONE mục tiêu này.”

The Group Governor delegates execution to the L2 Manager.
The L2 Manager runs the portfolio until evidence-backed completion or a valid terminal escalation.
