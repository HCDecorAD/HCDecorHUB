@echo off
setlocal
cd /d "%~dp0"
net session >nul 2>&1
if errorlevel 1 (echo Run as administrator & pause & exit /b 5)
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0meshcentral-check-fix-verify.ps1"
set "RC=%ERRORLEVEL%"
echo CHECKPOINT: %~dp0MESH-SERVICE-REPAIR-RESULT.json
echo EXIT: %RC%
pause
exit /b %RC%
