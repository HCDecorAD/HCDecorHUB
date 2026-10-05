# HCDecor HARD RULE — NO COMPLEX INLINE POWERSHELL OVER TRANSPORT

Status: HARD_GLOBAL
Scope: iMaster, Zeus, Transport Mesh, HCDR, workers, automation

## Mandatory laws
1. NO_COMPLEX_INLINE_POWERSHELL_OVER_TRANSPORT.
2. If a command contains PowerShell variables ($...), JSON payloads, pipes, nested quotes, here-strings, multiple statements, or non-trivial escaping, DO NOT send it through JSON/HCDR/cmd as powershell -Command.
3. WRITE_SCRIPT_FILE_THEN_EXECUTE_FILE.
4. Preferred execution:
   powershell.exe -NoProfile -ExecutionPolicy Bypass -File <script.ps1>
5. Transport jobs SHOULD contain only a short launcher command plus simple arguments.
6. Script source belongs in the project/repository and is synced to HOCUONG before execution.
7. Never fix repeated quoting failures by stacking more escape characters when a script file can remove the shell boundary.
8. Secrets/tokens MUST NOT be embedded in scripts, job bodies, logs, or command strings. Read them from authorized environment/secret stores at runtime.
9. A transport quoting/parser failure is a ROUTING/ENCODING defect, not an application failure.
10. One failed inline command MUST trigger conversion to file-based execution before retry.

## Trigger
FILE_MODE_REQUIRED when any applies:
- command contains PowerShell variable syntax
- JSON body/object is constructed
- pipe/redirection is used
- nested quoting/escaping is required
- more than one meaningful statement is required
- command crosses 2+ interpreters/serialization boundaries

## Canonical route
Chat/iMaster -> Transport -> short launcher -> powershell -File script.ps1 -> result/evidence

## Acceptance
No DONE from an inline-complex transport command. Real acceptance must execute the file-based script and return its result.
