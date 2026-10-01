@echo off
setlocal EnableExtensions
cd /d "%~dp0"
echo === HCDecor HUB Daily Gate ===
call hcdecor-foundation-full.bat
if errorlevel 1 goto FAIL
call hcdecor-release-ready.bat
if errorlevel 1 goto FAIL
powershell -NoProfile -ExecutionPolicy Bypass -File scripts\production-health-snapshot.ps1
if errorlevel 1 goto FAIL
echo.
echo HCDECOR_HUB_DAILY_PASS
exit /b 0
:FAIL
echo.
echo HCDECOR_HUB_DAILY_FAIL
exit /b 1
