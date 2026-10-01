@echo off
setlocal
cd /d %~dp0..
call scripts\52_AUTOCHAT_RC_PREFLIGHT.bat || exit /b 470
if not exist docs\AUTOCHAT-HOCUONG-LIVE.md exit /b 471
if not exist scripts\AUTOCHAT_LIVE_ACCEPTANCE.bat exit /b 472
if not exist scripts\AUTOCHAT_PUBLIC_GATE.bat exit /b 473
echo AUTOCHAT_HANDOFF_READY
echo Next live action: HCDR V1.2 must claim a job against the correct HOCUONG workspace.
exit /b 0
