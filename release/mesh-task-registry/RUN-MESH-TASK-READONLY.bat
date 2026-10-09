@echo off
powershell.exe -NoProfile -File "%~dp0MESH-TASK-READONLY.ps1" -Task meshcentral_status
if errorlevel 1 exit /b 1
powershell.exe -NoProfile -File "%~dp0MESH-TASK-READONLY.ps1" -Task meshcentral_logs
exit /b %ERRORLEVEL%
