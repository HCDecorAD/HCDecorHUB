$ErrorActionPreference='Stop'
$root='D:\HCDecorHUB'
$h=Get-Content "$root\runtime\hcdr-relay-heartbeat.json" -Raw|ConvertFrom-Json
$stamp=[datetimeoffset]::Parse([string]$h.at)
$now=[datetimeoffset]::UtcNow
$age=[math]::Round(($now-$stamp).TotalSeconds)
$p=Get-CimInstance Win32_Process -Filter "ProcessId=$($h.pid)" -ErrorAction SilentlyContinue
$report=[ordered]@{heartbeat_at=$stamp.ToString('o');now_utc=$now.ToString('o');age_seconds=$age;pid=$h.pid;process_exists=($null -ne $p);active_workers=$h.worker_pool.active;skill_loaded=$h.skill_binding.loaded;clock_sane=([math]::Abs($age) -lt 120)}
$report|ConvertTo-Json|Set-Content "$root\evidence\imaster-skill-binding\relay-time-check.json" -Encoding UTF8
Write-Output ('HEARTBEAT_AGE_SECONDS='+$age)
Write-Output ('PROCESS_EXISTS='+($null -ne $p))
Write-Output ('ACTIVE_WORKERS='+$h.worker_pool.active)
Write-Output ('SKILL_LOADED='+$h.skill_binding.loaded)
Write-Output ('CLOCK_SANE='+$report.clock_sane)
