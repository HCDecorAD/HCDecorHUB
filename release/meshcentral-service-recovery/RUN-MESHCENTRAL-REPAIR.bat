@echo off
setlocal
cd /d "%~dp0"
net session >nul 2>&1
if errorlevel 1 (echo Run as administrator & pause & exit /b 5)
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0meshcentral-service-repair.ps1"
set "RC=%ERRORLEVEL%"
echo Result code: %RC%
echo Logs: D:\HCDecorHUB\TransportMesh\logs
pause
exit /b %RC%
