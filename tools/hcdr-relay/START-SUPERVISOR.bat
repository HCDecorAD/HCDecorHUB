@echo off
setlocal
set PS=D:\HCDecorHUB\repos\HCDecorHUB\tools\hcdr-relay\hcdr-supervisor.ps1
set HB=D:\HCDecorHUB\runtime\hcdr-supervisor\heartbeat.json
if not exist "%PS%" (echo SUPERVISOR_SCRIPT_MISSING&exit /b 55)
powershell -NoProfile -Command "$alive=$false;if(Test-Path '%HB%'){try{$j=Get-Content '%HB%' -Raw|ConvertFrom-Json;$x=Get-Process -Id $j.pid -ErrorAction SilentlyContinue;if($x){$alive=$true}}catch{}};if(-not $alive){Start-Process powershell.exe -ArgumentList '-NoProfile','-ExecutionPolicy','Bypass','-File','%PS%' -WindowStyle Hidden}"
if errorlevel 1 exit /b 56
powershell -NoProfile -Command "Start-Sleep -Seconds 5"
powershell -NoProfile -Command "$j=Get-Content '%HB%' -Raw|ConvertFrom-Json;$age=([DateTimeOffset]::UtcNow-[DateTimeOffset]::Parse($j.at)).TotalSeconds;if(-not $j.ok -or $age -gt 90){exit 58};$x=Get-Process -Id $j.pid -ErrorAction SilentlyContinue;if(-not $x){exit 59};Write-Host ('HCDR_SUPERVISOR_PASS pid='+$j.pid)"
exit /b %errorlevel%
