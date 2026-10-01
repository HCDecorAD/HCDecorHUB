$ErrorActionPreference='Stop'
$root='D:\HCDecorHUB'
$repo=Join-Path $root 'repos\HCDecorHUB'
$runtime=Join-Path $root 'runtime'
$heartbeat=Join-Path $runtime 'hcdr-relay-heartbeat.json'
$launcher=Join-Path $repo 'tools\hcdr-relay\HCDR-Remote-Free.cmd'
$backup=Join-Path $runtime ('hcdr-relay-reload-backup-'+(Get-Date -Format 'yyyyMMdd-HHmmss'))
New-Item -ItemType Directory -Force -Path $backup | Out-Null
Copy-Item -Force (Join-Path $repo 'tools\hcdr-relay\relay-agent.mjs') $backup
Push-Location $repo
try {
  git status --porcelain
  if ($LASTEXITCODE -ne 0) { throw 'git_status_failed' }
  git pull --ff-only
  if ($LASTEXITCODE -ne 0) { throw 'git_pull_failed' }
} finally { Pop-Location }
$oldPid=$null
if(Test-Path $heartbeat){
  try{$oldPid=(Get-Content $heartbeat -Raw|ConvertFrom-Json).pid}catch{}
}
if($oldPid){
  $p=Get-Process -Id $oldPid -ErrorAction SilentlyContinue
  if($p -and $p.ProcessName -eq 'node'){ Stop-Process -Id $oldPid -Force }
}
Start-Sleep -Seconds 2
Start-Process -FilePath 'cmd.exe' -ArgumentList '/c',('"' + $launcher + '"') -WindowStyle Hidden
Start-Sleep -Seconds 12
if(!(Test-Path $heartbeat)){throw 'heartbeat_missing_after_reload'}
$h=Get-Content $heartbeat -Raw|ConvertFrom-Json
if(-not $h.ok){throw 'heartbeat_not_ok_after_reload'}
Write-Output ('HCDR_RELAY_RELOADED pid='+$h.pid+' at='+$h.at)
