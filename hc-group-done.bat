@echo off
setlocal EnableExtensions
cd /d "%~dp0"
echo === HC GROUP DONE ^> DONE ===
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\hc-group-done.ps1" %*
exit /b %ERRORLEVEL%
