@echo off
setlocal
cd /d "%~dp0"
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0tools\hcdr-relay\install-always-on.ps1"
exit /b %ERRORLEVEL%
