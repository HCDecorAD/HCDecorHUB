# HC GROUP — DONE > DONE RECURSIVE DELEGATION LAW

Version: 2026-10-02
Status: ACTIVE / MANDATORY

## Meaning
DONE > DONE means every parent DONE Goal may be decomposed into child DONE Missions.
Each child Mission must itself run under the full DONE protocol until terminal.

OWNER DONE
  -> GOVERNOR DONE
    -> L2 MANAGER DONE
      -> LANE DONE
        -> WORKER DONE
          -> EVIDENCE
        -> LANE FINAL GATE
      -> L2 FINAL GATE
    -> GOVERNOR FINAL GATE
  -> OWNER FINAL REPORT

## Roll-up rule
A parent cannot become DONE merely because work was delegated.
Parent DONE requires all required child DONE contracts to be terminal-success and the parent final gate to PASS.

## Continuation
CHILD_NOT_DONE + SAFE_TO_CONTINUE -> CONTINUE
CHILD_PASS -> CHECKPOINT -> NEXT CHILD/STAGE
CHILD_FAIL -> RECOVER/RESEARCH -> VERIFY -> RESUME
PARENT_WAITING_ON_ONE_CHILD != STOP_UNRELATED_CHILDREN

## Defects
DELEGATED != DONE
CHILD_PASS != PARENT_DONE
MANAGER_ACCEPTS_PARTIAL_DONE = DEFECT
PARENT_DONE_WITH_OPEN_CHILDREN = DEFECT
DONE_CHAIN_BREAKS_AT_MANAGEMENT_LAYER = DEFECT
