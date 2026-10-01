@echo off
setlocal EnableExtensions
cd /d "%~dp0"
echo === HCDecor Operator Matrix ===
start "Foundation" /b cmd /c call hcdecor-foundation-full.bat
start "DR" /b cmd /c call hcdecor-dr.bat
start "Web" /b cmd /c powershell -NoProfile -ExecutionPolicy Bypass -File scripts\web-prod-smoke.ps1
start "Commerce" /b cmd /c powershell -NoProfile -ExecutionPolicy Bypass -File scripts\commerce-readiness.ps1
start "Health" /b cmd /c powershell -NoProfile -ExecutionPolicy Bypass -File scripts\production-health-snapshot.ps1
echo Parallel operator checks started.
echo Use hcdecor candidate or hcdecor release only after all parallel checks finish.
exit /b 0
