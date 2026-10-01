param([string]$Root=(Split-Path -Parent $PSScriptRoot))
$ErrorActionPreference='Stop';Set-Location $Root
$run=Join-Path $Root ('.runtime\release-readiness-'+(Get-Date -Format 'yyyyMMdd-HHmmss'));New-Item -ItemType Directory -Force $run|Out-Null
$checks=@(
 @{n='foundation';c='call hcdecor-foundation-full.bat'},
 @{n='web-production';c='powershell -NoProfile -ExecutionPolicy Bypass -File scripts\web-prod-smoke.ps1'},
 @{n='wordpress-runtime';c='powershell -NoProfile -ExecutionPolicy Bypass -File scripts\verify-wordpress-runtime.ps1'},
 @{n='commerce-readiness';c='powershell -NoProfile -ExecutionPolicy Bypass -File scripts\commerce-readiness.ps1'}
)
$jobs=@();foreach($x in $checks){$jobs+=Start-Process cmd -ArgumentList '/d','/c',$x.c -PassThru -NoNewWindow -RedirectStandardOutput (Join-Path $run ($x.n+'.log')) -RedirectStandardError (Join-Path $run ($x.n+'.err'))}
$jobs|Wait-Process;$bad=@($jobs|?{$_.ExitCode -ne 0})
@{ok=($bad.Count-eq 0);checks=$checks.Count;failed=$bad.Count;production_mutation='locked';checked_at=(Get-Date).ToUniversalTime().ToString('o')}|ConvertTo-Json|Set-Content (Join-Path $run 'readiness.json')
if($bad.Count){exit 1};Write-Output "HCDECOR_RELEASE_READINESS_PASS $run"
