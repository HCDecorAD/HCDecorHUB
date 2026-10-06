# WRITE-SAFETY — WRITE VIA RDC + READ-BACK + REAL ACCEPTANCE

Status: ACTIVE / HARD / GLOBAL
Priority: P1
Rule ID: WRITE_SAFETY_RDC
Version: 1.0
Scope: HOCUONG WRITE / CODE / CONFIG / STARTUP / TASKS / SYSTEM MUTATION

## Trigger
Any operation will create, edit, replace, delete, rename, move, install, configure, register, enable/disable, or otherwise mutate files, code, Startup, Scheduled Tasks, services, registry, settings, or runtime state on HOCUONG.

## Mandatory action
1. HOCUONG WRITE MUST execute through RDC/Desktop Commander. Mesh/HCDR may inspect, orchestrate, health-check, or verify, but must not perform the WRITE.
2. Lock the target before WRITE; never permit concurrent writers.
3. Complex PowerShell/JSON/pipes/nested quoting/multi-command logic MUST be written to a script file and executed with powershell -File. Never stack escaping in an inline transport command.
4. After every generated/edited .cmd/.bat/.ps1/.vbs/.js/.mjs/config file: READ BACK the actual bytes/text from HOCUONG and verify syntax/line endings/escaping are what runtime expects.
5. A successful file write, command exit 0, build, or process existence is NOT PASS.
6. Run one REAL TARGETED ACCEPTANCE TEST through the same user-visible/runtime path the change is meant to support.
7. If acceptance fails: classify -> fix -> read-back -> retest. Do not call DONE while a self-service repair path remains.
8. Only after real acceptance PASS may the change be marked PASS/DONE and synced/versioned.
9. For Startup/autostart/task changes, acceptance includes validating the launched command/path and confirming it does not create unintended windows/prompts. When safe and practical, restart the specific task/process; full reboot is not required unless boot behavior itself is the only remaining acceptance.
10. Preserve a known-good/backup when modifying critical startup/runtime control files.

## Forbidden
- WRITE to HOCUONG through Mesh/HCDR/GitHub relay while RDC is available for the mutation.
- Complex inline PowerShell over transport.
- DONE from write success alone.
- Assuming generated CRLF/newlines/quotes are correct without read-back.
- Silently overriding a higher-priority safety/security/Owner boundary.

## Relationship / overrides
- RuleGuard remains P0 authority.
- This rule specializes HOCUONG mutation routing and OVERRIDES the WRITE-routing portions of DUAL_LANE and CONTINUE_AVAILABLE_TRANSPORT for HOCUONG WRITE.
- LOCAL_FIRST remains valid for reads/execution preference where no HOCUONG mutation occurs.
- NO_COMPLEX_INLINE_POWERSHELL and TestDONE remain mandatory and complementary.

## Acceptance
WRITE -> RDC -> READ-BACK -> REAL TARGETED TEST -> PASS -> VERSION/SYNC -> DONE.
