@echo off
setlocal
cd /d "%~dp0"
call hcdr-install-always-on.bat
if errorlevel 1 exit /b %ERRORLEVEL%
powershell -NoProfile -ExecutionPolicy Bypass -File scripts\done\hcdr-live-proof.ps1
exit /b %ERRORLEVEL%
