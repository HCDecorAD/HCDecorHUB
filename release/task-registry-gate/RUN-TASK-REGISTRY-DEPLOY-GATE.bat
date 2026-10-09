@echo off
setlocal
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0TASK-REGISTRY-DEPLOY-GATE.ps1"
exit /b %ERRORLEVEL%
