@echo off
setlocal
cd /d "%~dp0"
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\foundation-parallel.ps1"
exit /b %ERRORLEVEL%
