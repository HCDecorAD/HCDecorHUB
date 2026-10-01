@echo off
setlocal
cd /d D:\HCDecorHUB\repos\HCDecorHUB
call tools\hcdr-relay\ROOT-FIX-V12-CHECK.bat || exit /b 40
start "" /min cmd /c tools\hcdr-relay\HCDR-V12-Test.cmd
powershell -NoProfile -Command "Start-Sleep -Seconds 12"
powershell -NoProfile -Command "$p='D:\HCDecorHUB\HCDR Remote MCP\runtime\hcdr-v12-heartbeat.json';if(!(Test-Path $p)){exit 41};$j=Get-Content $p -Raw|ConvertFrom-Json;if(-not $j.ok){exit 42};Write-Host ('HCDR_V12_PASS pid='+$j.pid+' label='+$j.label)"
if errorlevel 1 exit /b %errorlevel%
echo HCDR_V12_RUNTIME_PASS
