$ErrorActionPreference='Stop'
$root='D:\HCDecorHUB'
$h=Get-Content "$root\runtime\hcdr-relay-heartbeat.json" -Raw|ConvertFrom-Json
$pidValue=[int]$h.pid
$p=Get-CimInstance Win32_Process -Filter "ProcessId=$pidValue" -ErrorAction SilentlyContinue
$tasks=@(Get-ScheduledTask -ErrorAction SilentlyContinue|Where-Object {$_.TaskName -match 'HCDR|Relay|Supervisor'}|Select-Object TaskName,State,@{N='Actions';E={($_.Actions|ForEach-Object {$_.Execute+' '+$_.Arguments}) -join '; '}})
$services=@(Get-CimInstance Win32_Service|Where-Object {$_.Name -match 'HCDR|Relay'}|Select-Object Name,State,StartMode,PathName)
$age=[math]::Round(((Get-Date).ToUniversalTime() - [datetime]$h.at).TotalSeconds)
$report=[ordered]@{heartbeat_pid=$pidValue;heartbeat_age_seconds=$age;process_exists=($null -ne $p);process_command=if($p){$p.CommandLine}else{''};workers=$h.worker_pool;skill_binding=$h.skill_binding;tasks=$tasks;services=$services;at=(Get-Date).ToUniversalTime().ToString('o')}
$report|ConvertTo-Json -Depth 7|Set-Content "$root\evidence\imaster-skill-binding\relay-startup-inspect.json" -Encoding UTF8
Write-Output ('HEARTBEAT_AGE_SECONDS='+$age)
Write-Output ('PROCESS_EXISTS='+($null -ne $p))
Write-Output ('ACTIVE_WORKERS='+$h.worker_pool.active)
Write-Output ('SKILL_LOADED='+$h.skill_binding.loaded)
Write-Output ('TASKS='+$tasks.Count)
Write-Output ('SERVICES='+$services.Count)
Write-Output 'EVIDENCE=relay-startup-inspect.json'
