@echo off
setlocal EnableExtensions
set "ROOT=D:\HCDecorHUB"
set "REPO=%ROOT%\repos\HCDecorHUB"
set "EVIDENCE=%ROOT%\evidence\hcdr-relay-recovery"
if not exist "%EVIDENCE%" mkdir "%EVIDENCE%"
echo [1/4] Checking relay agent source...
if not exist "%REPO%\tools\hcdr-relay\relay-agent.mjs" (echo BLOCKED: agent missing & exit /b 10)
where node >nul 2>&1 || (echo BLOCKED: node missing & exit /b 11)
node --check "%REPO%\tools\hcdr-relay\relay-agent.mjs" > "%EVIDENCE%\node-check.log" 2>&1
if errorlevel 1 (type "%EVIDENCE%\node-check.log" & exit /b 12)
echo [2/4] Invoking existing watchdog (no global service restart)...
powershell -NoProfile -File "%REPO%\tools\hcdr-relay\watchdog.ps1" > "%EVIDENCE%\watchdog.log" 2>&1
set "WATCHDOG_RC=%ERRORLEVEL%"
type "%EVIDENCE%\watchdog.log"
echo [3/4] Checking relay heartbeat freshness and PID...
powershell -NoProfile -Command "$p='D:\HCDecorHUB\runtime\hcdr-relay-heartbeat.json';if(!(Test-Path $p)){Write-Output 'FAIL_NO_HEARTBEAT';exit 21};try{$h=Get-Content $p -Raw|ConvertFrom-Json;$age=((Get-Date).ToUniversalTime()-([datetime]$h.at).ToUniversalTime()).TotalSeconds;$proc=Get-Process -Id ([int]$h.pid) -ErrorAction SilentlyContinue;Write-Output ('PID='+$h.pid+' AGE_SECONDS='+[int]$age+' POOL='+$h.worker_pool.max+' ACTIVE='+$h.worker_pool.active);if($h.ok -ne $true -or $age -gt 45 -or !$proc -or $proc.ProcessName -ne 'node'){exit 22}}catch{Write-Output 'FAIL_BAD_HEARTBEAT';exit 23}" > "%EVIDENCE%\heartbeat-check.log" 2>&1
set "HEARTBEAT_RC=%ERRORLEVEL%"
type "%EVIDENCE%\heartbeat-check.log"
echo [4/4] Results saved: %EVIDENCE%
if not "%WATCHDOG_RC%"=="0" (echo FAIL_WATCHDOG=%WATCHDOG_RC% & exit /b %WATCHDOG_RC%)
if not "%HEARTBEAT_RC%"=="0" (echo FAIL_HEARTBEAT=%HEARTBEAT_RC% & exit /b %HEARTBEAT_RC%)
echo PASS_LOCAL_RELAY_HEARTBEAT
echo NOTE: GITHUB_ROUNDTRIP_MUST_BE_VERIFIED_SEPARATELY
exit /b 0
