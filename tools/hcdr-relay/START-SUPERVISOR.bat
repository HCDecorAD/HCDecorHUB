@echo off
setlocal
set PS=D:\HCDecorHUB\repos\HCDecorHUB\tools\hcdr-relay\hcdr-supervisor.ps1
if not exist "%PS%" (echo SUPERVISOR_SCRIPT_MISSING&exit /b 55)
powershell -NoProfile -Command "$x=Get-CimInstance Win32_Process -Filter \"Name='powershell.exe'\" -ErrorAction SilentlyContinue|?{$_.CommandLine -match 'hcdr-supervisor\\.ps1\"?};if($x){exit 0};Start-Process powershell.exe -ArgumentList '-NoProfile','-ExecutionPolicy','Bypass','-File','%PS%' -WindowStyle Hidden"
if errorlevel 1 exit /b 56
powershell -NoProfile -Command "Start-Sleep -Seconds 5"
powershell -NoProfile -Command "$p='D:\HCDecorHUB\runtime\hcdr-supervisor\heartbeat.json';if(!(Test-Path $p)){exit 57};$j=Get-Content $p -Raw|ConvertFrom-Json;if(-not $j.ok){exit 58};Write-Host ('HCDR_SUPERVISOR_PASS pid='+$j.pid)"
exit /b %errorlevel%
};if($x){exit 0};Start-Process powershell.exe -ArgumentList '-NoProfile','-ExecutionPolicy','Bypass','-File','%PS%' -WindowStyle Hidden"
if errorlevel 1 exit /b 56
powershell -NoProfile -Command "Start-Sleep -Seconds 5"
powershell -NoProfile -Command "$p='D:\HCDecorHUB\runtime\hcdr-supervisor\heartbeat.json';if(!(Test-Path $p)){exit 57};$j=Get-Content $p -Raw|ConvertFrom-Json;if(-not $j.ok){exit 58};Write-Host ('HCDR_SUPERVISOR_PASS pid='+$j.pid)"
exit /b %errorlevel%
