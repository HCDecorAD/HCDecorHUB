@echo off
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0repair-hcdr.ps1"
if errorlevel 1 exit /b 1
echo HCDR_REPAIR_PASS
