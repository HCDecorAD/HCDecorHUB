param([string]$Root=(Split-Path -Parent $PSScriptRoot))
$ErrorActionPreference='Stop'; Set-Location $Root
$run=Join-Path $Root ('.runtime\foundation-'+(Get-Date -Format 'yyyyMMdd-HHmmss')); New-Item -ItemType Directory -Force $run|Out-Null
function Invoke-Check($n,$cmd){$o=Join-Path $run ($n+'.log');$e=Join-Path $run ($n+'.err');Start-Process cmd -ArgumentList '/d','/c',$cmd -NoNewWindow -PassThru -RedirectStandardOutput $o -RedirectStandardError $e}
$jobs=@(
 (Invoke-Check 'architecture' 'npm run test:architecture'),
 (Invoke-Check 'persistence' 'node scripts\persistence-contract-check.mjs'),
 (Invoke-Check 'commerce' 'node scripts\commerce-contract-check.mjs'),
 (Invoke-Check 'backup-integrity' 'powershell -NoProfile -ExecutionPolicy Bypass -File scripts\verify-latest-backup.ps1'),
 (Invoke-Check 'production-health' 'powershell -NoProfile -ExecutionPolicy Bypass -File scripts\production-health-snapshot.ps1')
)
$jobs|Wait-Process
$bad=@($jobs|Where-Object{$_.ExitCode -ne 0})
$summary=@{ok=($bad.Count -eq 0);run=$run;checks=$jobs.Count;failed=$bad.Count;checked_at=(Get-Date).ToUniversalTime().ToString('o')}
$summary|ConvertTo-Json|Tee-Object -FilePath (Join-Path $run 'summary.json')
if($bad.Count){exit 1}
