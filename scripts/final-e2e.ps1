param([string]$Root=(Split-Path -Parent $PSScriptRoot))
$ErrorActionPreference='Stop';Set-Location $Root
$run=Join-Path $Root ('.runtime\final-'+(Get-Date -Format 'yyyyMMdd-HHmmss'));New-Item -ItemType Directory -Force $run|Out-Null
function R($n,$cmd){$log=Join-Path $run ($n+'.log');cmd /d /c $cmd *> $log;if($LASTEXITCODE -ne 0){Get-Content $log;throw "$n failed"}}
R 'foundation-contract' 'node scripts\foundation-contract-check.mjs'
R 'architecture' 'npm run test:architecture'
R 'persistence' 'node scripts\persistence-contract-check.mjs'
R 'commerce' 'node scripts\commerce-contract-check.mjs'
R 'build' 'npm run build'
R 'master-e2e' 'set HC_ALLOW_LOCAL_E2E=true&& npm run test:master'
R 'cf-build' 'npm run cf:build'
R 'production-smoke' 'powershell -NoProfile -ExecutionPolicy Bypass -File scripts\production-smoke.ps1'
R 'production-health' 'powershell -NoProfile -ExecutionPolicy Bypass -File scripts\production-health-snapshot.ps1'
@{ok=$true;run=$run;production_mutation='locked';checked_at=(Get-Date).ToUniversalTime().ToString('o')}|ConvertTo-Json|Set-Content (Join-Path $run 'FINAL-PASS.json')
Write-Output "HCDECOR_FINAL_E2E_PASS $run"
