@echo off
setlocal
cd /d "%~dp0"
powershell -NoProfile -ExecutionPolicy Bypass -File scripts\done\remaining-program.ps1
exit /b %ERRORLEVEL%
