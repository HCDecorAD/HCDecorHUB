# HC GROUP — DONE PROTOCOL ENFORCEMENT

Version: 2026-10-02
Status: ACTIVE / MANDATORY

## Scope
HC DONE applies to every Goal, Mission, Lane, Manager, Worker and recovery path in HC Group.

## Core contract
GOAL -> PLAN -> APPROVE ONCE -> EXECUTE -> VERIFY -> CHECKPOINT -> NEXT READY STAGE -> FINAL GATE -> DONE -> REPORT

A chat turn, dispatch event, issue creation, worker claim, or intermediate PASS is never terminal by itself.

## Mandatory continuation
NOT_DONE + SAFE_TO_CONTINUE -> CONTINUE
STAGE_PASS -> EVIDENCE -> CHECKPOINT -> NEXT_READY_STAGE
STAGE_FAIL -> AUTODEBUG/RESEARCH -> BOUNDED_RETRY_OR_STRATEGY_CHANGE -> VERIFY -> RESUME
WAITING_ONE_MISSION != WAITING_THE_COMPANY

## Valid terminal states
DONE
OWNER_REQUIRED
SAFETY_STOP
HARD_BLOCKED after valid recovery budget is exhausted

## Delegation
Group Governor may delegate to L2/Lane Managers, but Goal ownership persists until a valid terminal state.
Delegation never weakens DONE semantics.

## DONE requires
- all required stages complete
- acceptance gates pass
- evidence references exist
- checkpoints are durable
- unresolved blockers are zero
- final report is generated

## Defects
DISPATCH_AND_STOP = DEFECT
PASS_ONE_STAGE_AND_STOP = DEFECT
WAIT_FOR_OWNER_NUDGE = DEFECT
CHAT_TURN_IS_EXECUTION_LOOP = DEFECT
MANAGER_RETURNS_OWNERSHIP_EARLY = DEFECT
DONE_WITHOUT_FINAL_GATE = DEFECT
DONE_WITHOUT_EVIDENCE = DEFECT
