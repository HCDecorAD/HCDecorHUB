@echo off
setlocal
cd /d "%~dp0"
powershell -NoProfile -ExecutionPolicy Bypass -File scripts\done\hcdr-live-proof.ps1
exit /b %ERRORLEVEL%
