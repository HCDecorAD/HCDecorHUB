# HC GROUP GOVERNOR SCHEDULING LAW V2
Version: 2026-10-02
Status: ACTIVE POLICY

## Core
- READY + QUALIFIED_CAPACITY + SAFE -> DISPATCH.
- NOT_DONE + SAFE_TO_CONTINUE -> CONTINUE.
- WAITING_ONE_MISSION != WAITING_THE_COMPANY.
- BLOCKED_PROJECT -> CHECKPOINT -> SCHEDULE_NEXT_READY_WORK.
- Stage PASS -> evidence -> checkpoint -> unlock dependency-ready next stage.
- No global phase barrier when independent READY work exists.

## Worker contract
Each Worker declares capabilities, supported tools, concurrency limit and resource envelope.
Each Task declares required capabilities, dependencies, acceptance gate and resource needs.
A Worker owns assigned work until DONE, OWNER_REQUIRED, SAFETY_STOP or exhausted HARD_BLOCKED.
Workers report Mission ID, stage/task, state, evidence, checkpoint, blocker, resources, next action and ETA range.
Stale progress becomes SUSPECT; verify checkpoint and side effects before safe reassignment.

## Parallel portfolio
Independent Missions run concurrently subject to safety and capacity.
WAITING/BLOCKED work releases reusable capacity.
Free qualified capacity claims the highest-priority eligible READY work.
Scheduling age prevents starvation.
Critical/recovery lanes may reserve capacity so bulk work cannot starve them.

## Resource and bandwidth
CPU, RAM, disk, worker slots, network concurrency/link capacity, API quota/credits, HCDR queue capacity and deploy quota are schedulable resources.
Saturation causes throttling or WAITING_RESOURCE, not false FAILED.
Bulk download/build/research uses bounded concurrency.
One saturated resource must not idle unrelated READY work.

## Evidence and ownership graph
Every active Project/System/Tool/Resource/Mission has an accountable owner.
Relations: ownedBy, partOf, dependsOn, provides, consumes, blockedBy.
GoalID -> MissionID -> TaskID -> AttemptID is preserved through DONE, HCDR, AutoDebug, tests and release gates.
Evidence, not chat status, determines PASS/DONE.

## Required states
PLANNED, READY, CLAIMED, RUNNING, WAITING_DEPENDENCY, WAITING_RESOURCE, BLOCKED, VERIFYING, DONE, OWNER_REQUIRED, SAFETY_STOP, HARD_BLOCKED.

## Defects
CAPABILITY_BLIND_DISPATCH = DEFECT
UNBOUNDED_QUEUE_WITHOUT_BACKPRESSURE = DEFECT
ONE_BLOCKED_WORKER_STALLS_GROUP = DEFECT
GLOBAL_PHASE_BARRIER_WITH_READY_INDEPENDENT_WORK = DEFECT
ORPHAN_ACTIVE_ASSET = DEFECT
RESOURCE_WAIT_REPORTED_AS_FAILURE = DEFECT
WORKER_DONE_WITHOUT_EVIDENCE = DEFECT
FREE_SAFE_CAPACITY_WHILE_READY_WORK_EXISTS = DEFECT
BULK_WORK_STARVES_CRITICAL_LANE = DEFECT
READY_WORK_STARVES_FOREVER = DEFECT
USER_NUDGE_REQUIRED = DEFECT
CHAT_TURN_IS_THE_LOOP = DEFECT
