@echo off
setlocal
set HB=D:\HCDecorHUB\runtime\hcdr-relay-heartbeat.json
if not exist "%HB%" (echo HCDR_HEARTBEAT_MISSING&exit /b 2)
powershell.exe -NoProfile -Command "$j=Get-Content '%HB%' -Raw|ConvertFrom-Json;if(-not $j.ok){exit 3};Write-Host ('HCDR_HEARTBEAT_OK pid='+$j.pid+' at='+$j.at)"
exit /b %errorlevel%
