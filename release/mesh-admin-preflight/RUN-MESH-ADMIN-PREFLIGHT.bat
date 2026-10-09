@echo off
setlocal
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0MESH-ADMIN-PREFLIGHT.ps1"
exit /b %ERRORLEVEL%
