@echo off
setlocal
set HB=D:\HCDecorHUB\runtime\hcdr-relay-heartbeat.json
if not exist "%HB%" (echo HCDR_HEARTBEAT_MISSING&exit /b 2)
powershell.exe -NoProfile -Command "$j=Get-Content '%HB%' -Raw|ConvertFrom-Json;if(-not $j.ok){exit 3};$age=([DateTimeOffset]::UtcNow-[DateTimeOffset]::Parse($j.at)).TotalSeconds;if($age -gt 90){Write-Host ('HCDR_HEARTBEAT_STALE age='+[math]::Round($age));exit 4};$p=Get-Process -Id $j.pid -ErrorAction SilentlyContinue;if(-not $p -or $p.ProcessName -ne 'node'){Write-Host 'HCDR_PID_DEAD';exit 5};Write-Host ('HCDR_HEARTBEAT_FRESH pid='+$j.pid+' age='+[math]::Round($age))"
exit /b %errorlevel%
