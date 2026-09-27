@echo off
setlocal EnableExtensions
title HCDecor HUB Sync v3
cd /d "%~dp0"
if not exist "%~dp0hcdecor-sync.ps1" (
 echo [ERROR] hcdecor-sync.ps1 must be in the same folder.
 pause
 exit /b 2
)
powershell.exe -NoLogo -NoProfile -ExecutionPolicy Bypass -File "%~dp0hcdecor-sync.ps1" %*
set "RC=%ERRORLEVEL%"
if not "%RC%"=="0" exit /b %RC%
echo.
echo Sync completed successfully.
pause
exit /b 0
