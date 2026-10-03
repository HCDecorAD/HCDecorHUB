$ErrorActionPreference='Stop'
$root=Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
Set-Location $root
$runtime=Join-Path $root '.runtime\hcdr-mobile-remote-done'
New-Item -ItemType Directory -Force -Path $runtime | Out-Null
$summary=[ordered]@{
 schema='hcdr-mobile-remote-done/v2'
 started_at=(Get-Date).ToUniversalTime().ToString('o')
 state='RUNNING'
 cloud_lane=[ordered]@{state='RUNNING';steps=@()}
 local_lane=[ordered]@{state='PENDING';resource='HOCUONG';steps=@()}
}
function Save-Summary { $summary|ConvertTo-Json -Depth 10|Set-Content -Encoding UTF8 (Join-Path $runtime 'summary.json') }
function CloudStep([string]$name,[scriptblock]$run){
 Write-Host "HC_MOBILE_CLOUD_STEP_START $name"
 & $run
 if($LASTEXITCODE -ne 0){throw "$name failed exit=$LASTEXITCODE"}
 $summary.cloud_lane.steps += [ordered]@{name=$name;state='PASS';at=(Get-Date).ToUniversalTime().ToString('o')}
 Save-Summary
 Write-Host "HC_MOBILE_CLOUD_STEP_PASS $name"
}
function LocalStep([string]$name,[scriptblock]$run){
 Write-Host "HC_MOBILE_LOCAL_STEP_START $name"
 try{
   & $run
   if($LASTEXITCODE -ne 0){throw "$name failed exit=$LASTEXITCODE"}
   $summary.local_lane.steps += [ordered]@{name=$name;state='PASS';at=(Get-Date).ToUniversalTime().ToString('o')}
   Save-Summary
   Write-Host "HC_MOBILE_LOCAL_STEP_PASS $name"
   return $true
 }catch{
   $summary.local_lane.state='WAITING_RESOURCE'
   $summary.local_lane.reason=$_.Exception.Message
   $summary.local_lane.resume_policy='AUTO_RESUME_WHEN_RESOURCE_AVAILABLE'
   $summary.local_lane.steps += [ordered]@{name=$name;state='WAITING_RESOURCE';error=$_.Exception.Message;at=(Get-Date).ToUniversalTime().ToString('o')}
   Save-Summary
   Write-Host ("HC_MOBILE_LOCAL_YIELD $name "+$_.Exception.Message)
   return $false
 }
}
try{
 CloudStep 'source-sync' {
   git status --porcelain
   if($LASTEXITCODE -ne 0){exit $LASTEXITCODE}
   git pull --ff-only
 }
 CloudStep 'always-on-contract' { npm run test:hcdr-always-on }
 CloudStep 'relay-source-contract' { npm run test:hcdr-relay-contract }
 CloudStep 'cloud-orchestrator-contract' { node scripts\mobile-hc-done-cloud.test.mjs }
 CloudStep 'canonical-final-freeze' { powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\done\final-freeze-v2.ps1 }
 $summary.cloud_lane.state='DONE'
 Save-Summary

 $summary.local_lane.state='RUNNING'; Save-Summary
 if(-not (LocalStep 'install-always-on' { call .\hcdr-install-always-on.bat })){ throw 'LOCAL_YIELD' }
 if(-not (LocalStep 'watchdog-health' { powershell -NoProfile -ExecutionPolicy Bypass -File .\tools\hcdr-relay\watchdog.ps1 })){ throw 'LOCAL_YIELD' }
 if(-not (LocalStep 'D02-live-proof' { call .\hc-done-02-hcdr-live.bat })){ throw 'LOCAL_YIELD' }

 $summary.local_lane.state='DONE'
 $summary.state='DONE'
 $summary.completed_at=(Get-Date).ToUniversalTime().ToString('o')
 Save-Summary
 Write-Host 'HCDR_MOBILE_REMOTE_DONE_PASS cloud=1 local=1 auto_resume=1'
 exit 0
}catch{
 if($_.Exception.Message -eq 'LOCAL_YIELD'){
   $summary.state='CLOUD_DONE_LOCAL_WAITING'
   $summary.yielded_at=(Get-Date).ToUniversalTime().ToString('o')
   Save-Summary
   Write-Host 'HCDR_MOBILE_REMOTE_YIELD cloud=DONE local=WAITING_RESOURCE(HOCUONG)'
   exit 10
 }
 $summary.state='NOT_DONE'
 $summary.error=$_.Exception.Message
 $summary.failed_at=(Get-Date).ToUniversalTime().ToString('o')
 Save-Summary
 Write-Host ('HCDR_MOBILE_REMOTE_DONE_FAIL '+$_.Exception.Message)
 exit 20
}
