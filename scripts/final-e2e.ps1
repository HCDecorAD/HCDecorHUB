param([string]$Root=(Split-Path -Parent $PSScriptRoot))
$ErrorActionPreference='Stop';Set-Location $Root
$run=Join-Path $Root ('.runtime\final-'+(Get-Date -Format 'yyyyMMdd-HHmmss'));New-Item -ItemType Directory -Force $run|Out-Null
function RunGate($n,$cmd){
  $log=Join-Path $run ($n+'.log')
  # Let cmd.exe own stderr redirection so PowerShell 5 does not promote harmless
  # native stderr (for example OpenNext Windows warnings) into NativeCommandError.
  $wrapped=$cmd + ' > "' + $log + '" 2>&1'
  cmd /d /c $wrapped
  $rc=$LASTEXITCODE
  if($rc -ne 0){Get-Content $log;throw "$n failed exit=$rc"}
}
RunGate 'foundation-contract' 'node scripts\foundation-contract-check.mjs'
RunGate 'architecture' 'npm run test:architecture'
RunGate 'persistence' 'node scripts\persistence-contract-check.mjs'
RunGate 'commerce' 'node scripts\commerce-contract-check.mjs'
RunGate 'dynamic-port' 'npm run test:dynamic-port'
RunGate 'governor-resource-scheduler' 'npm run test:governor'
RunGate 'build' 'npm run build'

$portRaw=& node scripts\allocate-test-port.mjs 3219 3299
if($LASTEXITCODE -ne 0){throw 'dynamic port allocation failed'}
$port=[int]($portRaw|Out-String).Trim()
$base="http://localhost:$port"
$env:HCDECOR_E2E_BASE=$base
$env:HC_ALLOW_LOCAL_E2E='true'
$serverOut=Join-Path $run 'local-server.log'
$serverErr=Join-Path $run 'local-server.err.log'
$server=Start-Process -FilePath 'cmd.exe' -ArgumentList @('/d','/c',"npm run start -- -p $port") -WorkingDirectory $Root -RedirectStandardOutput $serverOut -RedirectStandardError $serverErr -PassThru
@{requested_range='3219-3299';selected_port=$port;base=$base;server_pid=$server.Id;ownership='spawned-by-final-e2e'}|ConvertTo-Json|Set-Content (Join-Path $run 'DYNAMIC-PORT.json')
try{
  $ready=$false
  for($i=0;$i -lt 60;$i++){
    if($server.HasExited){break}
    try{Invoke-WebRequest -UseBasicParsing -Uri ($base+'/api/runtime') -TimeoutSec 2|Out-Null;$ready=$true;break}catch{Start-Sleep -Milliseconds 500}
  }
  if(-not $ready){
    if(Test-Path $serverErr){Get-Content $serverErr}
    throw "local server readiness failed on port $port"
  }
  RunGate 'master-e2e' 'set HC_ALLOW_LOCAL_E2E=true&& npm run test:master'
} finally {
  if($server -and -not $server.HasExited){cmd /d /c "taskkill /PID $($server.Id) /T /F" *> $null}
}
RunGate 'cf-build' 'npm run cf:build'
RunGate 'production-smoke' 'powershell -NoProfile -ExecutionPolicy Bypass -File scripts\production-smoke.ps1'
RunGate 'production-health' 'powershell -NoProfile -ExecutionPolicy Bypass -File scripts\production-health-snapshot.ps1'
@{ok=$true;run=$run;production_mutation='locked';dynamic_port=$port;checked_at=(Get-Date).ToUniversalTime().ToString('o')}|ConvertTo-Json|Set-Content (Join-Path $run 'FINAL-PASS.json')
Write-Output "HCDECOR_FINAL_E2E_PASS $run port=$port"
