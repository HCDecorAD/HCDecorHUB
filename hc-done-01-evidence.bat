@echo off
setlocal
cd /d "%~dp0"
node scripts\durable-evidence-store.test.mjs && node scripts\operator-telemetry.test.mjs && node scripts\operator-dashboard.test.mjs
if errorlevel 1 exit /b 1
echo HC_DONE_D01_PASS evidence_durability=1
