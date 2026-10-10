@echo off
powershell.exe -NoProfile -File "%~dp0INSTALL-TASK-REGISTRY-ROUTING.ps1"
if errorlevel 1 (echo INSTALL_FAILED - SEE REPORT&pause&exit /b 1)
echo STAGED_RESTART_REQUIRED - NO SERVICES RESTARTED
pause
