param([int]$IntervalSeconds=30)
$ErrorActionPreference='Continue'
$root='D:\HCDecorHUB'
$repo=Join-Path $root 'repos\HCDecorHUB'
$runtime=Join-Path $root 'runtime'
$watchdog=Join-Path $repo 'tools\hcdr-relay\watchdog.ps1'
$lock=Join-Path $runtime 'hcdr-user-supervisor.lock'
$heartbeat=Join-Path $runtime 'hcdr-user-supervisor-heartbeat.json'
New-Item -ItemType Directory -Force -Path $runtime | Out-Null

if(Test-Path $lock){
  try{
    $oldPid=[int](Get-Content $lock -Raw).Trim()
    $p=Get-Process -Id $oldPid -ErrorAction SilentlyContinue
    if($p){Write-Output ('HCDR_USER_SUPERVISOR_ALREADY_RUNNING pid='+$oldPid);exit 0}
  }catch{}
  Remove-Item -Force $lock -ErrorAction SilentlyContinue
}
Set-Content -Path $lock -Value $PID -Encoding ASCII
try{
  while($true){
    try{
      & powershell.exe -NoProfile -ExecutionPolicy Bypass -File $watchdog | Out-Null
    }catch{}
    @{ok=$true;pid=$PID;at=(Get-Date).ToUniversalTime().ToString('o')}|ConvertTo-Json|Set-Content -Encoding UTF8 $heartbeat
    Start-Sleep -Seconds ([Math]::Max(10,$IntervalSeconds))
  }
}finally{
  Remove-Item -Force $lock -ErrorAction SilentlyContinue
}
