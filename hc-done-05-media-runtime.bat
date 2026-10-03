@echo off
setlocal
cd /d "%~dp0"
powershell -NoProfile -ExecutionPolicy Bypass -File scripts\done\media-runtime.ps1
exit /b %ERRORLEVEL%
