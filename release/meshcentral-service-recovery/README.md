# MeshCentral Service Recovery — candidate release

Status: **CANDIDATE / NOT PUBLIC VERIFIED**.

The BAT/PowerShell pair is for the owner's Windows computer HOCUONG only.
Run the BAT as Administrator from the same directory as the PS1.

Safety:
- Requires elevation.
- Finds exactly one MeshCentral service.
- Requires expected HCDecorHUB service binary path.
- Sets delayed automatic start, Windows Service Recovery restart after 60s on three failures, reset count after one day, failure actions on non-crash errors.
- Starts service only when stopped.
- Does not stop Gateway/Zeus, modify firewall, alter Mesh executor allowlist, or publish Windows command execution.
- Writes logs under D:\HCDecorHUB\TransportMesh\logs.

Validation:
1. Run BAT as administrator locally.
2. Confirm exit code 0 and service Running.
3. Confirm sc qfailure and qc output.
4. Check Windows Event Log for actual stop root cause.
5. Confirm stable Mesh Agent reconnection before public freeze.

Note: Windows Recovery doesn't guarantee recovery from all clean/manual service stops.
