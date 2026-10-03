$ErrorActionPreference='Stop'
$root=Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
Set-Location $root
$runtime=Join-Path $root '.runtime\hcdr-mobile-remote-done'
New-Item -ItemType Directory -Force -Path $runtime | Out-Null
$summary=[ordered]@{
 schema='hcdr-mobile-remote-done/v1'
 started_at=(Get-Date).ToUniversalTime().ToString('o')
 state='RUNNING'
 steps=@()
}
function Step([string]$name,[scriptblock]$run){
 Write-Host "HC_MOBILE_REMOTE_STEP_START $name"
 & $run
 if($LASTEXITCODE -ne 0){throw "$name failed exit=$LASTEXITCODE"}
 $summary.steps += [ordered]@{name=$name;state='PASS';at=(Get-Date).ToUniversalTime().ToString('o')}
 $summary|ConvertTo-Json -Depth 8|Set-Content -Encoding UTF8 (Join-Path $runtime 'summary.json')
 Write-Host "HC_MOBILE_REMOTE_STEP_PASS $name"
}
try{
 Step 'source-sync' {
   git status --porcelain
   if($LASTEXITCODE -ne 0){exit $LASTEXITCODE}
   git pull --ff-only
 }
 Step 'always-on-contract' { npm run test:hcdr-always-on }
 Step 'relay-source-contract' { npm run test:hcdr-relay-contract }
 Step 'install-always-on' { call .\hcdr-install-always-on.bat }
 Step 'watchdog-health' { powershell -NoProfile -ExecutionPolicy Bypass -File .\tools\hcdr-relay\watchdog.ps1 }
 Step 'D02-live-proof' { call .\hc-done-02-hcdr-live.bat }
 Step 'D06-final-freeze' { call .\hc-done-06-final-freeze.bat }
 $summary.state='DONE'
 $summary.completed_at=(Get-Date).ToUniversalTime().ToString('o')
 $summary|ConvertTo-Json -Depth 8|Set-Content -Encoding UTF8 (Join-Path $runtime 'summary.json')
 Write-Host 'HCDR_MOBILE_REMOTE_DONE_PASS always_on=1 live_hcdr=1 final_freeze=1'
 exit 0
}catch{
 $summary.state='NOT_DONE'
 $summary.error=$_.Exception.Message
 $summary.failed_at=(Get-Date).ToUniversalTime().ToString('o')
 $summary|ConvertTo-Json -Depth 8|Set-Content -Encoding UTF8 (Join-Path $runtime 'summary.json')
 Write-Host ('HCDR_MOBILE_REMOTE_DONE_FAIL '+$_.Exception.Message)
 exit 20
}
