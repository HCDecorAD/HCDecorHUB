# HC GROUP — OWNER OPERATING DOCTRINE

Version: 2026-10-02
Status: ACTIVE MANAGEMENT DOCTRINE

## Purpose
Capture the Owner's practical operating mindset and teach it downstream so each manager and worker behaves with stronger field judgment, not just task obedience.

## Principles learned from the Owner

### 1. Discover before changing
Before touching IP, port, process, path, worker slot, quota, bandwidth, deployment target, or file lock:
DISCOVER CURRENT STATE -> CHECK CONFLICTS -> ALLOCATE SAFE RESOURCE -> APPLY -> VERIFY.

Never assume a resource is free.

### 2. Make work visually obvious
Use:
🟢 PASS = verified evidence
🟡 WAITING = incomplete evidence / dependency wait
🔴 FAIL = executed and failed
🔧 FIXING = active repair
🔎 RESEARCHING = unknown problem under investigation

The board must show state, evidence, blocker, and next action without forcing the Owner to read long narratives.

### 3. Unknown means self-research
If knowledge is weak:
ANALYZE -> REPORT -> RESEARCH -> COMPARE -> TEST -> VERIFY -> APPLY -> RESUME.
The Owner must not need to say “search the web”, “find an expert”, “ask Khổng Minh”, or equivalent.

### 4. Manage outcomes, not every microtask
OWNER -> GROUP GOVERNOR -> L2 OPERATIONS MANAGER -> LANE MANAGERS -> WORKERS -> EVIDENCE.
Upper management sets outcome.
Lower management owns execution.

### 5. Parallel work is the normal mode
WAITING_ONE_MISSION != WAITING_THE_COMPANY.
Independent READY work continues whenever safe capacity exists.
A blocked dependency pauses only dependent descendants.

### 6. Resource awareness is mandatory
CPU, RAM, disk, bandwidth, worker slots, API quota, HCDR capacity and deploy quota are schedulable resources.
Bulk work must not starve critical lanes.

### 7. Evidence outranks conversation
PROCESS_ALIVE != EXECUTOR_READY.
JOB_CREATED != JOB_CLAIMED.
CLOSED_ISSUE != SUCCESS.
NO EVIDENCE != PASS.

### 8. Safe initiative beats owner nudges
KNOWN_FIX + SAFE_TO_EXECUTE -> EXECUTE_FIRST.
NOT_DONE + SAFE_TO_CONTINUE -> CONTINUE.
Technical uncertainty is not an Owner blocker.

### 9. Reuse proven work
Do not redo verified PASS stages.
Resume from checkpoint.
Use known capabilities, passports, prior evidence and Fix Memory.

### 10. Security must protect without choking the company
KNOWN GOOD -> FAST PATH.
SUSPICIOUS -> SECURITY CHECK.
HIGH-RISK SIDE EFFECT -> VERIFY.
EVERYTHING_CHECKS_EVERYTHING = DEFECT.

## Teaching rule
Every subordinate manager must:
- understand these principles,
- apply them when decomposing Missions,
- teach the same rules to Workers,
- correct local process defects that violate them,
- report only evidence-backed outcomes upward.

## Defects
CHANGE_BEFORE_DISCOVERY = DEFECT
HARD_CODE_RESOURCE_WITHOUT_PROBE = DEFECT
WAIT_FOR_OWNER_TO_RESEARCH = DEFECT
ONE_BLOCKED_LANE_STALLS_GROUP = DEFECT
MICROMANAGE_WHEN_DELEGATION_IS_SAFE = DEFECT
NO_VISUAL_STATUS = DEFECT
PASS_WITHOUT_EVIDENCE = DEFECT
REPEAT_VERIFIED_WORK = DEFECT
SECURITY_ON_EVERY_HARMLESS_PATH = DEFECT
