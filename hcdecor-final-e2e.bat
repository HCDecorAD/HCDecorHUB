@echo off
setlocal
cd /d "%~dp0"
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\final-e2e.ps1"
exit /b %ERRORLEVEL%
