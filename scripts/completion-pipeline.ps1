param([string]$Root=(Split-Path -Parent $PSScriptRoot))
$ErrorActionPreference='Stop'
Set-Location $Root
$run=Join-Path $Root ('.runtime\completion-'+(Get-Date -Format 'yyyyMMdd-HHmmss'))
New-Item -ItemType Directory -Force -Path $run|Out-Null
function Run([string]$n,[string]$cmd){$log=Join-Path $run ($n+'.log'); cmd /d /c $cmd *> $log; $rc=$LASTEXITCODE; Set-Content (Join-Path $run ($n+'.exit')) $rc; if($rc -ne 0){throw "$n failed ($rc)"}}
function StartCheck([string]$n,[string]$cmd){$out=Join-Path $run ($n+'.log');$err=Join-Path $run ($n+'.err'); Start-Process cmd -ArgumentList '/d','/c',$cmd -NoNewWindow -PassThru -RedirectStandardOutput $out -RedirectStandardError $err}
Run 'architecture' 'npm run test:architecture'
$jobs=@()
$jobs+=StartCheck 'persistence' 'node scripts\persistence-contract-check.mjs'
$jobs+=StartCheck 'commerce' 'node scripts\commerce-contract-check.mjs'
$jobs+=StartCheck 'health' 'powershell -NoProfile -ExecutionPolicy Bypass -File scripts\production-health-snapshot.ps1'
$jobs|Wait-Process
foreach($j in $jobs){if($j.ExitCode -ne 0){throw "parallel check failed pid=$($j.Id) rc=$($j.ExitCode)"}}
Run 'build' 'npm run build'
Run 'cf-build' 'npm run cf:build'
Run 'production-smoke' 'powershell -NoProfile -ExecutionPolicy Bypass -File scripts\production-smoke.ps1'
Write-Output "HCDECOR_COMPLETION_PASS $run"
