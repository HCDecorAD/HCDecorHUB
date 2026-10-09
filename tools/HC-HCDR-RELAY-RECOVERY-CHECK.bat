@echo off
setlocal EnableExtensions
set "ROOT=D:\HCDecorHUB"
set "OUT=%ROOT%\evidence\hcdr-relay-recovery"
if not exist "%OUT%" mkdir "%OUT%"
echo [1] Service and scheduled tasks
powershell -NoProfile -Command "Get-CimInstance Win32_Service | Where-Object {$_.Name -match 'HCDR|Relay'} | Select-Object Name,State,PathName | Format-List" > "%OUT%\services.txt" 2>&1
powershell -NoProfile -Command "Get-ScheduledTask | Where-Object {$_.TaskName -match 'HCDecor|HCDR|Relay'} | Select-Object TaskName,State,@{N='Execute';E={$_.Actions.Execute -join '; '}},@{N='Arguments';E={$_.Actions.Arguments -join '; '}} | Format-List" > "%OUT%\tasks.txt" 2>&1
echo [2] Startup scripts
powershell -NoProfile -Command "$d='D:\HCDecorHUB\runtime\background\vbs'; 'hcdr-v3.vbs','hcdr-desktop-runtime.vbs' | ForEach-Object {$p=Join-Path $d $_; '=== '+$_; if(Test-Path $p){Get-Content $p}else{'NOT_FOUND'}}" > "%OUT%\startup-scripts.txt" 2>&1
echo [3] Relay worker candidates
powershell -NoProfile -Command "Get-CimInstance Win32_Process | Where-Object {$_.CommandLine -match 'relay|hcdr' -and $_.Name -match 'node|python|powershell|wscript|cscript'} | Select-Object ProcessId,Name,@{N='Command';E={$_.CommandLine -replace '(?i)(token|password|secret|key)=[^ ]+','$1=REDACTED'}} | Format-List" > "%OUT%\processes.txt" 2>&1
echo [4] Read-only evidence ready at %OUT%
echo No service restart, config changes or production writes performed.
exit /b 0
