param(
  [int]$MaxRetry = 1,
  [switch]$SkipProductionVerify
)
$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
Set-Location $root
$runtime = Join-Path $root '.runtime\hc-group-done'
$logs = Join-Path $runtime 'logs'
New-Item -ItemType Directory -Force -Path $logs | Out-Null

$localFirstScript = Join-Path $root 'scripts\hc-local-first.ps1'
if(-not (Test-Path $localFirstScript)){ throw "HC Local-First runtime missing: $localFirstScript" }
powershell -NoProfile -ExecutionPolicy Bypass -File $localFirstScript -Mode setup
if($LASTEXITCODE -ne 0){ exit $LASTEXITCODE }

$lanes = @(
  @{ Name='core'; Commands=@('npm run test:architecture','npm run test:dynamic-port','npm run test:deployment-adapter','npm run test:policy-engine') },
  @{ Name='governor'; Commands=@('npm run test:governor','npm run test:durable-budget','npm run test:governor-durable','npm run test:done-durable','npm run test:worker-heartbeats') },
  @{ Name='evidence'; Commands=@('npm run test:evidence-envelope','npm run test:evidence-store','npm run test:operator-telemetry','npm run test:operator-dashboard') },
  @{ Name='registry'; Commands=@('npm run test:corporate-catalog','npm run test:capability-registry','npm run test:lifecycle-registry','npm run test:active-asset-coverage') },
  @{ Name='tools'; Commands=@('npm run test:hcdr-relay-contract','npm run test:autodebug-implementation','npm run test:autodebug-repair','npm run test:agent-control-contract','npm run test:autochat-contract') },
  @{ Name='portfolio'; Commands=@('npm run test:transwarp-acceptance','npm run test:portfolio-pilot','npm run test:moon-gate','npm run test:durable-adapter','npm run test:social-media','npm run test:mediaflow-contract','npm run test:video-downloader-contract','npm run test:design-ai-contract') }
)

function Invoke-Lane([hashtable]$lane,[int]$attempt){
  $name=$lane.Name
  $log=Join-Path $logs ("{0}.attempt{1}.log" -f $name,$attempt)
  $runner=Join-Path $runtime ("lane-{0}.attempt{1}.cmd" -f $name,$attempt)
  $codeFile=Join-Path $runtime ("lane-{0}.attempt{1}.exitcode" -f $name,$attempt)
  if(Test-Path $codeFile){ Remove-Item -Force $codeFile }

  $lines=@('@echo off','setlocal EnableExtensions',('cd /d "'+$root+'"'))
  foreach($command in $lane.Commands){
    $lines += ('call '+$command)
    $lines += 'if errorlevel 1 goto :failed'
  }
  $lines += ('> "'+$codeFile+'" echo 0')
  $lines += 'exit /b 0'
  $lines += ':failed'
  $lines += 'set RC=%ERRORLEVEL%'
  $lines += ('> "'+$codeFile+'" echo %RC%')
  $lines += 'exit /b %RC%'
  $lines | Set-Content -Encoding ASCII $runner

  $p=Start-Process -FilePath 'cmd.exe' -ArgumentList @('/d','/c', ('"'+$runner+'"')) -WorkingDirectory $root -RedirectStandardOutput $log -RedirectStandardError ($log+'.err') -NoNewWindow -PassThru
  return @{ Name=$name; Process=$p; Log=$log; Attempt=$attempt; Commands=$lane.Commands; CodeFile=$codeFile; Runner=$runner }
}

function Wait-Lanes($running){
  $results=@()
  foreach($r in $running){
    $r.Process.WaitForExit()
    if(-not (Test-Path $r.CodeFile)){
      Write-Host "HC_DONE_EXITCODE_SIDECAR_MISSING lane=$($r.Name) attempt=$($r.Attempt)"
      $code=901
    } else {
      $raw=(Get-Content $r.CodeFile -Raw).Trim()
      $parsed=0
      if(-not [int]::TryParse($raw,[ref]$parsed)){
        Write-Host "HC_DONE_EXITCODE_SIDECAR_INVALID lane=$($r.Name) attempt=$($r.Attempt) value=$raw"
        $code=902
      } else {
        $code=$parsed
      }
    }
    $results += [pscustomobject]@{lane=$r.Name;attempt=$r.Attempt;exit_code=[int]$code;log=$r.Log;commands=$r.Commands}
  }
  return $results
}

$all=@()
$running=@()
foreach($lane in $lanes){ $running += Invoke-Lane $lane 1 }
$results=Wait-Lanes $running
$all += $results

for($retry=1;$retry -le $MaxRetry;$retry++){
  $failed=$results | Where-Object {$_.exit_code -ne 0}
  if(-not $failed){ break }
  Write-Host "HC_DONE_AUTODEBUG retry=$retry failed=$($failed.lane -join ',')"
  cmd /d /s /c "npm run test:fix-memory && npm run test:autodebug-implementation && npm run test:autodebug-repair"
  $running=@()
  foreach($f in $failed){
    $lane=$lanes | Where-Object {$_.Name -eq $f.lane} | Select-Object -First 1
    $running += Invoke-Lane $lane ($retry+1)
  }
  $results=Wait-Lanes $running
  $all += $results
}

$failed=$results | Where-Object {$_.exit_code -ne 0}
$summary=[ordered]@{
  schema='hc-group-done/v1'
  checked_at=(Get-Date).ToUniversalTime().ToString('o')
  execution_mode='LOCAL_FIRST'
  compute_provider='HOCUONG_LAPTOP'
  data_root='D:\HC_DATA'
  github_sync='ASYNC_NON_BLOCKING'
  lanes=$all
  failed_lanes=@($failed | ForEach-Object {$_.lane})
  research_required=([bool]$failed)
  production_verify_skipped=[bool]$SkipProductionVerify
  final_state='VERIFYING'
}
$summary | ConvertTo-Json -Depth 8 | Set-Content -Encoding UTF8 (Join-Path $runtime 'summary.json')

if($failed){
  $request=[ordered]@{
    schema='hc-group-research-request/v1'
    created_at=$summary.checked_at
    failed_lanes=@($failed | ForEach-Object { @{lane=$_.lane;log=$_.log;commands=$_.commands} })
    instruction='Analyze exact failures using repository evidence first; if insufficient, use approved plugins/web research. Propose bounded fix, verify, then resume HC DONE.'
  }
  $request | ConvertTo-Json -Depth 8 | Set-Content -Encoding UTF8 (Join-Path $runtime 'research-request.json')
  Write-Host "HC_DONE_RESEARCH_REQUIRED file=.runtime\hc-group-done\research-request.json"
  exit 20
}

function Invoke-MasterE2E {
  $serverLog=Join-Path $logs 'master-server.log'
  $serverErr=Join-Path $logs 'master-server.err.log'
  $server=Start-Process -FilePath 'cmd.exe' -ArgumentList @('/d','/c','npm run start -- -p 3219') -WorkingDirectory $root -RedirectStandardOutput $serverLog -RedirectStandardError $serverErr -NoNewWindow -PassThru
  try{
    $ready=$false
    for($i=0;$i -lt 30;$i++){
      Start-Sleep -Seconds 1
      try{
        $r=Invoke-WebRequest -UseBasicParsing -Uri 'http://localhost:3219/api/health' -TimeoutSec 2
        if($r.StatusCode -ge 200 -and $r.StatusCode -lt 500){ $ready=$true; break }
      } catch {}
      if($server.HasExited){ break }
    }
    if(-not $ready){
      Write-Host 'HC_DONE_MASTER_SERVER_NOT_READY port=3219'
      return 903
    }
    $env:HCDECOR_E2E_BASE='http://localhost:3219'
    cmd /d /s /c "npm run test:master 2>&1" | ForEach-Object { Write-Host $_ }
    $masterExit=$LASTEXITCODE
    return [int]$masterExit
  }
  finally{
    Remove-Item Env:HCDECOR_E2E_BASE -ErrorAction SilentlyContinue
    if($server -and -not $server.HasExited){
      cmd /d /c "taskkill /PID $($server.Id) /T /F" | Out-Null
    }
  }
}

Write-Host 'HC_DONE_FINAL_GATE starting mode=LOCAL_FIRST'
$final=@()

$buildCmd='powershell -NoProfile -ExecutionPolicy Bypass -File scripts\hc-local-first.ps1 -Mode build'
$final += $buildCmd
cmd /d /s /c $buildCmd
if($LASTEXITCODE -ne 0){
  $summary.final_state='HARD_BLOCKED'
  $summary.final_gate_failure=$buildCmd
  $summary | ConvertTo-Json -Depth 8 | Set-Content -Encoding UTF8 (Join-Path $runtime 'summary.json')
  exit $LASTEXITCODE
}

$masterCmd='npm run test:master'
$final += $masterCmd
$masterCode=Invoke-MasterE2E
if($masterCode -ne 0){
  $summary.final_state='HARD_BLOCKED'
  $summary.final_gate_failure=$masterCmd
  $summary.master_server_port=3219
  $summary | ConvertTo-Json -Depth 8 | Set-Content -Encoding UTF8 (Join-Path $runtime 'summary.json')
  exit $masterCode
}

if(-not $SkipProductionVerify){
  $prodCmd='powershell -NoProfile -ExecutionPolicy Bypass -File scripts\production-smoke.ps1'
  $final += $prodCmd
  cmd /d /s /c $prodCmd
  if($LASTEXITCODE -ne 0){
    $summary.final_state='HARD_BLOCKED'
    $summary.final_gate_failure=$prodCmd
    $summary | ConvertTo-Json -Depth 8 | Set-Content -Encoding UTF8 (Join-Path $runtime 'summary.json')
    exit $LASTEXITCODE
  }
}

$summary.final_state='DONE'
$summary.final_gate=@($final)
$summary.local_build_state='PASS_DONE'
$summary.master_runtime='LOCAL_LOCALHOST_3219'
$summary.github_sync_required_for_done=$false
$summary | ConvertTo-Json -Depth 8 | Set-Content -Encoding UTF8 (Join-Path $runtime 'summary.json')
Write-Host 'HC_GROUP_DONE_PASS state=DONE mode=LOCAL_FIRST parallel_lanes=6 github_sync=NON_BLOCKING evidence=.runtime\hc-group-done\summary.json'
exit 0
