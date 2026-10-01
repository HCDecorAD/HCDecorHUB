@echo off
setlocal
cd /d %~dp0..
netstat -ano | findstr "127.0.0.1:9222" | findstr "LISTENING" >nul
if errorlevel 1 (
 echo WATCHDOG_CDP_DOWN
 call scripts\03_ENSURE_MANAGED_EDGE.bat
 exit /b %errorlevel%
)
python src\core\cp1_probe.py > logs\watchdog-probe.log 2>&1
if errorlevel 1 (
 echo WATCHDOG_PROBE_FAIL
 exit /b 96
)
echo WATCHDOG_PASS
