@echo off
setlocal
set "REPO=%~dp0..\.."
echo [1/2] Checking public AMO site and catalog in parallel...
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0check-amo-public.ps1"
if errorlevel 1 (echo PUBLIC CHECK FAILED & exit /b 1)
echo [2/2] Running four local gates in parallel...
call "%REPO%\tools\hc-visual-builder-parallel-gates-20261009.bat" "D:\HCDecorHUB\HC_Visual_Builder"
if errorlevel 1 (echo LOCAL GATES FAILED & exit /b 2)
echo HTTP AND LOCAL GATES PASS. LIVE BINDING AND DEPLOYMENT STILL REQUIRED FOR PUBLIC.
exit /b 0
