param(
  [int]$StaleSeconds = 45
)
$ErrorActionPreference='Stop'
$root='D:\HCDecorHUB'
$repo=Join-Path $root 'repos\HCDecorHUB'
$runtime=Join-Path $root 'runtime'
$heartbeat=Join-Path $runtime 'hcdr-relay-heartbeat.json'
$launcher=Join-Path $repo 'tools\hcdr-relay\HCDR-Remote-Free.cmd'
New-Item -ItemType Directory -Force -Path $runtime | Out-Null

$healthy=$false
if(Test-Path $heartbeat){
  try{
    $h=Get-Content $heartbeat -Raw|ConvertFrom-Json
    $age=((Get-Date).ToUniversalTime() - ([datetime]$h.at).ToUniversalTime()).TotalSeconds
    $p=Get-Process -Id ([int]$h.pid) -ErrorAction SilentlyContinue
    $healthy=($h.ok -eq $true -and $age -le $StaleSeconds -and $p -and $p.ProcessName -eq 'node')
  }catch{$healthy=$false}
}
if($healthy){
  Write-Output 'HCDR_WATCHDOG_HEALTHY'
  exit 0
}

# A stale heartbeat means the relay loop is wedged. Restart only the relay
# process itself; any previously claimed job is quarantined by relay-agent on
# restart and is never blindly retried.
if(Test-Path $heartbeat){
  try{
    $old=Get-Content $heartbeat -Raw|ConvertFrom-Json
    $age=((Get-Date).ToUniversalTime() - ([datetime]$old.at).ToUniversalTime()).TotalSeconds
    if($age -gt $StaleSeconds -and $old.pid){
      $proc=Get-CimInstance Win32_Process -Filter ("ProcessId="+[int]$old.pid) -ErrorAction SilentlyContinue
      if($proc -and $proc.Name -eq 'node.exe' -and $proc.CommandLine -match 'relay-agent\.mjs'){
        Stop-Process -Id ([int]$old.pid) -Force -ErrorAction SilentlyContinue
        Start-Sleep -Seconds 2
        $jobs=''
        if($old.workers){$jobs=(@($old.workers|ForEach-Object{$_.job}) -join ',')}
        Write-Output ('HCDR_WATCHDOG_STALE_RELAY_STOPPED pid='+$old.pid+' active_jobs='+$jobs)
      }
    }
  }catch{}
}

# Remove stale lock only when recorded PID is absent.
$lock=Join-Path $runtime 'hcdr-relay.lock'
if(Test-Path $lock){
  try{
    $pidText=(Get-Content $lock -Raw).Trim()
    $p=Get-Process -Id ([int]$pidText) -ErrorAction SilentlyContinue
    if(-not $p){Remove-Item -Force $lock}
  }catch{Remove-Item -Force $lock -ErrorAction SilentlyContinue}
}
Start-Process -FilePath 'cmd.exe' -ArgumentList '/d','/s','/c',('"' + $launcher + '"') -WindowStyle Hidden
Start-Sleep -Seconds 12
if(!(Test-Path $heartbeat)){throw 'heartbeat_missing_after_watchdog_restart'}
$h2=Get-Content $heartbeat -Raw|ConvertFrom-Json
if(-not $h2.ok){throw 'heartbeat_not_ok_after_watchdog_restart'}
Write-Output ('HCDR_WATCHDOG_RESTARTED pid='+$h2.pid+' at='+$h2.at)
