@echo off
setlocal EnableExtensions
cd /d "%~dp0"
echo === HCDecor DR Readiness ===
node scripts\dr-readiness-check.mjs
if errorlevel 1 goto FAIL
powershell -NoProfile -ExecutionPolicy Bypass -File scripts\verify-latest-backup.ps1
if errorlevel 1 goto FAIL
echo HCDECOR_DR_BASELINE_PASS_LOCKED
exit /b 0
:FAIL
echo HCDECOR_DR_BASELINE_FAIL
exit /b 1
