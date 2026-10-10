@echo off
powershell.exe -NoProfile -File "%~dp0TASK-REGISTRY-SAFE-RESTART.ps1"
if errorlevel 1 (echo RESTART_FAILED - SEE REPORT&pause&exit /b 1)
echo RESTART_PASS - REPORT READY
pause
