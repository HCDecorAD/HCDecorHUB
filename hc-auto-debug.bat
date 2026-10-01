@echo off
setlocal EnableExtensions
cd /d "%~dp0"
echo === HC Auto Debug Full Project ===
start "HUB Foundation" /b cmd /c call hcdecor-foundation-full.bat
start "HUB DR" /b cmd /c call hcdecor-dr.bat
start "HUB Health" /b cmd /c powershell -NoProfile -ExecutionPolicy Bypass -File scripts\production-health-snapshot.ps1
start "HUB Web" /b cmd /c powershell -NoProfile -ExecutionPolicy Bypass -File scripts\web-prod-smoke.ps1
start "HUB Commerce" /b cmd /c powershell -NoProfile -ExecutionPolicy Bypass -File scripts\commerce-readiness.ps1
echo HC_AUTO_DEBUG_STREAMS_STARTED
echo Final certification remains serialized through hcdecor candidate.
exit /b 0
