@echo off
setlocal
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0MESH-REGISTRY-EXECUTOR-AUDIT.ps1"
exit /b %ERRORLEVEL%
